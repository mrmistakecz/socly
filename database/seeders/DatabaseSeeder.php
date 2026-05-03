<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Production seeder — no demo data.
     * Users register through the application.
     */
    public function run(): void
    {
        // No seed data in production.
        // All users, posts, and content are created via the application.
    }
}
