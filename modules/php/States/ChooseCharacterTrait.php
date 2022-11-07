<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Helpers\Sounds;
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
      $characters[$player->getId()] = [
        'characters' => array_map(function ($characterId) {
          return Players::getCharacter($characterId)->getUiData();
        }, $charactersIds)
      ];
    }
    return [
      '_private' => $characters,
    ];
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
    $this->gamestate->setPlayersMultiactive($playersIds, ST_CHARACTER_SETUP);
  }

  /**
   * actChooseCharacter: removes multiactive state for a player, sets a character and sends notification about that
   * @param int $characterId
   */
  public function actChooseCharacter($characterId)
  {
    $currentPlayer = Players::getCurrent();
    $currentPlayer->swapCharactersIfNeeded($characterId);
    $this->gamestate->setPlayerNonMultiactive($currentPlayer->getId(), ST_CHARACTER_SETUP);
  }

  /*
   * stChooseCharacter: pre-start state where we assign characters, show them to everyone and draw cards
   */
  public function stCharacterSetup()
  {
    foreach (Players::getLivingPlayers()->toArray() as $player) {
      $player->setupChosenCharacter();
    }
    // After setup, we need to re-fetch them from DB to get the updated information
    foreach (Players::getLivingPlayers()->toArray() as $player) {
      Notifications::characterChosen($player);
      $cards = Cards::deal($player->getId(), $player->getBullets());
      Notifications::drawCards($player, $cards);
      Notifications::updateHand($player);
    }
    Notifications::updateDistances();
    Notifications::playSound(Sounds::getSoundForStartGame());
    $this->gamestate->nextState(ST_START_OF_TURN);
  }
}