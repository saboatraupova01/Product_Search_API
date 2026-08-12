<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use RuntimeException;

class ElasticsearchService
{
    private Client $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([
                config('services.elasticsearch.host'),
            ])
            ->build();
    }

    /**
     * Index a single product.
     */
    public function indexProduct(array $product): array
    {
        try {
            return $this->client
                ->index([
                    'index' => 'products',
                    'id' => $product['id'],
                    'body' => $product,
                ])
                ->asArray();
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Elasticsearch index request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Delete a single product from Elasticsearch.
     */
    public function deleteProduct(int $id): array
    {
        try {
            return $this->client
                ->delete([
                    'index' => 'products',
                    'id' => $id,
                ])
                ->asArray();
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Elasticsearch delete request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Index products using Elasticsearch Bulk API.
     */
    public function bulkInsert(array $products): array
    {
        $params = [
            'index' => 'products',
            'body' => [],
        ];

        foreach ($products as $product) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'products',
                    '_id' => $product['id'],
                ],
            ];

            $params['body'][] = $product;
        }

        if (empty($params['body'])) {
            return [
                'errors' => false,
                'items' => [],
            ];
        }

        try {
            return $this->client
                ->bulk($params)
                ->asArray();
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Elasticsearch bulk request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Search products.
     */
    public function search(array $filters = []): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));

        $limit = min(
            100,
            max(1, (int) ($filters['limit'] ?? 20))
        );

        $from = ($page - 1) * $limit;

        $must = [];
        $filter = [];

        /*
        |--------------------------------------------------------------------------
        | Keyword search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['keyword'])) {
            $must[] = [
                'multi_match' => [
                    'query' => $filters['keyword'],
                    'fields' => [
                        'name^3',
                        'description',
                    ],
                    'type' => 'best_fields',
                    'operator' => 'and',
                ],
            ];
        } else {
            $must[] = [
                'match_all' => new \stdClass(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Category filter
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['category'])) {
            $filter[] = [
                'term' => [
                    'category' => $filters['category'],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Brand filter
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['brand'])) {
            $filter[] = [
                'term' => [
                    'brand' => $filters['brand'],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Price filter
        |--------------------------------------------------------------------------
        */

        if (
            isset($filters['min_price']) ||
            isset($filters['max_price'])
        ) {
            $priceRange = [];

            if (isset($filters['min_price'])) {
                $priceRange['gte'] = (float) $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $priceRange['lte'] = (float) $filters['max_price'];
            }

            $filter[] = [
                'range' => [
                    'price' => $priceRange,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Rating filter
        |--------------------------------------------------------------------------
        */

        if (isset($filters['rating'])) {
            $filter[] = [
                'range' => [
                    'rating' => [
                        'gte' => (float) $filters['rating'],
                    ],
                ],
            ];
        }
        /*
        |--------------------------------------------------------------------------
        | Active filter
        |--------------------------------------------------------------------------
        */

        if (isset($filters['is_active'])) {
            $isActive = filter_var(
                $filters['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($isActive !== null) {
                $filter[] = [
                    'term' => [
                        'is_active' => $isActive,
                    ],
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $query = [
            'bool' => [
                'must' => $must,
                'filter' => $filter,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sort = match ($filters['sort'] ?? null) {
            'price_asc' => [
                'price' => [
                    'order' => 'asc',
                ],
            ],

            'price_desc' => [
                'price' => [
                    'order' => 'desc',
                ],
            ],

            'rating_desc' => [
                'rating' => [
                    'order' => 'desc',
                ],
            ],

            'created_at_desc' => [
                'created_at' => [
                    'order' => 'desc',
                ],
            ],

            default => [
                '_score' => [
                    'order' => 'desc',
                ],
            ],
        };

        /*
        |--------------------------------------------------------------------------
        | Elasticsearch request
        |--------------------------------------------------------------------------
        */

        $params = [
            'index' => 'products',

            'body' => [
                'from' => $from,
                'size' => $limit,
                'track_total_hits' => true,
                'query' => $query,

                'sort' => [
                    $sort,
                ],

                'highlight' => [
                    'pre_tags' => ['<mark>'],
                    'post_tags' => ['</mark>'],

                    'fields' => [
                        'name' => new \stdClass(),
                        'description' => new \stdClass(),
                    ],
                ],
            ],
        ];

        try {
            return $this->client
                ->search($params)
                ->asArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Elasticsearch search request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get document count.
     */
    public function count(): int
    {
        $response = $this->client
            ->count([
                'index' => 'products',
            ])
            ->asArray();

        return (int) ($response['count'] ?? 0);
    }

    public function getProductsBatch(int $size = 1000, ?string $searchAfter = null): array
    {
        $body = [
            'size' => $size,
            'sort' => [
                ['id' => 'asc'],
            ],
            'query' => [
                'match_all' => new \stdClass(),
            ],
        ];

        if ($searchAfter !== null) {
            $body['search_after'] = [$searchAfter];
        }

        try {
            return $this->client
                ->search([
                    'index' => 'products',
                    'body' => $body,
                ])
                ->asArray();
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Elasticsearch batch request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
