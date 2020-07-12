<?php

namespace Tests\Feature\traits;

trait TestUser
{
    public function createTestUser(): void
    {
        $this->artisan('db:seed --class=CountriesSeeder');
        $this->artisan('db:seed --class=CarMarkSeeder');
        $this->artisan('db:seed --class=TestUserSeeder');
    }
}
