<?php

namespace Tests\Feature;

use App\Models\AccessLink;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AccessLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_link_returns_200(): void
    {
        $link = AccessLink::factory()->for(Player::factory())->create();

        $response = $this->get(route('link.show', $link));

        $response->assertOk();
    }

    public function test_unknown_token_returns_404(): void
    {
        $response = $this->get('/l/no-such-token');

        $response->assertNotFound();
    }

    public function test_revoked_link_returns_410(): void
    {
        $link = AccessLink::factory()->for(Player::factory())->revoked()->create();

        $response = $this->get(route('link.show', $link));

        $response->assertStatus(410);
    }

    public function test_expired_link_returns_410(): void
    {
        $link = AccessLink::factory()->for(Player::factory())->create();

        $this->travel(8)->days();

        $response = $this->get(route('link.show', $link));

        $response->assertStatus(410);
    }

    public function test_regenerate_revokes_old_link_and_issues_a_new_one_with_fresh_expiry(): void
    {
        $link = AccessLink::factory()->for(Player::factory())->create();
        $oldUrl = route('link.show', $link);

        $response = $this->post(route('link.regenerate', $link));

        $link->refresh();
        $this->assertTrue($link->isRevoked());

        $newLink = $link->player->accessLinks()->latest('id')->first();
        $this->assertNotSame($link->id, $newLink->id);
        $this->assertTrue($newLink->isActive());
        $this->assertTrue($newLink->expires_at->greaterThan(Carbon::now()->addDays(6)));

        $response->assertRedirect(route('link.show', $newLink));

        $this->get($oldUrl)->assertStatus(410);
    }

    public function test_deactivate_closes_access_to_the_link(): void
    {
        $link = AccessLink::factory()->for(Player::factory())->create();

        $response = $this->post(route('link.deactivate', $link));

        $response->assertRedirect(route('register.form'));

        $link->refresh();
        $this->assertTrue($link->isRevoked());

        $this->get(route('link.show', $link))->assertStatus(410);
    }
}
