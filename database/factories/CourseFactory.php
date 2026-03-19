<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        $price = fake()->randomFloat(2, 500, 3000);

        return [
            'instructor_id' => User::factory()->instructor(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->paragraph(2),
            'description' => fake()->paragraphs(3, true),
            'price' => $price,
            'original_price' => round($price * 1.3, 2),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'language' => 'Arabic & English',
            'is_published' => true,
            'is_featured' => false,
            'is_bundle' => false,
        ];
    }
}
