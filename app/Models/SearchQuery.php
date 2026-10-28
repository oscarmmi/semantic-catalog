<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $fillable = [
        'q',
        'result_skus',
        'latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'result_skus' => 'array',
        ];
    }
}
