define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.discardEndOfTurnTrait", null, {
    constructor(){
//      this._notifications.push(      );
    },

    onClickEndOfTurn(){
      this.takeAction("endTurn");
    },

    onClickCancelEndTurn(){
      this.takeAction("cancelEndTurn");
    },

    onEnteringStateDiscardExcess(args){
      debug("Discard excess", args);
      this._amount = args.amount;
      this._selectedCards = [];
      this.makeCardSelectable(args._private, "discardExcess");
    },

    onClickCardDiscardExcess(card){
      if(!this.toggleCard(card)) return;

      if(this._selectedCards.length < this._amount){
        this.removeActionButtons();
        this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
      } else {
        this.addActionButton('buttonConfirmDiscardExcess', _('Confirm discard'), 'onClickConfirmDiscardExcess', null, false, 'blue');
      }
    },

    onClickConfirmDiscardExcess(){
      this.takeAction("discardExcess", {
        cards:this._selectedCards.join(";"),
      });
    },
  });
});
