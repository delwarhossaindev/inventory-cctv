<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'model',
        'sku',
        'barcode',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'alert_quantity',
        'warranty_days',
        'unit',
        'main_category_id',
        'category_id',
        'sub_category_id',
        'status',
        'image_url',
        'short_description',
        'description',
        'advantages',
        'specifications',
        'meta_title',
        'meta_description',
        'gallery_images',
        'faqs',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'faqs' => 'array',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'alert_quantity' => 'integer',
    ];

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function batches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_quantity;
    }

    /**
     * Build a valid EAN-13 barcode from the product id (prefix "200" = in-store use).
     */
    public static function makeEan13(int $id): string
    {
        $base = '200' . str_pad((string) $id, 9, '0', STR_PAD_LEFT); // 12 digits
        $sum = 0;
        foreach (str_split($base) as $i => $digit) {
            $sum += (int) $digit * ($i % 2 === 0 ? 1 : 3);
        }
        $check = (10 - ($sum % 10)) % 10;

        return $base . $check;
    }

    /**
     * Assign an auto-generated barcode if one is not set yet.
     */
    public function ensureBarcode(): void
    {
        if (empty($this->barcode)) {
            $this->forceFill(['barcode' => self::makeEan13($this->id)])->saveQuietly();
        }
    }

    /**
     * Apply a signed stock change and record a movement.
     * Positive quantity = stock in, negative = stock out.
     * $costTotal is the signed cost value (positive in, negative COGS out).
     */
    public function recordStock(int $signedQuantity, string $type, $reference = null, ?string $note = null, float $costTotal = 0): void
    {
        $this->increment('stock_quantity', $signedQuantity);

        $this->stockMovements()->create([
            'type' => $type,
            'quantity' => $signedQuantity,
            'balance' => $this->stock_quantity,
            'cost_total' => $costTotal,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'note' => $note,
        ]);
    }

    /**
     * Add stock as a new FIFO batch (purchase, opening stock, positive adjustment).
     */
    public function stockIn(int $quantity, float $unitCost, string $type, $reference = null, ?string $note = null, $receivedAt = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        $this->batches()->create([
            'quantity' => $quantity,
            'remaining' => $quantity,
            'unit_cost' => $unitCost,
            'received_at' => $receivedAt ?? now(),
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'note' => $note,
        ]);

        $this->recordStock($quantity, $type, $reference, $note, $quantity * $unitCost);
    }

    /**
     * Remove stock consuming oldest batches first (FIFO). Returns the COGS consumed.
     */
    public function stockOut(int $quantity, string $type, $reference = null, ?string $note = null): float
    {
        if ($quantity <= 0) {
            return 0.0;
        }

        $toConsume = $quantity;
        $cogs = 0.0;

        $batches = $this->batches()
            ->where('remaining', '>', 0)
            ->orderBy('received_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($toConsume <= 0) {
                break;
            }
            $take = min($batch->remaining, $toConsume);
            $batch->decrement('remaining', $take);
            $cogs += $take * (float) $batch->unit_cost;
            $toConsume -= $take;
        }

        // Fallback for any quantity not covered by batches (e.g. legacy stock):
        // value it at the product's purchase price so COGS stays sensible.
        if ($toConsume > 0) {
            $cogs += $toConsume * (float) $this->purchase_price;
        }

        $this->recordStock(-$quantity, $type, $reference, $note, -$cogs);

        return $cogs;
    }
}
