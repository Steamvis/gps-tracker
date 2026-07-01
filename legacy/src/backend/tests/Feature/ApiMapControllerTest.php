<?php

namespace Tests\Feature;

use App\Helpers\Map\SectionGenerator;
use App\Models\Car\Car;
use App\Models\Car\CarPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\Feature\traits\TestUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiMapControllerTest extends TestCase
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
     * @group api-map
     */
    public function test_generate_route()
    {
        $car = Car::find(1);
        $apiCarCode = Str::limit($car->api_code, 10, '') . '_' . $car->id;

        $apiDataStartPoint = [
            'latitude'    => 50.1,
            'longitude'   => 43.54,
            'carInfo'     => $apiCarCode,
            'start_route' => 1,
            'end_route'   => 0,
        ];
        $apiDataMiddlePoint = [
            'latitude'    => 51.71,
            'longitude'   => 43.74,
            'carInfo'     => $apiCarCode,
            'start_route' => 0,
            'end_route'   => 0,
        ];
        $apiDataEndPoint = [
            'latitude'    => 51.9,
            'longitude'   => 43.94,
            'carInfo'     => $apiCarCode,
            'start_route' => 0,
            'end_route'   => 1,
        ];

        $apiDataNewRoute = [
            'latitude'    => 150.9,
            'longitude'   => 70.94,
            'carInfo'     => $apiCarCode,
            'start_route' => 1,
            'end_route'   => 0,
        ];

        $this
            ->post(route('api.gps', $apiDataStartPoint))
            ->assertStatus(200);

        $this
            ->post(route('api.gps', $apiDataMiddlePoint))
            ->assertStatus(200);

        $this
            ->post(route('api.gps', $apiDataEndPoint))
            ->assertStatus(200);

        sleep(1);

        $this
            ->post(route('api.gps', $apiDataNewRoute))
            ->assertStatus(200);

        $this
            ->assertDatabaseHas('cars_points', [
                'id'        => 1,
                'car_id'    => $car->id,
                'route_id'  => 1,
                'latitude'  => $apiDataStartPoint['latitude'],
                'longitude' => $apiDataStartPoint['longitude']
            ])
            ->assertDatabaseHas('cars_points', [
                'id'        => 2,
                'car_id'    => $car->id,
                'route_id'  => 1,
                'latitude'  => $apiDataMiddlePoint['latitude'],
                'longitude' => $apiDataMiddlePoint['longitude']
            ])
            ->assertDatabaseHas('cars_points', [
                'id'        => 3,
                'car_id'    => $car->id,
                'route_id'  => 1,
                'latitude'  => $apiDataEndPoint['latitude'],
                'longitude' => $apiDataEndPoint['longitude']
            ])
            ->assertDatabaseHas('cars_routes', [
                'id'     => 1,
                'car_id' => $car->id,
            ])
            ->assertDatabaseHas('cars_routes', [
                'id'     => 2,
                'car_id' => $car->id,
            ])
            ->assertDatabaseCount('cars_route_sections', 2)
            ->assertDatabaseCount('cars_routes', 2);
    }

    /**
     * @group api-map
     */
    public function test_section_generator_map_exception()
    {
        $car = Car::find(1);
        $apiCarCode = Str::limit($car->api_code, 10, '') . '_' . $car->id;
        $apiDataStartPoint = [
            'latitude'    => 50.1,
            'longitude'   => 43.54,
            'carInfo'     => $apiCarCode,
            'start_route' => 1,
            'end_route'   => 0,
        ];

        $i = 0;
        while ($i <= 4) {
            $this
                ->post(route('api.gps', $apiDataStartPoint))
                ->assertStatus(200);
            $i++;
        }
        $points = CarPoint::all();
        $sectionGenerator = new SectionGenerator();
        $sectionGenerator->generate($points[3], $points[2]);
    }
}
