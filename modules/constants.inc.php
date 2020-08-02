<?php

/*
 * State constants
 */
define('ST_GAME_SETUP', 1);
define('ST_NEXT_PLAYER', 3);
define('ST_START_OF_TURN', 4);
define('ST_PLAY_CARD', 5);
define('ST_AWAIT_REACTION',6);
define('ST_REACT', 7);
define('ST_END_OF_TURN', 8);
define('ST_GAME_END', 99);


/*
 * Game States(see sql)
 */
define('PLAY_CARD',0);
define('CHOOSE_PLAYER',1);
define('WAIT_REACTION',2);

/*
 * Options constants
 */
//define('OPTION_SETUP', 102);
//define('BASIC', 0);
//define('RANDOM', 1);

/*
 * Global game variables
 */
define('CURRENT_ROUND', 20);
define('FIRST_PLAYER', 21);



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

/*
 * Roles
 */
define('SHERIFF', 0);
define('DEPUTY', 1);
define('OUTLAW',2);
define('RENEGADE',3);


/*
 * Characters
 */
define('LUCKY_DUKE', 0);
define('EL_GRINGO', 1);
define('SID_KETCHUM', 2);
define('BART_CASSIDY', 3);
define('JOURDONNAIS', 4);
define('PAUL_REGRET', 5);
define('BLACK_JACK', 6);
define('PEDRO_RAMIREZ', 7);
define('SUZY_LAFAYETTE', 8);
define('KIT_CARLSON', 9);
define('VULTURE_SAM', 10);
define('JESSE_JONES', 11);
define('CALAMITY_JANET', 12);
define('SLAB_THE_KILLER', 13);
define('WILLY_THE_KID', 14);
define('ROSE_DOOLAN', 15);

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
 * Constants for card symbols
 */
define('BASIC_ATTACK', 0);
define('DRAW',1);
define('DEFENSIVE',2);
define('DISCARD',3);
define('LIFE_POINT_MODIFIER',4);
define('RANGE_INCREASE', 5);
define('RANGE_DECREASE', 6);
define('OTHER',7);

define('NONE',0);
define('INRANGE',1);
define('SPECIFIC_RANGE',2);
define('ALL_OTHER',3);
define('ALL',4);
define('ANY',5);
 
