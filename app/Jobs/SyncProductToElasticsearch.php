<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\ElasticsearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncProductToElasticsearch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $productId
    ) {
    }

    public function handle(ElasticsearchService $elasticsearch): void
    {
        $product = Product::find($this->productId);

        if (!$product) {
            return;
        }

        $elasticsearch->indexProduct($product->toArray());
    }
}
