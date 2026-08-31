<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public const LEVEL_MAIN = 1;
    public const LEVEL_CATEGORY = 2;
    public const LEVEL_SUB = 3;

    public const LEVELS = [
        self::LEVEL_MAIN => 'Main Category',
        self::LEVEL_CATEGORY => 'Category',
        self::LEVEL_SUB => 'Sub Category',
    ];

    protected $fillable = [
        'name',
        'slug',
        'level',
        'parent_id',
        'status',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getLevelNameAttribute(): string
    {
        return self::LEVELS[$this->level] ?? 'Unknown';
    }
}
