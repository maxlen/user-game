<?php

namespace Tests\Unit;

use App\Models\AccessLink;
use App\Models\Player;
use App\Repositories\AccessLinkRepository;
use App\Services\AccessLinkService;
use Mockery;
use Tests\TestCase;

class AccessLinkServiceTest extends TestCase
{
    public function test_regenerate_revokes_the_old_link_before_issuing_a_new_one(): void
    {
        $player = new Player(['username' => 'Alice', 'phone' => '+10000000']);
        $player->id = 1;
        $player->exists = true;

        $oldLink = new AccessLink(['token' => 'old-token']);
        $oldLink->id = 10;
        $oldLink->setRelation('player', $player);

        $newLink = new AccessLink(['token' => 'new-token']);
        $newLink->id = 11;

        /** @var AccessLinkRepository&\Mockery\MockInterface $repository */
        $repository = Mockery::mock(AccessLinkRepository::class);

        $sequence = [];

        $repository->shouldReceive('revoke')
            ->once()
            ->with($oldLink)
            ->ordered()
            ->andReturnUsing(function () use (&$sequence): void {
                $sequence[] = 'revoke';
            });

        $repository->shouldReceive('tokenExists')
            ->andReturn(false);

        $repository->shouldReceive('create')
            ->once()
            ->ordered()
            ->andReturnUsing(function () use (&$sequence, $newLink) {
                $sequence[] = 'create';

                return $newLink;
            });

        $service = new AccessLinkService($repository);

        $result = $service->regenerate($oldLink);

        $this->assertSame($newLink, $result);
        $this->assertSame(['revoke', 'create'], $sequence);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
