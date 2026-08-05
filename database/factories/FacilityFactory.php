<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        $name = 'OŠ '.fake()->unique()->lastName();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'ownership' => 'skola',
            'address' => fake()->streetAddress(),
            'lat' => fake()->latitude(43.30, 43.34),
            'lng' => fake()->longitude(21.88, 21.95),
        ];
    }
}
