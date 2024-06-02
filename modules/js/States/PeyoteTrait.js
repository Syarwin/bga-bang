define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.peyoteTrait', null, {
    constructor() {},

    /*
     * Peyote event - players try to guess the suit of the card they draw and keep drawing until they are wrong
     */
    onEnteringStatePeyote(args) {
      this._action = 'peyote';
      if (this.isCurrentPlayerActive()) {
        this.addPrimaryActionButton('buttonPeyoteRed', this.replaceSuits(_(args.options[0])), () => this.onClickPeyoteGuess(true));
        this.addPrimaryActionButton('buttonPeyoteBlack', this.replaceSuits(_(args.options[1])), () => this.onClickPeyoteGuess(false));
      }
    },

    replaceSuits(string) {
      return string.replaceAll(/{([HDSC])}/g, '<span class="card-copy-color" data-color="$1" data-color-override=""></span>')
    },

    onClickPeyoteGuess(guess) {
      this.takeAction('actPeyoteGuess', { isRed: guess })
    },
  });
});
