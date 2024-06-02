define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.hardLiquorTrait', null, {
    constructor() {},

    /*
     * Hard Liquor event - player may skip his drawing phase 1 to regain 1 life point
     */
    onEnteringStateHardLiquor(args) {
      this._action = 'hardLiquor';
      if (this.isCurrentPlayerActive()) {
        this.addPrimaryActionButton('buttonHardLiquorGainHP', _(args.options[0]), () => this.onClickHardLiquorGainHP());
        this.addPrimaryActionButton('buttonDeclineHardLiquor', _(args.options[1]), () => this.onClickDeclineHardLiquor());
      }
    },

    onClickHardLiquorGainHP() {
      this.takeAction('actHardLiquorGainHP', {})
    },

    onClickDeclineHardLiquor() {
      this.takeAction('actDeclineHardLiquor', {})
    },
  });
});
