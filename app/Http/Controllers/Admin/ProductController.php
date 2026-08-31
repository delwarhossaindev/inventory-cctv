<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view products')->only(['index', 'show', 'labels', 'batchLabels']);
        $this->middleware('permission:create products')->only(['create', 'store', 'bulkImport', 'bulkImportStore', 'importTemplate']);
        $this->middleware('permission:edit products')->only(['edit', 'update', 'bulkPricing', 'bulkPricingUpdate']);
        $this->middleware('permission:delete products')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Product::with(['mainCategory', 'category', 'subCategory'])->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('main_category_id')) {
            $query->where('main_category_id', $request->main_category_id);
        }

        $products = $query->paginate(15)->withQueryString();
        $mains = Category::level(Category::LEVEL_MAIN)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'mains'));
    }

    public function create()
    {
        return view('admin.products.create', $this->formData(new Product()));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data = $this->normalizeJson($request, $data);

        $opening = (int) $request->input('stock_quantity', 0);

        $product = Product::create($data);
        $product->ensureBarcode(); // auto-generate if left blank

        if ($opening > 0) {
            $product->stockIn($opening, (float) $product->purchase_price, 'adjustment', null, 'Opening stock');
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['mainCategory', 'category', 'subCategory']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $product->id);
        $data = $this->normalizeJson($request, $data);

        $product->update($data);
        $product->ensureBarcode();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function bulkPricing(Request $request)
    {
        $query = Product::with('mainCategory')->orderBy('name');
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
                ->orWhere('model', 'like', "%{$q}%"));
        }
        if ($request->filled('main_category_id')) {
            $query->where('main_category_id', $request->main_category_id);
        }

        return view('admin.products.bulk-pricing', [
            'products' => $query->paginate(30)->withQueryString(),
            'mains' => Category::level(Category::LEVEL_MAIN)->orderBy('name')->get(),
        ]);
    }

    public function bulkPricingUpdate(Request $request)
    {
        $data = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'rows.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'rows.*.alert_quantity' => ['nullable', 'integer', 'min:0'],
            'rows.*.stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $updated = 0;

        DB::transaction(function () use ($data, &$updated) {
            $products = Product::whereIn('id', array_keys($data['rows']))->get()->keyBy('id');

            foreach ($data['rows'] as $id => $row) {
                $product = $products->get($id);
                if (! $product) {
                    continue;
                }

                $product->purchase_price = $row['purchase_price'] ?? $product->purchase_price;
                $product->sale_price = $row['sale_price'] ?? $product->sale_price;
                $product->alert_quantity = $row['alert_quantity'] ?? $product->alert_quantity;
                $product->save();

                // Stock change goes through FIFO: add a batch or consume oldest first.
                if (isset($row['stock']) && (int) $row['stock'] !== $product->stock_quantity) {
                    $delta = (int) $row['stock'] - $product->stock_quantity;
                    if ($delta > 0) {
                        $product->stockIn($delta, (float) $product->purchase_price, 'adjustment', null, 'Bulk stock update');
                    } else {
                        $product->stockOut(-$delta, 'adjustment', null, 'Bulk stock update');
                    }
                }

                $updated++;
            }
        });

        return back()->with('success', "Updated {$updated} products.");
    }

    public function labels(Request $request)
    {
        $query = Product::whereNotNull('barcode')->orderBy('name');
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
                ->orWhere('barcode', 'like', "%{$q}%"));
        }
        if ($request->filled('main_category_id')) {
            $query->where('main_category_id', $request->main_category_id);
        }

        return view('admin.products.labels', [
            'products' => $query->get(),
            'mains' => Category::level(Category::LEVEL_MAIN)->orderBy('name')->get(),
            'copies' => max(1, min((int) $request->input('copies', 1), 50)),
        ]);
    }

    /** Printable barcode labels for each stock batch of a product. */
    public function batchLabels(Request $request, Product $product)
    {
        $query = $product->batches()->orderByDesc('received_at')->orderByDesc('id');

        // Print a single batch when requested (from the Batches list).
        if ($request->filled('batch')) {
            $query->where('id', $request->integer('batch'));
        } elseif (! $request->boolean('all')) {
            // Otherwise only batches that still have stock, unless "all" is requested.
            $query->where('remaining', '>', 0);
        }

        return view('admin.products.batch-labels', [
            'product' => $product,
            'batches' => $query->get(),
            'copies' => max(1, min((int) $request->input('copies', 1), 50)),
            'showAll' => $request->boolean('all'),
        ]);
    }

    /** Columns understood by the bulk importer (matches the products.xlsx layout). */
    private const IMPORT_COLUMNS = [
        'Name', 'Model', 'SKU', 'Barcode', 'Main Category', 'Category', 'Sub Category', 'Status',
        'Purchase Price', 'Sale Price', 'Stock', 'Alert Qty', 'Unit', 'Image URL',
        'Short Description', 'Description', 'Advantages', 'Specifications',
        'Meta Title', 'Meta Description', 'Gallery Images',
    ];

    public function bulkImport()
    {
        return view('admin.products.bulk-import', ['columns' => self::IMPORT_COLUMNS]);
    }

    public function importTemplate()
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="product-import-template.csv"'];

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, self::IMPORT_COLUMNS);
            // one example row
            fputcsv($out, [
                'Example Bur Handpiece', 'MDL-1', 'SKU-1', '', 'ENT Surgery', 'Surgical Power Device', 'Handpiece',
                'active', '1000', '1500', '20', '5', 'pcs', 'https://example.com/image.jpg',
                'Short text', '<p>Full description</p>', 'Key advantage', 'Spec list', 'Meta title', 'Meta description',
                'https://example.com/g1.jpg, https://example.com/g2.jpg',
            ]);
            fclose($out);
        }, 'product-import-template.csv', $headers);
    }

    public function bulkImportStore(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt', 'xlsx'], true)) {
            return back()->withErrors(['file' => 'Please upload a CSV or XLSX file.']);
        }

        $rows = Spreadsheet::rows($file->getRealPath(), $ext);
        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'The file has no data rows.']);
        }

        // Map header labels (normalized) to their column index.
        $header = array_shift($rows);
        $map = [];
        foreach ($header as $idx => $label) {
            $key = $this->normalizeHeader((string) $label);
            if ($key) {
                $map[$key] = $idx;
            }
        }
        if (! isset($map['name'])) {
            return back()->withErrors(['file' => 'A "Name" column is required.']);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $map, &$created, &$skipped, &$errors) {
            foreach ($rows as $n => $row) {
                $get = fn ($key) => isset($map[$key]) ? trim((string) ($row[$map[$key]] ?? '')) : '';

                $name = $get('name');
                if ($name === '') {
                    $skipped++;
                    continue;
                }

                try {
                    $mainId = $this->importCategory($get('main_category'), Category::LEVEL_MAIN, null);
                    $catId = $this->importCategory($get('category'), Category::LEVEL_CATEGORY, $mainId);
                    $subId = $this->importCategory($get('sub_category'), Category::LEVEL_SUB, $catId);

                    $gallery = collect(preg_split('/[,\n]+/', $get('gallery_images')))
                        ->map(fn ($u) => trim($u))->filter()->values()->all();

                    $product = Product::create([
                        'name' => $name,
                        'slug' => $this->uniqueSlug($get('slug') ?: $name),
                        'model' => $get('model') ?: null,
                        'sku' => $get('sku') ?: null,
                        'barcode' => $get('barcode') ?: null,
                        'main_category_id' => $mainId,
                        'category_id' => $catId,
                        'sub_category_id' => $subId,
                        'status' => strtolower($get('status')) === 'inactive' ? 'inactive' : 'active',
                        'purchase_price' => (float) ($get('purchase_price') ?: 0),
                        'sale_price' => (float) ($get('sale_price') ?: 0),
                        'alert_quantity' => (int) ($get('alert_quantity') ?: 0),
                        'unit' => $get('unit') ?: 'pcs',
                        'image_url' => $get('image_url') ?: null,
                        'short_description' => $get('short_description') ?: null,
                        'description' => $get('description') ?: null,
                        'advantages' => $get('advantages') ?: null,
                        'specifications' => $get('specifications') ?: null,
                        'meta_title' => $get('meta_title') ?: null,
                        'meta_description' => $get('meta_description') ?: null,
                        'gallery_images' => $gallery ?: null,
                    ]);
                    $product->ensureBarcode();

                    $opening = (int) ($get('stock') ?: 0);
                    if ($opening > 0) {
                        $product->stockIn($opening, (float) $product->purchase_price, 'adjustment', null, 'Bulk import opening stock');
                    }

                    $created++;
                } catch (\Throwable $e) {
                    $skipped++;
                    if (count($errors) < 10) {
                        $errors[] = 'Row ' . ($n + 2) . " ({$name}): " . $e->getMessage();
                    }
                }
            }
        });

        return back()->with('import_result', compact('created', 'skipped', 'errors'));
    }

    /** Normalize a header label (e.g. "Main Category") to a known key (e.g. "main_category"). */
    private function normalizeHeader(string $label): ?string
    {
        $key = strtolower(trim($label));
        $aliases = [
            'name' => 'name', 'slug' => 'slug', 'model' => 'model', 'sku' => 'sku', 'barcode' => 'barcode',
            'main category' => 'main_category', 'category' => 'category', 'sub category' => 'sub_category',
            'status' => 'status', 'purchase price' => 'purchase_price', 'sale price' => 'sale_price',
            'stock' => 'stock', 'opening stock' => 'stock', 'alert qty' => 'alert_quantity', 'alert quantity' => 'alert_quantity',
            'unit' => 'unit', 'image url' => 'image_url', 'short description' => 'short_description',
            'description' => 'description', 'advantages' => 'advantages', 'specifications' => 'specifications',
            'meta title' => 'meta_title', 'meta description' => 'meta_description', 'gallery images' => 'gallery_images',
        ];

        return $aliases[$key] ?? null;
    }

    /** Idempotently resolve/create a category at a level, returning its id (null when blank). */
    private function importCategory(string $name, int $level, ?int $parentId): ?int
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

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'mains' => Category::level(Category::LEVEL_MAIN)->orderBy('name')->get(),
            // Pre-load children so dropdowns show correctly when editing.
            'categories' => $product->main_category_id
                ? Category::where('parent_id', $product->main_category_id)->orderBy('name')->get()
                : collect(),
            'subCategories' => $product->category_id
                ? Category::where('parent_id', $product->category_id)->orderBy('name')->get()
                : collect(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'alert_quantity' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'main_category_id' => ['nullable', 'exists:categories,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'advantages' => ['nullable', 'string'],
            'specifications' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);
    }

    /**
     * Build gallery_images and faqs arrays from the repeating form inputs.
     */
    private function normalizeJson(Request $request, array $data): array
    {
        $gallery = collect($request->input('gallery_images', []))
            ->map(fn ($url) => trim((string) $url))
            ->filter()
            ->values()
            ->all();
        $data['gallery_images'] = $gallery ?: null;

        $faqs = collect($request->input('faqs', []))
            ->map(fn ($faq) => [
                'question' => trim((string) ($faq['question'] ?? '')),
                'answer' => trim((string) ($faq['answer'] ?? '')),
            ])
            ->filter(fn ($faq) => $faq['question'] !== '' || $faq['answer'] !== '')
            ->values()
            ->all();
        $data['faqs'] = $faqs ?: null;

        return $data;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
