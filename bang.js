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
  this._selectableCards = [];
  this._selectablePlayers = [];
  this._selectedCard = null;
  this._selectedCards = [];
  this._selectedPlayer = null;
  this._selectedOptionType = null;
  this._selectedOptionArg = null;
  this._dial = null;
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
  this._cards = [];
  Object.values(gamedatas.cards).forEach(card => {
    card.name = card.name.toUpperCase();
    this._cards[card.type] = card;
  });


  // Adding deck/discard
  dojo.place(this.format_block('jstpl_table', { deck : gamedatas.deck }), 'board');
  if(gamedatas.discard)
    this.addCard(gamedatas.discard, "discard");
  dojo.connect($("deck"), "onclick", () => this.onClickDeck() );
  dojo.connect($("discard"), "onclick", () => this.onClickDiscard() );

  // Usefull to reorder player board around the current player
  gamedatas.bplayers.forEach( player => {
    let isCurrent = player.id == this.player_id;

    if(player.role == null) player.role = 'hidden';
    player.handCount = isCurrent? player.hand.length : player.hand;
    player.powers = '<p>' + player.powers.join('</p><p>') + '</p>';

    dojo.place(this.format_block('jstpl_player', player), 'board');
    this.addTooltipHtml("player-character-" + player.id, this.format_block( 'jstpl_characterTooltip',  player));
    player.inPlay.forEach(card => this.addCard(card, 'player-inplay-' + player.id));
    dojo.connect($("player-character-" + player.id), "onclick", (evt) => { evt.preventDefault(); evt.stopPropagation(); this.onClickPlayer(player.id) });

    if(isCurrent){
      let role = this.getRole(player.role);
      dojo.place(this.format_block('jstpl_hand', role), 'board');
      player.hand.forEach(card => this.addCard(card, 'hand-cards') );
      this.addTooltip("role-card", role["role-text"], '');
    }
  });

  // Setting up player boards
  this.updatePlayers(gamedatas.bplayers);

  // Make the current player stand out
  this.setTurn(gamedatas.playerTurn);

	// Setup game notifications
	this.setupNotifications();
},

setTurn: function(playerId){
  dojo.query("div.bang-player-container").style("border", "1px solid rgba(50,50,50,0.8)");
  dojo.query("#bang-player-" + playerId + " .bang-player-container").style("border", "2px solid #" + this.gamedatas.players[playerId].color);
},


updatePlayers: function(players){
  var nPlayers = players.length;
  var playersAlive = players.reduce((carry,player) => carry + (player.eliminated? 0 : 1), 0);
  var playersEliminated = nPlayers - playersAlive;
  var newNo = 0;
  players.forEach( player => {
    if(!player.eliminated)
      player.no = newNo++;
  });
  var currentPlayerNo = players.reduce((carry, player) => (player.id == this.player_id)? player.no : carry, 0);

  players.forEach( player => {
    if(player.eliminated){
      dojo.addClass("overall_player_board_" + player.id, "eliminated");
      if(!$("player-role-" + player.id)){
        var role = this.getRole(player.role);
        dojo.place(this.format_block('jstpl_player_board_role', player), "player_board_" + player.id);
        this.addTooltip("player-role-" + player.id, role["role-name"], '');
      }

      if($("bang-player-" + player.id))
        dojo.destroy("bang-player-" + player.id);

      if(player.id == this.player_id && $("hand"))
        dojo.destroy("hand");
    } else {
      player.no = (player.no + playersAlive - currentPlayerNo) % playersAlive;
      dojo.attr("bang-player-" + player.id, "data-no", player.no);
    }
  });

  dojo.attr("board", "data-players", playersAlive);
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
  var activePlayers = (args.type == "activeplayer")? [args.active_player] : [];
  if(args.type == "multipleactiveplayer")
    activePlayers = args.multiactive;
  activePlayers.forEach(playerId => dojo.removeClass("bang-player-" + playerId, "inactive") );
  if(stateName == "playCard")
    this.setTurn(args.active_player);

	// Stop here if it's not the current player's turn for some states
	if (["drawCard", "playCard", "react", "multiReact", "discardExcess"].includes(stateName) && !this.isCurrentPlayerActive()) return;

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
 * onUpdateActionButtons:
 * 	called by BGA framework before onEnteringState
 *	in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function (stateName, args) {
	debug('Update action buttons: ' + stateName, args);

  if (stateName == "selectCard")
    this.addActionButton('buttonShowCards', _('Show cards'), () => this.dialogSelectCard(), null, false, 'blue');


	if (!this.isCurrentPlayerActive()) // Make sure the player is active
		return;

	if (stateName == "playCard"){
    if(args._private && args._private.character != null && this._selectedCard == null)
      this.makeCharacterAbilityUsable(args._private.character);

		this.addActionButton('buttonEndTurn', _('End of turn'), 'onClickEndOfTurn', null, false, 'blue');
  }

  if (stateName == "discardExcess")
		this.addActionButton('buttonCancelEnd', _('Cancel'), 'onClickCancelEndTurn', null, false, 'gray');

  if (stateName == "react" || stateName == "multiReact"){
    this.addActionButton('buttonSkip', _('Pass'), () => this.onClickPass(), null, false, 'blue');

    if(args._private && args._private.character != null && this._selectedCard == null && this._selectedCards.length == 0)
      this.makeCharacterAbilityUsable(args._private.character);
  }
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
  var cards = args._private.cards.filter(card => card.options != null);
  this.makeCardSelectable(cards, "selectCard");
},


/*
 * Given a list of cards, make them selectable
 */
makeCardSelectable: function(cards, action){
  this._action = action;
  if(this._action == "selectCard" || this._action == "discardExcess"){
    dojo.query("#hand .bang-card").addClass("unselectable");
    dojo.query("#bang-player-" + this.player_id + " .bang-card").addClass("unselectable");
  } else {
    if(action != "selectDialog")
      dojo.query(".bang-card").addClass("unselectable");
  }

  this._selectableCards = cards;
  this._selectableCards.forEach(card => {
    dojo.removeClass("bang-card-" + card.id, "unselectable");
    dojo.addClass("bang-card-" + card.id, "selectable");
  });

  if(this._action == "selectCard"){
    this.gamedatas.gamestate.descriptionmyturn = _("You can play a card");
    this.updatePageTitle();
  }
},


/*
 * Triggered whenever a player click on a card
 */
onClickCard: function(ocard){
  if(!this.isCurrentPlayerActive()) return;
  // Is the card in the discard ?
  if($("bang-card-" + ocard.id).parentNode.id == "discard")
    return this.onClickDiscard();

  // Is the card selectable ?
  var card = this._selectableCards.find(o => o.id == ocard.id);
  if(!card) return;

  var methodName = "onClickCard" + this._action.charAt(0).toUpperCase() + this._action.slice(1);
	if (this[methodName] !== undefined)
		this[methodName](card);
  else
    console.error("Trying to call " + methodName); // Should not happen
},


/*
 * Toggle a card : useful for multiple select or whenever we want a confirm button
 */
toggleCard: function(card){
  var domId = "bang-card-" + card.id;
  // Already selected, unselect it
  if(this._selectedCards.includes(card.id)){
    this._selectedCards = this._selectedCards.filter(id => id != card.id);
    dojo.removeClass(domId, "selected");
    this._selectableCards.forEach(c => dojo.query("#bang-card-" + c.id).addClass("selectable").removeClass("unselectable") );
  }
  // Not yet selected, add to selection
  else {
    if(this._selectedCards.length >= this._amount)
      return false;

    this._selectedCards.push(card.id);
    dojo.addClass(domId, "selected");

    if(this._selectedCards.length == this._amount){
      dojo.query(".bang-card.selectable").removeClass("selectable").addClass("unselectable");
      dojo.query(".bang-card.selected").removeClass("unselectable").addClass("selectable");
    }
  }

  return true;
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
    this.makePlayersCardsSelectable(card.options.targets);
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
makePlayersSelectable: function(players, append=false){
  if(!append) {
    this.removeActionButtons();
    this.gamedatas.gamestate.descriptionmyturn = _("You must choose a player");
    this.updatePageTitle();
    this.addActionButton('buttonCancel', _('Undo'), () => this.onClickCancelCardSelected(this._selectableCards), null, false, 'gray');
  }

  this._selectablePlayers = players;
  this._selectablePlayers.forEach(playerId => {
    this.addActionButton('buttonSelectPlayer' + playerId, this.gamedatas.players[playerId].name, () => this.onClickPlayer(playerId), null, false, 'blue');
    dojo.addClass("bang-player-" + playerId, "selectable");
  });
},

/*
 * Triggered when a player click on a player's board or action button
 */
onClickPlayer: function(playerId){
  debug("Click", playerId);
  if(!this._selectablePlayers.includes(playerId))
    return;
  if(this._action == 'drawCard') {
    this.onClickDraw(playerId);
    return;
  }
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
  this.gamedatas.gamestate.descriptionmyturn = _("You must choose a card in play or a player's hand");
  this.updatePageTitle();
  var oldSelectableCards = this._selectableCards;
  this.addActionButton('buttonCancel', _('Undo'), () => this.onClickCancelCardSelected(oldSelectableCards), null, false, 'gray');

  var cards = [];
  this._selectablePlayers = players;
  players.forEach(playerId => {
    dojo.addClass("bang-player-" + playerId, "selectable");
    dojo.query("#bang-player-" + playerId + " .bang-card").forEach(div => {
      cards.push({
        id:dojo.attr(div, "data-id"),
        playerId:playerId,
      })
    });
  });
  this.makeCardSelectable(cards, "selectOption");
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
  this._amount = null;
  this._selectedCards = [];
  this.makeCardSelectable(args._private.cards, "selectReact");
  var msg = this.isCurrentPlayerActive() ? args.msgActive : args.msgInactive;
  this.gamedatas.gamestate.descriptionmyturn = msg;
  this.updatePageTitle();
},
onEnteringStateMultiReact: function(args){
  this.onEnteringStateReact(args);
  var msg = this.isCurrentPlayerActive() ? args.msgActive : args.msgInactive;
  this.gamedatas.gamestate.descriptionmyturn = msg;
  this.updatePageTitle();
},


onClickCardSelectReact: function(card){
  // React with single card
  if(card.amount == 1 && this._amount == null){
    this._selectedCards = [card.id];
    this.onClickConfirmReact();
  }
  // React with several cards
  else {
    if(this._amount == null)
      this._amount = card.amount;

    // Toggle the card
    if(!this.toggleCard(card)) return;

    if(this._selectedCards.length < this._amount){
      if(this._selectedCards.length == 0)
        this._amount = null;
      this.removeActionButtons();
      this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
    } else {
      this.addActionButton('buttonConfirmReact', _('Confirm react'), 'onClickConfirmReact', null, false, 'blue');
    }
  }
},


onClickConfirmReact: function(){
  this.takeAction("react", {
    cards:this._selectedCards.join(";"),
  });
},



onClickPass: function(){
  this.checkAction('pass');
  this.takeAction("pass");
},






/********************
**** Select Card ****
********************/
onEnteringStateSelectCard: function(args){
  debug("Selecting cards", args);
  this.gamedatas.gamestate.args.cards = args.cards.length > 0? args.cards : (args._private? args._private.cards : this.getNBackCards(args.amount) );
  this.dialogSelectCard();
},


dialogSelectCard: function(){
  var args = this.gamedatas.gamestate.args;
  var dial = new ebg.popindialog();
  dial.create('selectCard');
  dial.setTitle(_("Pool of card"));
  dial.setContent(jstpl_dialog);
  args.cards.forEach(card => this.addCard(card, 'dialog-card-container') );
  dial.show();
  this._dial = dial;

  if(!this.isCurrentPlayerActive())
    return;

  this._amount = args.amountToPick;
  this._selectedCards = [];
  this.makeCardSelectable(args.cards, "selectDialog");
},


onClickCardSelectDialog: function(card){
  if(!this.toggleCard(card))
    return;

  dojo.empty("dialog-button-container");
  if(this._selectedCards.length == this._amount)
    this.addActionButton('buttonConfirmSelectCard', _('Confirm selection'), 'onClickConfirmSelection', 'dialog-button-container', false, 'blue');
},

onClickConfirmSelection: function(){
  if(this._dial != null)
    this._dial.destroy();
  this.takeAction("select", {
    cards:this._selectedCards.join(";"),
  });
},




/**********************************
*** Draw card / for some powers ***
**********************************/
onEnteringStateDrawCard: function(args){
  this._action = 'drawCard';
  var players = [];
  args._private.options.forEach(option => {
    switch(option) {
      case 'deck': this.makeDeckSelectable(); break;
      case 'discard': this.makeDiscardSelectable(); break;
      default: players.push(option); break;
    }
  });
  if(players.length > 0) this.makePlayersSelectable(players, true);
},

makeDeckSelectable:function(){
  this.addActionButton('buttonSelectDeck', _('Deck'), () => this.onClickDeck(), null, false, 'blue');
  this._isSelectableDeck = true;
  dojo.addClass("deck", "selectable");
},

onClickDeck: function(){
  if(!this.isCurrentPlayerActive() || !this._isSelectableDeck) return;

  if(this._action == "drawCard")
    this.onClickDraw('deck');
},

makeDiscardSelectable:function(){
  this.addActionButton('buttonSelectDiscard', _('Discard'), () => this.onClickDiscard(), null, false, 'blue');
  this._isSelectableDiscard = true;
  dojo.addClass("discard", "selectable");
},

onClickDiscard: function(){
  if(!this.isCurrentPlayerActive() || !this._isSelectableDiscard) return;

  if(this._action == "drawCard")
    this.onClickDraw('discard');
},



onClickDraw: function(arg) {
  this.takeAction('draw', { selected: arg });
},



/******************
*** Use ability ***
******************/
makeCharacterAbilityUsable:function(option){
  this._useAbilityOption = option;
  this.addActionButton('buttonUseAbility', _('Use ability'), () => this.onClickUseAbility(), null, false, 'blue');
},

onClickUseAbility: function(){
  //let OPTIONS_NONE = 0, OPTION_CARDS = 3;
  let SID_KETCHUM = 2, JOURDONNAIS = 4;
  this._selectedCards = [];
  if(this._useAbilityOption == JOURDONNAIS) {
    this.onClickConfirmUseAbility();
  } else if(this._useAbilityOption == SID_KETCHUM) {
    // Sid Ketchum power
    var cards = dojo.query("#hand .bang-card").map( card => { return {id : dojo.attr(card, 'data-id') }; })

    this._amount = 2;
    this.makeCardSelectable(cards, "useAbility");

    $("pagemaintitletext").innerHTML = _("You must select two cards");
    this.removeActionButtons();
    this.addActionButton('buttonCancelUseAbility', _('Cancel'), () => this.restartState() , null, false, 'gray');
  }
},


onClickCardUseAbility: function(card){
  this.toggleCard(card);

  if(this._selectedCards.length < this._amount){
    if($("buttonConfirmUseAbility"))
      dojo.destroy("buttonConfirmUseAbility");
  } else {
    this.addActionButton('buttonConfirmUseAbility', _('Confirm'), 'onClickConfirmUseAbility', null, false, 'blue');
  }
},

onClickConfirmUseAbility: function(){
  this.takeAction("useAbility", {
    cards:this._selectedCards.join(";"),
  });
},


////////////////////////////////
////////////////////////////////
///		End of turn / discard	 ///
////////////////////////////////
////////////////////////////////
onClickEndOfTurn: function(){
  this.takeAction("endTurn");
},

onClickCancelEndTurn: function(){
  this.takeAction("cancelEndTurn");
},

onEnteringStateDiscardExcess: function(args){
  debug("Discard excess", args);
  this._amount = args.amount;
  this._selectedCards = [];
  this.makeCardSelectable(args._private, "discardExcess");
},

onClickCardDiscardExcess: function(card){
  if(!this.toggleCard(card)) return;

  if(this._selectedCards.length < this._amount){
    this.removeActionButtons();
    this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
  } else {
    this.addActionButton('buttonConfirmDiscardExcess', _('Confirm discard'), 'onClickConfirmDiscardExcess', null, false, 'blue');
  }
},

onClickConfirmDiscardExcess: function(){
  this.takeAction("discardExcess", {
    cards:this._selectedCards.join(";"),
  });
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
  debug("Clearing everything");
  this._selectableCards = [];
  this._selectablePlayers = [];
  this._selectedCard = null;
  this._selectedPlayer = null;
  this._selectedOptionType = null;
  this._selectedOptionArg = null;
  this._isSelectableDeck = false;
  this._isSelectableDiscard = false;
  dojo.query(".bang-card").removeClass("unselectable selectable selected");
  dojo.query(".bang-player").removeClass("selectable");
  dojo.removeClass("deck", "selectable");
  dojo.removeClass("discard", "selectable");

	this.removeActionButtons();
	this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
},


restartState: function(){
  this.clearPossible();
  this.onEnteringState(this.gamedatas.gamestate.name, this.gamedatas.gamestate);
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
	return new Promise((resolve, reject) => {
		var animation = this.slideTemporaryObject(this.format_block(template, data), container, sourceId, targetId, duration, delay);
		setTimeout(function(){
			resolve();
		}, duration + delay)
	});
},

slideTemporaryToDiscard: function(card, sourceId, duration){
  var ocard = this.getCard(card, true);
  this.slideTemporary('jstpl_card', ocard, "board", sourceId, "discard", duration || 1000, 0)
  .then(() => this.addCard(card, "discard"));
},


getCardAndDestroy: function(card, val){
  var id = "bang-card-" + card.id;
  if($(id)){
    dojo.attr(id, "data-type", "empty");
    setTimeout(() => dojo.destroy(id), 700);
    return id;
  }
  else {
    return val;
  }
},

/*
 * getCard: factory function that create a card
 */
getCard: function(ocard, eraseId) {
  // Gets a card object ready to use in UI templates
  var card = {
    id: eraseId? -1 : ocard.id,
    type: ocard.type,
    name: '',
    text: '',
  	color: ocard.color,
 		value: ocard.value,
		flipped: (typeof ocard.flipped == "undefined" || !ocard.flipped)? "" : "flipped",
  };

	if(this._cards[ocard.type]){
		card.name = this._cards[ocard.type].name;
		card.text = this._cards[ocard.type].text;
	}

  return card;
},

getBackCard:function(){
  return {
    id:-1,
    color:0,
    value:0,
    name:'',
    text: '',
    type:"back",
    flipped:true,
  };
},

getNBackCards: function(n){
  return Array.apply(null, {length : n}).map(o => this.getBackCard());
},

addCard: function(ocard, container){
  var card = this.getCard(ocard);

  var div = dojo.place(this.format_block('jstpl_card', card), container);
  if(div.flipped == "")
    this.addTooltipHtml(div.id, this.format_block( 'jstpl_cardTooltip',  card));
  dojo.connect(div, "onclick", (evt) => { evt.preventDefault(); evt.stopPropagation(); this.onClickCard(card) });
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


incHandCount: function(playerId, amount){
  var currentHandCount = parseInt(dojo.attr("bang-player-" + playerId, "data-hand")),
      newHandCount = currentHandCount + parseInt(amount);
  dojo.attr("bang-player-" + playerId, "data-hand", newHandCount);
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
		['cardPlayed', 1500],
    ['cardLost', 1000],
    ['cardsGained', 1200],
    ['drawCard', 1000],
    ['updateHP', 200],
    ['updateHand', 200],
		['updateOptions', 200],
    ['playerEliminated', 1000],
    ['updatePlayers', 100],
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
  var currentHp = dojo.attr("bang-player-" + n.args.playerId, "data-bullets");
  dojo.query("#bang-player-" + n.args.playerId + " .bullet").forEach( (bullet, id) => {
    if( (currentHp <= id && id < n.args.hp) || (n.args.hp <= id && id < currentHp) ){
      dojo.removeClass(bullet, "pulse");
      bullet.offsetWidth;
      dojo.addClass(bullet, "pulse");
    }
  });
  dojo.attr("bang-player-" + n.args.playerId, "data-bullets", n.args.hp);
},

/*
* notification sent to all players when the hand count of a player changed
*/
notif_updateHand: function(n) {
  debug("Notif: update handcount of player", n);
  this.incHandCount(n.args.playerId, n.args.amount);
},


/*
* notification sent to all players when someone is eliminated
*/
notif_playerEliminated: function(n){
  debug("Notif: player eliminated", n);
  dojo.addClass('bang-player-' + n.args.who_quits, "eliminated");
},

notif_updatePlayers: function(n){
  debug("Notif: update players", n);
  this.updatePlayers(n.args.players);
},


/*
 * notification sent to all players when someone plays a card
 */
notif_cardPlayed: function(n) {
  debug("Notif: card played", n);
  var playerId = n.args.playerId,
      target = n.args.target,
      targetPlayer = n.args.targetPlayer;
  if(!targetPlayer && target == "inPlay")
    targetPlayer = playerId;

  var card = this.getCard(n.args.card, true);
  var sourceId = this.getCardAndDestroy(n.args.card, "player-character-" + playerId);
  if(targetPlayer){
    var duration = target == "inPlay"? 1500: 800;
    var targetId = (target == "inPlay"? "player-inplay-" : "player-character-") + targetPlayer
    this.slideTemporary('jstpl_card', card, "board", sourceId, targetId, duration, 0)
    .then(() => {
      // Add the card in front of player
      if(target == "inPlay")
        this.addCard(n.args.card, targetId)
      // Put the card in the discard pile
      else
        this.slideTemporaryToDiscard(n.args.card, targetId, 800);
    });
  }
  // Directly to discard
  else
    this.slideTemporaryToDiscard(n.args.card, sourceId, 1500);
},

/*
* notification sent to all players when someone gained a card (from deck or from someone else hand/inplay)
*/
notif_cardsGained: function(n) {
  if(this._dial != null)
    this._dial.destroy();

  debug("Notif: cards gained", n);
  var cards = n.args.cards.length > 0? n.args.cards.map(o => this.getCard(o)) : this.getNBackCards(n.args.amount);
  cards.forEach((card, i) => {
    let sourceId = (n.args.src == "deck")? "deck" : this.getCardAndDestroy(card, "player-character-" + n.args.victimId);
    let targetId = n.args.target == "hand"? (this.player_id == n.args.playerId ? "hand" : ("player-character-" + n.args.playerId)) : ("player-inplay-" + n.args.playerId);
    this.slideTemporary("jstpl_card", card, "board", sourceId, targetId, 800, 120*i).then(() => {
      if(targetId == "hand") this.addCard(card, 'hand-cards');
      if(n.args.target == "inPlay") this.addCard(card, targetId);
    });
  });

  this.incHandCount(n.args.playerId, n.args.amount);
  if(n.args.src == "deck")
    $("deck").innerHTML = parseInt($("deck").innerHTML) - n.args.amount;
  else if(n.args.src != "discard")
    this.incHandCount(n.args.victimId, -n.args.amount);
},

/*
* notification sent to all players when someone discard a card
*/
notif_cardLost: function(n) {
  debug("Notif: card lost", n);
  var sourceId = this.getCardAndDestroy(n.args.card, "player-character-" + n.args.playerId);
  this.slideTemporaryToDiscard(n.args.card, sourceId);
},


notif_drawCard: function(n){
  debug("Notif: card drawn", n);
  var card = n.args.card;
  card.flipped = true;
  //dojo.addClass("bang-card-" + n.args.src_id, "selected");
  this.addCard(card, "discard");
  setTimeout(() => dojo.removeClass("bang-card-" + card.id, "flipped"), 100);
  //setTimeout(() => dojo.removeClass("bang-card-" + n.args.src_id, "selected"), 1000);
},



notif_updateOptions: function(n){
  debug("Notif: update options", n);
	this.clearPossible();
	this.makeCardSelectable(n.args.cards, "selectCard");
},

	});
});
