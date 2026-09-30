<?php

namespace Tests\Feature;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_creates_player_and_active_link_and_redirects(): void
    {
        $response = $this->post('/register', [
            'username' => 'Alice',
            'phone' => '+380501112233',
        ]);

        $player = Player::where('phone', '+380501112233')->firstOrFail();

        $this->assertSame('Alice', $player->username);
        $this->assertCount(1, $player->accessLinks);

        $link = $player->accessLinks->first();
        $this->assertTrue($link->isActive());

        $response->assertRedirect(route('link.show', $link));
        $response->assertSessionHas('link_created', true);
    }

    public function test_empty_fields_fail_validation(): void
    {
        $response = $this->post('/register', [
            'username' => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['username', 'phone']);
        $this->assertSame(0, Player::count());
    }

    public function test_duplicate_phone_number_is_rejected(): void
    {
        Player::factory()->create(['phone' => '+380501112233']);

        $response = $this->post('/register', [
            'username' => 'Bob',
            'phone' => '+380501112233',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertSame(1, Player::count());
    }

    public function test_duplicate_phone_number_with_different_formatting_is_rejected(): void
    {
        Player::factory()->create(['phone' => '+380501112233']);

        $response = $this->post('/register', [
            'username' => 'Bob',
            'phone' => '+380 (50) 111-22-33',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertSame(1, Player::count());
    }
}
