<?php

declare(strict_types=1);

namespace Bang\Tests\PhaseOneDrawCards;

use BANG\Cards\Events\GhostTown;
use BANG\Cards\Events\Thirst;
use BANG\Cards\Events\TrainArrival;
use BANG\Characters\WillytheKid;

final class WillyKidPhaseOneDrawCardsTest extends AbstractPhaseOneDrawCardsTest
{
    protected function getPlayerData(array $override = []): array
    {
        $playerData = parent::getPlayerData($override);
        $playerData['player_bullets'] = 4;
        $playerData['player_character'] = WILLY_THE_KID;
        return $playerData;
    }

    public function testDefault(): void
    {
        $player = new WillytheKid();
        $this->assertSame(2, $player->defaultCardsToDraw());
    }

    public function testThirst(): void
    {
        $player = new WillytheKid();
        $thirst = new Thirst();
        $this->assertSame(1, $thirst->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testTrainArrival(): void
    {
        $player = new WillytheKid();
        $trainArrival = new TrainArrival();
        $this->assertSame(3, $trainArrival->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testGhostTownAlive(): void
    {
        $player = new WillytheKid($this->getPlayerData(['player_hp' => 2]));
        $ghostTown = new GhostTown();
        $this->assertSame(2, $ghostTown->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testGhostTownDead(): void
    {
        $player = new WillytheKid($this->getPlayerData(['player_hp' => 0]));
        $ghostTown = new GhostTown();
        $this->assertSame(3, $ghostTown->getPhaseOneAmountOfCardsToDraw($player));
    }
}
