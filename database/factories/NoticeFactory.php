<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notice_category_id' => NoticeCategory::factory(),
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'published_on' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'is_published' => true,
            'show_in_popup' => false,
            'is_pinned' => false,
        ];
    }
}
