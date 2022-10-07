<?php
namespace BANG\Models;

/*
 * EventCard:  class to handle blue cards
 */
class AbstractEventCard implements \JsonSerializable
{
  public function __construct($params = null)
  {
    if ($params != null) {
      $this->id = $params['id'];
      $this->lastCard = false;
    }
  }

  protected $id;

  // Static information about cards
  protected $type;
  protected $name;
  protected $text;
  protected $effect;
  protected $lastCard;
  protected $expansion;

  /*
   * getUiData: used in frontend to display cards
   */
  public function getUIData()
  {
    return [
      'type' => $this->type,
      'name' => $this->name,
      'text' => $this->text,
    ];
  }

  /*
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getExpansion()
  {
    return $this->expansion;
  }

  public function isLastCard()
  {
    return $this->lastCard;
  }

  public function getEffect()
  {
    return $this->effect;
  }

  public function resolveEffect($player = null)
  {
  }

  public function getColorOverride($currentColor)
  {
    return $currentColor;
  }

  public function nextPlayerClockwise()
  {
    return true;
  }

  public function getPhaseOneAmountOfCardsToDraw()
  {
    return 2;
  }

  public function isAbilityAvailable()
  {
    return true;
  }

  public function isBeerAvailable()
  {
    return true;
  }

  public function getBangsAmount()
  {
    return 1;
  }

  /*
   * jsonSerialize: used in frontend to manipulate cards
   */
  public function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
    ];
  }
}
