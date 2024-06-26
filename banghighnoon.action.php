<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * bang implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * bang.action.php
 *
 * bang main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/bang/bang/myAction.html", ...)
 *
 */

class action_banghighnoon extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = 'common_notifwindow';
      $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
    } else {
      $this->view = 'banghighnoon_banghighnoon';
      self::trace('Complete reinitialization of board game');
    }
  }

  public function actPlayCard()
  {
    self::setAjaxMode();
    $id = (int) self::getArg('id', AT_posint, true);
    $player = self::getArg('player', AT_posint, false);
    $optionType = self::getArg('optionType', AT_alphanum, false);
    $optionArg = self::getArg('optionArg', AT_posint, false);
    $secondCardId = self::getArg('secondCardId', AT_posint, false);
    $args = [
      'type' => $optionType,
      'player' => $player,
      'arg' => $optionArg,
      'secondCardId' => $secondCardId,
    ];
    $this->game->actPlayCard($id, $args);
    self::ajaxResponse();
  }

  public function actReact()
  {
    self::setAjaxMode();
    $cards = explode(';', self::getArg('cards', AT_numberlist, false));
    $this->game->actReact($cards);
    self::ajaxResponse();
  }

  public function actPass()
  {
    self::setAjaxMode();
    $this->game->actReact(null);
    self::ajaxResponse();
  }

  public function actCancelPreselection()
  {
    self::setAjaxMode();
    $this->game->actCancelPreSelection();
    self::ajaxResponse();
  }

  public function actSelect()
  {
    self::setAjaxMode();
    $cards = explode(';', self::getArg('cards', AT_numberlist, false));
    $this->game->actSelect($cards);
    self::ajaxResponse();
  }

  public function actDraw()
  {
    self::setAjaxMode();
    $id = self::getArg('selected', AT_alphanum, true);
    $this->game->draw($id);
    self::ajaxResponse();
  }

  public function actEndTurn()
  {
    self::setAjaxMode();
    $this->game->actEndTurn();
    self::ajaxResponse();
  }

  public function actCancelEndTurn()
  {
    self::setAjaxMode();
    $this->game->actCancelEndTurn();
    self::ajaxResponse();
  }

  public function actDiscardExcess()
  {
    self::setAjaxMode();
    $cards = array_map('intval', explode(';', self::getArg('cards', AT_numberlist, false)));
    $this->game->actDiscardExcess($cards);
    self::ajaxResponse();
  }

  public function actDefautDiscardExcess()
  {
    self::setAjaxMode();
    $this->game->actDefautDiscardExcess();
    self::ajaxResponse();
  }

  public function actDiscardEliminate()
  {
    self::setAjaxMode();
    $cards = array_map('intval', explode(';', self::getArg('cards', AT_numberlist, false)));
    $this->game->actDiscardEliminate($cards);
    self::ajaxResponse();
  }

  public function actDiscardVicePenalty()
  {
    self::setAjaxMode();
    $cards = array_map('intval', explode(';', self::getArg('cards', AT_numberlist, false)));
    $this->game->actDiscardVicePenalty($cards);
    self::ajaxResponse();
  }

  public function actDefautDiscardVicePenalty()
  {
    self::setAjaxMode();
    $this->game->actDefautDiscardVicePenalty();
    self::ajaxResponse();
  }

  public function actUseAbility()
  {
    self::setAjaxMode();
    $cards = array_map('intval', explode(';', self::getArg('cards', AT_numberlist, false)));
    $this->game->useAbility($cards);
    self::ajaxResponse();
  }

  public function actChooseCharacter()
  {
    self::setAjaxMode();
    $character = (int) self::getArg('character', AT_posint, false);
    $this->game->actChooseCharacter($character);
    self::ajaxResponse();
  }

  public function actChangePref()
  {
    self::setAjaxMode();
    $pref = self::getArg('pref', AT_posint, false);
    $value = self::getArg('value', AT_posint, false);
    $silent = self::getArg('silent', AT_bool, false);
    $this->game->changePreference($pref, $value, $silent);
    self::ajaxResponse();
  }

  public function actDiscardBlue()
  {
    self::setAjaxMode();
    $card = (int) self::getArg('card', AT_posint, false);
    $this->game->actDiscardBlue($card);
    self::ajaxResponse();
  }

  public function actAgreedToDisclaimer()
  {
    self::setAjaxMode();
    $this->game->actAgreedToDisclaimer();
    self::ajaxResponse();
  }

  public function actPassEndRussianRoulette()
  {
    self::setAjaxMode();
    $this->game->actPassEndRussianRoulette();
    self::ajaxResponse();
  }

  public function actReactBloodBrothers()
  {
    self::setAjaxMode();
    $playerId = self::getArg('playerId', AT_posint, false);
    if ($playerId) {
      $playerId = (int) $playerId;
    }
    $this->game->actReactBloodBrothers($playerId);
    self::ajaxResponse();
  }

  public function actHardLiquorGainHP()
  {
    self::setAjaxMode();
    $this->game->actHardLiquorGainHP();
    self::ajaxResponse();
  }

  public function actDeclineHardLiquor()
  {
    self::setAjaxMode();
    $this->game->actDeclineHardLiquor();
    self::ajaxResponse();
  }

  public function actDiscardCardsRanch()
  {
    self::setAjaxMode();
    $cardIds = explode(';', self::getArg('cardIds', AT_numberlist, false));
    $cardIds = array_map(function ($cardId) {
      return (int) $cardId;
    }, $cardIds);
    $this->game->actDiscardCardsRanch($cardIds);
    self::ajaxResponse();
  }

  public function actIgnoreRanch()
  {
    self::setAjaxMode();
    $this->game->actIgnoreRanch();
    self::ajaxResponse();
  }

  public function actPeyoteGuess()
  {
    self::setAjaxMode();
    $isRed = self::getArg('isRed', AT_bool, false);
    $this->game->actPeyoteGuess($isRed);
    self::ajaxResponse();
  }


}
