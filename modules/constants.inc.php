<?php

/*
 * State constants
 */
define('ST_GAME_SETUP', 1);
define('ST_NEXT_PLAYER', 3);
define('ST_START_OF_TURN', 4);
define('ST_RESOLVE_STACK', 90);

define('ST_PLAY_CARD', 5);
define('ST_REACT', 8);
define('ST_REACT_BEER', 9);
define('ST_END_OF_TURN', 11);
define('ST_DISCARD_EXCESS', 12);
define('ST_DRAW_CARDS', 13);
define('ST_SELECT_CARD', 15);
define('ST_ACTIVE_DRAW_CARD', 17);
define('ST_ELIMINATE', 16);
define('ST_FLIP_CARD', 19);
define('ST_RESOLVE_FLIPPED', 18);
define('ST_TRIGGER_ABILITY', 20);
define('ST_PRE_GAME_END', 98);
define('ST_GAME_END', 99);

/*
 * Game options
 */
define('OPTION_GENERAL_STORE_LAST_CARD', 108);

/*
 * Game States(see sql)
 */
define('PLAY_CARD', 0);
define('CHOOSE_PLAYER', 1);
define('WAIT_REACTION', 2);

/*
 * Options constants
 */
define('TARGET_NONE', 0);
define('TARGET_CARD', 1);
define('TARGET_PLAYER', 2);
define('TARGET_CARDS', 3);
//define('RANDOM', 1);

/*
 * Extensions
 */
define('BASE_GAME', 0);
define('HIGH_NOON', 1);
define('DODGE_CITY', 2);
define('FISTFUL_OF_CARDS', 3);

/*
 * Cards
 */
define('BROWN', 0);
define('BLUE', 1);

define('CARD_SCHOFIELD', 0);
define('CARD_VOLCANIC', 1);
define('CARD_REMINGTON', 2);
define('CARD_REV_CARABINE', 3);
define('CARD_WINCHESTER', 4);
define('CARD_BANG', 5);
define('CARD_MISSED', 6);
define('CARD_STAGECOACH', 7);
define('CARD_WELLS_FARGO', 8);
define('CARD_BEER', 9);
define('CARD_GATLING', 10);
define('CARD_PANIC', 11);
define('CARD_CAT_BALOU', 12);
define('CARD_SALOON', 13);
define('CARD_DUEL', 14);
define('CARD_GENERAL_STORE', 15);
define('CARD_INDIANS', 16);
define('CARD_JAIL', 17);
define('CARD_DYNAMITE', 18);
define('CARD_BARREL', 19);
define('CARD_SCOPE', 20);
define('CARD_MUSTANG', 21);

define('CARD_PUNCH', 22);
define('CARD_SPRINGFIELD', 23);
define('CARD_CANNON', 24);
define('CARD_DODGE', 25);
define('CARD_WHISKY', 26);
define('CARD_TEQUILA', 27);
define('CARD_BRAWL', 28);
define('CARD_RAG_TIME', 29);

define('PASS', 999); //has to be bigger than the maximum number of cards in the game

/*
 * Roles
 */
define('SHERIFF', 0);
define('DEPUTY', 1);
define('OUTLAW', 2);
define('RENEGADE', 3);

/*
 * Characters
 */
define('PAUL_REGRET', 0);
define('SLAB_THE_KILLER', 1);
define('EL_GRINGO', 2);
define('ROSE_DOOLAN', 3);
define('KIT_CARLSON', 4);
define('CALAMITY_JANET', 5);
define('PEDRO_RAMIREZ', 6);
define('SUZY_LAFAYETTE', 7);
define('BLACK_JACK', 8);
define('SID_KETCHUM', 9);
define('VULTURE_SAM', 10);
define('BART_CASSIDY', 11);
define('JESSE_JONES', 12);
define('JOURDONNAIS', 13);
define('WILLY_THE_KID', 14);
define('LUCKY_DUKE', 15);

define('MOLLY_STARK', 16);
define('APACHE_KID', 17);
define('ELENA_FUENTE', 18);
define('TEQUILA_JOE', 19);
define('VERA_CUSTER', 20);
define('BILL_NOFACE', 21);
define('HERB_HUNTER', 22);
define('PIXIE_PETE', 23);
define('SEAN_MALLORY', 24);
define('PAT_BRENNAN', 25);
define('JOSE_DELGADO', 26);
define('CHUCK_WENGAM', 27);
define('BELLE_STAR', 28);
define('DOC_HOLYDAY', 29);
define('GREG_DIGGER', 30);

/*
 * Constants for card effects
 */
define('OTHER', 0);
define('BASIC_ATTACK', 1);
define('DRAW', 2);
define('DISCARD', 3);
define('LIFE_POINT_MODIFIER', 4);
define('RANGE_INCREASE', 5);
define('RANGE_DECREASE', 6);
define('DEFENSIVE', 7);
define('STARTOFTURN', 9);

define('NONE', 0);
define('INRANGE', 1);
define('SPECIFIC_RANGE', 2);
define('ALL_OTHER', 3);
define('ALL', 4);
define('ANY', 5);

define('CHECK_BARREL', true);
define('NO_CHECK_BARREL', false);

define('PUBLIC_SELECTION', -1);

/*
 * Constants for card symbols
 */
define('SYMBOL_BANG', 0);
define('SYMBOL_MISSED', 1);
define('SYMBOL_LIFEPOINT', 2);
define('SYMBOL_DISCARD', 3);
define('SYMBOL_DRAW', 4);
define('SYMBOL_ANY', 5);
define('SYMBOL_OTHER', 6);
define('SYMBOL_INRANGE', 7);
define('SYMBOL_RANGE1', 8);
define('SYMBOL_RANGE2', 9);
define('SYMBOL_RANGE3', 10);
define('SYMBOL_RANGE4', 11);
define('SYMBOL_RANGE5', 12);
define('SYMBOL_BOOK', 13);
define('SYMBOL_DRAW_HEART', 14);
define('SYMBOL_DYNAMITE', 15);

/*
 * Constants for locations
 */
define('LOCATION_SELECTION', 'selection');
define('LOCATION_HAND', 'hand');
define('LOCATION_INPLAY', 'inPlay');
define('LOCATION_FLIPPED', 'flipped');
define('LOCATION_DECK', 'deck');
define('LOCATION_DISCARD', 'discard');

/*
 * Constants for General Store preference
 */
define('GENERAL_STORE_MANUAL_CHOOSE', 0);
define('GENERAL_STORE_AUTO_PICK', 1);