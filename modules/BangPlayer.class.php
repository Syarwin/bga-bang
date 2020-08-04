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
    $this->id = (int) $row['id'];
    $this->no = (int) $row['no'];
    $this->name = $row['name'];
    $this->color = $row['color'];
    $this->eliminated = $row['eliminated'] == 1;
    $this->zombie = $row['zombie'] == 1;
    $this->hp = $row['score'];
  }

  public function save() {
    $eliminated = 0;
    if($this->eliminated) $eliminated = 1;
    $sql = "UPDATE players SET player_eliminated=$eliminated, player_score=" . $this->score;
    self::DbQuery($sql);
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
  public function getHp(){ return $this->hp; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }

  public function setHp($hp){ $this->hp = $hp; }
  public function eliminate(){ $this->eliminated = false; }

  public function getUiData($currentPlayerId = null)
  {
    return [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->getName(),
      'color'     => $this->color,
    ];
  }

  public function startOfTurn()
  {

  }
}
