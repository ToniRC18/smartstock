<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SmartStockSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ClientContract::truncate();
        Product::truncate();
        Client::truncate();
        Schema::enableForeignKeyConstraints();

        $this->seedClients(base_path('database/seeders/data/tabla_clientes.csv'));
        $this->seedProducts(base_path('database/seeders/data/productos.csv'));
        $this->seedContracts(base_path('database/seeders/data/contratos_clientes.csv'));
    }

    private function seedClients(string $path): void
    {
        foreach ($this->csvRows($path) as $row) {
            [$id, $name] = $row;
            Client::create([
                'id' => (int) $id,
                'name' => $name,
            ]);
        }
    }

    private function seedProducts(string $path): void
    {
        foreach ($this->csvRows($path) as $row) {
            [$id, $name, $stockCurrent, $stockMinimum] = $row;
            Product::create([
                'id' => (int) $id,
                'name' => $name,
                'stock_current' => (int) $stockCurrent,
                'stock_minimum' => (int) $stockMinimum,
            ]);
        }
    }

    private function seedContracts(string $path): void
    {
        foreach ($this->csvRows($path) as $row) {
            [$id, $clientId, $productId, $limit, $current, $inactive] = $row;
            ClientContract::create([
                'id' => (int) $id,
                'client_id' => (int) $clientId,
                'product_id' => (int) $productId,
                'card_limit_amount' => (int) $limit,
                'card_current_amount' => (int) $current,
                'card_inactive_amount' => (int) $inactive,
            ]);
        }
    }

    /**
     * Generator that yields rows from a CSV, skipping the header.
     */
    private function csvRows(string $path): \Generator
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("No se pudo abrir el archivo CSV: {$path}");
        }

        $isHeader = true;
        while (($row = fgetcsv($handle)) !== false) {
            if ($isHeader) {
                $isHeader = false;
                continue;
            }
            if ($row === [null] || $row === false) {
                continue;
            }
            yield $row;
        }

        fclose($handle);
    }
}
