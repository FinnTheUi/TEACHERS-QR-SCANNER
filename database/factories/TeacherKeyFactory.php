<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TeacherKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherKey>
 */
class TeacherKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeacherKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'key_code' => 'QR-' . $this->faker->unique()->uuid,
            'description' => $this->faker->sentence(3),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-1 month'),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
        ];
    }

    /**
     * Indicate that the key is active.
     */
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'expires_at' => null,
            ];
        });
    }

    /**
     * Indicate that the key is expired.
     */
    public function expired(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
                'expires_at' => now()->subDay(),
            ];
        });
    }
}
