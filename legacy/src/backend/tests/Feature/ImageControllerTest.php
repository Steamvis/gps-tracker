<?php

namespace Tests\Feature;

use App\Models\Car\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\traits\TestUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImageControllerTest extends TestCase
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

        $this->user = User::find(1);
        $this->be($this->user); // login
    }

    /**
     * @group images
     */
    public function test_can_user_upload_image()
    {
        Storage::fake('public');

        $data = [
            'image' => new UploadedFile(
                resource_path('images/company/register_bg.jpg'),
                'register_bg.jpg',
                'image/jpeg',
                null,
                true
            )
        ];

        $this->actingAs($this->user)
            ->post(route('images.upload', app()->getLocale()), $data)
            ->assertStatus(200);
    }

    /**
     * @group images
     */
    public function test_can_user_destroy_image()
    {
        Storage::fake('public');

        $this->test_can_user_upload_image();

        $imagePath = Storage::disk('public')->allFiles()[0];

        $data = [
            'car'   => Car::first()->id,
            'image' => $imagePath
        ];

        $this->actingAs($this->user)
            ->delete(route('images.destroy', app()->getLocale()), $data)
            ->assertStatus(200);
    }
}
