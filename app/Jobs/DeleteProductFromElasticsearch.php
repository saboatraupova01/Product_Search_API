<?php

namespace App\Jobs;

use App\Services\ElasticsearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteProductFromElasticsearch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $productId
    ) {
    }

    public function handle(ElasticsearchService $elasticsearch): void
    {
        $elasticsearch->deleteProduct($this->productId);
    }
}
