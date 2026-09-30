<?php

namespace App\Contracts;

interface RandomNumberGenerator
{
    /**
     * Return a random integer between $min and $max, inclusive.
     */
    public function generate(int $min, int $max): int;
}
