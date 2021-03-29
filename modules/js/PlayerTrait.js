define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.playerTrait", null, {
    constructor(){
      this._notifications.push(
        ['updateHP', 200],
        ['updateHand', 200],
        ['playerEliminated', 1000],
        ['updatePlayers', 100]
      );
    },

    setupPlayerBoards(){
      this.forEachPlayer(player => {
        let isCurrent = player.id == this.player_id;

        if(player.role == null) player.role = 'hidden';
        player.handCount = isCurrent? player.hand.length : player.hand;
        player.powers = '<p>' + player.powers.join('</p><p>') + '</p>';

        dojo.place(this.format_block('jstpl_player', player), 'board');
        this.addTooltipHtml("player-character-" + player.id, this.format_block( 'jstpl_characterTooltip',  player));
        player.inPlay.forEach(card => this.addCard(card, 'player-inplay-' + player.id));
        dojo.connect($("player-character-" + player.id), "onclick", (evt) => { evt.preventDefault(); evt.stopPropagation(); this.onClickPlayer(player.id) });

        dojo.place(this.format_block('jstpl_player_board_data', player), "overall_player_board_" + player.id);

        if(isCurrent){
          let role = this.getRole(player.role);
          dojo.place(this.format_block('jstpl_hand', role), 'board');
          player.hand.forEach(card => this.addCard(card, 'hand-cards') );
          this.addTooltip("role-card", role["role-text"], '');

          dojo.place(jstpl_helpIcon, 'bang-player-board-' + player.id);
        }
      });

      this.updatePlayers();
    },

    /*
    * notification sent to all players when a player looses or gains hp
    */
    notif_updateHP(n) {
      debug("Notif: hp changed", n);
      var currentHp = dojo.attr("bang-player-" + n.args.playerId, "data-bullets");
      dojo.query("#bang-player-" + n.args.playerId + " .bullet").forEach( (bullet, id) => {
        if( (currentHp <= id && id < n.args.hp) || (n.args.hp <= id && id < currentHp) ){
          dojo.removeClass(bullet, "pulse");
          bullet.offsetWidth;
          dojo.addClass(bullet, "pulse");
        }
      });
      dojo.attr("bang-player-" + n.args.playerId, "data-bullets", n.args.hp);
      dojo.attr("bang-player-board-" + n.args.playerId, "data-bullets", n.args.hp);
    },


    /*
    * notification sent to all players when someone is eliminated
    */
    notif_playerEliminated(n){
      debug("Notif: player eliminated", n);
      dojo.addClass('bang-player-' + n.args.who_quits, "eliminated");
    },


    // TODO
    notif_updatePlayers(n){
      debug("Notif: update players", n);
      this.gamedatas.players = n.args.players;
      this.updatePlayers();
    },



    /*
     * Called to setup player board, called at setup and when someone is eliminated
     */
    updatePlayers(){
      var players = Object.values(this.gamedatas.players);
      var nPlayers = players.length;
      var playersAlive = players.reduce((carry,player) => carry + (player.eliminated? 0 : 1), 0);
      var playersEliminated = nPlayers - playersAlive;
      var newNo = 0;
      players.forEach( player => {
        if(!player.eliminated)
          player.no = newNo++;
      });
      var currentPlayerNo = players.reduce((carry, player) => (player.id == this.player_id && !player.eliminated)? player.no : carry, 0);

      players.forEach( player => {
        this.displayRoleIfPublic(player);
        if(player.eliminated){
          dojo.addClass("overall_player_board_" + player.id, "eliminated");

          if($("bang-player-" + player.id))
            dojo.destroy("bang-player-" + player.id);

          if(player.id == this.player_id && $("hand"))
            dojo.destroy("hand");
        } else {
          player.no = (player.no + playersAlive - currentPlayerNo) % playersAlive;
          dojo.attr("bang-player-" + player.id, "data-no", player.no);
        }
      });

      dojo.attr("board", "data-players", playersAlive);
    },


    displayRoleIfPublic(player){
      var role = this.getRole(player.role);
      if(role != null && !$("player-role-" + player.id)){
        dojo.place(this.format_block('jstpl_player_board_role', player), "player_board_" + player.id);
        this.addTooltip("player-role-" + player.id, role["role-name"], '');
      }
    },

    /*
     * Highlight with border whose turn it is (might be different to who is active)
     */
    updateCurrentTurnPlayer(playerId){
      dojo.query("div.bang-player-container").style("border", "1px solid rgba(50,50,50,0.8)");
      dojo.query("#bang-player-" + playerId + " .bang-player-container").style("border", "2px solid #" + this.gamedatas.players[playerId].color);
    },


    /*
     * Update player status (active/inactive)
     */
    updatePlayersStatus(){
      var args = this.gamedatas.gamestate;

      dojo.query(".bang-player").addClass("inactive");
      var activePlayers = (args.type == "activeplayer")? [args.active_player] : [];
      if(args.type == "multipleactiveplayer")
        activePlayers = args.multiactive;
      activePlayers.forEach(playerId => dojo.removeClass("bang-player-" + playerId, "inactive") );
      if(args.stateName == "playCard")
        this.updateCurrentTurnPlayer(args.active_player);
    },


    /*
     * getRole: factory function that return a role
     */
    getRole(roleId){
      const roles = {
        0: {
          "role":0,
          "role-name": _("Sheriff"),
          "role-text": _("Kill all the Outlaws and the Renegade!")
        },
        1:{
          "role":1,
          "role-name":_("Vice"),
          "role-text":_("Protect the Sheriff! Kill all the Outlaws and the Renegade!"),
        },
        2:{
          "role":2,
          "role-name":_("Outlaw"),
          "role-text":_("Kill the Sheriff!"),
        },
        3:{
          "role":3,
          "role-name":_("Renegade"),
          "role-text":_("Be the last one in play!"),
        },
      };
      return roleId == "hidden"? null : roles[roleId];
    },




    /*
    * notification sent to all players when the hand count of a player changed
    */
    notif_updateHand(n) {
      debug("Notif: update handcount of player", n);
      this.incHandCount(n.args.playerId, n.args.amount);
    },


    /*
     * Change player cards in hand counter
     */
    incHandCount(playerId, amount){
      var currentHandCount = parseInt(dojo.attr("bang-player-" + playerId, "data-hand")),
          newHandCount = currentHandCount + parseInt(amount);
      dojo.attr("bang-player-" + playerId, "data-hand", newHandCount);
      dojo.attr("bang-player-board-" + playerId, "data-hand", newHandCount);
    },


    displayPlayersHelp(){
      new customgame.modal("playersHelp", {
       autoShow:true,
       title:_("Players informations"),
       class:"bang_popin",
       closeIcon:'fa-times',
       openAnimation:true,
       openAnimationTarget:"help-icon",
       contentsTpl:jstpl_helpDialog,
       verticalAlign:'flex-start',
     });

     [0,2,2,3,1,2,1].forEach((roleId, i) => {
       if(i >= Object.keys(this.gamedatas.players).length)
         return;

        if($('dialog-role-count-' + roleId)){
          $('dialog-role-count-' + roleId).innerHTML = parseInt($('dialog-role-count-' + roleId).innerHTML) + 1;
        } else {
          dojo.place(this.format_block('jstpl_helpDialogRole', this.getRole(roleId)), "dialog-roles");
        }
     });

     this.gamedatas.bplayers.forEach( player => {
       dojo.place(this.format_block('jstpl_helpDialogCharacter', player), "dialog-players");
     });
    },
  });
});
