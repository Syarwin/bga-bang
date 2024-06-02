<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Core\Stack;
use BANG\Managers\EventCards;
use BANG\Managers\Players;

trait PeyoteTrait
{
  public function argPeyote()
  {
    return [
      'options' => [
        clienttranslate('Red {H}/{D}'), clienttranslate('Black {S}/{C}')
      ],
    ];
  }

  /**
   * @param boolean $guessedRed
   * @return void
   */
  public function actPeyoteGuess($guessedRed)
  {
    self::checkAction('actPeyoteGuess');
    $flipped = Cards::drawForLocation(LOCATION_FLIPPED, 1)->first();
    $src = EventCards::getActive();
    $player = Players::getCurrent();
    Notifications::flipCard($player, $flipped, $src);
    Cards::discard($flipped);
    $actualRed = in_array($flipped->getSuit(), ['H', 'D']);
    if ($actualRed === $guessedRed) {
      $cards = Cards::dealFromDiscard($player->getId(), 1);
      Notifications::drawCardFromDiscard($player, $cards, false);
    } else {
      Notifications::playerGuessedIncorrectly($player);
      Notifications::discardedCard($player, $flipped);
      Stack::unsuspendNext(ST_PEYOTE);
    }
    Stack::finishState();
  }
}
