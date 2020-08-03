
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
ALTER TABLE `player` ADD `player_character` INT(1) UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_bullets` INT(1) UNSIGNED NOT NULL;

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
-- -4-n depending on active card
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