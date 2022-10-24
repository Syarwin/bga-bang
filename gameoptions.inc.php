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
 *        with the same ID (see "initGameStateLabels" in banghighnoon.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

require_once 'modules/constants.inc.php';

$characters = [
  100 => ['name' => totranslate('Random'), 'tmdisplay' => totranslate('Random')],
  PAUL_REGRET => ['name' => totranslate('Paul Regret'), 'tmdisplay' => totranslate('Paul Regret')],
  SLAB_THE_KILLER => ['name' => totranslate('Slab the Killer'), 'tmdisplay' => totranslate('Slab the Killer')],
  EL_GRINGO => ['name' => totranslate('El Gringo'), 'tmdisplay' => totranslate('El Gringo')],
  ROSE_DOOLAN => ['name' => totranslate('Rose Doolan'), 'tmdisplay' => totranslate('Rose Doolean')],
  KIT_CARLSON => ['name' => totranslate('Kit Carlson'), 'tmdisplay' => totranslate('Kit Carlson')],
  CALAMITY_JANET => ['name' => totranslate('Calamity Janet'), 'tmdisplay' => totranslate('Calamity Janet')],
  PEDRO_RAMIREZ => ['name' => totranslate('Pedro Ramirez'), 'tmdisplay' => totranslate('Pedro Ramirez')],
  SUZY_LAFAYETTE => ['name' => totranslate('Suzy Lafayette'), 'tmdisplay' => totranslate('Suzy Lafayette')],
  BLACK_JACK => ['name' => totranslate('Black Jack'), 'tmdisplay' => totranslate('Black Jack')],
  SID_KETCHUM => ['name' => totranslate('Sid Ketchum'), 'tmdisplay' => totranslate('Sid Ketchum')],
  VULTURE_SAM => ['name' => totranslate('Vulture Sam'), 'tmdisplay' => totranslate('Vulture Sam')],
  BART_CASSIDY => ['name' => totranslate('Bart Cassidy'), 'tmdisplay' => totranslate('Bart Cassidy')],
  JESSE_JONES => ['name' => totranslate('Jesse Jones'), 'tmdisplay' => totranslate('Jesse Jones')],
  JOURDONNAIS => ['name' => totranslate('Jourdonnais'), 'tmdisplay' => totranslate('Jourdonnais')],
  WILLY_THE_KID => ['name' => totranslate('Willy the Kid'), 'tmdisplay' => totranslate('Willy the Kid')],
  LUCKY_DUKE => ['name' => totranslate('Lucky Duke'), 'tmdisplay' => totranslate('Lucky Duke')],
];

$game_options = [
  OPTION_CHOOSE_CHARACTERS => [
    'name' => totranslate('Choose characters'),
    'default' => CHARACTERS_RANDOM,
    'values' => [
      CHARACTERS_RANDOM => [
        'name' => totranslate('Randomly'),
        'description' => totranslate('Character will be chosen randomly for each player'),
      ],
      CHARACTERS_CHOOSE => [
        'name' => totranslate('Manually'),
        'description' => totranslate('Each player chooses one of two random characters on game start'),
      ],
    ],
  ],

  OPTION_CHAR_1 => [
    'name' => totranslate('Character 1'),
    'values' => $characters,
  ],

  OPTION_CHAR_2 => [
    'name' => totranslate('Character 2'),
    'values' => $characters,
  ],

  OPTION_CHAR_3 => [
    'name' => totranslate('Character 3'),
    'values' => $characters,
  ],

  OPTION_CHAR_4 => [
    'name' => totranslate('Character 4'),
    'values' => $characters,
  ],

  OPTION_CHAR_5 => [
    'name' => totranslate('Character 5'),
    'values' => $characters,
  ],

  OPTION_CHAR_6 => [
    'name' => totranslate('Character 6'),
    'values' => $characters,
  ],

  OPTION_CHAR_7 => [
    'name' => totranslate('Character 7'),
    'values' => $characters,
  ],
];

$game_preferences = [
  OPTION_GENERAL_STORE_LAST_CARD => [
    'name' => totranslate('Get General Store cards automatically'),
    'values' => [
      1 => ['name' => totranslate('Get card automatically if possible')],
      0 => ['name' => totranslate('Always choose cards manually')],
    ],
  ],
];
