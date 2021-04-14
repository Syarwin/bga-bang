<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;
use BANG\Managers\Cards;

class LuckyDuke extends \BANG\Models\Player
{
  private $selectedCard = null;
  public function __construct($row = null)
  {
    $this->character = LUCKY_DUKE;
    $this->character_name = clienttranslate('Lucky Duke');
    $this->text = [
      clienttranslate(
        'Each time he is required to "draw!", he flips the top two cards from the deck, and chooses the result he prefers.'
      ),
      clienttranslate('Discard both cards afterwards.'),
    ];
    $this->bullets = 4;
    parent::__construct($row);

    $this->selectedCard = null;
  }

  public function flip($src)
  {
    $cards = Cards::drawForLocation(LOCATION_SELECTION, 2);
    foreach ($cards as $card) {
      Notifications::flipCard($this, $card, $src);
    }

    Log::addAction('selection', ['players' => [$this->id], 'src' => $src->getName()]);
    $atom = [
      'state' => ST_RESOLVE_FLIPPED,
      'pId' => $this->id,
      'src' => $src->jsonSerialize(),
    ];
    Stack::insertAfter($atom);
    $this->prepareSelection($src, [$this->getId()], true, 1, true);
  }

  public function useAbility($args)
  {
    $this->selectedCard = Cards::getCard($args['selected'][0]);

    $this->discardCard(Cards::getCard($args['rest'][0]));
    Notifications::tell(clienttranslate('${player_name} chooses ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name' => $this->name,
      'card_name' => $this->selectedCard->getNameAndValue(),
    ]);
    return Cards::getCurrentCard()->activate($this, []);
  }
}
