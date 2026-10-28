<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array<int, string> $tags
 */
class CatalogItem extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'category',
        'description',
        'tags',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'price' => 'decimal:2',
        ];
    }
}
