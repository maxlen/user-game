<?php

namespace App\Repositories;

use App\Models\Player;

class PlayerRepository
{
    public function create(string $username, string $phone): Player
    {
        return Player::create([
            'username' => $username,
            'phone' => $phone,
        ]);
    }

    public function findByPhone(string $phone): ?Player
    {
        return Player::where('phone', $phone)->first();
    }

    public function existsByPhone(string $phone): bool
    {
        return Player::where('phone', $phone)->exists();
    }
}
