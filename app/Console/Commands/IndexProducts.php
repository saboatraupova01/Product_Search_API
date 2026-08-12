<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchService;
use Illuminate\Console\Command;
use JsonException;

class IndexProducts extends Command
{
    protected $signature = 'products:index
                            {--batch=1000 : Number of products per bulk request}
                            {--limit=0 : Maximum number of products to index, 0 means all}';

    protected $description = 'Index generated products into Elasticsearch';

    public function handle(): int
    {
        $file = storage_path('app/products.jsonl');

        $batchSize = (int) $this->option('batch');
        $limit = (int) $this->option('limit');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        if ($batchSize < 1) {
            $this->error('Batch size must be greater than 0.');

            return self::FAILURE;
        }

        if ($limit < 0) {
            $this->error('Limit cannot be negative.');

            return self::FAILURE;
        }

        $handle = fopen($file, 'rb');

        if ($handle === false) {
            $this->error("Unable to open {$file}");

            return self::FAILURE;
        }

        /*
         * Resolve our own service manually.
         *
         * We intentionally do not inject Elasticsearch Client
         * into the command constructor because the current
         * Elasticsearch transport configuration does not provide
         * Psr\Http\Client\ClientInterface through Laravel's container.
         */
        $elasticsearch = new ElasticsearchService();

        $this->info('Starting product indexing...');
        $this->info("File: {$file}");
        $this->info("Batch size: {$batchSize}");

        if ($limit > 0) {
            $this->info("Limit: {$limit}");
        } else {
            $this->info('Limit: all products');
        }

        $this->newLine();

        $processed = 0;
        $indexed = 0;
        $failed = 0;

        $batch = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            /*
             * Stop when the requested limit is reached.
             */
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            try {
                $product = json_decode(
                    $line,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                $this->error(
                    'Invalid JSON: ' . $e->getMessage()
                );

                $failed++;

                continue;
            }

            if (! is_array($product)) {
                $this->error('Invalid product data.');

                $failed++;

                continue;
            }

            if (! isset($product['id'])) {
                $this->error('Product does not contain an ID.');

                $failed++;

                continue;
            }

            $batch[] = $product;
            $processed++;

            /*
             * Send batch to Elasticsearch.
             */
            if (count($batch) >= $batchSize) {
                [$batchIndexed, $batchFailed] =
                    $this->indexBatch($elasticsearch, $batch);

                $indexed += $batchIndexed;
                $failed += $batchFailed;

                $this->info(
                    'Processed: ' . number_format($processed) .
                    ' | Indexed: ' . number_format($indexed) .
                    ' | Failed: ' . number_format($failed)
                );

                $batch = [];
            }
        }

        fclose($handle);

        /*
         * Send remaining products.
         */
        if ($batch !== []) {
            [$batchIndexed, $batchFailed] =
                $this->indexBatch($elasticsearch, $batch);

            $indexed += $batchIndexed;
            $failed += $batchFailed;
        }

        $this->newLine();

        $this->info('Indexing completed.');
        $this->info('Processed: ' . number_format($processed));
        $this->info('Indexed: ' . number_format($indexed));
        $this->info('Failed: ' . number_format($failed));

        /*
         * Check actual Elasticsearch document count.
         */
        $this->newLine();

        try {
            $response = $elasticsearch->count();

            $count = $response['count'] ?? 0;

            $this->info(
                'Documents in Elasticsearch: ' .
                number_format($count)
            );

            if ($limit > 0) {
                $this->info(
                    'Expected documents added in this run: ' .
                    number_format($indexed)
                );
            }
        } catch (\Throwable $e) {
            $this->error(
                'Unable to check Elasticsearch document count: ' .
                $e->getMessage()
            );

            return self::FAILURE;
        }

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    /**
     * Send one batch to Elasticsearch.
     *
     * @return array{int, int}
     */
    private function indexBatch(
        ElasticsearchService $elasticsearch,
        array $products
    ): array {
        try {
            $response = $elasticsearch->bulkInsert($products);

            $items = $response['items'] ?? [];

            $indexed = 0;
            $failed = 0;

            foreach ($items as $item) {
                $result = $item['index'] ?? [];

                if (isset($result['error'])) {
                    $failed++;

                    $this->error(
                        'Product ID ' .
                        ($result['_id'] ?? 'unknown') .
                        ' failed: ' .
                        json_encode(
                            $result['error'],
                            JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES
                        )
                    );

                    continue;
                }

                $indexed++;
            }
            return [$indexed, $failed];
        } catch (\Throwable $e) {
            $this->error(
                'Bulk request failed: ' .
                $e->getMessage()
            );

            return [0, count($products)];
        }
    }
}
