<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Notification;
use Tests\TestCase;
use Tests\TestClasses\TestEmailVerificationNotification;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    const ROUTE_PASSWORD_EMAIL        = 'password.email';
    const ROUTE_PASSWORD_REQUEST      = 'password.request';
    const ROUTE_PASSWORD_RESET        = 'password.reset';
    const ROUTE_PASSWORD_RESET_SUBMIT = 'password.reset';

    const USER_ORIGINAL_PASSWORD = 'secret';

    public array $userData = [
        'first_name'            => 'Test',
        'last_name'             => 'Testovich',
        'gender'                => 'male',
        'email'                 => 'test@mail.ru',
        'password'              => 'test-password',
        'password_confirmation' => 'test-password',
    ];

    /**
     * @group auth
     */
    public function testCanUserRegister()
    {
        $response = $this->get(route('register', app()->getLocale()));
        $response->assertStatus(200);

        $response = $this->post(route('register',
            array_merge(['locale' => app()->getLocale()], $this->userData)
        ));
        $response
            ->assertStatus(302)
            ->assertRedirect('/');

        $user = [
            'first_name' => $this->userData['first_name'],
            'last_name'  => $this->userData['last_name'],
            'email'      => $this->userData['email']
        ];
        $this->assertDatabaseHas('users', $user);
    }

    /**
     * @group auth
     */
    public function testCanWrongUserRegister()
    {
        $userWrongData = [
            'first_name' => 'Wrong',
            'last_name'  => 'Wrongovich',
            'email'      => 'test@wrong.test',
            'password'   => 'wrong-password'
        ];

        $response = $this->get(route('register', app()->getLocale()));
        $response->assertStatus(200);


        $response = $this->post(route('register',
            array_merge(['locale'], $userWrongData)
        ));

        $response
            ->assertStatus(302)
            ->assertRedirect(route('register', app()->getLocale()));

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @group auth
     */
    public function testCanUserWithoutVerifiesToGetDashboard()
    {
        $this->testCanUserRegister();

        $response = $this->get(route('dashboard.index', app()->getLocale()));
        $response
            ->assertStatus(302)
            ->assertLocation(route('verification.notice', app()->getLocale()));

        $response = $this->get(route('company_register', app()->getLocale()));
        $response
            ->assertStatus(302)
            ->assertLocation(route('verification.notice', app()->getLocale()));
    }

    /**
     * @group auth
     */
    public function testCanUserVerifyEmail()
    {
        /**
         * @var TestEmailVerificationNotification
         */
        $notification = new TestEmailVerificationNotification();

        $user = factory(User::class)->create(['email_verified_at' => null]);

        $uri = $notification->verificationUrl($user);

        $this->assertSame(null, $user->email_verified_at);

        $this->actingAs($user)->get($uri);

        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * @group auth
     */
    public function testRedirectToRegisterCompanydVerifiedUser()
    {
        $this->testCanUserVerifyEmail();
        $response = $this->get(route('dashboard.index', [
            'locale' => app()->getLocale(),
        ]));

        $response
            ->assertStatus(302)
            ->assertLocation(route('company_register', app()->getLocale()));
    }

    /**
     * @group auth
     */
    public function testShowPasswordResetRequestPage()
    {
        Notification::fake();

        $this
            ->get(route(static::ROUTE_PASSWORD_REQUEST, app()->getLocale()))
            ->assertSuccessful()
            ->assertSee(__('auth.reset password'))
            ->assertSee(__('user.general.email'))
            ->assertSee(__('auth.send password link'));
    }

//    /**
//     * @group auth
//     */
//    public function testSubmitPasswordResetRequestInvalidEmail()
//    {
//        $this
//            ->followingRedirects()
//            ->from(route(static::ROUTE_PASSWORD_REQUEST, app()->getLocale()))
//            ->post(route(static::ROUTE_PASSWORD_EMAIL, app()->getLocale()), [
//                'email' => Str::random(),
//            ])
//            ->assertSuccessful()
//            ->assertSee(__('validation.email', [
//                'attribute' => 'email',
//            ]));
//    }
//
//    /**
//     * @group auth
//     */
//    public function testSubmitPasswordResetRequestEmailNotFound()
//    {
//        $this
//            ->followingRedirects()
//            ->from(route(static::ROUTE_PASSWORD_REQUEST, app()->getLocale()))
//            ->post(route(static::ROUTE_PASSWORD_EMAIL, app()->getLocale()), [
//                'email' => $this->faker->unique()->safeEmail,
//            ])
//            ->assertSuccessful()
//            ->assertSee(__('passwords.user'));
//    }

    /**
     * @group auth
     * @throws \Exception
     */
    public function testSubmitPasswordResetRequest()
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $this
            ->followingRedirects()
            ->from(route(static::ROUTE_PASSWORD_REQUEST, app()->getLocale()))
            ->post(route(static::ROUTE_PASSWORD_EMAIL, app()->getLocale()), [
                'email' => $user->email,
            ])
            ->assertSuccessful()
            ->assertSee(__('auth.send password link'));
    }

    /**
     * @group auth
     * @throws \Exception
     */
    public function testShowPasswordResetPage()
    {
        $user = factory(User::class)->create();

        $token = Password::broker()->createToken($user);

        $this
            ->get(route(static::ROUTE_PASSWORD_RESET, [
                'locale' => app()->getLocale(),
                'token'  => $token,
            ]))
            ->assertSuccessful()
            ->assertSee(__('auth.reset password'))
            ->assertSee(__('user.general.email'))
            ->assertSee(__('user.general.password'))
            ->assertSee(__('user.general.password_confirm'));
    }
}
