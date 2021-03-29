<?php
namespace BANG\Core;
use bang;

/*
 * Stack: a class that handle resolution stack
 */
class Stack
{
  public static function getGame()
  {
    return bang::get();
  }

  public static function setup($flow)
  {
    $stack = [];
    foreach ($flow as $atom) {
      if (is_int($atom)) {
        $stack[] = [
          'state' => $atom,
          'pId' => Globals::getPIdTurn(),
        ];
      }
    }
    Globals::setStack($stack);
  }


  public function top()
  {
    $stack = Globals::getStack();
    return reset($stack);
  }

  public function shift()
  {
    $stack = Globals::getStack();
    $elem = array_shift($stack);
    Globals::setStack($stack);
    return $elem;
  }

  public function resolve()
  {
    $atom = self::top();
    if ($atom == false) {
      throw new \feException('Stack engine is empty !');
    }

    Globals::setStackCtx($atom);

    $pId = self::getGame()->getActivePlayerId();
    // Jump to resolveStack state to ensure we can change active pId
    if ($atom['pId'] != null && $pId != $atom['pId']) {
      self::getGame()->gamestate->jumpToState(ST_RESOLVE_STACK);
      self::getGame()->gamestate->changeActivePlayer($atom['pId']);
    }

    self::getGame()->gamestate->jumpToState($atom['state']);
  }

  public function nextState()
  {
    self::shift();
    self::resolve();
  }

  public function insertOnTop($atom)
  {
    $stack = Globals::getStack();
    array_unshift($stack, $atom);
    Globals::setStack($stack);
    return $atom;
  }

  /*
  public function nextState($transition, $newState){
    if($newState != null){
      self::push($transition);
    } else {
      $newState = $transition;
    }
    // Classic transition
    self::getGame()->gamestate->nextState($newState);
  }
*/
}
