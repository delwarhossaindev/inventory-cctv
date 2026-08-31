<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'batch_no', 'quantity', 'remaining', 'unit_cost', 'received_at',
        'reference_type', 'reference_id', 'note',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'unit_cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Give every batch a unique, scannable batch number.
        static::created(function (StockBatch $batch) {
            if (empty($batch->batch_no)) {
                $batch->batch_no = 'B' . str_pad($batch->id, 6, '0', STR_PAD_LEFT);
                $batch->saveQuietly();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
