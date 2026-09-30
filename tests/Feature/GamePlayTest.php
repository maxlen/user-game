<?php

namespace Tests\Feature;

use App\Contracts\RandomNumberGenerator;
use App\Models\AccessLink;
use App\Models\Player;
use App\Models\Spin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamePlayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bind a fake generator that returns the given numbers in order, one per
     * call. Bound only once per test: Laravel's Router caches the resolved
     * controller instance on the Route object itself (see
     * Illuminate\Routing\Route::getController()), so rebinding the
     * container between requests to the same route within a single test
     * would have no effect on already-resolved controllers/services.
     */
    private function fakeRandomNumbers(int ...$numbers): void
    {
        $this->app->bind(RandomNumberGenerator::class, fn () => new class($numbers) implements RandomNumberGenerator
        {
            public function __construct(private array $numbers) {}

            public function generate(int $min, int $max): int
            {
                return array_shift($this->numbers);
            }
        });
    }

    public function test_spin_records_a_win_with_expected_amount_and_shows_it(): void
    {
        $this->fakeRandomNumbers(902); // even, > 900 => 70%

        $link = AccessLink::factory()->for(Player::factory())->create();

        $response = $this->post(route('link.lucky', $link));

        $response->assertRedirect(route('link.show', $link));

        $spin = Spin::first();
        $this->assertSame(902, $spin->number);
        $this->assertTrue($spin->is_win);
        $this->assertEqualsWithDelta(631.4, (float) $spin->amount, 0.001);

        // Session serialization is JSON (config/session.php), so an object
        // flashed here would reach the view as a plain array and blow up on
        // property access. The array session driver used in tests keeps
        // objects intact, so only this assertion catches that mismatch.
        $response->assertSessionHas('spin_result');
        $this->assertIsArray(session('spin_result'));

        $page = $this->get(route('link.show', $link));
        $page->assertOk();
        $page->assertSee('902');
        $page->assertSee('631.40');
        $page->assertSee('alert-success', false);
    }

    public function test_spin_records_a_loss_with_zero_amount(): void
    {
        $this->fakeRandomNumbers(901); // odd => lose

        $link = AccessLink::factory()->for(Player::factory())->create();

        $this->post(route('link.lucky', $link));

        $spin = Spin::first();
        $this->assertSame(901, $spin->number);
        $this->assertFalse($spin->is_win);
        $this->assertEqualsWithDelta(0.0, (float) $spin->amount, 0.001);
    }

    public function test_history_shows_last_three_spins_in_reverse_chronological_order(): void
    {
        $player = Player::factory()->create();
        $link = AccessLink::factory()->for($player)->create();

        $this->fakeRandomNumbers(2, 4, 6, 8, 10);
        foreach (range(1, 5) as $ignored) {
            $this->post(route('link.lucky', $link));
        }

        $response = $this->get(route('link.history', $link));

        $response->assertOk();

        $history = $response->viewData('history');
        $this->assertCount(3, $history);
        $this->assertSame([10, 8, 6], $history->pluck('number')->all());
    }

    public function test_history_survives_link_regeneration(): void
    {
        $player = Player::factory()->create();
        $link = AccessLink::factory()->for($player)->create();

        $this->fakeRandomNumbers(2);
        $this->post(route('link.lucky', $link));

        $response = $this->post(route('link.regenerate', $link));
        $newLink = $player->accessLinks()->latest('id')->first();

        $historyResponse = $this->get(route('link.history', $newLink));
        $history = $historyResponse->viewData('history');

        $this->assertCount(1, $history);
        $this->assertSame(2, $history->first()->number);
    }
}
