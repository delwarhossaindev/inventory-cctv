<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'quote_no', 'customer_id', 'quote_date', 'valid_until',
        'subtotal', 'discount', 'tax', 'total', 'status', 'note', 'created_by',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }
}
