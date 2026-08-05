<?php

namespace Database\Factories;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Models\Court;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    protected $model = Court::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'facility_id' => Facility::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'type' => fake()->randomElement(CourtType::cases()),
            'surface' => fake()->randomElement(['beton', 'tartan', 'veštačka trava']),
            'dimensions' => '28 × 15 m',
            'has_lighting' => fake()->boolean(),
            'access' => CourtAccess::Public,
            'lat' => fake()->latitude(43.30, 43.34),
            'lng' => fake()->longitude(21.88, 21.95),
            'is_active' => true,
        ];
    }
}
