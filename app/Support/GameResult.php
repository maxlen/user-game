<?php

namespace App\Support;

/**
 * Result of a single game spin, computed purely from the rolled number.
 *
 * Rules:
 *  - even number => win, odd number => lose;
 *  - a loss always pays out 0;
 *  - a win pays out a percentage of the number, the percentage being
 *    determined by which threshold the number falls into (checked from
 *    the highest threshold down to the lowest).
 */
final readonly class GameResult
{
    public function __construct(
        public int $number,
        public bool $isWin,
        public float $amount,
    ) {}

    public static function forNumber(int $number): self
    {
        $isWin = $number % 2 === 0;

        $percent = match (true) {
            $number > 900 => 70,
            $number > 600 => 50,
            $number > 300 => 30,
            default => 10,
        };

        $amount = $isWin ? round($number * $percent / 100, 2) : 0.0;

        return new self($number, $isWin, $amount);
    }

    /**
     * Flat representation for the flash session.
     *
     * Session serialization is JSON (see config/session.php), so objects do
     * not survive a round-trip through the session — they come back as plain
     * arrays. Anything flashed to the view therefore has to be scalars.
     *
     * @return array{number: int, is_win: bool, amount: float}
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'is_win' => $this->isWin,
            'amount' => $this->amount,
        ];
    }
}
