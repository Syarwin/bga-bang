var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define(["dojo", "dojo/_base/declare", "dojo/Evented", "dojo/on", g_gamethemeurl + "modules/js/bangCardSelector.js"], function (dojo, declare, Evented) {
  return declare("bang.playerSelector", [Evented], {
    game:{},
    selectablePlayers:null,
    selectedPlayer:null,
    canSelectCard:false,

    connections:null,
    selectors:null,

    constructor(args) {
      dojo.safeMixin(this, args);
      this.connections = [];
    },

    clearPossible(){
      this.connections.forEach(dojo.disconnect);
      this.selectors.forEach(selector => selector.clearPossible() );
      this.connections = [];
      this.selectors = [];
    },


    wait(){
      this.selectedPlayer = null;

      var cards = [];
      this.selectablePlayers.forEach(playerId => {
        this.makePlayerSelectable(playerId);

        // Fetch cards
        dojo.query("#bang-player-" + playerId + " .bang-card").forEach(div => {
          cards.push({
            id:dojo.attr(div, "data-id"),
            playerId:playerId,
          })
        });
      });

      if(this.canSelectCard){
        // TODO create card selector
      }


      return new Promise((resolve, reject) => {
        this.on("select", (pId) => {
          resolve(pId);
        })
      })
    },


    makePlayerSelectable(pId){
      // CSS styling
      dojo.addClass("bang-player-" + playerId, "selectable");

      // Add a button
      this.game.addPrimaryActionButton(
        'buttonSelectPlayer' + playerId,
        this.game.gamedatas.players[playerId].name,
        () => this.onClickPlayer(playerId)
      );

      // Make the character clickable
      this.connections.push(dojo.connect(
        $("player-character-" + playerId),
        "click",
        (evt) => {
          evt.preventDefault(); evt.stopPropagation();
          this.onClickPlayer(playerId)
        })
      );
    },

    onClickPlayer(pId){
      debug("Clicked on player", pId);
      if(!this.selectablePlayers.includes(pId))
        return;

      this.emmit("select", pId);
    }
  });
});
