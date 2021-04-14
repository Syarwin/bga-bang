
/**
 * react: whenever a player react by passing or playing a card
 */
public function react($ids)
{
  $action = Log::getLastActions(['selection', 'react'])[0];
  $args = json_decode($action['action_arg'], true);
  $src = $action['action'] == 'react' ? $args['src'] : Cards::getCurrentCard();

  // Beer reaction when dying
  if ($src == 'hp') {
    if (is_null($ids)) {
      // PASS
      // nothing to do, i think
    } else {
      foreach ($ids as $i) {
        $card = Cards::getCard($i);
        $card->play($this, ['player' => null]);
        //          Cards::discardCard($card);
        Notifications::cardPlayed($this, $card, []);
        Log::addCardPlayed($this, $card, $args);
      }
    }
    return null;
  }

  // "Normal" react
  else {
    $card = $src instanceof Card ? $src : Cards::getCard($src);
    if (is_null($ids)) {
      // PASS
      return $card->pass($this);
    } else {
      $newstate = null;
      if (!is_array($ids)) {
        $ids = [$ids];
      }

      foreach ($ids as $id) {
        $reactionCard = Cards::getCard($id);
        $newstate = $card->react($reactionCard, $this);
        $this->onCardsLost();
      }
      return $newstate;
    }
  }
}


public function attack($playerIds, $checkMissed = true)
{
  foreach (Players::getPlayers($playerIds) as $player) {
    // Player has defensive equipment ? (eg Barrel)
    $reaction = $checkMissed ? $player->getDefensiveOptions() : $player->getBangCards();
    $reaction['selection'] = [];
    $handcount = $player->countCardsInHand();

    if (count($reaction['cards']) > 0 || $handcount > 0 || $reaction['character'] != null) {
      $reaction['src'] = Log::getCurrentCard();
      $reactions[$player->getId()] = $reaction; // Give him a chance to (pretend to) react
    } else {
      $curr = Players::getCurrentTurn();
      $byPlayer = $this->id == $curr ? $this : null;
      $newstate = $player->loseLife(); // Lost life immediatly
      $state = $newstate ?? $state;
    }
  }
  // Go to corresponding state
  if (count($reactions) > 0) {
    $card = Cards::getCard(Log::getCurrentCard());

    $src = $card->getName();
    if ($this->character == CALAMITY_JANET && $card->getType() == CARD_MISSED) {
      $src = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    $inactive =
      count($reactions) > 1
        ? clienttranslate('Players may react to ${src_name}')
        : clienttranslate('${actplayer} may react to ${src_name}');
    $args = [
      'msgActive' => clienttranslate('${you} may react to ${src_name}'),
      'msgWaiting' => clienttranslate(
        '${actplayer} has to react to ${src_name}. You may already select your reaction'
      ),
      'msgInactive' => $inactive,
      'src_name' => $src,
      'src' => Log::getCurrentCard(),
      'attack' => true,
      'order' => array_filter($playerIds, function ($id) use ($reactions) {
        return isset($reactions[$id]);
      }),
      '_private' => $reactions,
    ];
    Log::addAction('react', $args);

    return 'react';
  }

  return $state;
}



public function registerAbility($args = [])
{
  Log::addAction('registerAbility', ['id' => $this->id, 'args' => $args]);
}

/***************************************
 ****************************************
 ************** templates ***************
 ****************************************
 ***************************************/

public function statePhaseOne()
{
  return null;
}

public function useAbility($args)
{
}

/**
 * called whenever a card from the hand is lost(played, stolen, discarded, etc)
 * atm just for Suzy
 */
public function onCardsLost()
{
} //todo löschen wenn es mit checkHand funktioniert

public function checkHand()
{
}

/**
 * called whenever a player is eliminated
 * atm just for Vulture Sam
 */
public function onPlayerEliminated($player)
{
}

public function getAmountToCounterBang()
{
  return 1;
}
