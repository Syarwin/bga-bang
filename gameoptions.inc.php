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

$game_options = [
  OPTION_CHAR_1 => [
    'name' => totranslate('Character 1'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],

  OPTION_CHAR_2 => [
    'name' => totranslate('Character 2'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],

  OPTION_CHAR_3 => [
    'name' => totranslate('Character 3'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],

  OPTION_CHAR_4 => [
    'name' => totranslate('Character 4'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],

  OPTION_CHAR_5 => [
    'name' => totranslate('Character 5'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],

  OPTION_CHAR_6 => [
    'name' => totranslate('Character 6'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],

  OPTION_CHAR_7 => [
    'name' => totranslate('Character 7'),
    'values' => [
      100 => ['name' => 'Random'],
      PAUL_REGRET => ['name' => 'Paul Regret'],
      SLAB_THE_KILLER => ['name' => 'Slab the Killer'],
      EL_GRINGO => ['name' => 'El Gringo'],
      ROSE_DOOLAN => ['name' => 'Rose Doolan'],
      KIT_CARLSON => ['name' => 'Kit Carlson'],
      CALAMITY_JANET => ['name' => 'Calamity Janet'],
      PEDRO_RAMIREZ => ['name' => 'Pedro Ramirez'],
      SUZY_LAFAYETTE => ['name' => 'Suzy Lafayette'],
      BLACK_JACK => ['name' => 'Black Jack'],
      SID_KETCHUM => ['name' => 'Sid Ketchum'],
      VULTURE_SAM => ['name' => 'Vulture Sam'],
      BART_CASSIDY => ['name' => 'Bart Cassidy'],
      JESSE_JONES => ['name' => 'Jesse Jones'],
      JOURDONNAIS => ['name' => 'Jourdonnais'],
      WILLY_THE_KID => ['name' => 'Willy the Kid'],
      LUCKY_DUKE => ['name' => 'Lucky Duke'],
    ],
  ],
];
