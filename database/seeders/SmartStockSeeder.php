<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CardRequest;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\ContractAllocation;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SmartStockSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ContractAllocation::truncate();
        Shipment::truncate();
        CardRequest::truncate();
        ClientContract::truncate();
        Product::truncate();
        Client::truncate();
        Schema::enableForeignKeyConstraints();

        $this->seedClients(base_path('database/seeders/data/tabla_clientes.csv'));
        $this->seedProducts(base_path('database/seeders/data/productos.csv'));
        $this->seedContracts(base_path('database/seeders/data/contratos_clientes.csv'));
        $this->ensureBaseProducts();
        $this->seedSyntheticContracts();
        $this->seedAllocations();
        $this->seedSampleRequests();
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
                'card_expired_amount' => random_int(0, max(0, (int) $inactive)),
            ]);
        }
    }

    /**
     * Ensure core product types exist even if the CSV lacks them.
     */
    private function ensureBaseProducts(): void
    {
        $baseProducts = [
            'Combustible',
            'Despensa',
            'Premios',
        ];

        foreach ($baseProducts as $name) {
            Product::firstOrCreate(
                ['name' => $name],
                [
                    'stock_current' => random_int(100, 400),
                    'stock_minimum' => random_int(30, 80),
                ]
            );
        }
    }

    /**
     * Create synthetic contracts per client/product type to have data to show.
     */
    private function seedSyntheticContracts(): void
    {
        $products = Product::whereIn('name', ['Combustible', 'Despensa', 'Premios'])->get();

        Client::all()->each(function (Client $client) use ($products) {
            foreach ($products as $product) {
                $exists = ClientContract::where('client_id', $client->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $limit = random_int(30, 150);
                $inUse = random_int(10, $limit);
                $inactive = random_int(0, max(0, $limit - $inUse));
                $expired = random_int(0, max(0, (int) floor($inactive / 2)));

                ClientContract::create([
                    'client_id' => $client->id,
                    'product_id' => $product->id,
                    'card_limit_amount' => $limit,
                    'card_current_amount' => $inUse,
                    'card_inactive_amount' => $inactive,
                    'card_expired_amount' => $expired,
                ]);
            }
        });
    }

    /**
     * Split each contract into allocations across the three base products (synthetic).
     */
    private function seedAllocations(): void
    {
        $productIds = Product::whereIn('name', ['Combustible', 'Despensa', 'Premios'])->pluck('id')->values();

        ClientContract::all()->each(function (ClientContract $contract) use ($productIds) {
            if ($productIds->isEmpty()) {
                return;
            }

            $splits = function (int $total) {
                $parts = [random_int(0, $total), random_int(0, $total), random_int(0, $total)];
                $sum = array_sum($parts);
                if ($sum === 0) {
                    return [0, 0, 0];
                }
                return array_map(fn ($p) => (int) floor(($p / $sum) * $total), $parts);
            };

            $limits = $splits((int) $contract->card_limit_amount);
            $current = $splits((int) $contract->card_current_amount);
            $inactive = $splits((int) $contract->card_inactive_amount);
            $expired = $splits((int) $contract->card_expired_amount);

            // Adjust last element to ensure sums match totals.
            $fixSum = function (array $values, int $target): array {
                $diff = $target - array_sum($values);
                $values[2] = max(0, $values[2] + $diff);
                return $values;
            };

            $limits = $fixSum($limits, (int) $contract->card_limit_amount);
            $current = $fixSum($current, (int) $contract->card_current_amount);
            $inactive = $fixSum($inactive, (int) $contract->card_inactive_amount);
            $expired = $fixSum($expired, (int) $contract->card_expired_amount);

            foreach ($productIds as $idx => $productId) {
                ContractAllocation::create([
                    'client_contract_id' => $contract->id,
                    'product_id' => $productId,
                    'card_limit_amount' => $limits[$idx] ?? 0,
                    'card_current_amount' => $current[$idx] ?? 0,
                    'card_inactive_amount' => $inactive[$idx] ?? 0,
                    'card_expired_amount' => $expired[$idx] ?? 0,
                ]);
            }
        });
    }

    /**
     * Seed sample card requests and shipments for demo flows.
     */
    private function seedSampleRequests(): void
    {
        $clients = Client::with(['contracts', 'contracts.product'])->take(2)->get();

        foreach ($clients as $client) {
            $contract = $client->contracts->first();
            if (!$contract) {
                continue;
            }

            $productId = $contract->product_id;

            CardRequest::create([
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'product_id' => $productId,
                'reason' => 'new_employee',
                'quantity' => 15,
                'notes' => 'Nuevas altas de personal',
                'status' => 'pending',
            ]);

            $approved = CardRequest::create([
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'product_id' => $productId,
                'reason' => 'expired',
                'quantity' => 10,
                'notes' => 'Reposición por vencimiento',
                'status' => 'approved',
                'admin_note' => 'Procesando reposición',
            ]);

            Shipment::create([
                'card_request_id' => $approved->id,
                'tracking_code' => 'SS-DEMO-' . $client->id,
                'status' => 'en_ruta',
                'eta_date' => now()->addDays(2)->toDateString(),
            ]);

            CardRequest::create([
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'product_id' => $productId,
                'reason' => 'lost',
                'quantity' => 1200,
                'notes' => 'Pedido masivo',
                'status' => 'rejected',
                'admin_note' => 'Excede límite disponible, ajuste solicitado.',
            ]);

            $product = $contract->product;
            if ($product && $product->stock_current > 10) {
                $product->decrement('stock_current', 10);
            }
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
