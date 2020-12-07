define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.playerSelectorTrait", null, {
    constructor(){
    },


    /********************
    *** OPTION_PLAYER ***
    ********************/

    /*
     * Make some players selectable with either action button or directly on the board
     */
    makePlayersSelectable(players, append = false){
      if(!append) {
        this.removeActionButtons();
        this.gamedatas.gamestate.descriptionmyturn = _("You must choose a player");
        this.updatePageTitle();
        this.addActionButton('buttonCancel', _('Undo'), () => this.onClickCancelCardSelected(this._selectableCards), null, false, 'gray');
      }

      this._selectablePlayers = players;
      this._selectablePlayers.forEach(playerId => {
        this.addActionButton('buttonSelectPlayer' + playerId, this.gamedatas.players[playerId].name, () => this.onClickPlayer(playerId) );
        dojo.addClass("bang-player-" + playerId, "selectable");
      });
    },


    /*
     * Triggered when a player click on a player's board or action button
     */
    onClickPlayer(playerId){
      debug("Clicked on player", playerId);
      if(!this._selectablePlayers.includes(playerId))
        return;

      if(this._action == 'drawCard') {
        this.onClickDraw(playerId);
      } else {
        this._selectedOptionType = "player";
        this._selectedPlayer = playerId;
        this.onSelectOption();
      }
    },
  });
});
