define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.russianRouletteTrait', null, {
    constructor() {
    },

    /*
     * Main state of game : active player can play cards from his hand
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
