<?php
namespace BANG\Core;
use BANG\Managers\Rules;
use banghighnoon;
use BANG\Helpers\Utils;

/*
 * Stack: a class that handle resolution stack
 */
class Stack
{
  public static function getGame()
  {
    return banghighnoon::get();
  }

  public static function setup($flow)
  {
    $ctx = Stack::getCtx();
    if (empty($ctx)) { // Ok, that should be the very game start
      $firstAtom = Stack::newAtom(ST_START_OF_TURN);
      Stack::setCtx($firstAtom);
      $stack = [$firstAtom];
    } else {
      $stack = [$ctx];
    }

    foreach ($flow as $state) {
      if (is_int($state)) {
        $options = [
          'pId' => ($state == ST_END_OF_TURN || $state == ST_GAME_END) ? null : Rules::getCurrentPlayerId(),
        ];
        if ($state == ST_PLAY_CARD) {
          $options['suspended'] = true;
        }
        $stack[] = Stack::newAtom($state, $options);
      }
    }
    Stack::set($stack);
    Stack::setCtx($stack[0]);
  }

  public static function top()
  {
    $stack = Stack::get();
    return reset($stack);
  }

  public static function getNextState()
  {
    $stack = Stack::get();
    return $stack[1] ?? null;
  }

  public static function resolve()
  {
    /*
    if (Globals::getGameIsOver()) {
      return;
    }
    */

    $atom = self::top();
    if (Globals::enabledStackLogger()) {
      var_dump("[Stack logger] This atom is going to be resolved now:");
      var_dump($atom);
    }
    if ($atom == false) {
      throw new \feException('Stack engine is empty !');
    }

    Stack::setCtx($atom);

    $pId = self::getGame()->getActivePlayerId();
    // Jump to resolveStack state to ensure we can change active pId
    self::getGame()->gamestate->jumpToState(ST_RESOLVE_STACK);
    if (isset($atom['pId']) && $atom['pId'] != null && $pId != $atom['pId']) {
      self::getGame()->gamestate->changeActivePlayer($atom['pId']);
    }

    self::getGame()->gamestate->jumpToState($atom['state']);
  }

  // TODO: Try to merge with insertAfter(). After Stack refactoring they might not differ
  public static function insertOnTop($atom)
  {
    $stack = Stack::get();
    array_unshift($stack, $atom);
    Stack::set($stack);
    if (Globals::enabledStackLogger()) {
      var_dump("[Stack logger] Inserted a new atom on top and now Stack looks like this:");
      var_dump(Stack::get());
    }
    return $atom;
  }

  public static function insertAfter($atom, $pos = 1)
  {
    $stack = Stack::get();
    array_splice($stack, $pos, 0, [$atom]);
    Stack::set($stack);
    if (Globals::enabledStackLogger()) {
      var_dump("[Stack logger] Inserted a new atom at position {$pos} and now Stack looks like this:");
      var_dump(Stack::get());
    }
    return $atom;
  }

  public static function insertAfterCardResolution($atom, $raiseException = true)
  {
    if (Globals::enabledStackLogger()) {
      var_dump("[Stack logger] insertAfterCardResolution is called");
    }
    // Compute pos
    $top = Stack::top();
    if (!isset($top['src']) || !isset($top['src']['id'])) {
      if ($raiseException) {
        throw new \feException('No card resolution in progress');
      } else {
        self::insertOnTop($atom);
        return;
      }
    }

    $cId = $top['src']['id'];
    $stack = Stack::get();
    for ($i = 1; $i < count($stack); $i++) {
      if (!isset($stack[$i]['src']) || $stack[$i]['src']['id'] != $cId) {
        break;
      }
    }
    self::insertAfter($atom, $i);
  }

  public static function isItLastElimination()
  {
    $stack = Stack::get();
    return count($stack) <= 1 || $stack[1]['state'] != ST_PRE_ELIMINATE_DISCARD;
  }

  public static function clearAllLeaveLast()
  {
    $stack = Stack::get();
    Stack::set([Stack::getCtx(), end($stack)]);
  }

  public static function removePlayerAtoms($pId)
  {
    $stack = Stack::get();
    Utils::filter($stack, function ($atom) use ($pId) {
      return !isset($atom['pId']) || $atom['pId'] != $pId || $atom['uid'] == Stack::getCtx()['uid'];
    });
    Stack::set($stack);
  }

  /**
   * @param string $type
   */
  public static function removeAllAtomsWithState($state)
  {
    $stack = Stack::get();
    Utils::filter($stack, function ($atom) use ($state) {
      return $atom['state'] !== $state;
    });
    Stack::set($stack);
  }

  private static function get() {
    return Globals::getStack();
  }

  private static function set($stack) {
    Globals::setStack($stack);
  }

  public static function getCtx() {
    return Globals::getStackCtx();
  }

  private static function setCtx($ctx) {
    Globals::setStackCtx($ctx);
  }

  public static function newAtom($state, $atom = []) {
    $atom['state'] = $state;
    $atom = ['uid' => uniqid()] + $atom;
    return $atom;
  }

  public static function newSimpleAtom($state, $player) {
    $pId = is_int($player) ? $player : $player->getId();
    return self::newAtom($state, [
      'pId' => $pId,
    ]);
  }

  public static function finishState() {
    $ctx = Stack::getCtx();
    if (!Stack::isSuspended($ctx)) {
      $ctxIndex = Stack::getAtomIndexByUid($ctx['uid']);
      $currentStack = Stack::get();
      if (Globals::enabledStackLogger()) {
        var_dump('CTX:');
        var_dump($ctx);
        var_dump('INDEX:');
        var_dump($ctxIndex);
      }
      array_splice($currentStack, $ctxIndex, 1);
      if (Globals::enabledStackLogger()) {
        var_dump('NEW STACK:');
        var_dump($currentStack);
      }
      Stack::set($currentStack);
    } else {
      if (Globals::enabledStackLogger()) {
        var_dump('finishState() is called however the ctx atom is suspended');
        var_dump('CTX:');
        var_dump($ctx);
      }
    }
    if (Globals::enabledStackLogger()) {
      var_dump('FINISHED WITH FINISHING!');
    }
    Stack::resolve();
  }

  private static function isSuspended($atom) {
    return isset($atom['suspended']) && $atom['suspended'];
  }

  public static function suspendCtx() {
    $ctx = Stack::getCtx();
    if (!Stack::isSuspended($ctx)) {
      if (Globals::enabledStackLogger()) {
        var_dump('Ctx isSuspended is');
        var_dump(Stack::isSuspended($ctx));
        var_dump('BEFORE stack is');
        var_dump(Stack::get());
      }

      $stack = Stack::get();
      $ctxIndex = Stack::getAtomIndexByUid($ctx['uid']);
      $atom = array_splice($stack, $ctxIndex, 1);
      $atom[0]['suspended'] = true;
      array_splice($stack, $ctxIndex, 0, $atom);
      Stack::set($stack);
      Stack::setCtx($stack[$ctxIndex]);

      if (Globals::enabledStackLogger()) {
        var_dump('AFTER stack is');
        var_dump(Stack::get());
      }
    }
  }

  public static function unsuspendNext($state = null) {
    if ($state == null) {
      $atomIndex = Stack::getFirstSuspendedAtomIndex();
    } else {
      $atomIndex = Stack::getFirstAtomIndexByState($state);
    }
    $stack = Stack::get();
    // TODO: Convert atom to object to avoid this splicing
    if (Stack::isSuspended($stack[$atomIndex])) {
      $atom = array_splice($stack, $atomIndex, 1);
      unset($atom[0]['suspended']);
      array_splice($stack, $atomIndex, 0, $atom);
      Stack::set($stack);
    }

    if ($stack[$atomIndex]['uid'] == Stack::getCtx()['uid']) {
      Stack::setCtx($stack[$atomIndex]);
    }
  }

  private static function getAtomIndexByUid($uid) {
    return Stack::findBy('uid', $uid);
  }

  private static function getFirstAtomIndexByState($state) {
    return Stack::findBy('state', $state);
  }

  private static function getFirstSuspendedAtomIndex() {
    return Stack::findBy('suspended', true);
  }

  /**
   * Finds the first atom matching all $properties and returns its index
   *
   * @param array $properties
   * @return int -1 if no atom matches $properties, atom position otherwise
   */
  public static function getFirstIndex($properties)
  {
    $stack = Stack::get();
    foreach ($stack as $index => $atom) {
      foreach ($properties as $key => $value) {
        if (!isset($atom[$key]) || $atom[$key] !== $value) {
          continue 2; //go to next atom
        }
      }
      //current atom matches all properties
      return $index;
    }

    return -1;
  }

  private static function findBy($option, $value) {
    $ctxIndex = -1;
    $stack = Stack::get();
    foreach ($stack as $key => $atom) {
      if (isset($atom[$option]) && $atom[$option] == $value) {
        $ctxIndex = $key;
        break;
      }
    }
    if ($ctxIndex == -1) {
      throw new \BgaVisibleSystemException('Class Stack: ctxIndex == -1. Please report this to BGA bug tracker');
    }
    return $ctxIndex;
  }

  public static function updateAttackAtomAfterAction($missedNeeded, $abilityOrCardUsed)
  {
    $stack = Stack::get();
    $atomIndex = Stack::getFirstAtomIndexByState(ST_REACT);
    if ($missedNeeded === 0) {
      Stack::unsuspendNext(ST_REACT);
      if (Stack::getCtx()['state'] != ST_REACT) {
        array_splice($stack, $atomIndex, 1);
      }
    } else {
      $atom = array_splice($stack, $atomIndex, 1)[0];
      $atom['missedNeeded'] = $missedNeeded;
      $used = $atom['used'] ?? [];
      array_push($used, $abilityOrCardUsed);
      $atom['used'] = $used;
      array_splice($stack, $atomIndex, 0, [$atom]);
    }
    Stack::set($stack);
  }
}
