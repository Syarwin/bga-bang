<?php

/*
 * BangPlayer: all utility functions concerning a player
 */
class BangPlayer extends APP_GameClass
{
  private $game;
  private $id;
  private $no; // natural order
  private $name;
  private $color;
  private $eliminated = false;
  private $zombie = false;

  private $role;
  private $bullets;

  public function __construct($game, $row)
  {
    $this->game = $game;
    $this->id = (int) $row['id'];
    $this->no = (int) $row['no'];
    $this->name = $row['name'];
    $this->color = $row['color'];
    $this->eliminated = $row['eliminated'] == 1;
    $this->zombie = $row['zombie'] == 1;

    $this->role = (int) $row['role'];
    $this->bullets = (int) $row['bullets'];
  }


  public function setupNewGame()
  {
    // Draw a character
    $this->game->characters->drawCharacter($this->id);

    // Setup initial number of bullets/cards
    $bullets = $this->getCharacter()->getBullets();
    if($this->role == SHERIFF){
      $bullets++;
    }
    $this->setBullets($bullets);

    // Draw initial cards in hand
    $this->game->cards->pickCards($bullets, 'deck', $this->id);
  }


  public function getId(){ return $this->id; }
  public function getNo(){ return $this->no; }
  public function getName(){ return $this->name; }
  public function getColor(){ return $this->color; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }
  public function getRole(){ return $this->role; }
  public function getBullets(){ return $this->bullets; }
  public function getCharacter(){ return $this->game->characters->getCharacterOfPlayer($this->id); }

  public function getUiData($currentPlayerId = null)
  {
    $data = [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->name,
      'color'     => $this->color,
      'role'      => ($this->id == $currentPlayerId)? $this->role : NULL,
      'character' => $this->getCharacter(),
      'bullets'   => $this->bullets,
      'hand'      => ($this->id == $currentPlayerId)? $this->getCardsInHand() : [], // TODO : return the number of cards in hand
      'board'     => $this->getCardsInPlay(),
    ];
  }


  public function setBullets($bullets)
  {
    self::DbQuery("UPDATE player SET player_bullets = $bullets WHERE id = {$this->id}");
  }

  public function getCardsInHand()
  {
    return []; // TODO
  }

  public function getCardsInPlay()
  {
    return []; // TODO
  }
}
