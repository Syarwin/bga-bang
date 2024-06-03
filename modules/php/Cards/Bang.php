<?php
namespace BANG\Cards;
use BANG\Managers\Rules;
use BANG\Models\BangActionCard;
use BANG\Models\Player;

class Bang extends BangActionCard
{
  public function __construct($params = null)
  {
    parent::__construct($params);
    $this->type = CARD_BANG;
    $this->name = clienttranslate('BANG!');
    $this->text = clienttranslate('A Bang to a player in range. Can usually only be played once per turn');
    $this->symbols = [[SYMBOL_BANG, SYMBOL_INRANGE]];
    $this->copies = [
      // prettier-ignore
      BASE_GAME => [ 'AS', '8D', '9D', '10D', 'JD', 'QD', 'KD', 'AD', '2C', '3C', 'QH', 'KH', 'AH', '2D', '3D', '4D', '5D', '6D', '7D', '4C', '5C', '6C', '7C', '8C', '9C' ],
      HIGH_NOON => [],
      DODGE_CITY => ['8S', '5C', '6C', 'KC'],
    ];
    $this->effect = [
      'type' => BASIC_ATTACK,
      'range' => 0,
      'impacts' => INRANGE,
    ];
  }

  /**
   * Only one bang per turn, unless unlimitedBangs granted by Volcanic or by character
   * @param Player $player
   */
  public function getPlayOptions($player)
  {
    if (!Rules::isBangStrictlyForbidden() && ($player->hasUnlimitedBangs() || Rules::getBangsAmountLeft() > 0)) {
      $playOptions = parent::getPlayOptions($player);
      if (Rules::isBangCouldBePlayedWithAnotherBang()) {
        $bangsWithoutThisCard = array_values(array_filter($player->getBangCards()['cards'], function ($card) {
          return $card['id'] !== $this->getId();
        }));
        if (count($bangsWithoutThisCard) > 0) {
          $playOptions['with_another_card'] = ['strict' => false, 'targets' => $bangsWithoutThisCard];
        }
      }
      return $playOptions;
    } else {
      return null;
    }
  }

  public function play($player, $args)
  {
    if (!$args['secondCardId']) { // FAQ, Q07. Sniper doesn't count as Bang!
      Rules::bangPlayed();
    }
    parent::play($player, $args);
  }
}
