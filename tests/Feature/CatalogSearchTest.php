<?php

use App\Models\SearchQuery;
use App\Services\Catalog\TfIdfSearchService;
use App\Services\Llm\LlmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Support\FakeLlmClient;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
});

it('renders the catalog with its seeded inventory', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog')
            ->where('productCount', 24)
            ->where('categories', ['Bags', 'Cables', 'Fans', 'Lamps']));
});

it('ranks a quiet fan first and explains the matching terms', function (): void {
    $this->postJson('/api/search', ['q' => 'quiet fan'])
        ->assertOk()
        ->assertJsonPath('results.0.sku', 'NW-FN-001')
        ->assertJsonPath('results.0.category', 'Fans')
        ->assertJsonPath('mode', 'TF-IDF');

    expect(SearchQuery::query()->latest()->first())
        ->q->toBe('quiet fan')
        ->result_skus->toContain('NW-FN-001')
        ->latency_ms->toBeInt();
});

it('returns relevant products for the example gift query', function (): void {
    $response = $this->postJson('/api/search', ['q' => 'gift under 40'])
        ->assertOk()
        ->assertJsonCount(8, 'results')
        ->assertJsonPath('mode', 'TF-IDF');

    expect(collect($response->json('results'))->every(
        fn (array $item): bool => $item['price'] <= 40,
    ))->toBeTrue();
});

it('returns cable results for a messy anti-tangle query', function (): void {
    $this->postJson('/api/search', ['q' => "cable that won't tangle"])
        ->assertOk()
        ->assertJsonPath('results.0.category', 'Cables')
        ->assertJsonPath('results.0.matched_terms', ['cable', 'tangle']);
});

it('validates an empty query with a 422 response', function (): void {
    $this->postJson('/api/search', ['q' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('q');
});

it('binds the fake reranker in tests and applies its gift ordering', function (): void {
    $retrieved = app(TfIdfSearchService::class)->search('gift under 40');
    $this->app->instance(LlmClient::class, new FakeLlmClient);
    config(['services.gemini.key' => 'test-key']);

    $fake = $this->app->make(LlmClient::class);

    expect($fake)->toBeInstanceOf(FakeLlmClient::class);

    $this->postJson('/api/search', ['q' => 'gift under 40'])
        ->assertOk()
        ->assertJsonPath('results.0.sku', $retrieved[1]['sku'])
        ->assertJsonPath('mode', 'TF-IDF + Gemini');

    expect($fake->calls)->toBe(1);
});

it('falls back to retrieval when the optional reranker fails', function (): void {
    config(['services.gemini.key' => 'test-key']);
    $this->app->instance(LlmClient::class, new class implements LlmClient
    {
        public function rerank(string $query, array $candidates): array
        {
            throw new RuntimeException('Simulated provider outage.');
        }
    });

    $this->postJson('/api/search', ['q' => 'quiet fan'])
        ->assertOk()
        ->assertJsonPath('results.0.sku', 'NW-FN-001')
        ->assertJsonPath('mode', 'TF-IDF');
});
