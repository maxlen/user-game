<?php

namespace Tests\Unit;

use App\Support\GameResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GameResultTest extends TestCase
{
    #[DataProvider('winningNumbers')]
    public function test_win_amount_for_threshold_boundaries(int $number, float $expectedAmount): void
    {
        $result = GameResult::forNumber($number);

        $this->assertTrue($result->isWin);
        $this->assertEqualsWithDelta($expectedAmount, $result->amount, 0.001);
    }

    public static function winningNumbers(): array
    {
        return [
            // <= 300 => 10%
            '2 -> 10%' => [2, 0.2],
            '300 -> 10% (upper edge)' => [300, 30.0],
            // > 300 and <= 600 => 30%
            '302 -> 30% (just above 300)' => [302, 90.6],
            '600 -> 30% (upper edge)' => [600, 180.0],
            // > 600 and <= 900 => 50%
            '602 -> 50% (just above 600)' => [602, 301.0],
            '900 -> 50% (upper edge)' => [900, 450.0],
            // > 900 => 70%
            '902 -> 70% (just above 900)' => [902, 631.4],
            '1000 -> 70% (max number)' => [1000, 700.0],
        ];
    }

    #[DataProvider('oddNumbers')]
    public function test_odd_numbers_always_lose_with_zero_amount(int $number): void
    {
        $result = GameResult::forNumber($number);

        $this->assertFalse($result->isWin);
        $this->assertSame(0.0, $result->amount);
    }

    public static function oddNumbers(): array
    {
        return [
            [1],
            [301],
            [601],
            [901],
            [999],
        ];
    }

    public function test_number_is_preserved_on_the_result(): void
    {
        $result = GameResult::forNumber(42);

        $this->assertSame(42, $result->number);
    }
}
