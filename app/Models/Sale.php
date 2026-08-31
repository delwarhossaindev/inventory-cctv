<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'customer_id', 'sale_date',
        'subtotal', 'discount', 'tax', 'total', 'paid', 'due',
        'payment_method', 'status', 'source', 'note',
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function recalculateDue(): void
    {
        $totalPaid = $this->paid + $this->payments()->sum('amount');
        $this->forceFill(['due' => max($this->total - $totalPaid, 0)])->saveQuietly();
    }
}
