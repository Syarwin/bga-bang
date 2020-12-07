var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define(["dojo", "dojo/_base/declare",  "dojo/on",
  g_gamethemeurl + "modules/js/bangCardSelector.js",
], function (dojo, declare) {
  return declare("bang.cards", null, {
    gamedatas:{},

    constructor(args) {
      dojo.safeMixin(this, args);
      this.selectors = [];

      // Formatting cards
      this._cards = [];
      Object.values(this.game.gamedatas.cards).forEach(card => {
        card.name = card.name.toUpperCase();
        this._cards[card.type] = card;
      });
    },

    clearPossible(){
      this.selectors.forEach(selector => selector.clearPossible() );
    },


    prompt(cards, amount = 1){
      let selector = new bang.cardSelector({
        game:this.game,
        location: '#hand',
        addSelectedClass:true,
        selectableCards:cards,
        amount:amount,
      });
      this.selectors.push(selector);
      return selector.wait();
    }
  });
});
