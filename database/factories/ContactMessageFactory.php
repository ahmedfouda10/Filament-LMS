<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->randomElement(['Enrollment Issue', 'Technical Support', 'Billing Question', 'Course Inquiry', 'Certificate Request']),
            'message' => fake()->paragraph(3),
            'is_read' => fake()->boolean(40),
        ];
    }
}
