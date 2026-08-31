<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles, permissions, and the default admin/cashier users.
        $this->call(RolePermissionSeeder::class);

        // 2. Default counter customer (sales fall back to this one).
        Customer::updateOrCreate(
            ['name' => 'Walk-in Customer'],
            ['phone' => null, 'status' => 'active']
        );

        // 3. CCTV catalog: categories, products with photos, prices,
        //    suppliers, units and two FIFO cost layers per product.
        $this->command->info('Seeding the CCTV catalog ...');
        Artisan::call('app:seed-cctv-catalog');
        $this->command->line(trim(Artisan::output()));
    }
}
