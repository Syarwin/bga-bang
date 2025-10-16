<?php

declare(strict_types=1);

namespace Bang\Tests\PhaseOneDrawCards;

use PHPUnit\Framework\TestCase;

abstract class AbstractPhaseOneDrawCardsTest extends TestCase
{
    protected array $playerData = [
        'player_id' => 123,
        'player_no' => 1,
        'player_name' => 'Test',
        'player_color' => 'red',
        'player_eliminated' => 0,
        'player_zombie' => 0,
        'player_role' => RENEGADE,
        'player_score' => 0,
        'player_autopick_general_store' => true,
        'player_alt_character' => -1,
        'player_unconscious' => FULLY_ALIVE,
        'player_agreed_to_disclaimer' => 1,
    ];

    protected function getPlayerData(array $override = []): array
    {
        return array_merge($this->playerData, $override);
    }
}
