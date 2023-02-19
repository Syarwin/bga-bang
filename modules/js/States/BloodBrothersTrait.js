define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.bloodBrothersTrait', null, {
    constructor() {},

    /*
     * Russian Roulette event - player can lose 1 HP to share with anyone
     */
    onEnteringStateBloodBrothers(args) {
      this._action = 'bloodBrothers';
      if (args._private) {
        this.makePlayersSelectable(args._private.players, true);
        this.addPrimaryActionButton('buttonSkipBloodBrothers', _('Skip'), () => this.onClickBloodBrothersPass());
      }

    },

    onClickPlayerBloodBrothers(playerId) {
      this.takeAction('actReactBloodBrothers', { playerId: playerId })
    },

    onClickBloodBrothersPass() {
      this.takeAction('actReactBloodBrothers', {})
    },
  });
});
