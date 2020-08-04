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
		constructor: function () { },

		/*
		 * Setup:
		 *	This method set up the game user interface according to current game situation specified in parameters
		 *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
		 *
		 * Params :
		 *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
		 */
		setup: function (gamedatas) {
			var _this = this;
			debug('SETUP', gamedatas);
      var myId = gamedatas.currentPlayer.id;

			// Setting up player boards
      gamedatas.bplayers.forEach( player => {
        var name = player.name;
				if(player.id == gamedatas.sheriff) name += " (S)";	// todo replaye with design for sheriff
				name += " (" + player.character + ")";

				document.getElementById('title_' + player.id).innerHTML = name;
				document.getElementById('title_' + player.id).style.color = "#" + player.color;
				 //this.setText("#handCount_" + player_id, player.hand);
				document.getElementById('handCount_' + player.id).innerHTML = player.hand;
				this.addTooltipHtml('title_' + player.id, this.createTooltip(player.character, player.powers[0]));	 //todo how does the translate work?
				this.setText("#deck", gamedatas.deck);
				document.getElementById('playarea_' + gamedatas.currentPlayer.id).classList.add("self");
      });

			this.fillHand(gamedatas.currentPlayer.hand);
			for( var i in gamedatas.cardsInPlay) {
				var card = gamedatas.cardsInPlay[i];

				var div = document.createElement("DIV");
				div.classList.add("card");
				div.id = "card_" + card.id;
				document.getElementById("cards_" + card.card_position).appendChild(div);
				this.addTooltipHtml(div.id, this.createTooltip(card.card_name, card.card_text));
			}
			dojo.connect(window,"onresize",this, 'resize');
			var playareas = document.getElementById('gameareas');
			var h = playareas.children[0].children.length * 400;
			playareas.style.height = h + "px";
			h += 350;
			board.style.height = h + "px";


			dojo.query('#checkDesc').connect('onclick',this,'toggleDesc');

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
		//	if (["playerBuild"].includes(stateName) && !this.isCurrentPlayerActive()) return;

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
		/////////		Utils		//////////
		////////////////////////////////
		////////////////////////////////
		resize: function(event) {


		},

		createTooltip: function(name, desc) {
			var div = document.createElement("DIV");
			div.classList.add("tooltip");
			div.innerHTML = "<h3>" + name + "</h3>" + desc;

			var tmp = document.createElement("DIV");
			tmp.appendChild(div);



			return tmp.innerHTML;
		},

		setText: function(query, value) {
			dojo.query(query).forEach(function(node, index, arr){
				node.innerHTML = value;
			  });
		},

		removeOptions: function() {
			var options = document.getElementById("options");
			while(options.children.length > 1) options.children[1].remove();
			options.style.display = "none";
		},

        getCurrentId: function() {
			return document.getElementsByClassName("self")[0].id.split("_")[1];
		},

		getBGPosition: function(pos) {
			var x = pos%10;
			var y = Math.floor(pos/10);
			return "-" + Math.floor(x*157.6) + "px -" + (y*244) + "px";
		},

		fillHand: function(cards) {
			var hand = document.getElementById("yourHand");
			while(hand.children.length>0) hand.children[0].remove();
			for( var i in cards) {
				var card = cards[i];

				var div = document.createElement("DIV");
				div.classList.add("bigcard");
				div.classList.add("card");
				div.id = "card_" + card.id;
				var pos = parseInt(card.id)+20;
				div.style.backgroundPosition = this.getBGPosition(pos);

				var desc = document.createElement("P");
				desc.classList.add("description");
				desc.innerHTML = card.card_text;
				div.appendChild(desc);

				hand.appendChild(div);
				this.addTooltipHtml(div.id, this.createTooltip(card.card_name, card.card_text));
			}
			dojo.query('.card').connect('onclick',this,'onChooseCard');
		},

		///////////////////////////////////////////////////
        //// Player's action
		onChooseCard: function( evt ) {
			dojo.stopEvent(evt);
			if(!evt.currentTarget.id.startsWith("card_")) return;
			var id = evt.currentTarget.id.split('_')[1];
			if(this.checkAction('play')) {
				this.ajaxcall("/bang/bang/playCard.html", {id:id}, this, function(result){});
				return;
			}
			if(this.checkAction('react')) {
				this.ajaxcall("/bang/bang/selectOption.html", {id:id}, this, function(result){});
				return;
			}
		},

		onSelectOption: function( evt ) {
			dojo.stopEvent(evt);
			var id = evt.currentTarget.id.split('_')[1];
			this.removeOptions();
			this.ajaxcall("/bang/bang/selectOption.html", {id:id}, this, function(result){});
		},

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


		toggleDesc: function(evt) {
			if(evt.target.checked) {
				dojo.query(".description").forEach(function(node,idx,arr){ node.style.display = "block";});
			} else {
				dojo.query(".description").forEach(function(node,idx,arr){ node.style.display = "none";});
			}
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

			var _this = this;
			notifs.forEach(function (notif) {
				dojo.subscribe(notif[0], _this, "notif_" + notif[0]);
				_this.notifqueue.setSynchronous(notif[0], notif[1]);
			});
		},

		/** just for troubleshooting */
		notif_debug:function(notif) {
			console.log('DEBUG');
			console.log(notif);
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
			var card = document.getElementById('card_' + notif.args.card);
			var r = null;
			if(card == null) {
				card = document.getElementById('hand_' + notif.args.player);
				p = 3;
				r = card.getBoundingClientRect();

			} else {
				var p = 2;
				r = card.getBoundingClientRect();
				r.width -= 10;
				r.height -= 10;
				r.x += 5;
				r.y += 5;
				card.remove();
			}
			var e = document.getElementById('handCount_' + notif.args.player);
			e.innerHTML = parseInt(e.innerHTML) - 1;

			//todo reveal

			var rect = document.getElementById('board').getBoundingClientRect();
			var pos = this.getBGPosition(parseInt(notif.args.card)+20);
			console.log(pos);
			e = dojo.place( this.format_block( 'jstpl_card', {
				pos: pos,
				x:r.x-rect.x,
				y:r.y-rect.y
			} ) , 'board' );

			dojo.animateProperty({node:"tmpcard", properties:{
					scale:2, //doesn't work...
					top: 400,
					left: rect.width/2 - r.width/2
				},
				onEnd: function() {
						e.remove();
					}
				}).play();
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
