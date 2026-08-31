<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = ['user_id', 'opening_balance', 'closing_balance', 'opened_at', 'closed_at', 'note'];

    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }
}
