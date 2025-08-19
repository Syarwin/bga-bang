<?php

namespace Bang\Tests\PhaseOneDrawCards;

use BANG\Characters\WillytheKid;
use PHPUnit\Framework\TestCase;

final class WillyKidPhaseOneDrawCardsTest extends TestCase
{
    public function testBullets()
    {
        $player = new WillytheKid([]);
        $this->assertEquals(4, $player->getBullets());
    }
}
