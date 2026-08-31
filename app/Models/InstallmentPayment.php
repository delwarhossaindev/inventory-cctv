<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    protected $fillable = ['installment_plan_id', 'installment_no', 'due_date', 'paid_date', 'amount', 'status'];

    protected $casts = ['due_date' => 'date', 'paid_date' => 'date'];

    public function plan() { return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id'); }
}
