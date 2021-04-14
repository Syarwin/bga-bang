<?php
namespace BANG\Models;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Notifications;

/*
 * AbstractCard: base class to handle actions cards
 */
class AbstractCard implements \JsonSerializable
{
  public function __construct($params = null)
  {
    if ($params != null) {
      $this->id = $params['id'];
      if (array_key_exists('value', $params) && array_key_exists('color', $params)) {
        $this->value = $params['value'];
        $this->color = $params['color'];
      }
    }
  }

  /*
   * Attributes
   */
  protected $id;
  protected $color;
  protected $value;

  // Static informations about cards
  protected $type;
  protected $name;
  protected $text;
  protected $symbols;
  protected $effect; // array with type, impact and sometimes range
  protected $copies = [];

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
   * jsonSerialize: used in frontend to manipulate cards
   */
  public function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'color' => $this->color,
      'value' => $this->value,
    ];
  }

  /*
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getText()
  {
    return $this->text;
  }

  public function getEffect()
  {
    return $this->effect;
  }

  public function getSymbols()
  {
    return $this->symbols;
  }

  public function getCopies()
  {
    return $this->copies;
  }

  public function getCopy()
  {
    return $this->copy;
  }

  public function getColor()
  {
    return null; // Will be overwrite by Blue/Brown class
  }

  public function getCopyValue()
  {
    return $this->value;
  }

  public function getCopyColor()
  {
    return $this->color;
  }

  public function getEffectType()
  {
    return $this->effect['type'];
  }

  public function isEquipment()
  {
    return false;
  }

  public function isAction()
  {
    return false;
  }

  public function isWeapon()
  {
    return $this->effect['type'] == WEAPON;
  }
  public function getNameAndValue()
  {
    $colors = [
      'H' => clienttranslate('Hearts'),
      'C' => clienttranslate('Clubs'),
      'D' => clienttranslate('Diamonds'),
      'S' => clienttranslate('Spades'),
    ];
    return $this->name . ' (' . $colors[$this->color] . ' ' . $this->value . ')';
  }

  public function wasPlayed()
  {
    return Cards::wasPlayed($this->id);
  }
  public function discard()
  {
    Cards::discard($this);
  }

  /**
   * getPlayOption : default function to know which card can be played by $player
   * return: type of option and targets if any
   */
  public function getPlayOptions($player)
  {
    return [];
  }

  /**
   * play : default function to play a card that. Can be used for cards that have only symbols
   * return: null if the game should continue the play loop, "stateName" if another state need to be called
   */
  public function play($player, $args)
  {
  }

  /**
   * getReactionOptions: default function to handle possible reaction (attack => defense)
   * return: list of options (cards/abilities) that can be used
   */
  public function getReactionOptions($player)
  {
    $options = $player->getDefensiveOptions();
    return $options;
  }

  /**
   * react: default function to handle reaction using a card
   */
  public function react($card, $player)
  {
    if ($this->effect['type'] == BASIC_ATTACK) {
      if ($card->getColor() == BROWN) {
        Notifications::cardPlayed($player, $card);
        Cards::play($card->id);
      } else {
        // TODO : what the heck is this supposed to do ? A non-brown card with basic attack effect ???
        // TODO : notification to highlight the card
        //        return $card->activate($player);
      }
    }
  }

  /**
   * pass: default function to handle reaction by clicking "pass" button
   */
  public function pass($player)
  {
    if ($this->effect['type'] == BASIC_ATTACK) {
      $player->loseLife();
    }
  }

  /**
   * function to overwrite by blue cards like barrel, jail, dynamite
   */
  public function activate($player, $args = [])
  {
  }

  public function startOfTurn($player)
  {
  }
}
