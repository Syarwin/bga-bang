define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.playerCardSelectorTrait", null, {
    constructor(){
    },

    /******************
    *** OPTION_CARD ***
    ******************/
    /*
     * Make some players' cards selectable with sometimes the deck
     */
    makePlayersCardsSelectable(players){
      this.removeActionButtons();
      this.gamedatas.gamestate.descriptionmyturn = _("You must choose a card in play or a player's hand");
      this.updatePageTitle();
      var oldSelectableCards = this._selectableCards;
      this.addActionButton('buttonCancel', _('Undo'), () => this.onClickCancelCardSelected(oldSelectableCards), null, false, 'gray');

      var cards = [];
      this._selectablePlayers = players;
      players.forEach(playerId => {
        dojo.addClass("bang-player-" + playerId, "selectable");
        dojo.query("#bang-player-" + playerId + " .bang-card").forEach(div => {
          cards.push({
            id:dojo.attr(div, "data-id"),
            playerId:playerId,
          })
        });
      });
      this.makeCardSelectable(cards, "selectOption");
    },


    onClickCardSelectOption(card){
      this._selectedPlayer = card.playerId;
      this._selectedOptionType = "inplay";
      this._selectedOptionArg = card.id;
      this.onSelectOption();
    },
  });
});
