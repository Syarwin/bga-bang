<?php

declare(strict_types=1);

namespace Bang\Tests\CardsGetPlayOptions;

use BANG\Managers\EventCards;
use Bang\Tests\Mocks\PlayerMockerAndFaker;
use PHPUnit\Framework\TestCase;

abstract class AbstractCardsGetPlayOptionsTest extends TestCase
{
  use PlayerMockerAndFaker;

  protected function setUp(): void
  {
    // no event card in play by default, can be overriden in some tests
    EventCards::setActiveForTest();
  }
}
