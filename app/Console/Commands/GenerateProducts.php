<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateProducts extends Command
{
    protected $signature = 'products:generate
                            {--count=100000 : Number of products to generate}';

    protected $description = 'Generate products for the Elasticsearch catalog';

    /**
     * 20 required categories.
     */
    private array $categories = [
        'Smartphones',
        'Tablets',
        'Headphones',
        'Bluetooth Speakers',
        'Chargers',
        'Power Banks',
        'USB Cables',
        'Phone Cases',
        'Screen Protectors',
        'Phone Holders',
        'Smartwatches',
        'Car Accessories',
        'Laptops',
        'Monitors',
        'Keyboards',
        'Mice',
        'Webcams',
        'Gaming Accessories',
        'Storage Devices',
        'Smart Home',
    ];

    /**
     * 50 brands.
     *
     * The assignment requires 50 brands.
     * They are used as catalog values for filtering,
     * aggregations and full-text search.
     */
    private array $brands = [
        'Apple',
        'Samsung',
        'Xiaomi',
        'Google',
        'Sony',
        'JBL',
        'Anker',
        'UGREEN',
        'Baseus',
        'Belkin',
        'Spigen',
        'ESR',
        'Huawei',
        'Lenovo',
        'ASUS',
        'Acer',
        'Dell',
        'HP',
        'MSI',
        'Logitech',
        'Razer',
        'Corsair',
        'Kingston',
        'SanDisk',
        'Seagate',
        'Western Digital',
        'TP-Link',
        'Tenda',
        'Philips',
        'Panasonic',
        'OnePlus',
        'Nothing',
        'Motorola',
        'OPPO',
        'Realme',
        'Vivo',
        'Honor',
        'Nokia',
        'Beats',
        'Marshall',
        'Bose',
        'Sennheiser',
        'HyperX',
        'SteelSeries',
        'Gigabyte',
        'BenQ',
        'ViewSonic',
        'AOC',
        'ZTE',
        'Roborock',
    ];

    /**
     * Product models for each category.
     *
     * We deliberately keep this reasonably compact.
     * The generator creates 100,000 different records
     * from these combinations.
     */
    private array $models = [
        'Smartphones' => [
            'iPhone 15',
            'iPhone 15 Pro',
            'iPhone 16',
            'iPhone 16 Pro',
            'Galaxy S24',
            'Galaxy S24 Ultra',
            'Galaxy A55',
            'Redmi Note 13 Pro',
            'Xiaomi 14',
            'Pixel 8',
            'Pixel 8 Pro',
            'OnePlus 12',
        ],

        'Tablets' => [
            'iPad 10',
            'iPad Air',
            'iPad Pro 11',
            'Galaxy Tab S9',
            'Galaxy Tab A9',
            'Xiaomi Pad 6',
            'Lenovo Tab P12',
            'Huawei MatePad',
        ],

        'Headphones' => [
            'WH-CH720N',
            'WH-1000XM5',
            'Tune 770NC',
            'AirPods Pro 2',
            'QuietComfort',
            'Momentum 4',
            'Studio Pro',
            'Cloud III Wireless',
        ],

        'Bluetooth Speakers' => [
            'Flip 6',
            'Charge 5',
            'Xtreme 3',
            'SoundLink Flex',
            'Marshall Emberton',
            'Portable Speaker',
        ],

        'Chargers' => [
            'Nano Charger',
            'Nexode Charger',
            'Super Si Charger',
            'Fast Charger',
            'Wall Charger',
            'GaN Charger',
        ],

        'Power Banks' => [
            'PowerCore',
            'Prime Power Bank',
            'Blade Power Bank',
            'MagGo Power Bank',
            'Portable Power Bank',
        ],

        'USB Cables' => [
            'USB-C Cable',
            'USB-C to USB-C Cable',
            'USB-C to Lightning Cable',
            'BoostCharge Cable',
            'Fast Charging Cable',
        ],

        'Phone Cases' => [
            'Liquid Air Case',
            'Classic Hybrid Case',
            'Armor Case',
            'Clear Protective Case',
            'Silicone Case',
            'Magnetic Case',
        ],

        'Screen Protectors' => [
            'Tempered Glass Screen Protector',
            'Armorite Screen Protector',
            'Privacy Screen Protector',
            'HD Screen Protector',
        ],

        'Phone Holders' => [
            'Air Vent Phone Holder',
            'Magnetic Phone Holder',
            'Dashboard Phone Holder',
            'Car Phone Holder',
            'Desk Phone Stand',
        ],

        'Smartwatches' => [
            'Apple Watch Series 9',
            'Galaxy Watch 6',
            'Watch GT 4',
            'Smart Watch Pro',
            'Fitness Watch',
        ],

        'Car Accessories' => [
            'Car Phone Holder',
            'Car Charger',
            'Wireless Car Charger',
            'Car Bluetooth Adapter',
            'Dashboard Mount',
        ],

        'Laptops' => [
            'MacBook Air',
            'MacBook Pro',
            'Galaxy Book',
            'ThinkPad E14',
            'IdeaPad 5',
            'ROG Strix',
            'VivoBook 15',
            'Aspire 5',
            'Inspiron 15',
            'Pavilion 15',
        ],

        'Monitors' => [
            '24-inch Full HD Monitor',
            '27-inch Full HD Monitor',
            '27-inch QHD Monitor',
            '32-inch 4K Monitor',
            'Gaming Monitor',
            'UltraWide Monitor',
        ],

        'Keyboards' => [
            'Mechanical Keyboard',
            'Wireless Keyboard',
            'Gaming Keyboard',
            'RGB Mechanical Keyboard',
            'Compact Keyboard',
            'Office Keyboard',
        ],

        'Mice' => [
            'Wireless Mouse',
            'Gaming Mouse',
            'Ergonomic Mouse',
            'Bluetooth Mouse',
            'RGB Gaming Mouse',
            'Office Mouse',
        ],

        'Webcams' => [
            'Full HD Webcam',
            '4K Webcam',
            'USB Webcam',
            'Streaming Webcam',
            'Conference Webcam',
        ],

        'Gaming Accessories' => [
            'Gaming Headset',
            'Gaming Controller',
            'Gaming Mouse Pad',
            'Gamepad',
            'RGB Gaming Stand',
            'Gaming Microphone',
        ],

        'Storage Devices' => [
            'Portable SSD',
            'External HDD',
            'USB Flash Drive',
            'NVMe SSD',
            'MicroSD Card',
            'External SSD',
        ],

        'Smart Home' => [
            'Smart Plug',
            'Smart Bulb',
            'Security Camera',
            'Smart Speaker',
            'Robot Vacuum',
            'Smart Hub',
        ],
    ];

    /**
     * Generic descriptions by category.
     */
    private array $descriptions = [
        'Smartphones' =>
            'Smartphone with a high-quality display, reliable performance and modern connectivity. Suitable for communication, photography, entertainment and everyday productivity.',

        'Tablets' =>
            'Tablet with a sharp display, responsive performance and long battery life. Suitable for studying, work, multimedia and everyday tasks.',

        'Headphones' =>
            'Wireless headphones designed for comfortable listening. They provide clear audio, reliable connectivity and a practical design for everyday use.',

        'Bluetooth Speakers' =>
            'Portable Bluetooth speaker designed to provide clear and powerful sound. Suitable for home use, travel and outdoor activities.',

        'Chargers' =>
            'Compact charger designed for smartphones, tablets and other compatible devices. Provides reliable and efficient charging for everyday use.',

        'Power Banks' =>
            'Portable power bank designed to provide additional battery capacity for compatible devices. Suitable for travel and everyday use.',

        'USB Cables' =>
            'Durable cable designed for charging and data transfer between compatible devices. Suitable for everyday use at home, work or while travelling.',

        'Phone Cases' =>
            'Protective phone case designed to improve grip and protect a compatible smartphone from everyday scratches and minor impacts.',

        'Screen Protectors' =>
            'Protective screen accessory designed to reduce scratches, fingerprints and everyday display damage while maintaining touchscreen responsiveness.',

        'Phone Holders' =>
            'Adjustable phone holder designed to keep a smartphone securely positioned at a desk, in a vehicle or in another convenient location.',

        'Smartwatches' =>
            'Modern smartwatch combining notifications, activity tracking and connected-device features. Suitable for everyday activity and communication.',

        'Car Accessories' =>
            'Practical car accessory designed to improve convenience and usability while driving. Suitable for everyday vehicle use.',

        'Laptops' =>
            'Portable laptop designed for work, study, entertainment and everyday productivity. Combines performance, display quality and practical connectivity.',

        'Monitors' =>
            'Computer monitor designed for work, entertainment and multimedia. Provides a clear image and practical connectivity for everyday use.',

        'Keyboards' =>
            'Computer keyboard designed for comfortable typing and everyday productivity. Suitable for office work, gaming and home use.',

        'Mice' =>
            'Computer mouse designed for precise and comfortable navigation. Suitable for office work, gaming and everyday computer use.',

        'Webcams' =>
            'USB webcam designed for video calls, streaming and online meetings. Provides convenient connectivity and clear video for everyday communication.',

        'Gaming Accessories' =>
            'Gaming accessory designed to improve the gaming experience. Suitable for PC and console gaming, entertainment and competitive play.',

        'Storage Devices' =>
            'Storage device designed for saving, transferring and backing up digital files. Suitable for computers, mobile devices and everyday data management.',

        'Smart Home' =>
            'Smart home device designed to improve convenience, automation and connected-device control in modern homes.',
    ];

    public function handle(): int
    {
        $count = (int) $this->option('count');

        if ($count < 1) {
            $this->error('Count must be greater than 0.');

            return self::FAILURE;
        }

        $path = storage_path('app/products.jsonl');

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            $this->error("Unable to create {$path}");

            return self::FAILURE;
        }

        $this->info("Generating {$count} products...");
        $this->info('Categories: ' . count($this->categories));
        $this->info('Brands: ' . count($this->brands));
        $this->info("File: {$path}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        for ($id = 1; $id <= $count; $id++) {
            $product = $this->generateProduct($id);

            fwrite(
                $handle,
                json_encode(
                    $product,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ) . PHP_EOL
            );

            $progressBar->advance();
        }

        fclose($handle);

        $progressBar->finish();
        $this->newLine(2);

        $size = filesize($path);

        $this->info('Products generated successfully.');
        $this->info('Products: ' . number_format($count));
        $this->info('Categories: ' . count($this->categories));
        $this->info('Brands: ' . count($this->brands));
        $this->info('File size: ' . $this->formatBytes($size));

        return self::SUCCESS;
    }

    private function generateProduct(int $id): array
    {
        $category = $this->categories[
        array_rand($this->categories)
        ];

        $brand = $this->brands[
        array_rand($this->brands)
        ];

        $model = $this->models[$category][
        array_rand($this->models[$category])
        ];

        $variant = $this->generateVariant($category);

        $color = $this->generateColor();

        $nameParts = [
            $brand,
            $model,
        ];

        if ($variant !== null) {
            $nameParts[] = $variant;
        }

        if ($color !== null) {
            $nameParts[] = $color;
        }

        $name = implode(' ', $nameParts);

        $price = $this->generatePrice($category);

        $quantity = random_int(0, 500);

        $rating = round(
            random_int(30, 50) / 10,
            1
        );

        $isActive = random_int(1, 100) <= 90;

        /*
         * Products without stock should normally be inactive.
         */
        if ($quantity === 0) {
            $isActive = false;
        }

        $createdAt = Carbon::now()
            ->subDays(random_int(0, 365))
            ->format('Y-m-d');

        return [
            'id' => $id,
            'name' => $name,
            'description' => $this->descriptions[$category],
            'category' => $category,
            'brand' => $brand,
            'price' => $price,
            'quantity' => $quantity,
            'rating' => $rating,
            'is_active' => $isActive,
            'created_at' => $createdAt,
        ];
    }

    private function generateVariant(string $category): ?string
    {
        return match ($category) {
            'Smartphones' => $this->randomValue([
                '128GB',
                '256GB',
                '512GB',
                '1TB',
            ]),

            'Tablets' => $this->randomValue([
                '64GB Wi-Fi',
                '128GB Wi-Fi',
                '256GB Wi-Fi',
                '256GB 5G',
                '512GB 5G',
            ]),

            'Headphones' => $this->randomValue([
                'Wireless',
                'Bluetooth',
                'ANC',
            ]),

            'Bluetooth Speakers' => $this->randomValue([
                'Standard',
                'Portable',
                'Waterproof',
            ]),

            'Chargers' => $this->randomValue([
                '20W',
                '30W',
                '45W',
                '65W',
                '100W',
            ]),

            'Power Banks' => $this->randomValue([
                '10000mAh',
                '20000mAh',
                '30000mAh',
            ]),

            'USB Cables' => $this->randomValue([
                '1m',
                '2m',
                '3m',
                '60W',
                '100W',
            ]),

            'Phone Cases' => $this->randomValue([
                'iPhone 15',
                'iPhone 15 Pro',
                'iPhone 16',
                'Galaxy S24',
                'Galaxy S24 Ultra',
            ]),

            'Screen Protectors' => $this->randomValue([
                'iPhone 15',
                'iPhone 15 Pro',
                'iPhone 16',
                'Galaxy S24',
                'Galaxy S24 Ultra',
            ]),

            'Phone Holders' => $this->randomValue([
                'Standard',
                'Magnetic',
                'Adjustable',
            ]),

            'Smartwatches' => $this->randomValue([
                '40mm',
                '41mm',
                '44mm',
                '45mm',
                '46mm',
            ]),

            'Car Accessories' => $this->randomValue([
                'Standard',
                'Wireless',
                'Fast Charging',
            ]),

            'Laptops' => $this->randomValue([
                '8GB RAM',
                '16GB RAM',
                '32GB RAM',
                '256GB SSD',
                '512GB SSD',
                '1TB SSD',
            ]),

            'Monitors' => $this->randomValue([
                '60Hz',
                '75Hz',
                '120Hz',
                '144Hz',
                '165Hz',
            ]),

            'Keyboards' => $this->randomValue([
                'Wireless',
                'Mechanical',
                'RGB',
                'TKL',
            ]),

            'Mice' => $this->randomValue([
                'Wireless',
                'Bluetooth',
                'RGB',
                '16000 DPI',
            ]),

            'Webcams' => $this->randomValue([
                '720p',
                '1080p',
                '2K',
                '4K',
            ]),

            'Gaming Accessories' => $this->randomValue([
                'USB',
                'Wireless',
                'RGB',
                'Pro',
            ]),

            'Storage Devices' => $this->randomValue([
                '128GB',
                '256GB',
                '512GB',
                '1TB',
                '2TB',
            ]),

            'Smart Home' => $this->randomValue([
                'Wi-Fi',
                'Bluetooth',
                'Smart',
                'Pro',
            ]),

            default => null,
        };
    }

    private function generateColor(): ?string
    {
        return $this->randomValue([
            'Black',
            'White',
            'Blue',
            'Gray',
            'Silver',
            'Green',
            'Red',
            'Purple',
            'Pink',
            'Gold',
        ]);
    }

    private function generatePrice(string $category): int
    {
        return match ($category) {
            'Smartphones' => random_int(2000, 18000),
            'Tablets' => random_int(3000, 15000),
            'Headphones' => random_int(500, 6000),
            'Bluetooth Speakers' => random_int(500, 5000),
            'Chargers' => random_int(150, 2000),
            'Power Banks' => random_int(400, 4000),
            'USB Cables' => random_int(100, 1000),
            'Phone Cases' => random_int(100, 1000),
            'Screen Protectors' => random_int(100, 700),
            'Phone Holders' => random_int(150, 1200),
            'Smartwatches' => random_int(1500, 9000),
            'Car Accessories' => random_int(150, 2000),
            'Laptops' => random_int(5000, 30000),
            'Monitors' => random_int(2000, 20000),
            'Keyboards' => random_int(300, 5000),
            'Mice' => random_int(150, 4000),
            'Webcams' => random_int(300, 5000),
            'Gaming Accessories' => random_int(300, 7000),
            'Storage Devices' => random_int(300, 10000),
            'Smart Home' => random_int(300, 8000),
            default => random_int(100, 10000),
        };
    }

    private function randomValue(array $values): mixed
    {
        return $values[array_rand($values)];
    }

    private function formatBytes(int|false $bytes): string
    {
        if ($bytes === false) {
            return 'unknown';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        }

        return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    }
}
