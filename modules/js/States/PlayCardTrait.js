define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.playCardTrait', null, {
    constructor() {
      //      this._notifications.push(      );
    },

    /*
     * Main state of game : active player can play cards from his hand
     */
    onEnteringStatePlayCard(args) {
      // TODO : do it on server's side
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
      this._selectedCard = card;

      // What kind of target ?
      let TARGET_NONE = 0,
        TARGET_CARD = 1,
        TARGET_PLAYER = 2;
      if (card.options.target_type == TARGET_NONE) {
        this.onSelectOption();
      } else if (card.options.target_type == TARGET_PLAYER) {
        this.makePlayersSelectable(card.options.targets);
      } else if (card.options.target_type == TARGET_CARD) {
        this.makePlayersCardsSelectable(card.options.targets);
      }
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
      this.takeAction('actPlayCard', data);
    },
  });
});
