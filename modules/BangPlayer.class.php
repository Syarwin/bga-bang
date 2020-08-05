<?php

/*
 * BangPlayer: all utility functions concerning a player
 */
class BangPlayer extends APP_GameClass
{
  private $id;
  private $no; // natural order
  private $name;
  private $color;
  private $eliminated = false;
  private $hp;
  private $zombie = false;

  public $character;

  public function __construct($row)
  {
    $this->id = (int) $row['player_id'];
    $this->no = (int) $row['player_no'];
    $this->name = $row['player_name'];
    $this->color = $row['player_color'];
    $this->eliminated = $row['player_eliminated'] == 1;
    $this->zombie = $row['player_zombie'] == 1;
    $this->hp = $row['player_score'];
    $this->role = $row['player_role'];
    $this->character = $char = new BangPlayerManager::$classes[$row['player_character']]();
  }


  public function getId(){ return $this->id; }
  public function getNo(){ return $this->no; }
  public function getName(){ return $this->name; }
  public function getColor(){ return $this->color; }
  public function getHp(){ return $this->hp; }
  public function getRole(){ return $this->role; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }

  public function setHp($hp){ $this->hp = $hp; }
  public function eliminate(){ $this->eliminated = false; }

  public function getUiData($currentPlayerId = null)
  {

    $current = $this->id == $currentPlayerId;

    return [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->getName(),
      'color'     => $this->color,
      'hand' => ($current) ? array_values(BangCardManager::getHand($currentPlayerId)) : BangCardManager::countCards('hand', $currentPlayerId),
      'role' => ($current || $this->role==SHERIFF) ? $this->role : null,
      'character' => $this->character->getName(),
      'powers' => $this->character->getText(),
      'bullets' => $this->character->getBullets(),
      'hp' => $this->hp,
    ];
  }

  public function save() {
    $eliminated = 0;
    if($this->eliminated) $eliminated = 1;
    $sql = "UPDATE players SET player_eliminated=$eliminated, player_score=" . $this->score;
    self::DbQuery($sql);
  }

  public function startOfTurn()
  {

  }
}
