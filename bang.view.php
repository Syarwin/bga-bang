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
 * bang.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in bang_bang.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_bang_bang extends game_view
{
  function getGameName() {
    return "bang";
  }

  function build_page( $viewArgs ) {
    // Get players & players number
    $players = $this->game->loadPlayersBasicInfos();
    $players_nbr = count( $players );

	$player_sorted = array();
	foreach($players as $k => $row) {
		$player_sorted[intval($row['player_no'])] = $row;
	}
	
	//$width = 450;
	$height = 20;				
	$n = floor($players_nbr / 2);
	$this->page->begin_block( "bang_bang", "playarealeft" );
	for($x = $players_nbr-1; $x >= $n; $x--) $this->page->insert_block("playarealeft", array('X' => $player_sorted[$x+1]['player_id']));
	
	$this->page->begin_block( "bang_bang", "playarearight" );
	for($x = 0; $x < $n; $x++) $this->page->insert_block("playarearight", array('X' => $player_sorted[$x+1]['player_id']));
		
  }
}
