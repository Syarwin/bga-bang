define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.selectCardTrait", null, {
    constructor(){
//      this._notifications.push(      );
    },

    onEnteringStateSelectCard(args){
      debug("Selecting cards", args);
      var cards = [], display = true;
      if(args.cards.length > 0)
        cards = args.cards;
      else if(args._private)
        cards = args._private.cards;
      else
        display = false;

//      this.gamedatas.gamestate.args.cards = args.cards.length > 0? args.cards : (args._private? args._private.cards : this.getNBackCards(args.amount) );
      if(display)
        this.dialogSelectCard();
    },



    dialogSelectCard(){
      var args = this.gamedatas.gamestate.args;
      this._dial = new customgame.modal("selectCard", {
         autoShow:true,
         title:_("Pool of cards"),
         class:"bang_popin",
         closeIcon:'fa-times',
         openAnimation:true,
         openAnimationTarget:"buttonShowCards",
         contentsTpl:jstpl_dialog,
       });
      args.cards.forEach(card => this.addCard(card, 'dialog-card-container') );
      $('dialog-title-container').innerHTML = $('pagemaintitletext').innerHTML;

      if(!this.isCurrentPlayerActive())
        return;

      this._amount = args.amountToPick;
      this.makeCardSelectable(args.cards, "selectDialog");
    },


    onClickCardSelectDialog: function(card){
      if(!this.toggleCard(card))
        return;

      dojo.empty("dialog-button-container");
      if(this._selectedCards.length == this._amount)
        this.addActionButton('buttonConfirmSelectCard', _('Confirm selection'), 'onClickConfirmSelection', 'dialog-button-container', false, 'blue');
    },

    onClickConfirmSelection: function(){
      if(this._dial != null)
        this._dial.destroy();
      this.takeAction("select", {
        cards:this._selectedCards.join(";"),
      });
    },


  });
});
