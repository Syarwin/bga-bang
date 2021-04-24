var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  'dojo',
  'dojo/_base/declare',
  'dojo/Evented',
  'dojo/on',
  g_gamethemeurl + 'modules/js/bangPlayerSelector.js',
], function (dojo, declare, Evented) {
  const TARGET_NONE = 0,
    TARGET_CARD = 1,
    TARGET_PLAYER = 2;

  return declare('bang.cardSelector', [Evented], {
    game: {},
    pId: null,
    location: null,
    addSelectedClass: false,
    selectableCards: null,
    selectedCards: null,
    amount: 1,

    connections: null,
    selectors: null,

    constructor(args) {
      dojo.safeMixin(this, args);
      this.connections = [];
      this.selectors = [];
    },

    clearPossible() {
      this.connections.forEach(dojo.disconnect);
      this.selectors.forEach((selector) => selector.clearPossible());
      this.connections = [];
      this.selectors = [];
    },

    wait() {
      this.selectedCards = [];

      // Add unselectable class to cards
      if (this.location != null) {
        dojo.query(this.location + ' .bang-card').addClass('unselectable');
      }
      if (this.pId != null) {
        dojo.query('#bang-player-' + this.pId + ' .bang-card').addClass('unselectable');
      }

      this.selectableCards.forEach((card) => {
        let id = 'bang-card-' + card.id;
        dojo.removeClass(id, 'unselectable');
        dojo.addClass(id, 'selectable');
        this.connections.push(dojo.connect($(id), 'click', () => this.onClickCard(card)));
      });

      return new Promise((resolve, reject) => {
        this.on('select', (data) => {
          resolve(data);
          this.clearPossible();
        });
      });
    },

    toggleCard(card) {
      var domId = 'bang-card-' + card.id;
      // Already selected, unselect it
      if (this.selectedCards.includes(card.id)) {
        this.selectedCards = this.selectedCards.filter((id) => id != card.id);
        dojo.removeClass(domId, 'selected');
        this.selectableCards.forEach((c) =>
          dojo
            .query('#bang-card-' + c.id)
            .addClass('selectable')
            .removeClass('unselectable'),
        );
      }
      // Not yet selected, add to selection
      else {
        if (this.selectedCards.length >= this.amount) return false;

        this.selectedCards.push(card.id);
        dojo.addClass(domId, 'selected');

        if (this.selectedCards.length == this.amount) {
          dojo.query('.bang-card.selectable').removeClass('selectable').addClass('unselectable');
          dojo.query('.bang-card.selected').removeClass('unselectable').addClass('selectable');
        }
      }

      return true;
    },

    async onClickCard(ocard) {
      // Is the card selectable ?
      var card = this.selectableCards.find((o) => o.id == ocard.id);
      if (!card) return;

      if (this.location != null) {
        dojo
          .query(this.location + ' .bang-card')
          .removeClass('selectable')
          .addClass('unselectable');
      }

      if (this.addSelectedClass) {
        dojo.removeClass('bang-card-' + card.id, 'unselectable');
        dojo.addClass('bang-card-' + card.id, 'selected');
      }

      this.selectedCards.push(card);

      // TODO : always set ?
      var data = {
        id: card.id,
        player: null,
        optionType: null,
        optionArg: null,
      };

      if (card.options && card.options.target_type == TARGET_PLAYER) {
        let playerSelector = new bang.playerSelector({
          game: this.game,
          selectablePlayers: card.options.targets,
        });
        let pId = await playerSelector.wait();
        data.optionType = 'player';
        data.player = pId;
      }
      /*
      else if(card.options.target_type == TARGET_CARD){
        this.makePlayersCardsSelectable(card.options.targets);
      }
*/
      /*
      data = {
        id:this._selectedCard.id,
        player:this._selectedPlayer,
        optionType:this._selectedOptionType,
        optionArg:this._selectedOptionArg,
      }
      */

      this.emit('select', data);
    },
  });
});
