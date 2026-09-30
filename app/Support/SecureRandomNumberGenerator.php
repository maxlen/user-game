<?php

namespace App\Support;

use App\Contracts\RandomNumberGenerator;

final class SecureRandomNumberGenerator implements RandomNumberGenerator
{
    public function generate(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}
