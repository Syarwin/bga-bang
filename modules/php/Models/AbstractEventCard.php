<?php
namespace BANG\Models;

/**
 * EventCard:  class to handle blue cards
 *
 * @property-read $type
 * @property-read string $name
 * @property-read string $text
 * @property-read $effect
 * @property-read $lastCard
 * @property-read $expansion
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

  public function getName()
  {
    return $this->name;
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

  /**
   * Returns the suit of all cards when this event is active or null if this event does not override suits.
   * @return string|null
   */
  public function getSuitOverride()
  {
    return null;
  }

  public function nextPlayerCounterClockwise()
  {
    return false;
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

  public function isBangStrictlyForbidden()
  {
    return false;
  }

  /**
   * @return boolean
   */
  public function isResurrectionEffect()
  {
    return false;
  }

  /*
   * jsonSerialize: used in frontend to manipulate cards
   */
  public function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'colorOverride' => $this->getSuitOverride()
    ];
  }
}