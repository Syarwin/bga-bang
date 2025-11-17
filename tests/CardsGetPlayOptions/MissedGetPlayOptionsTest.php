<?php

declare(strict_types=1);

namespace Bang\Tests\CardsGetPlayOptions;

use BANG\Cards\Missed;

final class MissedGetPlayOptionsTest extends AbstractCardsGetPlayOptionsTest
{
  public function testMissedOptions(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    $card = new Missed();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }
}
