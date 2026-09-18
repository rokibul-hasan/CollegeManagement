<?php

namespace Database\Factories;

use App\Models\NoticeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NoticeCategory>
 */
class NoticeCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
