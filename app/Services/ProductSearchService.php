<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;

class ProductSearchService
{
    private Client $client;
    public function __construct()
    {
        $this->client = app('elasticsearch');
    }

    public function search(array $filters): array
    {
        $query = [
            'bool' => [
                'must' => [],
                'filter' => [],
            ],
        ];

    // Поиск по названию и описанию
        if (!empty($filters['keyword'])) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query' => $filters['keyword'],
                    'fields' => [
                        'name',
                        'description'
                    ],
                ],
            ];
        }

    // Категория
        if (!empty($filters['category'])) {
            $query['bool']['filter'][] = [
                'term' => [
                    'category' => $filters['category'],
                ],
            ];
        }

    // Бренд
        if (!empty($filters['brand'])) {
            $query['bool']['filter'][] = [
                'term' => [
                    'brand' => $filters['brand'],
                ],
            ];
        }

    // Только активные товары
        if (isset($filters['is_active'])) {
            $query['bool']['filter'][] = [
                'term' => [
                    'is_active' => $filters['is_active'],
                ],
            ];
        }

    // Цена
        if (
            isset($filters['min_price']) ||
            isset($filters['max_price'])
        ) {
            $range = [];

            if (isset($filters['min_price'])) {
                $range['gte'] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $range['lte'] = $filters['max_price'];
            }

            $query['bool']['filter'][] = [
                'range' => [
                    'price' => $range,
                ],
            ];
        }

    // Рейтинг
        if (isset($filters['rating'])) {
            $query['bool']['filter'][] = [
                'range' => [
                    'rating' => [
                        'gte' => $filters['rating'],
                    ],
                ],
            ];
        }


        $params = [
            'index' => 'products',
            'body' => [
                'query' => $query,

                'track_total_hits' => true,

                'from' => (($filters['page'] ?? 1) - 1)
                    * ($filters['limit'] ?? 20),

                'size' => $filters['limit'] ?? 20,
            ],
        ];

    // Сортировка
        if (!empty($filters['sort'])) {

            $sorts = [
                'price_asc' => [
                    'price' => 'asc'
                ],

                'price_desc' => [
                    'price' => 'desc'
                ],

                'rating_desc' => [
                    'rating' => 'desc'
                ],

                'created_at_desc' => [
                    'created_at' => 'desc'
                ],
            ];

            if (isset($sorts[$filters['sort']])) {
                $params['body']['sort'] = [
                    $sorts[$filters['sort']]
                ];
            }
        }

        return $this->client
            ->search($params)
            ->asArray();
    }
}
