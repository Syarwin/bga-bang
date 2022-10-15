<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\Players;

trait ChooseCharacterTrait
{
  /**
   * Returns 2 characters to choose from
   * @return array
   */
  public function argChooseCharacter()
  {
    $players = Players::getAll();
    $characters = [];
    foreach ($players as $player) {
      $charactersIds = $player->getBothCharacters();
      $characters[$player->getId()] = array_map(function ($characterId) {
        return Players::getCharacter($characterId)->getUiData();
      }, $charactersIds);
    }
    return ['characters' => $characters];
  }

  /*
   * stPreChooseCharacter: simply redirects user to ST_CHOOSE_CHARACTER if some characters are not chosen or start turn otherwise
   */
  public function stPreChooseCharacter()
  {
    $allCharactersChosen = !Players::getAll()->map(function ($player) {
      return $player->isCharacterChosen();
    })->contains(false);
    if ($allCharactersChosen) {
      $this->gamestate->nextState(ST_START_OF_TURN);
    } else {
      $this->gamestate->nextState(ST_CHOOSE_CHARACTER);
    }
  }

  /*
   * stChooseCharacter: first state in the game for a player when they choose a character from 2 random ones
   */
  public function stChooseCharacter()
  {
    $playersIds = array_map(function ($player) {
      return $player->getId();
    }, Players::getLivingPlayers()->toArray());
    $this->gamestate->setPlayersMultiactive($playersIds, ST_START_OF_TURN);
  }

  /**
   * actChooseCharacter: removes multiactive state for a player, sets a character and sends notification about that
   * @param int $characterId
   */
  public function actChooseCharacter($characterId)
  {
    $currentPlayer = Players::getCurrent();
    $currentPlayer->setCharacter($characterId);
    $characterObj = Players::getCharacter($characterId);
    $cards = Cards::deal($currentPlayer->getId(), $characterObj->getBullets());
    Notifications::characterChosen($currentPlayer, $characterObj);
    Notifications::drawCards($currentPlayer, $cards, false, LOCATION_DECK, false);
    Notifications::updateDistances();
    $this->gamestate->setPlayerNonMultiactive($currentPlayer->getId(), ST_START_OF_TURN);
  }
}