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

//# sourceURL=banghighnoon.js
//@ sourceURL=banghighnoon.js
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};
define([
  'dojo',
  'dojo/_base/declare',
  'ebg/core/gamegui',
  'ebg/counter',
  g_gamethemeurl + 'modules/js/Game/game.js',
  g_gamethemeurl + 'modules/js/Game/modal.js',
  g_gamethemeurl + 'modules/js/Game/infDialog.js',
  g_gamethemeurl + 'modules/js/Game/dialogManager.js',

  g_gamethemeurl + 'modules/js/States/PlayCardTrait.js',
  g_gamethemeurl + 'modules/js/States/ReactTrait.js',
  g_gamethemeurl + 'modules/js/States/SelectCardTrait.js',
  g_gamethemeurl + 'modules/js/States/ChooseCharacterTrait.js',
  g_gamethemeurl + 'modules/js/States/DiscardEndOfTurnTrait.js',
  g_gamethemeurl + 'modules/js/States/DiscardBlueCardTrait.js',

  g_gamethemeurl + 'modules/js/EventTrait.js',
  g_gamethemeurl + 'modules/js/CardTrait.js',
  g_gamethemeurl + 'modules/js/PlayerTrait.js',
], function (dojo, declare) {
  return declare(
    'bgagame.banghighnoon',
    [
      customgame.game,
      bang.informationdialog,
      bang.playCardTrait,
      bang.reactTrait,
      bang.selectCardTrait,
      bang.chooseCharacterTrait,
      bang.discardEndOfTurnTrait,
      bang.playerTrait,
      bang.cardTrait,
      bang.discardBlueCardTrait,
      bang.eventTrait,
      bang.dialogManager,
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
        this._dial = {};

        // States that need the player to be active to be entered
        this._activeStates = [
          'drawCard',
          'playCard',
          'react',
          'multiReact',
          'discardExcess',
          'preEliminate',
          'vicePenalty',
          'playLastCardManually',
        ];

        this.default_viewport = 'width=840';

        this._settingsConfig = {
          magasin: { type: 'pref', prefId: 108 },
          handPosition: {
            default: 0,
            name: _('Hand position'),
            attribute: 'hand',
            type: 'select',
            values: {
              0: _('Top'),
              1: _('Bottom'),
            },
          },
          playerPosition: {
            default: 0,
            name: _('Current player position'),
            type: 'select',
            values: {
              0: _('Top'),
              1: _('Bottom'),
            },
          },
        };
      },

      onScreenWidthChange() {
        dojo.style('page-content', 'zoom', '');
        dojo.style('page-title', 'zoom', '');
        dojo.style('right-side-first-part', 'zoom', '');
        this.centerCardsIfFew();
      },

      centerCardsIfFew() {
        const hand = dojo.query('#hand-cards').shift();
        if (hand) {
          const handWidth = hand ? hand.clientWidth : 0;
          const cards = dojo.query('#hand-cards .bang-card');
          const cardWidthsSum = cards.reduce(function (acc, val) {
            return acc + val.clientWidth;
          }, 0);
          if (cardWidthsSum < handWidth) {
            dojo.style(hand, 'justify-content', 'center');
          } else {
            dojo.style(hand, 'justify-content', 'flex-start');
          }
        }
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
        this.setupInfoPanel();
        this.inherited(arguments);

        // Formatting cards
        this._cards = [];
        Object.values(gamedatas.cards).forEach((card) => {
          card.name = card.name.toUpperCase();
          this._cards[card.type] = card;
        });

        // Adding deck/discard
        dojo.place(this.format_block('jstpl_table', { deckCount: gamedatas.deckCount }), 'board');
        // Adding events cards
        if (gamedatas.eventsDeckCount !== undefined) {
          dojo.place(this.format_block('jstpl_events_row', { eventsDeckCount: gamedatas.eventsDeckCount }), 'table-container');
          dojo.place(this.format_block('jstpl_noEvents', { noEventsLexeme: _('No active events') }), 'eventActive');
          dojo.addClass('board', 'events');
        }
        if (gamedatas.discard) {
          // gamedatas.discard.extraClass = ' '; //empty space is important
          this.addCard(gamedatas.discard, 'discard');
        }
        if (gamedatas.eventActive) this.addEventCard(gamedatas.eventActive, 'eventActive');
        if (gamedatas.eventNext) this.addEventCard(gamedatas.eventNext, 'eventNext');
        dojo.connect($('deck'), 'onclick', () => this.onClickDeck());
        dojo.connect($('discard'), 'onclick', () => this.onClickDiscard());

        // Setting up player boards order
        this.setupPlayerBoards();

        dojo.connect($('help-icon'), 'click', () => this.displayPlayersHelp());

        // Make the current player stand out
        if (gamedatas.gamestate.name !== 'chooseCharacter') {
          this.updateCurrentTurnPlayer(gamedatas.playerTurn);
        }
        if (gamedatas.notAgreedToDisclaimer && gamedatas.notAgreedToDisclaimer.includes(this.player_id)) {
          const iAgreeButtonId = this.showGhostTownDisclaimer();
          dojo.connect($(iAgreeButtonId), 'onclick', () => this.onClickAgreeToDisclaimer());
        }
      },

      onLoadingComplete() {
        debug('Loading complete');
      },

      /*
       * onUpdateActionButtons:
       * 	called by BGA framework before onEnteringState
       *	in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
       */
      onUpdateActionButtons(stateName, args, showBarrel = true) {
        debug('Update action buttons: ' + stateName, args);
        this.updatePlayersStatus(); // Called when a player go inactive

        if (stateName == 'selectCard' && (args.cards.length > 0 || args._private)) {
          this.addActionButton('buttonShowCards', _('Show cards'), () => this.dialogSelectCard(), null, false, 'blue');
        }

        if (!this.isCurrentPlayerActive())
          // Make sure the player is active
          return;

        if (stateName == 'chooseCharacter') {
          this.addActionButton('buttonShowCharacters', _('Show characters'), () => this.dialogChooseCharacter(), null, false, 'blue');
        }

        if (stateName == 'playCard') {
          if (args._private && args._private.character != null && this._selectedCard == null)
            this.makeCharacterAbilityUsable(args._private.character);

          this.addActionButton('buttonEndTurn', _('End of turn'), 'onClickEndOfTurn', null, false, 'blue');
        }

        if (stateName == 'discardExcess')
          this.addActionButton('buttonCancelEnd', _('Cancel'), 'onClickCancelEndTurn', null, false, 'gray');

        if (stateName == 'react') {
          if (args.type == 'attack')
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
            if ($('bang-card-' + card.id).parentNode.id != 'hand-cards' && showBarrel) {
              this.addPrimaryActionButton('buttonUseBarrel', _('Use barrel'), () => this.onClickCardSelectReact(card));
            }
          });
        }

        if (stateName == 'preEliminate') {
          this.addActionButton(
            'buttonDefaultDiscardExcess',
            _('Use default order'),
            () => this.takeAction('actDefautDiscardExcess', {}),
            null,
            false,
            'gray',
          );
        }


        if (stateName == 'vicePenalty') {
          this.addActionButton(
            'buttonDefaultDiscardVicePenalty',
            _('Use default order'),
            () => this.takeAction('actDefautDiscardVicePenalty', {}),
            null,
            false,
            'gray',
          );
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
        //let TARGET_NONE = 0, TARGET_CARDS = 3;
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

          var oldStateDescription = this.gamedatas.gamestate.descriptionmyturn;
          this.gamedatas.gamestate.descriptionmyturn = _('You must select two cards');
          this.updatePageTitle();
          this.gamedatas.gamestate.descriptionmyturn = oldStateDescription;

          this.removeActionButtons();
          this.addActionButton('buttonCancelUseAbility', _('Cancel'), () => this.restartState(), null, false, 'gray');
        }
      },

      onClickCardUseAbility: function (card) {
        this.toggleCard(card);

        const buttonVisible = $('buttonConfirmUseAbility');
        if (this._selectedCards.length < this._amount) {
          if (buttonVisible) dojo.destroy('buttonConfirmUseAbility');
        } else {
          if (!buttonVisible) {
            this.addActionButton(
                'buttonConfirmUseAbility',
                _('Confirm'),
                'onClickConfirmUseAbility',
                null,
                false,
                'blue',
            );
          }
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

      onClickAgreeToDisclaimer() {
        this.takeAction('actAgreedToDisclaimer', { lock: false }, false, false);
        this.removeDialog('ghostTown');
      },

      setupInfoPanel() {
        dojo.place(this.format_string(jstpl_configPlayerBoard, {}), 'player_boards', 'first');
        this.addTooltip('help-icon', _('Informations about each role and character'), '');
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
        this.updatePageTitle();
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

      onPreferenceChange(pref, newValue) {
        const OPTION_GENERAL_STORE_LAST_CARD = 108;
        pref = parseInt(pref);
        if (pref === OPTION_GENERAL_STORE_LAST_CARD) {
          data = { pref: pref, lock: false, value: newValue, player: this.player_id, silent: false };
          this.takeAction('actChangePref', data, false, false);
        }
      },

      checkPreferencesConsistency(backPrefs) {
        Object.keys(backPrefs).forEach((pref) => {
          if (this.prefs[pref].value != backPrefs[pref]) {
            data = { pref: pref, lock: false, value: this.prefs[pref].value, player: this.player_id, silent: true };
            this.takeAction('actChangePref', data, false, false);
          }
        });
      },
    },
  );
});
