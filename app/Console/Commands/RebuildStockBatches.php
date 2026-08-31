<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class RebuildStockBatches extends Command
{
    protected $signature = 'app:rebuild-stock-batches';

    protected $description = 'Create opening FIFO batches for any current stock not yet backed by batches';

    public function handle(): int
    {
        $created = 0;

        foreach (Product::all() as $product) {
            $batched = (int) $product->batches()->sum('remaining');
            $missing = $product->stock_quantity - $batched;

            if ($missing > 0) {
                // Backdate so these opening batches are consumed before any newer stock.
                $product->batches()->create([
                    'quantity' => $missing,
                    'remaining' => $missing,
                    'unit_cost' => $product->purchase_price,
                    'received_at' => now()->subYear(),
                    'note' => 'Opening batch (backfill)',
                ]);
                $created++;
                $this->line("  ✓ {$product->name}: opening batch of {$missing} @ {$product->purchase_price}");
            }
        }

        $this->info("Created {$created} opening batches.");

        return self::SUCCESS;
    }
}
