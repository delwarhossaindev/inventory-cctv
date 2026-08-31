<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'supplier_id', 'purchase_date',
        'subtotal', 'discount', 'tax', 'total', 'paid', 'due',
        'status', 'note',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function recalculateDue(): void
    {
        $totalPaid = $this->paid + $this->payments()->sum('amount');
        $this->forceFill(['due' => max($this->total - $totalPaid, 0)])->saveQuietly();
    }
}
