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
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};
define([
  'dojo',
  'dojo/_base/declare',
  'ebg/core/gamegui',
  'ebg/counter',
  g_gamethemeurl + 'modules/js/Game/game.js',
  g_gamethemeurl + 'modules/js/Game/modal.js',

  g_gamethemeurl + 'modules/js/States/PlayCardTrait.js',
  g_gamethemeurl + 'modules/js/States/ReactTrait.js',
  g_gamethemeurl + 'modules/js/States/SelectCardTrait.js',
  g_gamethemeurl + 'modules/js/States/DiscardEndOfTurnTrait.js',

  g_gamethemeurl + 'modules/js/CardTrait.js',
  g_gamethemeurl + 'modules/js/PlayerTrait.js',
], function (dojo, declare) {
  return declare(
    'bgagame.bang',
    [
      customgame.game,
      bang.playCardTrait,
      bang.reactTrait,
      bang.selectCardTrait,
      bang.discardEndOfTurnTrait,
      bang.playerTrait,
      bang.cardTrait,
    ],
    {
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

        // States that need the player to be active to be entered
        this._activeStates = ['drawCard', 'playCard', 'react', 'multiReact', 'discardExcess'];
      },

      /*
       * Setup:
       *	This method set up the game user interface according to current game situation specified in parameters
       *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
       *
       * Params :
       *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
       */
      setup(gamedatas) {
        debug('SETUP', gamedatas);
        // Formatting cards
        this._cards = [];
        Object.values(gamedatas.cards).forEach((card) => {
          card.name = card.name.toUpperCase();
          this._cards[card.type] = card;
        });

        // Adding deck/discard
        dojo.place(this.format_block('jstpl_table', { deck: gamedatas.deck }), 'board');
        if (gamedatas.discard) this.addCard(gamedatas.discard, 'discard');
        dojo.connect($('deck'), 'onclick', () => this.onClickDeck());
        dojo.connect($('discard'), 'onclick', () => this.onClickDiscard());

        // Setting up player boards order
        this.setupPlayerBoards();

        if (this.isSpectator) {
          dojo.place(jstpl_helpIcon, document.querySelector('.player-board.spectator-mode'));
          dojo.query('.player-board.spectator-mode .roundedbox_main').style('display', 'none');
        }
        dojo.connect($('help-icon'), 'click', () => this.displayPlayersHelp());

        // Make the current player stand out
        this.updateCurrentTurnPlayer(gamedatas.playerTurn);

        this.inherited(arguments);
      },

      onLoadingComplete() {
        debug('Loading complete');
        /*
  if(this.gamedatas.turn == 1){
    this.displayPlayersHelp();
  }
  */
      },

      /*
       * onUpdateActionButtons:
       * 	called by BGA framework before onEnteringState
       *	in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
       */
      onUpdateActionButtons(stateName, args) {
        debug('Update action buttons: ' + stateName, args);
        this.updatePlayersStatus(); // Called when a player go inactive

        if (stateName == 'selectCard' && (args.cards.length > 0 || args._private)) {
          this.addActionButton('buttonShowCards', _('Show cards'), () => this.dialogSelectCard(), null, false, 'blue');
        }

        if (!this.isCurrentPlayerActive())
          // Make sure the player is active
          return;

        if (stateName == 'playCard') {
          if (args._private && args._private.character != null && this._selectedCard == null)
            this.makeCharacterAbilityUsable(args._private.character);

          this.addActionButton('buttonEndTurn', _('End of turn'), 'onClickEndOfTurn', null, false, 'blue');
        }

        if (stateName == 'discardExcess')
          this.addActionButton('buttonCancelEnd', _('Cancel'), 'onClickCancelEndTurn', null, false, 'gray');

        if (stateName == 'react') {
          if (args.type == "attack")
            this.addActionButton(
              'buttonSkip',
              _('Pass and lose life point'),
              () => this.onClickPass(),
              null,
              false,
              'red',
            );
          else this.addActionButton('buttonSkip', _('Pass'), () => this.onClickPass(), null, false, 'blue');

          if (
            args._private &&
            args._private.character != null &&
            this._selectedCard == null &&
            this._selectedCards.length == 0
          )
            this.makeCharacterAbilityUsable(args._private.character);

          // Button for barrel
          args._private.cards.forEach((card) => {
            if ($('bang-card-' + card.id).parentNode.id != 'hand-cards') {
              this.addPrimaryActionButton('buttonUseBarrel', _('Use barrel'), () => this.onClickCardSelectReact(card));
            }
          });
        }
      },

      ////////////////////////////////
      ////////////////////////////////
      /////////		Actions		//////////
      ////////////////////////////////
      ////////////////////////////////

      /******************
       *** Use ability ***
       ******************/
      makeCharacterAbilityUsable(option) {
        this._useAbilityOption = option;
        this.addActionButton('buttonUseAbility', _('Use ability'), () => this.onClickUseAbility(), null, false, 'blue');
      },

      onClickUseAbility() {
        debug('test');
        //let OPTIONS_NONE = 0, OPTION_CARDS = 3;
        let SID_KETCHUM = 9,
          JOURDONNAIS = 13;
        this._selectedCards = [];
        if (this._useAbilityOption == JOURDONNAIS) {
          this.onClickConfirmUseAbility();
        } else if (this._useAbilityOption == SID_KETCHUM) {
          // Sid Ketchum power
          var cards = dojo.query('#hand .bang-card').map((card) => {
            return { id: dojo.attr(card, 'data-id') };
          });

          this._amount = 2;
          this.makeCardSelectable(cards, 'useAbility');

          $('pagemaintitletext').innerHTML = _('You must select two cards');
          this.removeActionButtons();
          this.addActionButton('buttonCancelUseAbility', _('Cancel'), () => this.restartState(), null, false, 'gray');
        }
      },

      onClickCardUseAbility: function (card) {
        this.toggleCard(card);

        if (this._selectedCards.length < this._amount) {
          if ($('buttonConfirmUseAbility')) dojo.destroy('buttonConfirmUseAbility');
        } else {
          this.addActionButton(
            'buttonConfirmUseAbility',
            _('Confirm'),
            'onClickConfirmUseAbility',
            null,
            false,
            'blue',
          );
        }
      },

      onClickConfirmUseAbility: function () {
        this.takeAction('actUseAbility', {
          cards: this._selectedCards.join(';'),
        });
      },

      /**********************************
       *** Draw card / for some powers ***
       **********************************/
      onEnteringStateDrawCard: function (args) {
        this._action = 'drawCard';
        var players = [];
        args._private.options.forEach((option) => {
          switch (option) {
            case 'deck':
              this.makeDeckSelectable();
              break;
            case 'discard':
              this.makeDiscardSelectable();
              break;
            default:
              players.push(option);
              break;
          }
        });
        if (players.length > 0) this.makePlayersSelectable(players, true);
      },

      makeDeckSelectable: function () {
        this.addActionButton('buttonSelectDeck', _('Deck'), () => this.onClickDeck(), null, false, 'blue');
        this._isSelectableDeck = true;
        dojo.addClass('deck', 'selectable');
      },

      onClickDeck: function () {
        if (!this.isCurrentPlayerActive() || !this._isSelectableDeck) return;

        if (this._action == 'drawCard') this.onClickDraw('deck');
      },

      makeDiscardSelectable: function () {
        this.addActionButton('buttonSelectDiscard', _('Discard'), () => this.onClickDiscard(), null, false, 'blue');
        this._isSelectableDiscard = true;
        dojo.addClass('discard', 'selectable');
      },

      onClickDiscard: function () {
        if (!this.isCurrentPlayerActive() || !this._isSelectableDiscard) return;

        if (this._action == 'drawCard') this.onClickDraw('discard');
      },

      onClickDraw: function (arg) {
        this.takeAction('actDraw', { selected: arg });
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
        debug('Clearing everything');
        this._selectableCards = [];
        this._selectablePlayers = [];
        this._selectedCard = null;
        this._selectedCards = [];
        this._selectedPlayer = null;
        this._selectedOptionType = null;
        this._selectedOptionArg = null;
        this._isSelectableDeck = false;
        this._isSelectableDiscard = false;
        dojo.query('.bang-card').removeClass('unselectable selectable selected');
        dojo.query('.bang-player').removeClass('selectable');
        dojo.removeClass('deck', 'selectable');
        dojo.removeClass('discard', 'selectable');

        this.removeActionButtons();
        dojo.empty('customActions');
        this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
      },

      restartState: function () {
        this.clearPossible();
        this.onEnteringState(this.gamedatas.gamestate.name, this.gamedatas.gamestate);
      },

      ///////////////////////////////////////////////////
      //////	 Reaction to cometD notifications	 ///////
      ///////////////////////////////////////////////////

      /** just for troubleshooting */
      notif_debug: function (notif) {
        debug(notif);
      },
    },
  );
});
