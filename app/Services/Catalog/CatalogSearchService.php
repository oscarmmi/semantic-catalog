<?php

namespace App\Services\Catalog;

use App\Models\SearchQuery;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class CatalogSearchService
{
    public function __construct(
        private readonly TfIdfSearchService $retriever,
        private readonly LlmClient $llm,
    ) {}

    /** @return array{results: array<int, array<string, mixed>>, mode: string, latency_ms: int} */
    public function search(string $query): array
    {
        $startedAt = hrtime(true);
        $results = $this->retriever->search($query);
        $mode = 'TF-IDF';

        if ($results !== [] && filled(config('services.gemini.key'))) {
            try {
                $results = $this->applyRerank($query, $results);
                $mode = 'TF-IDF + Gemini';
            } catch (Throwable $exception) {
                Log::warning('Gemini rerank failed; returning TF-IDF results.', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $latency = (int) round((hrtime(true) - $startedAt) / 1_000_000);
        SearchQuery::create([
            'q' => $query,
            'result_skus' => array_column($results, 'sku'),
            'latency_ms' => $latency,
        ]);

        return [
            'results' => $results,
            'mode' => $mode,
            'latency_ms' => $latency,
        ];
    }

    /** @param array<int, array<string, mixed>> $results
     * @return array<int, array<string, mixed>>
     */
    private function applyRerank(string $query, array $results): array
    {
        $bySku = collect($results)->keyBy('sku');
        $orderedSkus = $this->llm->rerank($query, $results);
        $ordered = [];

        foreach ($orderedSkus as $sku) {
            if ($bySku->has($sku)) {
                $ordered[] = $bySku->pull($sku);
            }
        }

        return [...$ordered, ...$bySku->values()->all()];
    }
}
