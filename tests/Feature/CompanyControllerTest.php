<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group company
     */
    public function testCanUserRegisterCompany()
    {
        $user = factory(User::class)->create(['email_verified_at' => Carbon::now()]);

        $companyData = [
            'country_id' => 3,
            'title'      => 'TestCompany - 1'
        ];

        $response = $this->actingAs($user)->get(route('company_register', app()->getLocale()));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('company_register',
            array_merge([app()->getLocale()], $companyData)
        ));

        $this->assertDatabaseHas('companies', ['title' => $companyData['title']]);
    }

    /**
     * @group company
     */
    public function testRegisterCompanyEqualsUser()
    {
        $this->testCanUserRegisterCompany();

        $this->assertTrue(User::find(1)->id === Company::find(1)->owner->id);
    }
}
