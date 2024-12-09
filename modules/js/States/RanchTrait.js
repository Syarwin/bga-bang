define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.ranchTrait', null, {
    constructor() {
    },

    /*
     * Russian Roulette event - player can discard a Missed! or lose 2 HP
     */
    onEnteringStateRanch(args) {
      if (args._private) {
        this._amount = undefined;
        this.makeCardSelectable(args._private.cards, 'ranch');
        this.addPrimaryActionButton('buttonIgnoreRanch', _('Pass'), () => {
          this.takeAction('actIgnoreRanch', {})
        });
      }
    },

    onClickCardRanch(card) {
      this.onClickCardToSelect(card, 'buttonConfirmRanch', _('Discard'), 'onClickConfirmRanch');
    },

    onClickConfirmRanch() {
      this.takeAction('actDiscardCardsRanch', {
        cardIds: this._selectedCards.join(';'),
      });
    },
  });
});
