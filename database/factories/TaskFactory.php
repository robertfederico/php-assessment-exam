<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->unique()->sentence(3),
            'content' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'is_published' => fake()->boolean(80),
            'image_path' => fake()->optional(0.3)->imageUrl(800, 600, 'business', true),
            'subtasks' => fake()->optional(0.4)->passthrough($this->generateSubtasks()),
        ];
    }

    /**
     * Generate random subtasks array.
     */
    private function generateSubtasks(): array
    {
        $subtaskCount = fake()->numberBetween(2, 5);
        $subtasks = [];

        for ($i = 0; $i < $subtaskCount; $i++) {
            $subtasks[] = [
                'id' => fake()->uuid(),
                'title' => fake()->sentence(2),
                'completed' => fake()->boolean(60), // 60% chance of being completed
                'created_at' => fake()->dateTimeBetween('-1 month')->format('Y-m-d H:i:s'),
            ];
        }

        return $subtasks;
    }
}
