/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Bang implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Bang.js
 *
 * Bang user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

//# sourceURL=bang.js
//@ sourceURL=bang.js
var isDebug = true;
var debug = isDebug ? console.info.bind(window.console) : function () { };
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter"], function (dojo, declare) {
	return declare("bgagame.bang", ebg.core.gamegui, {

/*
 * Constructor
 */
constructor: function () {
},

/*
 * Setup:
 *	This method set up the game user interface according to current game situation specified in parameters
 *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
 *
 * Params :
 *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
 */
setup: function (gamedatas) {
	debug('SETUP', gamedatas);

  // Formatting cards
  Object.values(gamedatas.cards).forEach(card => {
    card.name = card.name.toUpperCase();
  });


	// Setting up player boards
  var nPlayers = gamedatas.bplayers.length;
  dojo.attr("board", "data-players", nPlayers); // Usefull to setup the layout

  // Usefull to reorder player board around the current player
  var currentPlayerNo = gamedatas.bplayers.reduce((carry, player) => (player.id == this.player_id)? player.no : carry, 0);
  gamedatas.bplayers.forEach( player => {
    let isCurrent = player.id == this.player_id;

    if(player.role == null) player.role = 'hidden';
    player.no = (player.no + nPlayers - currentPlayerNo) % nPlayers;
    player.handCount = isCurrent? player.hand.length : player.hand;
    player.powers = '<p>' + player.powers.join('</p><p>') + '</p>';

    dojo.place(this.format_block('jstpl_player', player), 'board');

    // TODO
    //player.inplay.forEach(card => this.addCard(card, 'player-board-' + player.id));

    if(isCurrent){
      dojo.place(this.format_block('jstpl_hand', { role : player.role }), 'board');
      player.hand.forEach(card => this.addCard(card, 'hand-cards') );
    }
  });

	// Setup game notifications
	this.setupNotifications();
},



/*
 * onEnteringState:
 * 	this method is called each time we are entering into a new game state.
 * params:
 *	- str stateName : name of the state we are entering
 *	- mixed args : additional information
 */
onEnteringState: function (stateName, args) {
	debug('Entering state: ' + stateName, args);

	// Stop here if it's not the current player's turn for some states
	if (["playCard"].includes(stateName) && !this.isCurrentPlayerActive()) return;

	// Call appropriate method
	var methodName = "onEnteringState" + stateName.charAt(0).toUpperCase() + stateName.slice(1);
	if (this[methodName] !== undefined)
		this[methodName](args.args);
},

/*
 * onLeavingState:
 * 	this method is called each time we are leaving a game state.
 *
 * params:
 *	- str stateName : name of the state we are leaving
 */
onLeavingState: function (stateName) {
	debug('Leaving state: ' + stateName);
	this.clearPossible();
},


/*
 * onLeavingState:
 * 	this method is called each time we are leaving a game state.
 *
 * params:
 *	- str stateName : name of the state we are leaving
 */
onLeavingState: function (stateName) {
	debug('Leaving state: ' + stateName);
	this.clearPossible();
},


/*
 * onUpdateActionButtons:
 * 	called by BGA framework before onEnteringState
 *	in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function (stateName, args, suppressTimers) {
	debug('Update action buttons: ' + stateName, args);

	if (!this.isCurrentPlayerActive()) // Make sure the player is active
		return;

/*
	if (stateName == "confirmTurn") {
		this.addActionButton('buttonConfirm', _('Confirm'), 'onClickConfirm', null, false, 'blue');
		this.addActionButton('buttonCancel', _('Restart turn'), 'onClickCancel', null, false, 'gray');
		if (!suppressTimers)
			this.startActionTimer('buttonConfirm');
	}
*/
},



////////////////////////////////
////////////////////////////////
/////////		Actions		//////////
////////////////////////////////
////////////////////////////////
/*
 * Main state of game : active player can play cards from his hand
 */
onEnteringStatePlayCard: function(args){
  // TODO : do it on server's side
  var cards = args._private.filter(card => card.options != null);
  this.makeCardSelectable(cards, "selectCard");
},


/*
 * Given a list of cards, make them selectable
 */
makeCardSelectable: function(cards, action){
  this._action = action;
  if(this.action == "selectCard"){
    dojo.query("#hand .bang-card").addClass("unselectable");
  } else {
    dojo.query(".bang-card").addClass("unselectable");
  }

  this._selectableCards = cards;
  this._selectableCards.forEach(card => {
    dojo.removeClass("bang-card-" + card.id, "unselectable");
    dojo.addClass("bang-card-" + card.id, "selectable");
  });

  this.gamedatas.gamestate.descriptionmyturn = _("You can play a card");
  this.updatePageTitle();
},


/*
 * Triggered whenever a player click on a card
 */
onClickCard: function(ocard){
  if(!this.isCurrentPlayerActive()) return;

  // Is the card selectable ?
  var card = this._selectableCards.find(o => o.id == ocard.id);
  if(!card) return;

  if(this._action == "selectCard")   this.onClickCardSelectCard(card);
  if(this._action == "selectOption") this.onClickCardSelectOption(card);
},


/*
 * Triggered whenever a player clicked on a selectable card to play
 */
onClickCardSelectCard: function(card){
  dojo.query("#hand .bang-card").removeClass("selectable").addClass("unselectable");
  dojo.removeClass("bang-card-" + card.id, "unselectable");
  dojo.addClass("bang-card-" + card.id, "selected");
  this._selectedCard = card;

  // What kind of target ?
  let OPTIONS_NONE = 0, OPTION_CARD = 1, OPTION_PLAYER = 2;
  if(card.options.type == OPTIONS_NONE) {
    this.onSelectOption();
  } else if(card.options.type == OPTION_PLAYER) {
    this.makePlayersSelectable(card.options.targets);
  } else if(card.options.type == OPTION_CARD){
    this.makePlayersCardSelectable(card.options.targets);
  }
},

/*
 * Triggerd when clicked on the undo button
 */
onClickCancelCardSelected: function(cards){
  this.clearPossible();
  this.makeCardSelectable(cards, "selectCard");
},


/*
 * Whenever the card and the option are selected, send that to the server
 */
onSelectOption: function(){
  if(!this.checkAction('play')) return;

  var data = {
    id:this._selectedCard.id,
    player:this._selectedPlayer,
    optionType:this._selectedOptionType,
    optionArg:this._selectedOptionArg,
  };
  debug(data);
  this.takeAction("playCard", data);
},



/********************
*** OPTION_PLAYER ***
********************/

/*
 * Make some players selectable with either action button or directly on the board
 */
makePlayersSelectable: function(players){
  this.removeActionButtons();
  this.gamedatas.gamestate.descriptionmyturn = _("You must choose a player");
  this.updatePageTitle();
  this.addActionButton('buttonCancel', _('Undo'), () => this.onClickCancelCardSelected(this._selectableCards), null, false, 'gray');

  this._selectablePlayers = players;
  this._selectablePlayers.forEach(playerId => {
    this.addActionButton('buttonSelectPlayer' + playerId, this.gamedatas.players[playerId].name, () => this.onClickPlayer(playerId), null, false, 'blue');
    // TODO : make selectable
  });
},

/*
 * Triggered when a player click on a player's board or action button
 */
onClickPlayer: function(playerId){
  if(!this._selectablePlayers.includes(playerId))
    return;

  this._selectedOptionType = "player";
  this._selectedPlayer = playerId;
  this.onSelectOption();
},




/******************
*** OPTION_CARD ***
******************/
/*
 * Make some players' cards selectable with sometimes the deck
 */
makePlayersCardsSelectable: function(players){
  this.removeActionButtons();
  this.gamedatas.gamestate.descriptionmyturn = _("You must choose a card in play or a hand");
  this.updatePageTitle();
  this.addActionButton('buttonCancel', _('Undo'), () => this.onClickCancelCardSelected(this._selectableCards), null, false, 'gray');

  var cards = [];
  players.forEach(playerId => {
    dojo.query("#bang-player-" + playerId + " .bang-card").forEach(div => {
      cards.push({
        id:dojo.attr(div, "data-id"),
        playerId:playerId,
      })
    });
  });
  this.makeCardSelectable(cards, "selectOption");
/*
  this._selectablePlayers = players;
  this._selectablePlayers.forEach(playerId => {
    this.addActionButton('buttonSelectPlayer' + playerId, this.gamedatas.players[playerId].name, () => onClickPlayer(playerId), null, false, 'blue');
    // TODO : make selectable
  });
*/
},


onClickCardSelectOption: function(card){
  this._selectedPlayer = card.playerId;
  this._selectedOptionType = "card";
  this._selectedOptionArg = card.id;
  this.onSelectOption();
},


////////////////////////////////
////////////////////////////////
/////////		Utils		////////////
////////////////////////////////
////////////////////////////////
/*
 * clearPossible:	clear every clickable space
 */
clearPossible: function () {
  this._selectableCards = [];
  this._selectablePlayers = [];
  this._selectedCard = null;
  this._selectedPlayer = null;
  this._selectedOptionType = null;
  this._selectedOptionArg = null;
  dojo.query(".bang-card").removeClass("unselectable selectable selected");

	this.removeActionButtons();
	this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
},

/*
 * takeAction: default AJAX call with locked interface
 */
takeAction: function (action, data, callback) {
	data = data || {};
	data.lock = true;
	callback = callback || function (res) { };
	this.ajaxcall("/bang/bang/" + action + ".html", data, this, callback);
},


/*
 * slideTemporary: a wrapper of slideTemporaryObject using Promise
 */
slideTemporary: function (template, data, container, sourceId, targetId, duration, delay) {
	var _this = this;
	return new Promise(function (resolve, reject) {
		var animation = _this.slideTemporaryObject(_this.format_block(template, data), container, sourceId, targetId, duration, delay);
		setTimeout(function(){
			resolve();
		}, duration + delay)
	});
},


/*
 * getCard: factory function that create a card
 */
getCard: function(ocard) {
  // Gets a card object ready to use in UI templates
  var card = this.gamedatas.cards[ocard.type] || {
    id: 0,
    type: 0,
    name: '',
  };
  card.id = ocard.id;
  card.color = ocard.color;
  card.value = ocard.value;

  return card;
},


addCard: function(ocard, container){
  var card = this.getCard(ocard);

  var div = dojo.place(this.format_block('jstpl_card', card), container);
  this.addTooltipHtml(div.id, this.format_block( 'jstpl_cardTooltip',  card));

  dojo.connect(div, "onclick", (e) => this.onClickCard(ocard) );
},



///////////////////////////////////////////////////
//////	 Reaction to cometD notifications	 ///////
///////////////////////////////////////////////////

/*
 * setupNotifications:
 *	In this method, you associate each of your game notifications with your local method to handle it.
 *	Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" in the santorini.game.php file.
 */
setupNotifications: function () {
	var notifs = [
		['debug',500],
		['choosePlayer', 500],
		['cardPlayed', 500],
		['handChange', 500],
		['lostLife', 500]
	];

	notifs.forEach(notif => {
		dojo.subscribe(notif[0], this, "notif_" + notif[0]);
		this.notifqueue.setSynchronous(notif[0], notif[1]);
	});
},


/** just for troubleshooting */
notif_debug:function(notif) {
	debug(notif);
},



/**
 * called when the player has to choose another player as target from something
 * notif.args: [
 * 		msg: the message to asking to choose
 *		targets: the names of the possible targets
 *		card: the id of the card that triggered this selection
 * ]
 */
notif_choosePlayer: function(notif) {
	debug('notif_choosePlayer', notif);
	var options = document.getElementById("options");
	options.style.removeProperty("display");
	var rect = document.getElementById('board').getBoundingClientRect();
	var height = (Object.keys(notif.args.targets).length+1-options.children.length)*40;
	while(options.children.length > 1) options.children[1].remove();

  notif.args.targets.forEach( player => {
    var p = dojo.place( this.format_block( 'jstpl_option', {
			name: player.name,
			id: player.id,
			color: player.color
		} ) , 'options' );
		dojo.connect(p,"onclick", this, "onSelectOption");
  })
	dojo.animateProperty({node:"board", properties:{height: rect.height + height}}).play();

	document.getElementById('optionsTitle').innerHTML = notif.args.msg;
	dojo.query(".card").forEach(function(node, idx, arr) {node.style.removeProperty("border")});
	document.getElementById('card_'+notif.args.card).style.border = "5px solid red";
},


/**
 * called when a player played a card
 * notif.args: [
 *		player: the id of the player who played it
 *		card: the id of the card that was played
 * ]
 */
notif_cardPlayed: function(notif) {
	// if the following element exists it's the current player who played it

},



/**
 * called when any player gained or lost a card
 * notif.args: [
 *		hands: the amount of cards in each players hand
 *		hand: the cards(id, card_name, card_text) of the current player (only if they changed)
 *		card: the card that was gained/lost (only for that player)
 *		gain: the id of the player who gained the card (if any)
 *		loose: the id of the player who lost the card (if any)
 * ]
 */
notif_handChange: function(notif) {

	var current = this.getCurrentId();
	var rect = document.getElementById("board").getBoundingClientRect();
	var origin = null;
	var dest = null;
	if(notif.args.gain > 0) {
		var e = null;
		dest = document.getElementById("hand_"+notif.args.gain).getBoundingClientRect();
		var r = null;
		if(notif.args.loose > 0) {
			origin = document.getElementById("hand_"+notif.args.loose).getBoundingClientRect();
			if(notif.args.gain==current) {
				dest.x = rect.width/2-origin.width/2;
				dest.y = rect.y+20;
			}
			if(notif.args.loose==current) {
				origin = document.getElementById("hand_"+notif.args.loose).getBoundingClientRect();
			}
		} else {
			origin = new DOMRect(rect.width/2-dest.width/2, rect.height/2-dest.height/2);
		}

	} else {
		if(notif.args.loose==current) {
			origin = document.getElementById("card_"+notif.args.card.id).getBoundingClientRect();
			document.getElementById("card_"+notif.args.card.id).destroy();
		} else {
			origin = document.getElementById("hand"+notif.args.card.loose).getBoundingClientRect();
		}
		dest = new DOMRect(rect.width/2-origin.width/2, rect.height/2-origin.height/2);
	}
	e = dojo.place( this.format_block( 'jstpl_card', {
				   width: origin.width,
				   height: origin.height,
				   x:origin.x-rect.x,
				   y:origin.y-rect.y
			} ) , 'board' );
	dojo.animateProperty({node:"tmpcard",
			properties: {
				left:dest.x-rect.x,
				top:dest.y-rect.y},
			onEnd: function() {
				for(var id in notif.cards) {
					document.getElementById('handCount_'+id).innerHTML=notif.hands[id];
				}
				if(notif.args.gain==current) fillHand();
				e.remove();
			}
		}).play();
	if(notif.args.hand != null)
		this.fillHand(notif.args.hand);
},


/**
 * called when any player lost a life
 * notif.args: [
 *		id: the id of the player who lost a life
 *		hp: the remaining hp
 * ]
 */
notif_lostLife: function(notif) {
	// todo
}



	});
});
