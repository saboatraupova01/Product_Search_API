<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ElasticsearchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-products')]
#[Description('Synchronize products from MySQL to Elasticsearch')]
class SyncProducts extends Command
{
    public function handle(ElasticsearchService $elasticsearch): int
    {
        $this->info('Products synchronization started.');

        $total = Product::count();

        if ($total === 0) {
            $this->warn('No products found in MySQL.');

            return self::SUCCESS;
        }

        $this->info("Found {$total} products.");

        $synced = 0;

        Product::query()
            ->orderBy('id')
            ->chunkById(1000, function ($products) use ($elasticsearch, &$synced) {
                $data = $products->map(function (Product $product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'category' => $product->category,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'quantity' => $product->quantity,
                        'rating' => $product->rating,
                        'is_active' => $product->is_active,
                        'created_at' => $product->created_at?->format('Y-m-d'),
                    ];
                })->toArray();

                $elasticsearch->bulkInsert($data);

                $synced += count($data);

                $this->info("Synced: {$synced}");
            });

        $this->info("Products synchronization completed. Total: {$synced}");

        return self::SUCCESS;
    }
}
