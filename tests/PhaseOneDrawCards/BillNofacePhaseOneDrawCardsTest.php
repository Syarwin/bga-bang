<?php

declare(strict_types=1);

namespace Bang\Tests\PhaseOneDrawCards;

use BANG\Cards\Events\GhostTown;
use BANG\Cards\Events\Thirst;
use BANG\Cards\Events\TrainArrival;
use BANG\Characters\BillNoface;

final class BillNofacePhaseOneDrawCardsTest extends AbstractPhaseOneDrawCardsTest
{
    protected function getPlayerData(array $override = []): array
    {
        $playerData = parent::getPlayerData($override);
        $playerData['player_bullets'] = 4;
        $playerData['player_character'] = BILL_NOFACE;
        return $playerData;
    }

    public function testDefault(): void
    {
        $player = new BillNoface($this->getPlayerData(['player_hp' => 4]));
        $this->assertSame(1, $player->defaultCardsToDraw());

        $player = new BillNoface($this->getPlayerData(['player_hp' => 3]));
        $this->assertSame(2, $player->defaultCardsToDraw());

        $player = new BillNoface($this->getPlayerData(['player_hp' => 2]));
        $this->assertSame(3, $player->defaultCardsToDraw());

        $player = new BillNoface($this->getPlayerData(['player_hp' => 1]));
        $this->assertSame(4, $player->defaultCardsToDraw());
    }

    public function testThirst(): void
    {
        $player = new BillNoface($this->getPlayerData(['player_hp' => 4]));
        $thirst = new Thirst();
        $this->assertSame(1, $thirst->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testTrainArrival(): void
    {
        $player = new BillNoface($this->getPlayerData(['player_hp' => 4]));
        $trainArrival = new TrainArrival();
        $this->assertSame(2, $trainArrival->getPhaseOneAmountOfCardsToDraw($player));

        $player = new BillNoface($this->getPlayerData(['player_hp' => 3]));
        $trainArrival = new TrainArrival();
        $this->assertSame(3, $trainArrival->getPhaseOneAmountOfCardsToDraw($player));

        $player = new BillNoface($this->getPlayerData(['player_hp' => 2]));
        $trainArrival = new TrainArrival();
        $this->assertSame(4, $trainArrival->getPhaseOneAmountOfCardsToDraw($player));

        $player = new BillNoface($this->getPlayerData(['player_hp' => 1]));
        $trainArrival = new TrainArrival();
        $this->assertSame(5, $trainArrival->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testGhostTownAlive(): void
    {
        $player = new BillNoface($this->getPlayerData(['player_hp' => 2]));
        $ghostTown = new GhostTown();
        $this->assertSame(3, $ghostTown->getPhaseOneAmountOfCardsToDraw($player));
    }

    public function testGhostTownDead(): void
    {
        $player = new BillNoface($this->getPlayerData(['player_hp' => 0]));
        $ghostTown = new GhostTown();
        $this->assertSame(3, $ghostTown->getPhaseOneAmountOfCardsToDraw($player));
    }
}
