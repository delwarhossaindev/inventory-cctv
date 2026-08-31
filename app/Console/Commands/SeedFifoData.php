<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedFifoData extends Command
{
    protected $signature = 'app:seed-fifo-data';

    protected $description = 'Set realistic prices and build two FIFO cost layers per product so FIFO COGS is meaningful';

    public function handle(): int
    {
        $this->warn('This resets stock batches/movements and rebuilds them. Continue...');

        DB::transaction(function () {
            foreach (Product::all() as $product) {
                // Deterministic but varied base cost (BDT), rounded to nearest 50.
                $base = $this->round50((($product->id * 1234 + 700) % 9500) + 500);
                $newerCost = $this->round50($base * 1.12);   // price rose ~12% for the newer batch
                $salePrice = $this->round50($base * 1.40);   // 40% markup over base

                $product->purchase_price = $base;
                $product->sale_price = $salePrice;
                $product->save();

                // Clean slate: drop existing layers + ledger for this product.
                $product->batches()->delete();
                $product->stockMovements()->delete();
                $product->forceFill(['stock_quantity' => 0])->save();

                // Layer 1 (older, cheaper) — consumed first by FIFO.
                $product->stockIn(30, $base, 'purchase', null, 'Opening batch (older)', now()->subDays(20));
                // Layer 2 (newer, dearer).
                $product->stockIn(30, $newerCost, 'purchase', null, 'Restock batch (newer)', now()->subDays(5));

                $this->line("  ✓ {$product->name}: cost {$base}/{$newerCost}, sale {$salePrice}, stock 60");
            }
        });

        $this->info('Done. Each product now has 2 FIFO cost layers (30 + 30 = 60 units).');

        return self::SUCCESS;
    }

    private function round50(float $n): float
    {
        return round($n / 50) * 50;
    }
}
