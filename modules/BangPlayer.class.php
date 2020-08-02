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
  private $zombie = false;

  public $character;

  public function __construct($row)
  {
    $this->id = (int) $row['id'];
    $this->no = (int) $row['no'];
    $this->name = $row['name'];
    $this->color = $row['color'];
    $this->eliminated = $row['eliminated'] == 1;
    $this->zombie = $row['zombie'] == 1;
  }


  public function setupNewGame()
  {
/*
    $sqlSettlements = 'INSERT INTO piece (player_id, location) VALUES ';
    $values = [];
    for($i = 0; $i < 40; $i++){
      $values[] = "('" . $this->id . "','hand')";
    }
    self::DbQuery($sqlSettlements . implode($values, ','));

    $this->drawTerrain();
*/
  }


  public function getId(){ return $this->id; }
  public function getNo(){ return $this->no; }
  public function getName(){ return $this->name; }
  public function getColor(){ return $this->color; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }

  public function getUiData($currentPlayerId = null)
  {
    return [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->name,
      'color'     => $this->color,
    ];
  }

  public function startOfTurn()
  {

  }
}
