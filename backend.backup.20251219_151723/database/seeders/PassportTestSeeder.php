<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;

class PassportTestSeeder extends Seeder
{
    /**
     * Seed Passport clients for testing.
     *
     * @return void
     */
    public function run(): void
    {
        $repo = app(ClientRepository::class);

        if (!DB::table('oauth_clients')->where('personal_access_client', 1)->exists()) {
            $repo->createPersonalAccessClient(null, 'Personal Access Client', config('app.url'));
        }

        if (!DB::table('oauth_clients')->where('password_client', 1)->exists()) {
            $repo->createPasswordGrantClient(null, 'Password Grant Client', config('app.url'));
        }
    }
}

