<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group company
     */
    public function testCanUserCreateCar()
    {
        $user    = factory(User::class)->create([
            'email_verified_at' => Carbon::now()
        ]);
        $company = factory(Company::class)->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('cars.index', app()->getLocale()));
        $response->assertStatus(200);

        $car = [
            'name'    => 'test car',
            'mark_id' => 2,
        ];

        $this->actingAs($user)->post(route('cars.store',
            array_merge([app()->getLocale()], $car)
        ));

        $this->assertDatabaseHas('cars', ['name' => $car['name']]);
    }

    /**
     * @group company
     */
    public function testCanUserDestroyCar()
    {
        $user             = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $company          = factory(Company::class)->create(['owner_id' => $user->id]);
        $car              = factory(Car::class, 5)->create(['company_id' => $company->id]);
        $user->company_id = $company->id;

        auth()->login($user);

        $response = $this->actingAs($user)->delete(route('cars.destroy',
            [
                'locale' => app()->getLocale(),
                'car'    => Car::find(2),
            ]
        ));

        $this->assertDatabaseCount('cars', 4);
    }

    /**
     * @group company
     */
    public function testInvalidUserDestroyCarAnotherUser()
    {
        $user                    = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $userAnother             = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $company                 = factory(Company::class)->create(['owner_id' => $user->id]);
        $companyAnother          = factory(Company::class)->create(['owner_id' => $userAnother->id]);
        $cars                    = factory(Car::class, 5)->create(['company_id' => $company->id]);
        $carsAnother             = factory(Car::class, 5)->create(['company_id' => $companyAnother->id]);
        $user->company_id        = $company->id;
        $userAnother->company_id = $companyAnother->id;

        auth()->login($user);

        $response = $this->actingAs($user)->delete(route('cars.destroy',
            [
                'locale' => app()->getLocale(),
                'car'    => Car::find(6),
            ]
        ));

        $this->assertDatabaseHas('cars', ['id' => Car::find(6)->id]);
        $this->assertDatabaseCount('cars', 10);
    }

    /**
     * @group company
     */
    public function testCanUserDestroyManyCar()
    {
        $user             = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $company          = factory(Company::class)->create(['owner_id' => $user->id]);
        $cars             = factory(Car::class, 5)->create(['company_id' => $company->id]);
        $user->company_id = $company->id;

        auth()->login($user);

        $cars = $cars->map(fn($car) => $car->id)->toArray();

        $response = $this->actingAs($user)->delete(route('cars.destroy.many',
            array_merge(['locale' => app()->getLocale()], ['action' => $cars]),

        ));

        $this->assertDatabaseCount('cars', 0);
    }

    /**
     * @group company
     */
    public function testInvalidUserDestroyManyCarAnotherUser()
    {
        $user                    = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $userAnother             = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $company                 = factory(Company::class)->create(['owner_id' => $user->id]);
        $companyAnother          = factory(Company::class)->create(['owner_id' => $userAnother->id]);
        $cars                    = factory(Car::class, 5)->create(['company_id' => $company->id]);
        $carsAnother             = factory(Car::class, 5)->create(['company_id' => $companyAnother->id]);
        $user->company_id        = $company->id;
        $userAnother->company_id = $companyAnother->id;

        auth()->login($user);

        $carsAnother = $carsAnother->map(fn($car) => $car->id)->toArray();

        $response = $this->actingAs($user)->delete(route('cars.destroy.many',
            array_merge(['locale' => app()->getLocale()], ['action' => $carsAnother]),
        ));

        $this->assertDatabaseHas('cars', ['id' => Car::find(6)->id]);
        $this->assertDatabaseCount('cars', 10);
    }
}
