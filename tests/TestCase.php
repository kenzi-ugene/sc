<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop all tables first
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            Schema::drop($table);
        }
        
        // Run migrations in correct order
        $this->artisan('migrate:fresh', [
            '--path' => [
                'database/migrations/2014_10_12_000000_create_users_table.php',
                'database/migrations/2014_10_12_100000_create_password_resets_table.php',
                'database/migrations/2019_08_19_000000_create_failed_jobs_table.php',
                'database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php',
                'database/migrations/2024_03_19_add_fields_to_users_table.php',
                'database/migrations/2025_05_20_151558_create_products_table.php',
                'database/migrations/2025_05_21_011205_create_carts_table.php',
                'database/migrations/2025_05_23_032338_create_orders_table.php',
            ]
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Use the existing database configuration
        config(['database.connections.mysql.database' => env('DB_DATABASE', 'sc')]);
        
        // Mock the Page model for Voyager
        if (!class_exists(Page::class)) {
            class_alias(Page::class, Page::class);
        }
    }
}
