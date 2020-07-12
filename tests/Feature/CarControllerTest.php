<?php

namespace Tests\Feature;

use App\Models\Car\Car;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\traits\TestUser;
use Tests\TestCase;

class CarControllerTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;
    use TestUser;

    public User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->runDatabaseMigrations();
        $this->createTestUser();

        $this->user = User::first();
        $this->be($this->user); // login
    }

    /**
     * @group car
     */
    public function test_can_user_use_search_filter()
    {
        $this
            ->get(route('cars.index', [
                'locale' => app()->getLocale(),
                'search' => 'tgx'
            ]))
            ->assertStatus(200);
    }

    /**
     * @group car
     */
    public function test_can_user_create_car()
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->get(route('cars.index', app()->getLocale()))
            ->assertStatus(200);

        $data = [
            'name'    => 'test car',
            'mark_id' => 2,
            'image'   => new UploadedFile(
                resource_path('images/company/register_bg.jpg'),
                'register_bg.jpf',
                'image/jpeg',
                null,
                true
            )
        ];

        $this
            ->actingAs($this->user)
            ->post(route('cars.store', app()->getLocale()), $data)
            ->getContent();

        $this->assertDatabaseHas('cars', ['name' => $data['name']]);
    }

    /**
     * @group car
     */
    public function test_can_user_destroy_car_softdelete()
    {
        $this
            ->get(route('cars.create', app()->getLocale()))
            ->assertStatus(200);

        $this
            ->actingAs($this->user)
            ->delete(route('cars.destroy', [
                'locale' => app()->getLocale(),
                'car'    => Car::first(),
            ]));

        $this->assertSoftDeleted('cars', ['id' => 1]);
    }

    /**
     * @group car
     */
    public function test_cannot_user_destroy_car_another_user()
    {
        $userAnother = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $companyAnother = factory(Company::class)->create(['owner_id' => $userAnother->id]);
        factory(Car::class, 1)->create(['company_id' => $companyAnother->id]);

        $userAnother->company_id = $companyAnother->id;

        $this->actingAs($this->user)
            ->delete(route('cars.destroy', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(2),
            ]));

        $this->assertDatabaseHas('cars', ['id' => Car::find(2)->id]);
        $this->assertDatabaseCount('cars', 2);
    }

    /**
     * @group car
     */
    public function test_can_user_destroy_many_cars_softdelete()
    {
        factory(Car::class, 2)->create(['company_id' => $this->user->company_id]);

        $this
            ->actingAs($this->user)
            ->delete(route('cars.destroy.many', [
                'locale' => app()->getLocale(),
                'action' => ['2,3']
            ]));

        for ($i = 2; $i <= 3; $i++) {
            $this->assertSoftDeleted('cars', ['id' => $i]);
        }
    }

    /**
     * @group car
     */
    public function test_cannot_user_destroy_many_cars_another_user()
    {
        $userAnother = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $companyAnother = factory(Company::class)->create(['owner_id' => $userAnother->id]);
        factory(Car::class, 2)->create(['company_id' => $companyAnother->id]);

        $userAnother->company_id = $companyAnother->id;

        $this
            ->actingAs($this->user)
            ->delete(route('cars.destroy.many', [
                'locale' => app()->getLocale(),
                'action' => ['2,3']
            ]));

        for ($i = 2; $i <= 3; $i++) {
            $this->assertDatabaseHas('cars', ['id' => $i]);
        }
        $this->assertDatabaseCount('cars', 3);
    }

    /**
     * @group car
     */
    public function test_can_user_update_car()
    {
        Storage::fake('public');

        $this
            ->get(route('cars.edit', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(1)
            ]))->assertStatus(200);

        $data = [
            'name'    => 'NEW TEST NAME',
            'year'    => 2020,
            'mark_id' => 2,
            'image'   => new UploadedFile(
                resource_path('images/company/register_bg.jpg'),
                'register_bg.jpf',
                'image/jpeg',
                null,
                true
            )
        ];

        $this
            ->patch(route('cars.update', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(1)
            ]), $data);

        $this->assertSame($data['name'], Car::find(1)->name);
        $this->assertSame((int)$data['year'], (int)Car::find(1)->year);
        $this->assertSame((int)$data['mark_id'], (int)Car::find(1)->mark_id);
    }

    /**
     * @group car
     */
    public function test_cannot_user_update_car_another_user()
    {
        $userAnother = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $companyAnother = factory(Company::class)->create(['owner_id' => $userAnother->id]);
        factory(Car::class, 1)->create(['company_id' => $companyAnother->id]);

        $userAnother->company_id = $companyAnother->id;

        Storage::fake('public');

        $this
            ->get(route('cars.edit', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(2)
            ]))->assertStatus(404);

        $data = [
            'name'    => 'NEW TEST NAME',
            'year'    => 2020,
            'mark_id' => 2,
            'image'   => new UploadedFile(
                resource_path('images/company/register_bg.jpg'),
                'register_bg.jpf',
                'image/jpeg',
                null,
                true
            )
        ];

        $this
            ->patch(route('cars.update', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(2)
            ]), $data)
            ->assertStatus(302);
    }

    /**
     * @group car
     */
    public function test_can_user_see_car_show_page()
    {
        $this
            ->get(route('cars.show', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(1)
            ]))
            ->assertStatus(200);
    }

    /**
     * @group car
     */
    public function test_cannot_user_see_car_another_user()
    {
        $userAnother = factory(User::class)->create(['email_verified_at' => Carbon::now()]);
        $companyAnother = factory(Company::class)->create(['owner_id' => $userAnother->id]);
        factory(Car::class, 1)->create(['company_id' => $companyAnother->id]);

        $userAnother->company_id = $companyAnother->id;

        $this
            ->get(route('cars.show', [
                'locale' => app()->getLocale(),
                'car'    => Car::find(2)
            ]))
            ->assertStatus(404);
    }
}
