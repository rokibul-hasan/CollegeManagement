<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageSection>
 */
class PageSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => 'text',
            'width' => 'full',
            'anchor' => null,
            'data' => ['heading' => fake()->sentence(3), 'body' => fake()->paragraph()],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
