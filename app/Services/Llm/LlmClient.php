<?php

namespace App\Services\Llm;

interface LlmClient
{
    /**
     * Return candidate SKUs in relevance order. The candidate set is already
     * limited by retrieval, so an LLM can only reorder known catalog items.
     *
     * @param  array<int, array<string, mixed>>  $candidates
     * @return array<int, string>
     */
    public function rerank(string $query, array $candidates): array;
}
