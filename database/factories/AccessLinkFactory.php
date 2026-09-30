<?php

namespace Database\Factories;

use App\Models\AccessLink;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AccessLink>
 */
class AccessLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'player_id' => Player::factory(),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(config('game.link_lifetime_days')),
            'revoked_at' => null,
        ];
    }

    /**
     * Link that already expired in the past.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Link that has been revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now(),
        ]);
    }
}
