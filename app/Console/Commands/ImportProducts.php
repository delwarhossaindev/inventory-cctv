<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ZipArchive;

class ImportProducts extends Command
{
    protected $signature = 'app:import-products {file=public/products.xlsx}';

    protected $description = 'Import products and categories from the products.xlsx file';

    public function handle(): int
    {
        $path = base_path($this->argument('file'));
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $rows = $this->readXlsx($path);
        if (count($rows) < 2) {
            $this->error('No data rows found.');

            return self::FAILURE;
        }

        array_shift($rows); // drop header
        $imported = 0;

        foreach ($rows as $r) {
            $name = trim($r[1] ?? '');
            if ($name === '') {
                continue;
            }

            // Build the Main > Category > Sub hierarchy.
            $mainId = $this->category($r[4] ?? '', Category::LEVEL_MAIN, null);
            $catId = $this->category($r[5] ?? '', Category::LEVEL_CATEGORY, $mainId);
            $subId = $this->category($r[6] ?? '', Category::LEVEL_SUB, $catId);

            $slug = Str::slug($r[2] ?? '') ?: Str::slug($name);

            $product = Product::updateOrCreate(
                ['slug' => $this->uniqueSlug($slug, $name)],
                [
                    'name' => $name,
                    'model' => $this->val($r[3] ?? ''),
                    'main_category_id' => $mainId,
                    'category_id' => $catId,
                    'sub_category_id' => $subId,
                    'status' => strtolower(trim($r[7] ?? 'active')) === 'inactive' ? 'inactive' : 'active',
                    'image_url' => $this->val($r[8] ?? ''),
                    'short_description' => $this->val($r[9] ?? ''),
                    'description' => $this->val($r[10] ?? ''),
                    'advantages' => $this->val($r[11] ?? ''),
                    'specifications' => $this->val($r[12] ?? ''),
                    'meta_title' => $this->val($r[13] ?? ''),
                    'meta_description' => $this->val($r[14] ?? ''),
                    'gallery_images' => $this->splitList($r[15] ?? ''),
                    'faqs' => null,
                    'created_at' => $this->parseDate($r[17] ?? ''),
                    'updated_at' => $this->parseDate($r[18] ?? ''),
                ]
            );

            $product->ensureBarcode();
            $imported++;
            $this->line("  ✓ {$product->name}  [{$product->barcode}]");
        }

        $this->info("Imported/updated {$imported} products across " . Category::count() . ' categories.');

        return self::SUCCESS;
    }

    private function val(string $v): ?string
    {
        $v = trim($v);

        return $v === '' ? null : $v;
    }

    private function splitList(string $v): ?array
    {
        $items = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $v))));

        return $items ?: null;
    }

    private function parseDate(string $v): ?Carbon
    {
        $v = trim($v);
        if ($v === '') {
            return null;
        }
        foreach (['d M Y, h:i A', 'd M Y h:i A', 'd M Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $v);
            } catch (\Throwable $e) {
                // try next format
            }
        }

        return null;
    }

    /** Idempotently create a category at a level, return its id (or null when blank). */
    private function category(string $name, int $level, ?int $parentId): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $category = Category::firstOrCreate(
            ['name' => $name, 'level' => $level, 'parent_id' => $parentId],
            ['slug' => $this->uniqueCategorySlug(Str::slug($name)), 'status' => 'active']
        );

        return $category->id;
    }

    private function uniqueCategorySlug(string $base): string
    {
        $slug = $base ?: 'category';
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function uniqueSlug(string $base, string $name): string
    {
        $base = $base ?: Str::slug($name);

        // updateOrCreate keys on slug, so an existing slug for the same product is fine.
        return $base;
    }

    /**
     * Minimal XLSX reader (no external dependency): pulls shared strings + sheet1 cells.
     */
    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $shared = [];
        if ($xml = $zip->getFromName('xl/sharedStrings.xml')) {
            $ss = simplexml_load_string($xml);
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string) $si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                    $shared[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! $sheetXml) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $idx = $this->colIndex((string) $c['r']);
                $v = (string) $c->v;
                if ((string) $c['t'] === 's') {
                    $v = $shared[(int) $v] ?? '';
                }
                $cells[$idx] = $v;
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private function colIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $n = 0;
        foreach (str_split($m[1]) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return $n - 1;
    }
}
