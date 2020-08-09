/*
 * notification sent to all players when someone plays a card
 */
notif_cardPlayed: function(notif) {
	var card = notif.args.card; // contains id, type, color, value (the last 2 are from type_arg)
	var playerId = notif.args.player;
},

/*
 * notification sent to all players when a player looses or gains hp
 */
notif_updateHP: function(notif) {
	var playerId = notif.args.player;
	// if you need only one of those, tell me which one
	var amount = notif.args.amount;
	var newHP = notif.args.hp;
	var oldHP = newHP - amount;
},

/*
 * notification sent only to the player who got Cards to his Hand
 */
notif_cardsGained: function(notif) {
	var cards = notif.args.cards; //array of gained cards all having id, type, color, value (the last 2 are from type_arg)	
	if(notif.args.src == 'deck') {
		
	} else {
		var playerId = notif.args.src;
	}
},

/*
 * notification sent only to the player who lost Cards from his and or playarea
 */
notif_cardsLost: function(notif) {
	var cards = notif.args.cards; //array of the ids of the lost cards	
},

/*
 * notification sent to all players when the hand count of a player changed
 */
notif_updateHand: function(notif) {
	var playerId = notif.args.player;
	var amount = notif.args.amount;
	var currentHandCount = 0; //todo get the currently displayed value
	var newHandCount = currentHandCount + amount;
},

