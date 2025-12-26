<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Seed database for each test run to ensure Passport clients and core data exist.
     */
    protected bool $seed = true;

    /**
     * Use the main DatabaseSeeder which seeds PassportTestSeeder and BeautyBooking data.
     */
    protected string $seeder = DatabaseSeeder::class;
}
