<?php

namespace App\Http\Controllers;

use App\Services\Catalog\CatalogSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, CatalogSearchService $search): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:200'],
        ]);

        return response()->json($search->search(trim($validated['q'])));
    }
}
