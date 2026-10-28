<?php

use App\Models\CatalogItem;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Catalog', [
        'productCount' => CatalogItem::count(),
        'categories' => CatalogItem::query()
            ->distinct()
            ->orderBy('category')
            ->pluck('category'),
        'examples' => [
            'quiet fan for studio',
            'gift under 40',
            "cable that won't tangle",
        ],
    ]);
})->name('home');
