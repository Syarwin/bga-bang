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

//# sourceURL=santorini.js
//@ sourceURL=santorini.js
var isDebug = true;
var debug = isDebug ? console.info.bind(window.console) : function () { };
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter"], function (dojo, declare) {
  return declare("bgagame.bang", ebg.core.gamegui, {

/*
 * Constructor
 */
constructor: function () { },

/*
 * Setup:
 *  This method set up the game user interface according to current game situation specified in parameters
 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
 *
 * Params :
 *  - mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
 */
setup: function (gamedatas) {
  var _this = this;
  debug('SETUP', gamedatas);

  // Setup player's board
  gamedatas.fplayers.forEach(function(player){
//    dojo.place( _this.format_block( 'jstpl_player_panel', player) , 'overall_player_board_' + player.id );
//    player.tiles.forEach(_this.addTile.bind(_this));
  });


  // Setup game notifications
  this.setupNotifications();
},



/*
 * onEnteringState:
 * 	this method is called each time we are entering into a new game state.
 * params:
 *  - str stateName : name of the state we are entering
 *  - mixed args : additional information
 */
onEnteringState: function (stateName, args) {
  debug('Entering state: ' + stateName, args);

  // Stop here if it's not the current player's turn for some states
//  if (["playerBuild"].includes(stateName) && !this.isCurrentPlayerActive()) return;

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
 *  - str stateName : name of the state we are leaving
 */
onLeavingState: function (stateName) {
  debug('Leaving state: ' + stateName);
  this.clearPossible();
},


/*
 * onUpdateActionButtons:
 * 	called by BGA framework before onEnteringState
 *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function (stateName, args, suppressTimers) {
  debug('Update action buttons: ' + stateName, args); // Make sure it the player's turn

  if (!this.isCurrentPlayerActive())
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
/////////    Utils    //////////
////////////////////////////////
////////////////////////////////


/*
 * clearPossible:	clear every clickable space
 */
clearPossible: function clearPossible() {
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


///////////////////////////////////////////////////
//////   Reaction to cometD notifications   ///////
///////////////////////////////////////////////////

/*
 * setupNotifications:
 *  In this method, you associate each of your game notifications with your local method to handle it.
 *	Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" in the santorini.game.php file.
 */
setupNotifications: function () {
  var notifs = [
//    ['build', 1000],
  ];

  var _this = this;
  notifs.forEach(function (notif) {
    dojo.subscribe(notif[0], _this, "notif_" + notif[0]);
    _this.notifqueue.setSynchronous(notif[0], notif[1]);
  });
}

   });
});
