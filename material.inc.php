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
 * material.inc.php
 *
 * bang game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


require_once("modules/Utils.class.php");
require_once("modules/BangLog.class.php");
require_once("modules/BangCards.class.php");
require_once("modules/BangPlayer.class.php");
require_once("modules/BangPlayerManager.class.php");
require_once("modules/BangCharacter.class.php");
foreach (BangPlayerManager::$classes as $className) {
  require_once("modules/characters/$className.class.php");
}
