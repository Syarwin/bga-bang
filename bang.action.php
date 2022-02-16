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

class action_bang extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = 'common_notifwindow';
      $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
    } else {
      $this->view = 'bang_bang';
      self::trace('Complete reinitialization of board game');
    }
  }

  public function actPlayCard()
  {
    self::setAjaxMode();
    $id = self::getArg('id', AT_posint, true);
    $player = self::getArg('player', AT_posint, false);
    $optionType = self::getArg('optionType', AT_alphanum, false);
    $optionArg = self::getArg('optionArg', AT_posint, false);
    $args = [
      'type' => $optionType,
      'player' => $player,
      'arg' => $optionArg,
    ];
    $this->game->actPlayCard($id, $args);
    self::ajaxResponse();
  }

  public function actReact()
  {
    self::setAjaxMode();
    $cards = explode(';', self::getArg('cards', AT_numberlist, false));
    //		$id = self::getArg( "id", AT_posint, true );
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

  public function actChangePref()
  {
    self::setAjaxMode();
    $pref = self::getArg('pref', AT_posint, false);
    $value = self::getArg('value', AT_posint, false);
    $silent = self::getArg('silent', AT_bool, false);
    $this->game->changePreference($pref, $value, $silent);
    self::ajaxResponse();
  }
}
