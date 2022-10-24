
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
  `turn` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `card_id` int(11),
  `action` varchar(16) NOT NULL,
  `action_arg` json,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


-- see constants.inc.php --
ALTER TABLE `player` ADD `player_role` INT(1) UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_character` INT(1) UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_alt_character` INT(1) NOT NULL;
ALTER TABLE `player` ADD `player_character_chosen` TINYINT UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_bullets` INT(1) UNSIGNED NULL;
ALTER TABLE `player` ADD `player_activate` TINYINT UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_hp` TINYINT;
ALTER TABLE `player` ADD `player_autopick_general_store` TINYINT NOT NULL;

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_location` varchar(32) NOT NULL,
  `card_state` int(10),
  `type` int(10),
  `value` varchar(2) NOT NULL,
  `color` varchar(2) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Please make sure all fields correspond with constants for rules from constants.inc.php
CREATE TABLE IF NOT EXISTS `rules` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player_id` int(11) NOT NULL,
    `phase_one_amount_to_draw_beginning` int(1) NOT NULL,
    `phase_one_player_ability_draw` int(1) NOT NULL,
    `phase_one_amount_to_draw_end` int(1) NOT NULL,
    `ability_available` int(1) NOT NULL,
    `beer_availability` int(1) NOT NULL,
    `bangs_amount_left` int(1) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events` (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_location` varchar(32) NOT NULL,
    `card_state` int(10),
    `type` int(10),
    PRIMARY KEY (`card_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `global_variables` (
  `name` varchar(255) NOT NULL,
  `value` json,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
