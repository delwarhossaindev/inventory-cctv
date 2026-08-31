<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    protected $fillable = [
        'sale_id', 'customer_id', 'total_amount', 'down_payment',
        'num_installments', 'installment_amount', 'status',
    ];

    public function sale() { return $this->belongsTo(Sale::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function payments() { return $this->hasMany(InstallmentPayment::class); }

    public function paidCount(): int
    {
        return $this->payments()->where('status', 'paid')->count();
    }
}
