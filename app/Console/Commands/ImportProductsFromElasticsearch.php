<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ElasticsearchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-products-from-elasticsearch')]
#[Description('Import products from Elasticsearch into MySQL')]
class ImportProductsFromElasticsearch extends Command
{
    public function handle(ElasticsearchService $elasticsearch): int
    {
        $this->info('Starting import...');

        $searchAfter = null;
        $totalImported = 0;

        do {
            $response = $elasticsearch->getProductsBatch(
                size: 1000,
                searchAfter: $searchAfter
            );

            $hits = $response['hits']['hits'] ?? [];

            if (empty($hits)) {
                break;
            }

            $products = [];

            foreach ($hits as $hit) {
                $source = $hit['_source'];

                $products[] = [
                    'id' => $source['id'],
                    'name' => $source['name'],
                    'description' => $source['description'],
                    'category' => $source['category'],
                    'brand' => $source['brand'],
                    'price' => $source['price'],
                    'quantity' => $source['quantity'],
                    'rating' => $source['rating'],
                    'is_active' => $source['is_active'],
                    'created_at' => $source['created_at'],
                ];
            }

            Product::insert($products);

            $totalImported += count($products);

            $lastHit = end($hits);

            $searchAfter = $lastHit['sort'][0] ?? null;

            $this->info("Imported: {$totalImported}");

        } while (!empty($hits));

        $this->info("Import completed. Total: {$totalImported}");

        return self::SUCCESS;
    }
}
