<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'user_id' => 3, // Kiểm tra xem user với ID 3 đã tồn tại
            'category_id' => rand(1, 5),
            'job_type_id' => rand(1, 5),
            'vacancy' => rand(1, 5),
            'location' => $this->faker->city,
            'description' => $this->faker->text,
            'experience' => rand(1, 10),
            'company_name' => $this->faker->company,
        ];
    }
    
}
