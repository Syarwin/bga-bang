
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

----------- type -------------------------
-- 1x: action
-- 2x: Equipment
-- 30: weapon
-- 10: bang
-- x1: evade
-- x2: rest
-- 99: character
----------- position ---------------------
--  >0: player id
-- -1: deck
-- -2: discard
-- -3: active
----------- value ---------------------
-- xC: Clovers
-- xS: Pikes
-- xD: Spades
-- xH: Hearts
CREATE TABLE IF NOT EXISTS `cards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `card_id` int NOT NULL,
  `card_type` int NOT NULL,
  `card_name` text NOT NULL,
  `card_text` text NOT NULL,
  `card_value` text NOT NULL,
  `card_position` int NOT NULL, 
  `card_onHand` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

----------- roles ---------------------
-- 0: Sheriff
-- 1: Deputy
-- 2: Outlaw
-- 3: Renegade 
CREATE TABLE IF NOT EXISTS `playerinfo` (
  `id` int unsigned NOT NULL, 
  `role` int NOT NULL,
  `character_id` int NOT NULL,
  `max_hp` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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