define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.cardSelectorTrait", null, {
    constructor(){
    },


    /*
     * Given a list of cards, make them selectable
     */
    makeCardSelectable(cards, action, suffix = ''){
      this._action = action;

      // Add unselectable class
      if(this._action == "selectCard" || this._action == "discardExcess"){
        dojo.query("#hand .bang-card").addClass("unselectable");
        dojo.query("#bang-player-" + this.player_id + " .bang-card").addClass("unselectable");
      } else {
        if(action != "selectDialog")
          dojo.query(".bang-card").addClass("unselectable");
      }

      this._selectableCards = cards;
      this._selectableCards.forEach(card => {
        card.uid = card.id + suffix;
        dojo.removeClass("bang-card-" + card.uid, "unselectable");
        dojo.addClass("bang-card-" + card.uid, "selectable");
      });

      if(this._action == "playCard"){
        this.gamedatas.gamestate.descriptionmyturn = _("You can play a card");
        this.updatePageTitle();
      }
    },


    /*
     * Triggered whenever a player click on a card
     */
    onClickCard(ocard){
      //if(!this.isCurrentPlayerActive()) return;
      // Is the card in the discard ?
      if($("bang-card-" + ocard.uid).parentNode.id == "discard")
        return this.onClickDiscard();

      // Is the card selectable ?
      var card = this._selectableCards.find(o => o.id == ocard.id);
      if(!card) return;

      var methodName = "onClickCard" + this._action.charAt(0).toUpperCase() + this._action.slice(1);
    	if (this[methodName] !== undefined)
    		this[methodName](card);
      else
        console.error("Trying to call " + methodName); // Should not happen
    },


    /*
     * Toggle a card : useful for multiple select or whenever we want a confirm button
     */
    toggleCard(card){
      var domId = "bang-card-" + card.uid;
      // Already selected, unselect it
      if(this._selectedCards.includes(card.id)){
        this._selectedCards = this._selectedCards.filter(id => id != card.id);
        dojo.removeClass(domId, "selected");
        this._selectableCards.forEach(c => dojo.query("#bang-card-" + c.uid).addClass("selectable").removeClass("unselectable") );
      }
      // Not yet selected, add to selection
      else {
        if(this._selectedCards.length >= this._amount)
          return false;

        this._selectedCards.push(card.id);
        dojo.addClass(domId, "selected");

        if(this._selectedCards.length == this._amount){
          dojo.query(".bang-card.selectable").removeClass("selectable").addClass("unselectable");
          dojo.query(".bang-card.selected").removeClass("unselectable").addClass("selectable");
        }
      }

      return true;
    },
  });
});
