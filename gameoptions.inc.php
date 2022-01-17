<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * bang implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * bang game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in bang.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

require_once 'modules/constants.inc.php';

$game_options = [];

$game_preferences = [
  OPTION_GENERAL_STORE_LAST_CARD => [
    'name' => totranslate('Get General Store cards automatically'),
    'values' => [
      1 => ['name' => totranslate('Get card automatically if possible') ],
      0 => ['name' => totranslate('Always choose cards manually') ],
    ]
  ]
];
