<?php

namespace Database\Factories;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityImage>
 */
class ActivityImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_activity_id' => BusinessActivity::factory(),
            'image_path' => 'activities/'.fake()->unique()->lexify('??????').'.jpg',
            'caption' => null,
            'sort_order' => 0,
        ];
    }
}
