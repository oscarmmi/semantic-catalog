<?php

namespace Tests\Support;

use App\Services\Llm\LlmClient;

class FakeLlmClient implements LlmClient
{
    public int $calls = 0;

    public function rerank(string $query, array $candidates): array
    {
        $this->calls++;
        $skus = array_column($candidates, 'sku');

        if (str_contains(mb_strtolower($query), 'gift') && count($skus) > 1) {
            [$skus[0], $skus[1]] = [$skus[1], $skus[0]];
        }

        return $skus;
    }
}
