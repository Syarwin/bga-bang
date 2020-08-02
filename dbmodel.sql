
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- bang implementation : © <Your name here> <Your email address here>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

CREATE TABLE IF NOT EXISTS `log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `round` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `card_id` int(11),
  `action` varchar(16) NOT NULL,
  `action_arg` json,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


-- see constants.inc.php --
ALTER TABLE `player` ADD `player_role` INT(1) UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_bullets` INT(1) UNSIGNED NOT NULL;


-- the deck of character : only usefull on the setup --
CREATE TABLE IF NOT EXISTS `characters` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- the deck of cards --
CREATE TABLE IF NOT EXISTS `cards` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


----------- game table ----------------
-- game_state: current state of the game, see below
-- game_text and game_options: if a player is required to choose an option(for ex. a target)
-- game_card: the id of the card that is currently handled
-- game_target: the target of the active card
-- game_player: the player whos turn it is
-- game_bangPlayed`: whether the card Bang has been played this turn
----------- state ---------------------
-- 0: play card
-- 1: choose player
-- 2: wait for reaction
CREATE TABLE IF NOT EXISTS `game` (
  `game_id` int unsigned NOT NULL AUTO_INCREMENT,
  `game_state` int NOT NULL,
  `game_text` text,
  `game_options` text,
  `game_card` int,
  `game_target` int,
  `game_player` int NOT NULL,
  `game_bangPlayed` tinyint NOT NULL,
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
