<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\traits\TestUser;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;
    use TestUser;

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDatabaseMigrations();
        $this->createTestUser();

        $this->user = User::find(2);
        $this->be($this->user); // login
    }

    /**
     * @group company
     */
    public function test_can_user_register_company()
    {
        $user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);

        $companyData = [
            'locale'     => app()->getLocale(),
            'country_id' => 3,
            'title'      => 'TestCompany - 1'
        ];

        $this
            ->actingAs($user)
            ->get(route('company_register', app()->getLocale()))
            ->assertStatus(200);

        $this->actingAs($user)->post(route('company_register', $companyData));
        $this->assertDatabaseHas('companies', ['title' => $companyData['title']]);
        $this->assertEquals(auth()->user()->company_id, Company::find(2)->id);
    }

    /**
     * @group company
     */
    public function test_cannot_user_with_company_register_company()
    {
        $user = User::find(1);

        $companyData = [
            'locale'     => app()->getLocale(),
            'country_id' => 3,
            'title'      => 'TestCompany - 1'
        ];

        $this->actingAs($user)
            ->get(route('company_register', app()->getLocale()))
            ->assertStatus(302);

        $this->actingAs($user)->post(route('company_register', $companyData));
        $this->assertDatabaseMissing('companies', ['title' => $companyData['title']]);
        $this->assertDatabaseCount('companies', 1);
    }
}
