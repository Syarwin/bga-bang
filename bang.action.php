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
    $result = $this->game->actPlayCard($id, $args);
    self::ajaxResponse();
  }

  public function actReact()
  {
    self::setAjaxMode();
    $cards = explode(';', self::getArg('cards', AT_numberlist, false));
    //		$id = self::getArg( "id", AT_posint, true );
    $result = $this->game->actReact($cards);
    self::ajaxResponse();
  }

  public function actPass()
  {
    self::setAjaxMode();
    $result = $this->game->actReact(null);
    self::ajaxResponse();
  }

  public function actCancelPreselection()
  {
    self::setAjaxMode();
    $result = $this->game->actCancelPreSelection();
    self::ajaxResponse();
  }

  public function actSelect()
  {
    self::setAjaxMode();
    $cards = explode(';', self::getArg('cards', AT_numberlist, false));
    $result = $this->game->select($cards);
    self::ajaxResponse();
  }

  public function actDraw()
  {
    self::setAjaxMode();
    $id = self::getArg('selected', AT_alphanum, true);
    $result = $this->game->draw($id);
    self::ajaxResponse();
  }

  public function actEndTurn()
  {
    self::setAjaxMode();
    $result = $this->game->actEndTurn();
    self::ajaxResponse();
  }

  public function actCancelEndTurn()
  {
    self::setAjaxMode();
    $result = $this->game->cancelEndTurn();
    self::ajaxResponse();
  }

  public function actDiscardExcess()
  {
    self::setAjaxMode();
    $cards = array_map('intval', explode(';', self::getArg('cards', AT_numberlist, false)));
    $result = $this->game->actDiscardExcess($cards);
    self::ajaxResponse();
  }

  public function actUseAbility()
  {
    self::setAjaxMode();
    $cards = array_map('intval', explode(';', self::getArg('cards', AT_numberlist, false)));
    $result = $this->game->useAbility($cards);
    self::ajaxResponse();
  }
}
