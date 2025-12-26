<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            UserSeeder::class,
            PassportTestSeeder::class,
        ]);

        // Dev/Test fixtures for beauty module (local/testing only)
        // داده‌های تست/توسعه ماژول بیوتی (فقط لوکال/تست)
        if (app()->environment(['local', 'development', 'testing'])) {
            $this->call([
                DevTestFixtureSeeder::class,
            ]);
        }
        
        // Seed Beauty Booking module if it's published
        // Seed کردن ماژول رزرو زیبایی در صورت فعال بودن
        if (addon_published_status('BeautyBooking')) {
            $this->call([
                \Modules\BeautyBooking\Database\Seeders\BeautyBookingDatabaseSeeder::class
            ]);
        }
    }
}
