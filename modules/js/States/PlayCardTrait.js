define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.playCardTrait', null, {
    constructor() {
      //      this._notifications.push(      );
    },

    /*
     * Main state of game : active player can play cards from his hand
     */
    onEnteringStatePlayCard(args) {
      var cards = args._private.cards.filter((card) => card.options != null);
      this.waitForDisappearance('.slide').then(() => {
        this.makeCardSelectable(cards, 'playCard');
      });
    },

    onEnteringStatePlayLastCardManually(args) {
      var cards = args._private.cards.filter((card) => card.options != null);
      this.makeCardSelectable(cards, 'playCard');
    },

    /*
     * Triggered whenever a player clicked on a selectable card to play
     */
    onClickCardPlayCard(card) {
      dojo.query('#hand .bang-card').removeClass('selectable').addClass('unselectable');
      dojo.removeClass('bang-card-' + card.id, 'unselectable');
      dojo.addClass('bang-card-' + card.id, 'selected');
      if (this._selectedCard) {
        dojo.removeClass('bang-card-' + this._selectedCard.id, 'unselectable');
        dojo.addClass('bang-card-' + this._selectedCard.id, 'selected');
      } else {
        this._selectedCard = card;
      }

      if (!!card.options.with_another_card?.strict && !this._isToSelectSecondCard) {
        this.makeCardsSelectable(card.options.with_another_card.cards);
        this.doSomeCleanupAndAddUndo(_('You must choose a second card to play with'));
        this._isToSelectSecondCard = true;
      } else {
        this._selectablePlayers = [];
        // What kind of target ?
        let TARGET_NONE = 0,
            TARGET_CARD = 1,
            TARGET_PLAYER = 2,
            TARGET_ALL_CARDS = 3;
        if (card.options.target_types.includes(TARGET_NONE)) {
          this.onSelectOption();
        }
        if (card.options.target_types.includes(TARGET_PLAYER)) {
          if (this._isToSelectSecondCard) {
            this.makePlayersSelectable(card.options.with_another_card?.targets ?? card.options.targets);
            this._selectableCards = [];
            this._isToSelectSecondCard = false;
            this._selectedCardSecond = card;
          } else {
            this.makePlayersSelectable(card.options.targets);
            if (card.options.with_another_card) {
              this.makeCardsSelectable(card.options.with_another_card.cards);
              this._isToSelectSecondCard = true;
            } else if (!card.options.target_types.includes(TARGET_ALL_CARDS)) {
              this._selectableCards = [];
            }
          }
        }
        if (card.options.target_types.includes(TARGET_CARD)) {
          this.makePlayersCardsSelectable(card.options.targets);
        }
        if (card.options.target_types.includes(TARGET_ALL_CARDS)) {
          this.makePlayersCardsSelectable(Object.keys(this.gamedatas.players).map(Number), true);
        }
      }
    },

    makeCardsSelectable(cards) {
      cards.forEach((card) => {
        dojo.removeClass('bang-card-' + card.id, 'unselectable');
        dojo.addClass('bang-card-' + card.id, 'selectable');
      });
    },

    /*
     * Triggered when clicked on the undo button
     */
    onClickCancelCardSelected(cards) {
      this.clearPossible();
      this.makeCardSelectable(cards, 'playCard');
    },

    /*
     * Whenever the card and the option are selected, send that to the server
     */
    onSelectOption() {
      var data = {
        id: this._selectedCard.id,
        player: this._selectedPlayer, // TODO: Rename to "targetPlayer" not to be confused with player who played this
        optionType: this._selectedOptionType,
        optionArg: this._selectedOptionArg,
      };
      if (this._selectedCardSecond) {
        data.secondCardId = this._selectedCardSecond.id;
      }
      this._selectedCard = null;
      this.takeAction('actPlayCard', data);
    },
  });
});
