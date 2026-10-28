<?php

namespace App\Services\Catalog;

use App\Models\CatalogItem;

class TfIdfSearchService
{
    private const STOP_WORDS = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'for', 'from', 'i', 'in',
        'is', 'it', 'of', 'on', 'or', 'that', 'the', 'to', 'want', 'with',
        'will', 'wont', 'under', 'without', 'you', 'your',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query): array
    {
        $items = CatalogItem::query()->get();

        if ($items->isEmpty()) {
            return [];
        }

        $documents = $items->mapWithKeys(function (CatalogItem $item): array {
            $text = implode(' ', [
                $item->name,
                $item->description,
                implode(' ', $item->tags),
            ]);

            return [$item->id => $this->termFrequency($text)];
        });
        $queryTerms = $this->termFrequency($query);

        if ($queryTerms === []) {
            return [];
        }

        $documentCount = $items->count();
        $documentFrequency = [];

        foreach ($documents as $terms) {
            foreach (array_keys($terms) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }
        }

        $queryVector = $this->weightedVector($queryTerms, $documentFrequency, $documentCount);
        $queryMagnitude = $this->magnitude($queryVector);

        $ranked = $items->map(function (CatalogItem $item) use (
            $documents,
            $documentFrequency,
            $documentCount,
            $queryTerms,
            $queryVector,
            $queryMagnitude,
        ): array {
            $documentVector = $this->weightedVector(
                $documents[$item->id],
                $documentFrequency,
                $documentCount,
            );
            $dotProduct = 0.0;

            foreach ($queryVector as $term => $weight) {
                $dotProduct += $weight * ($documentVector[$term] ?? 0.0);
            }

            $documentMagnitude = $this->magnitude($documentVector);
            $score = $queryMagnitude > 0 && $documentMagnitude > 0
                ? $dotProduct / ($queryMagnitude * $documentMagnitude)
                : 0.0;
            $matchedTerms = array_values(array_intersect(
                array_keys($queryTerms),
                array_keys($documents[$item->id]),
            ));

            return [
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category,
                'description' => $item->description,
                'tags' => $item->tags,
                'price' => (float) $item->price,
                'score' => round($score, 4),
                'matched_terms' => $matchedTerms,
            ];
        });

        return $ranked
            ->filter(fn (array $item): bool => $item['score'] > 0)
            ->sort(function (array $left, array $right): int {
                return ($right['score'] <=> $left['score']) ?: ($left['sku'] <=> $right['sku']);
            })
            ->take(8)
            ->values()
            ->all();
    }

    /** @return array<string, int> */
    private function termFrequency(string $text): array
    {
        $text = mb_strtolower(str_replace(["'", '’'], '', $text));
        preg_match_all('/[a-z0-9]+/', $text, $matches);
        $terms = array_filter($matches[0], fn (string $term): bool => strlen($term) > 1 && ! in_array($term, self::STOP_WORDS, true)
        );

        return array_count_values($terms);
    }

    /** @param array<string, int> $terms
     * @param  array<string, int>  $documentFrequency
     * @return array<string, float>
     */
    private function weightedVector(array $terms, array $documentFrequency, int $documentCount): array
    {
        $vector = [];

        foreach ($terms as $term => $frequency) {
            $inverseDocumentFrequency = log(1 + ($documentCount / (1 + ($documentFrequency[$term] ?? 0))));
            $vector[$term] = (1 + log($frequency)) * $inverseDocumentFrequency;
        }

        return $vector;
    }

    /** @param array<string, float> $vector */
    private function magnitude(array $vector): float
    {
        return sqrt(array_sum(array_map(fn (float $weight): float => $weight ** 2, $vector)));
    }
}
