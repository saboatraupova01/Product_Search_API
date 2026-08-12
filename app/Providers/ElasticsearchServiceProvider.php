<?php

namespace App\Providers;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('elasticsearch', function () {

            return ClientBuilder::create()
                ->setHosts([
                    config('services.elasticsearch.host')
                ])
                ->build();

        });
    }

    public function boot(): void
    {
        //
    }
}
