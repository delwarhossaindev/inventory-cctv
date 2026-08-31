<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'short_name', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
