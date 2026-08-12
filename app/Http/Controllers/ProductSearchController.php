<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductSearchRequest;
use App\Services\ElasticsearchService;
use Illuminate\Http\JsonResponse;

class ProductSearchController extends Controller
{
    public function __construct(
        private ElasticsearchService $elasticsearch
    ) {
    }

    public function search(ProductSearchRequest $request): JsonResponse
    {
        $results = $this->elasticsearch->search(
            $request->validated()
        );

        $hits = $results['hits']['hits'] ?? [];

        $products = array_map(
            function (array $hit): array {
                $product = $hit['_source'] ?? [];

                if (isset($hit['highlight'])) {
                    $product['highlight'] = $hit['highlight'];
                }

                return $product;
            },
            $hits
        );

        $total = $results['hits']['total']['value'] ?? 0;

        $page = max(
            1,
            (int) $request->input('page', 1)
        );

        $limit = min(
            100,
            max(
                1,
                (int) $request->input('limit', 20)
            )
        );

        return response()->json([
            'data' => $products,

            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'last_page' => $limit > 0
                    ? (int) ceil($total / $limit)
                    : 0,
            ],
        ]);
    }
}
