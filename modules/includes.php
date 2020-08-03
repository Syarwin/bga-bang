<?php
require_once("constants.inc.php");
require_once("Utils.class.php");
require_once("BangLog.class.php");

require_once("BangCardManager.class.php");
require_once("BangCard.class.php");
foreach (BangCardManager::$classes as $className) {
  require_once("cards/$className.class.php");
}

require_once("BangPlayerManager.class.php");
require_once("BangPlayer.class.php");

require_once("BangCharacterManager.class.php");
require_once("BangCharacter.class.php");
foreach (BangCharacterManager::$classes as $className) {
  require_once("characters/$className.class.php");
}
?>
