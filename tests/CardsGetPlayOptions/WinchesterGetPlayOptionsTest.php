<?php

declare(strict_types=1);

namespace Bang\Tests\CardsGetPlayOptions;

use BANG\Cards\Events\Judge;
use BANG\Cards\Winchester;
use BANG\Managers\EventCards;

final class WinchesterGetPlayOptionsTest extends AbstractCardsGetPlayOptionsTest
{
  public function testPlayerHasNoCardsInPlay(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    $card = new Winchester();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = [
      'target_types' => [TARGET_NONE]
    ];
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testPlayerHasSameWeaponInPlay(): void
  {
    $player = $this->createPlayerMockWithCardsInPlay(PAUL_REGRET, [CARD_WINCHESTER]);

    $card = new Winchester();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testPlayerHasOtherWeaponInPlay(): void
  {
    $player = $this->createPlayerMockWithCardsInPlay(PAUL_REGRET, [CARD_VOLCANIC]);

    $card = new Winchester();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = [
      'target_types' => [TARGET_NONE],
      'confirmationMsg' => 'This weapon will replace the current one. Are you sure?',
    ];
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testJudgeInPlay(): void
  {
    EventCards::setActiveForTest(new Judge());

    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    $card = new Winchester();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }
}
