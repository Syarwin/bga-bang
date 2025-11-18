<?php

namespace BANG\Models;

/**
 * EventCard:  class to handle blue cards
 *
 * @property-read int $type
 * @property-read string $name
 * @property-read string $text
 * @property-read int $effect
 * @property-read bool $lastCard
 * @property-read int $expansion
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

  protected int $id;

  // Static information about cards
  protected int $type;
  protected string  $name;
  protected string $text;
  protected int $effect;
  protected bool $lastCard;
  protected int $expansion;

  /*
   * getUiData: used in frontend to display cards
   */
  public function getUIData(): array
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
  public function getId(): int
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getExpansion(): int
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

  public function resolveEffect(Player $player): void
  {
  }

  /**
   * Returns the suit of all cards when this event is active or null if this event does not override suits.
   */
  public function getSuitOverride(): ?string
  {
    return null;
  }

  public function nextPlayerCounterClockwise(): bool
  {
    return false;
  }

  public function getPhaseOneAmountOfCardsToDraw(Player $player): int
  {
    return $player->defaultCardsToDraw();
  }

  public function isAbilityAvailable(): bool
  {
    return true;
  }

  public function isBeerAvailable(): bool
  {
    return true;
  }

  public function getBangsAmount(): int
  {
    return 1;
  }

  public function isBangStrictlyForbidden(): bool
  {
    return false;
  }

  public function isResurrectionEffect(?Player $player = null): bool
  {
    return false;
  }

  public function isPhaseOneSpecialDraw(): bool
  {
    return false;
  }

  public function getDrawCardsLocation(string $requestedLocation): string
  {
    return $requestedLocation;
  }

  public function isDistanceForcedToOne(): bool
  {
    return false;
  }

  public function isIgnoreCardsInPlay(): bool
  {
    return false;
  }

  public function isAimingCards(): bool
  {
    return false;
  }

  public function isBangCouldBePlayedWithAnotherBang(): bool
  {
    return false;
  }

  public function isCanPlayBlueGreenCards(): bool
  {
    return true;
  }

  public function isAllowPlayerPhaseOne(): bool
  {
    return true;
  }

  public function drawCardsPhaseOne(Player $player): void
  {

  }

  public function getRules(): array
  {
    return [
      RULE_ABILITY_AVAILABLE => $this->isAbilityAvailable(),
      RULE_BEER_AVAILABLE => $this->isBeerAvailable(),
      RULE_BANGS_AMOUNT_LEFT => $this->getBangsAmount(),
      RULE_PHASE_ONE_EVENT_SPECIAL_DRAW => $this->isPhaseOneSpecialDraw(),
    ];
  }

  /*
   * jsonSerialize: used in frontend to manipulate cards
   */
  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'colorOverride' => $this->getSuitOverride()
    ];
  }
}