define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.reactTrait", null, {
    constructor(){
//      this._notifications.push(      );
    },

    /*
     * React state : active player can play cards from his hand in reaction
     */
    onEnteringStateReact(args){
      this._amount = null;
      this._selectedCards = [];
      this.makeCardSelectable(args._private.cards, "selectReact");
      this.gamedatas.gamestate.descriptionmyturn = args.msgActive;
      this.gamedatas.gamestate.description = args.msgInactive;
      this.updatePageTitle();
    },
    onEnteringStateMultiReact(args){
      this.onEnteringStateReact(args);
    },


    onClickCardSelectReact(card){
      // React with single card
      if(card.amount == 1 && this._amount == null){
        this._selectedCards = [card.id];
        this.onClickConfirmReact();
      }
      // React with several cards
      else {
        if(this._amount == null)
          this._amount = card.amount;

        // Toggle the card
        if(!this.toggleCard(card)) return;

        if(this._selectedCards.length < this._amount){
          if(this._selectedCards.length == 0)
            this._amount = null;
          this.removeActionButtons();
          this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
        } else {
          this.addActionButton('buttonConfirmReact', _('Confirm react'), 'onClickConfirmReact', null, false, 'blue');
        }
      }
    },


    onClickConfirmReact(){
      this.takeAction("react", {
        cards:this._selectedCards.join(";"),
      });
    },


    onClickPass(){
      this.checkAction('pass');
      this.takeAction("pass");
    },
  });
});
