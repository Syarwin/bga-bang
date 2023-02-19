define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.russianRouletteTrait', null, {
    constructor() {
    },

    /*
     * Russian Roulette event - player can discard a Missed! or lose 2 HP
     */
    onEnteringStateRussianRoulette(args) {
      if (args._private) {
        this.makeCardSelectable(args._private.cards, 'russianRoulette');
        this.addDangerActionButton('buttonPass', _('Pass and lose 2 life points'), () => {
          this.takeAction('actPassEndRussianRoulette', {})
        });
      }
    },

    onClickCardRussianRoulette(card) {
      this.takeAction('actReactRussianRoulette', { cardId: card.id })
    }
  });
});
