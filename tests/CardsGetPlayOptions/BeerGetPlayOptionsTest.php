<?php

declare(strict_types=1);

namespace Bang\Tests\CardsGetPlayOptions;

use BANG\Cards\Beer;
use BANG\Managers\Players;
use BANG\Managers\Rules;

final class BeerGetPlayOptionsTest extends AbstractCardsGetPlayOptionsTest
{
  public function testFullHpBeerAvailable(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    Rules::setAvailableRulesForTest([RULE_BEER_AVAILABLE]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
      $this->createPlayerMockWithNoCardsInPlay(SLAB_THE_KILLER),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = [
      'target_types' => [TARGET_NONE],
      'confirmationMsg' => 'You have maximum amount of life points. Drinking a beer would currently have no effect. Do you still want to drink it?',
    ];
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testFullHpBeerAvailableOnlyTwoPlayers(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    Rules::setAvailableRulesForTest([RULE_BEER_AVAILABLE]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = [
      'target_types' => [TARGET_NONE],
      'confirmationMsg' => 'Drinking a beer when only 2 players are left have no effect. Do you still want to drink it?',
    ];
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testFullHpBeerNotAvailable(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    Rules::setAvailableRulesForTest([]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
      $this->createPlayerMockWithNoCardsInPlay(SLAB_THE_KILLER),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }


  public function testFullHpBeerNotAvailableOnlyTwoPlayers(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET);

    Rules::setAvailableRulesForTest([]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testHalfHpBeerAvailable(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET, 2);

    Rules::setAvailableRulesForTest([RULE_BEER_AVAILABLE]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
      $this->createPlayerMockWithNoCardsInPlay(SLAB_THE_KILLER),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = [
      'target_types' => [TARGET_NONE],
    ];
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testHalfHpBeerAvailableOnlyTwoPlayers(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET, 2);

    Rules::setAvailableRulesForTest([RULE_BEER_AVAILABLE]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = [
      'target_types' => [TARGET_NONE],
      'confirmationMsg' => 'Drinking a beer when only 2 players are left have no effect. Do you still want to drink it?',
    ];
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testHalfHpBeerNotAvailable(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET, 2);

    Rules::setAvailableRulesForTest([]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
      $this->createPlayerMockWithNoCardsInPlay(SLAB_THE_KILLER),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }

  public function testHalfHpBeerNotAvailableOnlyTwoPlayers(): void
  {
    $player = $this->createPlayerMockWithNoCardsInPlay(PAUL_REGRET, 2);

    Rules::setAvailableRulesForTest([]);
    Players::setPlayersForTest([
      $player,
      $this->createPlayerMockWithNoCardsInPlay(ROSE_DOOLAN),
    ]);

    $card = new Beer();
    $playOptions = $card->getPlayOptions($player);
    $expectedPlayOptions = null;
    $this->assertSame($expectedPlayOptions, $playOptions);
  }
}
