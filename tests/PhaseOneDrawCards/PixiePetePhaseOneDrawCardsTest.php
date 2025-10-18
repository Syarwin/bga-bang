<?php

declare(strict_types=1);

namespace Bang\Tests\PhaseOneDrawCards;

use BANG\Cards\Events\GhostTown;
use BANG\Cards\Events\Thirst;
use BANG\Cards\Events\TrainArrival;
use BANG\Characters\PixiePete;

final class PixiePetePhaseOneDrawCardsTest extends AbstractPhaseOneDrawCardsTest
{
    protected function getPlayerData(array $override = []): array
    {
        $playerData = parent::getPlayerData($override);
        $playerData['player_bullets'] = 3;
        $playerData['player_character'] = PIXIE_PETE;
        return $playerData;
    }

    public function testDefault(): void
    {
        $player = new PixiePete($this->getPlayerData(['player_hp' => 3]));
        $this->assertSame(3, $player->defaultCardsToDraw());

        $player = new PixiePete($this->getPlayerData(['player_hp' => 2]));
        $this->assertSame(3, $player->defaultCardsToDraw());

        $player = new PixiePete($this->getPlayerData(['player_hp' => 1]));
        $this->assertSame(3, $player->defaultCardsToDraw());
    }

    public function testThirst(): void
    {
        $player = new PixiePete($this->getPlayerData(['player_hp' => 3]));
        $thirst = new Thirst();
        $this->assertSame(1, $thirst->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testTrainArrival(): void
    {
        $player = new PixiePete($this->getPlayerData(['player_hp' => 3]));
        $trainArrival = new TrainArrival();
        $this->assertSame(4, $trainArrival->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testGhostTownAlive(): void
    {
        $player = new PixiePete($this->getPlayerData(['player_hp' => 3]));
        $ghostTown = new GhostTown();
        $this->assertSame(3, $ghostTown->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testGhostTownDead(): void
    {
        $player = new PixiePete($this->getPlayerData(['player_hp' => 0]));
        $ghostTown = new GhostTown();
        $this->assertSame(3, $ghostTown->getPhaseOneAmountOfCardsToDraw($player));
    }
}
