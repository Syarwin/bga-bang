define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("bang.cardTrait", null, {
    constructor(){
      this._notifications.push(
        ['cardPlayed', 2000],
        ['cardsGained', 1200],
        ['cardLost', 1000],
        ['drawCard', 1000]
      );
    },


    getCardAndDestroy(card, val){
      var id = "bang-card-" + card.id;
      if($(id)){
        dojo.attr(id, "data-type", "empty");
        setTimeout(() => dojo.destroy(id), 700);
        return id;
      }
      else {
        return val;
      }
    },

    /*
     * getCard: factory function that create a card
     */
    getCard(ocard, eraseId) {
      // Gets a card object ready to use in UI templates
      var card = {
        id: eraseId? -1 : ocard.id,
        type: ocard.type,
        name: '',
        text: '',
      	color: ocard.color,
     		value: ocard.value,
    		flipped: (typeof ocard.flipped == "undefined" || !ocard.flipped)? "" : "flipped",
      };

    	if(this._cards[ocard.type]){
    		card.name = this._cards[ocard.type].name;
    		card.text = this._cards[ocard.type].text;
    	}

      return card;
    },

    /*
     * Create a flipped card
     */
    getBackCard(){
      return {
        id:-1,
        color:0,
        value:0,
        name:'',
        text: '',
        type:"back",
        flipped:true,
      };
    },

    /*
     * Create several card backs
     */
    getNBackCards(n){
      return Array.apply(null, {length : n}).map(o => this.getBackCard());
    },


    /*
     * Create and add a new card to a container
     */
    addCard(ocard, container){
      var card = this.getCard(ocard);

      var div = dojo.place(this.format_block('jstpl_card', card), container);
      if(card.flipped == "")
        this.addTooltipHtml(div.id, this.format_block( 'jstpl_cardTooltip',  card));
      dojo.connect(div, "onclick", (evt) => { evt.preventDefault(); evt.stopPropagation(); this.onClickCard(card) });
    },


    /*
     * notification sent to all players when someone plays a card
     */
    notif_cardPlayed(n) {
      debug("Notif: card played", n);
      var playerId = n.args.playerId,
          target = n.args.target,
          targetPlayer = n.args.targetPlayer;
      var animationDuration = 1500;

      if(!targetPlayer && target == "inPlay"){
        targetPlayer = playerId;
        animationDuration = 1000;
      }
      if(targetPlayer && target != 'inPlay'){
        // Slide to player then to discard
        animationDuration = 1600;
      }
      this.notifqueue.setSynchronousDuration(animationDuration);


      var card = this.getCard(n.args.card, true);
      var sourceId = this.getCardAndDestroy(n.args.card, "player-character-" + playerId);
      if(targetPlayer){
        var duration = target == "inPlay"? animationDuration: (animationDuration/2);
        var targetId = (target == "inPlay"? "player-inplay-" : "player-character-") + targetPlayer
        this.slideTemporary('jstpl_card', card, "board", sourceId, targetId, duration, 0)
        .then(() => {
          // Add the card in front of player
          if(target == "inPlay")
            this.addCard(n.args.card, targetId)
          // Put the card in the discard pile
          else
            this.slideTemporaryToDiscard(n.args.card, targetId, animationDuration/2);
        });
      }
      // Directly to discard
      else {
        this.slideTemporaryToDiscard(n.args.card, sourceId, animationDuration);
      }
    },




    /*
    * notification sent to all players when someone gained a card (from deck or from someone else hand/inplay)
    */
    notif_cardsGained(n) {
      if(this._dial != null)
        this._dial.destroy();

      debug("Notif: cards gained", n);
      var cards = n.args.cards.length > 0? n.args.cards.map(o => this.getCard(o)) : this.getNBackCards(n.args.amount);
      cards.forEach((card, i) => {
        let sourceId = (n.args.src == "deck")? "deck" : this.getCardAndDestroy(card, "player-character-" + n.args.victimId);
        let targetId = n.args.target == "hand"? (this.player_id == n.args.playerId ? "hand" : ("player-character-" + n.args.playerId)) : ("player-inplay-" + n.args.playerId);

        this.slideTemporary("jstpl_card", card, "board", sourceId, targetId, 800, 120*i).then(() => {
          if(targetId == "hand") this.addCard(card, 'hand-cards');
          if(n.args.target == "inPlay") this.addCard(card, targetId);
        });
      });

      // Update hand counters
      this.incHandCount(n.args.playerId, n.args.amount);
      if(n.args.src == "deck")
        $("deck").innerHTML = parseInt($("deck").innerHTML) - n.args.amount;
      else if(n.args.src != "discard")
        this.incHandCount(n.args.victimId, -n.args.amount);
    },

    /*
    * notification sent to all players when someone discard a card
    */
    notif_cardLost(n) {
      debug("Notif: card lost", n);
      var sourceId = this.getCardAndDestroy(n.args.card, "player-character-" + n.args.playerId);
      this.slideTemporaryToDiscard(n.args.card, sourceId);
    },



    /*
    * Flip card
    */
    notif_drawCard(n){
      debug("Notif: card drawn", n);
      var card = n.args.card;
      card.flipped = true;
      //dojo.addClass("bang-card-" + n.args.src_id, "selected");
      this.addCard(card, "discard");
      setTimeout(() => dojo.removeClass("bang-card-" + card.id, "flipped"), 100);
      //setTimeout(() => dojo.removeClass("bang-card-" + n.args.src_id, "selected"), 1000);
    },


  });
});
