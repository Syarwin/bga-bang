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


  // Adding deck/discard
  dojo.place(this.format_block('jstpl_table', { deck : gamedatas.deck }), 'board');
  if(gamedatas.discard)
    this.addCard(gamedatas.discard, "discard");

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
    this.addTooltipHtml("player-character-" + player.id, this.format_block( 'jstpl_characterTooltip',  player));
    player.inPlay.forEach(card => this.addCard(card, 'player-inplay-' + player.id));

    if(isCurrent){
      let role = this.getRole(player.role);
      dojo.place(this.format_block('jstpl_hand', role), 'board');
      player.hand.forEach(card => this.addCard(card, 'hand-cards') );
      this.addTooltip("role-card", role["role-text"], '');
    }
  });

  this.setTurn(gamedatas.turn);

	// Setup game notifications
	this.setupNotifications();
},

setTurn: function(playerId){
  dojo.addClass("bang-player-" + playerId, "turn");
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

  dojo.query(".bang-player").addClass("inactive");
  // TODO : handle multiple active
  dojo.removeClass("bang-player-" + args.active_player, "inactive");

	// Stop here if it's not the current player's turn for some states
	if (["playCard", "react"].includes(stateName) && !this.isCurrentPlayerActive()) return;

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
  if(this._action == "selectCard"){
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
    this.makePlayersCardSelectable(card.options.targets, card.option.deck);
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
makePlayersCardsSelectable: function(players, deck){
  this.removeActionButtons();
  this.gamedatas.gamestate.descriptionmyturn = _("You must choose a card or a deck");
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
  this._selectedOptionType = "inplay";
  this._selectedOptionArg = card.id;
  this.onSelectOption();
},




/************
*** REACT ***
************/

/*
 * React state : active player can play cards from his hand in reaction
 */
onEnteringStateReact: function(args){
  this.makeCardSelectable(args._private, "selectCard");
  this.addActionButton('buttonSkip', _('Pass'), () => this.onClickPass(), null, false, 'blue');
},


onClickPass: function(){
  this.checkAction('pass');
  this.takeAction("pass");
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


/*
 * getRole: factory function that return a role
 */
getRole: function(roleId){
  const roles = {
    0: {
      "role":0,
      "role-name": _("Sheriff"),
      "role-text": _("Kill all the Outlaws and the Renegade!")
    },
    1:{
      "role":1,
      "role-name":_("Vice"),
      "role-text":_("Protect the Sheriff! Kill all the Outlaws and the Renegade!"),
    },
    2:{
      "role":2,
      "role-name":_("Outlaw"),
      "role-text":_("Kill the Sheriff!"),
    },
    3:{
      "role":3,
      "role-name":_("Renegade"),
      "role-text":_("Be the last one in play!"),
    },
  };
  return roles[roleId];
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
		['cardPlayed', 2000],
    ['updateHP', 500],
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


/*
* notification sent to all players when a player looses or gains hp
*/
notif_updateHP: function(n) {
  debug("Notif: hp changed", n);
  dojo.attr("bang-player-" + n.args.id, "data-bullets", n.args.hp);
},

/*
* notification sent to all players when the hand count of a player changed
*/
notif_updateHand: function(n) {
  debug("Notif: update handcount of player", n);
  var currentHandCount = parseInt(dojo.attr("bang-player-" + n.args.player, "data-hand")),
      newHandCount = currentHandCount + parseInt(n.args.amount);
  dojo.attr("bang-player-" + n.args.player, "data-hand", newHandCount);
},



/*
 * notification sent to all players when someone plays a card
 */
notif_cardPlayed: function(n) {
  debug("Notif: card played", n);
  var playerId = n.args.player,
      target = n.args.target,
      targetPlayer = n.args.targetPlayer;

  if(!targetPlayer && target == "inPlay")
    targetPlayer = playerId;

  var card = this.getCard(n.args.card);
  card.id = -1;

  var sourceId = "player-character-" + playerId;
  if(this.player_id == playerId){
    sourceId = "bang-card-" + n.args.card.id;
    dojo.attr(sourceId, "data-type", "empty");
    setTimeout(() => dojo.destroy(sourceId), 700);
  }

  if(targetPlayer){
    var targetId = (target == "inPlay"? "player-inplay-" : "player-character-") + targetPlayer
    this.slideTemporary('jstpl_card', card, "board", sourceId, targetId, 1000, 0)
    .then(() => {
      // Add the card in front of player
      if(target == "inPlay"){
        this.addCard(n.args.card, targetId)
      }
      // Put the card in the discard pile
      else {
        this.slideTemporary('jstpl_card', card, "board", targetId, "discard", 1000, 0)
        .then(() => this.addCard(n.args.card, "discard"));
      }
    });
  }
  // Directly to discard
  else {
    this.slideTemporary('jstpl_card', card, "board", sourceId, "discard", 1000, 0)
    .then(() => this.addCard(n.args.card, "discard"));
  }
},

/*
* notification sent only to the player who got Cards to his Hand
*/
notif_cardsGained: function(notif) {
 var cards = notif.args.cards; //array of gained cards all having id, type, color, value (the last 2 are from type_arg)
 if(notif.args.src == 'deck') {

 } else {
   var playerId = notif.args.src;
 }
},

/*
* notification sent only to the player who lost Cards from his and or playarea
*/
notif_cardsLost: function(notif) {
 var cards = notif.args.cards; //array of the ids of the lost cards
},



	});
});
