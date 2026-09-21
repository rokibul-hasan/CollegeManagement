<?php

namespace Database\Factories;

use App\Enums\SiteTemplate;
use App\Models\PageSection;
use App\Models\TemplateSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateSection>
 */
class TemplateSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template' => SiteTemplate::Classic,
            'page_section_id' => PageSection::factory(),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
