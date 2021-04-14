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
          'pId' => $atom == ST_END_OF_TURN? null : Globals::getPIdTurn(),
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
    if (isset($atom['pId']) && $atom['pId'] != null && $pId != $atom['pId']) {
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

  public function insertAfter($atom, $pos = 1)
  {
    $stack = Globals::getStack();
    array_splice($stack, $pos, 0, [$atom]);
    Globals::setStack($stack);
    return $atom;
  }

  public function insertAfterCardResolution($atom)
  {
    // Compute pos
    $top = Globals::getStackCtx();
    if (!isset($top['src']) || !isset($top['src']['id'])) {
      throw new \feException('No card resolution in progress');
    }

    $cId = $top['src']['id'];
    $stack = Globals::getStack();
    for ($i = 1; $i < count($stack); $i++) {
      if (!isset($stack[$i]['src']) || $stack[$i]['src']['id'] != $cId) {
        break;
      }
    }
    self::insertAfter($atom, $i);
  }

  public function isItLastElimination()
  {
    $stack = Globals::getStack();
    return count($stack) == 1 || $stack[1]['state'] != ST_ELIMINATE;
  }

  public function clearAllLeaveLast()
  {
    $stack = Globals::getStack();
    Globals::setStack([end($stack)]);
    Stack::resolve();
  }

  public function removePlayerNodes($pId)
  {
    $stack = Globals::getStack();
    Utils::filter($stack, function($atom) use ($pId){
      return !isset($atom['pId']) || $atom['pId'] != $pId;
    });
    Globals::setStack($stack);
  }
}
