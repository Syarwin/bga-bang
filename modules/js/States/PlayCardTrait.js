define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.playCardTrait", null, {
    constructor(){
//      this._notifications.push(      );
    },


    /*
     * Main state of game : active player can play cards from his hand
     */
    onEnteringStatePlayCard(args){
      // TODO : do it on server's side
      var cards = args._private.cards.filter(card => card.options != null);
      this.makeCardSelectable(cards, "playCard");
    },


    /*
     * Triggered whenever a player clicked on a selectable card to play
     */
    onClickCardPlayCard(card){
      dojo.query("#hand .bang-card").removeClass("selectable").addClass("unselectable");
      dojo.removeClass("bang-card-" + card.id, "unselectable");
      dojo.addClass("bang-card-" + card.id, "selected");
      this._selectedCard = card;

      // What kind of target ?
      let OPTIONS_NONE = 0, OPTION_CARD = 1, OPTION_PLAYER = 2;
      if(card.options.type == OPTIONS_NONE) {
        this.onSelectOption();
      } else if(card.options.type == OPTION_PLAYER) {
        this.makePlayersSelectable(card.options.targets);
      } else if(card.options.type == OPTION_CARD){
        this.makePlayersCardsSelectable(card.options.targets);
      }
    },

    /*
     * Triggerd when clicked on the undo button
     */
    onClickCancelCardSelected(cards){
      this.clearPossible();
      this.makeCardSelectable(cards, "playCard");
    },


    /*
     * Whenever the card and the option are selected, send that to the server
     */
    onSelectOption(){
      if(!this.checkAction('play')) return;

      var data = {
        id:this._selectedCard.id,
        player:this._selectedPlayer,
        optionType:this._selectedOptionType,
        optionArg:this._selectedOptionArg,
      };

      this.takeAction("playCard", data);
    },
  });
});
