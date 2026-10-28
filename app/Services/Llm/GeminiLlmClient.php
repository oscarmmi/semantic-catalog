<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function rerank(string $query, array $candidates): array
    {
        $catalog = array_map(fn (array $item): array => [
            'sku' => $item['sku'],
            'name' => $item['name'],
            'category' => $item['category'],
            'description' => $item['description'],
            'tags' => $item['tags'],
            'price' => $item['price'],
        ], $candidates);

        $response = Http::timeout(12)
            ->connectTimeout(3)
            ->acceptJson()
            ->post(sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                config('services.gemini.model'),
                config('services.gemini.key'),
            ), [
                'contents' => [[
                    'parts' => [[
                        'text' => "Rerank these catalog candidates for the shopper's request. Return only a JSON array of candidate SKU strings, best first. Never invent SKUs.\nRequest: {$query}\nCandidates: ".json_encode($catalog, JSON_THROW_ON_ERROR),
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0,
                    'responseMimeType' => 'application/json',
                ],
            ])
            ->throw()
            ->json('candidates.0.content.parts.0.text');

        if (! is_string($response)) {
            throw new RuntimeException('Gemini returned no ranking response.');
        }

        $skus = json_decode($response, true);

        if (! is_array($skus)) {
            throw new RuntimeException('Gemini returned an invalid ranking response.');
        }

        return array_values(array_filter($skus, 'is_string'));
    }
}
