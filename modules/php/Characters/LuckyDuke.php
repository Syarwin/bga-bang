<?php
namespace Bang\Characters;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Game\Utils;
use Bang\Cards\Cards;

class LuckyDuke extends Player {
  private $selectedCard = null;
  public function __construct($row = null)
  {
    $this->character    = LUCKY_DUKE;
    $this->character_name = clienttranslate('Lucky Duke');
    $this->text  = [
      clienttranslate('Each time he is required to "draw!", he flips the top two cards from the deck, and chooses the result he prefers.'),
      clienttranslate("Discard both cards afterwards.")
    ];
    $this->bullets = 4;
    parent::__construct($row);

    $this->selectedCard = null;
  }

  public function flip($args, $src) {
    if(!is_null($this->selectedCard)) return $this->selectedCard;

    if(isset($args['pattern'])) {
      $cards = [Cards::draw(), Cards::draw()];
      Notifications::drawCard($this, $cards[0], $src);
      Notifications::drawCard($this, $cards[1], $src);
      if(preg_match($args['pattern'],$cards[0]->getCopy()))
        return $cards[0];
      return $cards[1];
    }
    $cards = Cards::toObjects(Cards::createSelection(2));
    Notifications::drawCard($this, $cards[0], $src);
    Notifications::drawCard($this, $cards[1], $src);
    Log::addAction("selection", ["players" => [$this->id], 'src' => $src->getName()]);
    return "select";
  }

  public function useAbility($args) {
    $this->selectedCard = Cards::getCard($args['selected'][0]);

    $this->discardCard(Cards::getCard($args['rest'][0]));
    Notifications::tell(clienttranslate('${player_name} chooses ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name' => $this->name,
      'card_name' => $this->selectedCard->getNameAndValue()
    ]);
    return Cards::getCurrentCard()->activate($this, []);
  }
}
