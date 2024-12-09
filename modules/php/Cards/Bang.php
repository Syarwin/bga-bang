<?php
namespace BANG\Cards;
use BANG\Managers\Players;
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
    $aimingCards = Rules::isAimingCards();
    $bangPossible = !Rules::isBangStrictlyForbidden() && ($player->hasUnlimitedBangs() || Rules::getBangsAmountLeft() > 0);
    $bangsWithoutThis = $this->getBangsWithoutThisCard($player);
    $canPlayWithAnotherBang = Rules::isBangCouldBePlayedWithAnotherBang() && $bangsWithoutThis;
    if (!$aimingCards && !$bangPossible && !$canPlayWithAnotherBang) { return null; }

    $playOptions = [];
    $targetTypes = [];
    if ($aimingCards) {
      $targetTypes[] = TARGET_ALL_CARDS;
      // Rules::isAimingCards() is true currently for Ricochet only. Move this to card itself if this logic changes
      $playOptions['status_bar_message'] = clienttranslate('You must choose a card in play (Ricochet effect) or a player to use BANG! normally');
    }
    if ($bangPossible || $canPlayWithAnotherBang) {
      $targetTypes[] = TARGET_PLAYER;
    }

    $playOptions['target_types'] = $targetTypes;

    if ($canPlayWithAnotherBang) {
      if (count($bangsWithoutThis) > 0) {
        $playOptions['with_another_card'] = [
          'strict' => !$bangPossible,
          'cards' => $bangsWithoutThis,
          'targets' => $this->getTargetablePlayers($player)
        ];
      }
    }
    $playOptions['targets'] = $this->getTargetablePlayers($player);
    return $playOptions;
  }

  /**
   * @param Player $player
   * @return array
   */
  private function getBangsWithoutThisCard($player)
  {
    $bangOptions = [ 'targets' => Players::getLivingPlayers($player->getId())->getIds() ];
    return array_values(array_filter($player->getBangCards($bangOptions)['cards'], function ($card) {
      return $card['id'] !== $this->getId();
    }));
  }

  public function play($player, $args)
  {
    // FAQ, Q07. Sniper doesn't count as Bang! (secondCardId is set)
    // FAQ, Q09, Ricochet doesn't count as Bang! ($args['type'] should be 'player', not 'inPlay')
    if (!$args['secondCardId'] && $args['type'] !== 'inPlay') {
      Rules::bangPlayed();
    }
    parent::play($player, $args);
  }
}
