<?php

namespace Database\Factories;

use App\Models\AccessLink;
use App\Models\Player;
use App\Models\Spin;
use App\Support\GameResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spin>
 */
class SpinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $result = GameResult::forNumber($this->faker->numberBetween(1, 1000));

        return [
            'player_id' => Player::factory(),
            'access_link_id' => AccessLink::factory(),
            'number' => $result->number,
            'is_win' => $result->isWin,
            'amount' => $result->amount,
        ];
    }
}
