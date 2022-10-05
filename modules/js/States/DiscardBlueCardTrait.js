define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.discardBlueCardTrait', null, {
    constructor() {
      //      this._notifications.push(      );
    },

    /*
     * Main state of game : active player can play cards from his hand
     */
    onEnteringStateChooseAndDiscardBlueCard(args) {
      this._amount = args.amount;
      if (args._private) {
        this.makeCardSelectable(args._private.cards, 'discardBlue');
      }
    },

    onClickCardDiscardBlue(card) {
      this.onClickCardToSelect(card, 'buttonConfirmDiscardBlue', _('Confirm discard'), 'onClickConfirmDiscardBlue');
    },

    onClickConfirmDiscardBlue() {
      this.takeAction('actDiscardBlue', {
        card: this._selectedCards[0],
      });
    },
  });
});
