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
 * states.inc.php
 *
 * bang game states description
 *
 */

/*
	 Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
	 in a very easy way from this configuration file.

	 Please check the BGA Studio presentation about game state to understand this, and associated documentation.

	 Summary:

	 States types:
	 _ activeplayer: in this type of state, we expect some action from the active player.
	 _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
	 _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
	 _ manager: special type for initial and final state

	 Arguments of game states:
	 _ name: the name of the GameState, in order you can recognize it on your own code.
	 _ description: the description of the current game state is always displayed in the action status bar on
									the top of the game. Most of the time this is useless for game state with 'game' type.
	 _ descriptionmyturn: the description of the current game state when it's your turn.
	 _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
	 _ action: name of the method to call when this game state become the current game state. Usually, the
						 action method is prefixed by 'st' (ex: 'stMyGameStateName').
	 _ possibleactions: array that specify possible player actions on this step. It allows you to use 'checkAction'
											method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
	 _ transitions: the transitions are the possible paths to go from a game state to another. You must name
									transitions in order to use transition names in 'nextState' PHP method, and use IDs to
									specify the next game state for each transition.
	 _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
					 client side to be used on 'onEnteringState' or to set arguments in the gamestate description.
	 _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
														method).
*/

//		!! It is not a good idea to modify this file when a game is running !!


$machinestates = [
	/*
	 * BGA framework initial state. Do not modify.
	 */
	ST_GAME_SETUP => [
		'name' => 'gameSetup',
		'description' => '',
		'type' => 'manager',
		'action' => 'stGameSetup',
		'transitions' => [
			'' => ST_START_OF_TURN,
		],
	],


	ST_NEXT_PLAYER => [
		'name' => 'nextPlayer',
		'description' => '',
		'type' => 'game',
		'action' => 'stNextPlayer',
		'transitions' => [
			'next' => ST_NEXT_PLAYER,
			'start' => ST_START_OF_TURN,
			'endgame' => ST_GAME_END,
		],
		'updateGameProgression' => true,
	],

	ST_START_OF_TURN => [
		'name' => 'startOfTurn',
		'description' => '',
		'type' => 'game',
		'action' => 'stStartOfTurn',
		'transitions' => [
			'play'	=> ST_PLAY_CARD,
			'endgame' => ST_GAME_END,
		],
	],



	ST_PLAY_CARD => [
		'name' => 'playCard',
		'description' => clienttranslate('${actplayer} can play a card'),
		'descriptionmyturn' => clienttranslate('${you} can play a card'),
		'type' => 'activeplayer',
		'args' => 'argPlayCards',
		'possibleactions' => ['play', 'endTurn'],
		'transitions' => [
			'zombiePass' => ST_END_OF_TURN,
			'endTurn'		=> ST_END_OF_TURN,
			'awaitReaction' => ST_AWAIT_REACTION,
			'awaitMultiReaction' => ST_AWAIT_MULTIREACTION
		],
	],

	ST_AWAIT_REACTION => [
		'name' => 'awaitReaction',
		'description' => '',
		'type' => 'game',
		'action' => 'stAwaitReaction',
		'updateGameProgression' => true,
		'transitions' => ['awaitReaction' => ST_REACT, 'finishedReaction' => ST_PLAY_CARD]
	],

	ST_REACT => [
		'name' => 'react',
		'description' => clienttranslate('${actplayer} must react'),
		'descriptionmyturn' => clienttranslate('${you} must react'),
		'type' => 'activeplayer',
		'args' => 'argReact',
		'possibleactions' => ['react', 'pass'],
		'transitions' => [
			'react' => ST_END_REACT
		]
	],

	ST_AWAIT_MULTIREACTION => [
		'name' => 'awaitMultiReaction',
		'description' => '',
		'type' => 'game',
		'action' => 'stAwaitMultiReaction',
		'updateGameProgression' => true,
		'transitions' => ['awaitReaction' => ST_MULTIREACT]
	],

	ST_MULTIREACT => [
		'name' => 'multiReact',
		'description' => clienttranslate('${actplayer} must react'),
		'descriptionmyturn' => clienttranslate('${you} must react'),
		'type' => 'multipleactiveplayer',
		'args' => 'argMultiReact',
		'possibleactions' => ['react','pass'],
		'transitions' => [
			'finishedReaction' => ST_END_REACT
		]
	],

	ST_END_REACT => [
		'name' => 'endReaction',
		'description' => '',
		'type' => 'game',
		'action' => 'stEndReaction',
		'updateGameProgression' => true,
		'transitions' => ['finishedReaction' => ST_PLAY_CARD]
	],


	ST_END_OF_TURN => [
		'name' => 'endOfTurn',
		'description' => '',
		'type' => 'game',
		'action' => 'stEndOfTurn',
		'transitions' => [
			'next' => ST_NEXT_PLAYER,
			'endgame' => ST_GAME_END,
		],
	],

	/*
	 * BGA framework final state. Do not modify.
	 */
	ST_GAME_END => [
		'name' => 'gameEnd',
		'description' => clienttranslate('End of game'),
		'type' => 'manager',
		'action' => 'stGameEnd',
		'args' => 'argGameEnd'
	]

];
