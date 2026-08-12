<?php

namespace App\Observers;

use App\Jobs\DeleteProductFromElasticsearch;
use App\Jobs\SyncProductToElasticsearch;
use App\Models\Product;

class ProductObserver
{
    public function created(Product $product): void
    {
        SyncProductToElasticsearch::dispatch($product->id);
    }

    public function updated(Product $product): void
    {
        SyncProductToElasticsearch::dispatch($product->id);
    }

    public function deleted(Product $product): void
    {
        DeleteProductFromElasticsearch::dispatch($product->id);
    }

    public function restored(Product $product): void
    {
        //
    }

    public function forceDeleted(Product $product): void
    {
        //
    }
}
