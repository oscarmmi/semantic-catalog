<?php

namespace App\Services\Llm;

class RetrievalOnlyLlmClient implements LlmClient
{
    public function rerank(string $query, array $candidates): array
    {
        return array_column($candidates, 'sku');
    }
}
