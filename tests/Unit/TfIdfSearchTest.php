<?php

use App\Models\CatalogItem;
use App\Services\Catalog\TfIdfSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns at most eight positive cosine matches with explanations', function (): void {
    $this->seed();

    $results = app(TfIdfSearchService::class)->search('quiet fan');

    expect($results)
        ->toHaveCount(8)
        ->and($results[0]['sku'])->toBe('NW-FN-001')
        ->and($results[0]['score'])->toBeGreaterThan(0)
        ->and($results[0]['matched_terms'])->toContain('quiet', 'fan');
});

it('returns no matches for terms outside the catalog', function (): void {
    CatalogItem::create([
        'sku' => 'NW-TEST-001',
        'name' => 'Test Product',
        'category' => 'Cables',
        'description' => 'A simple cable for testing.',
        'tags' => ['cable'],
        'price' => 10,
    ]);

    expect(app(TfIdfSearchService::class)->search('spaceship telescope'))
        ->toBe([]);
});
