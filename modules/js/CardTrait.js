define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.cardTrait', null, {
    constructor() {
      const ignoreFunction = (n) => {
        return n.args.ignore && n.args.ignore.includes(this.player_id);
      };
      this._notifications.push(
        ['cardPlayed', null],
        ['cardsGained', 1200, ignoreFunction],
        ['cardLostToDeck', 1200, ignoreFunction],
        ['cardLost', null],
        ['flipCard', 800],
        ['reshuffle', 1500],
        ['updateOptions', 200],
      );
    },

    slideTemporaryToDiscard(card, sourceId, duration) {
      var ocard = this.getCard(card, true);
      ocard.uid = ocard.id + 'discard';
      ocard.extraClass += ' slide';
      this.slideTemporary('jstpl_card', ocard, 'board', sourceId, 'discard', duration || 1000, 0).then(() => {
        var div = this.addCard(card, 'discard');
        dojo.style(div, 'zIndex', dojo.query('#discard .bang-card').length);
        dojo.style(div, 'transformStyle', "initial");
      });
      dojo.style('bang-card-' + ocard.uid, 'zIndex', dojo.query('#discard .bang-card').length);
      this.centerCardsIfFew();
    },

    getCardAndDestroy(card, val) {
      var id = 'bang-card-' + card.id;
      if ($(id)) {
        dojo.attr(id, 'data-type', 'empty');
        setTimeout(() => dojo.destroy(id), 700);
        return id;
      } else {
        return val;
      }
    },

    /*
     * getCard: factory function that create a card
     */
    getCard(ocard, eraseId) {
      // Gets a card object ready to use in UI templates
      var card = {
        id: eraseId ? -1 : ocard.id,
        type: ocard.type,
        name: '',
        text: '',
        color: ocard.color,
        value: ocard.value,
        flipped: ocard.flipped === undefined || !ocard.flipped ? '' : 'flipped',
        enforceTooltip: ocard.enforceTooltip === undefined ? false : ocard.enforceTooltip,
        extraClass: '',
        colorOverride: this.gamedatas?.eventActive?.colorOverride || '',
      };

      if (this._cards[ocard.type]) {
        card.name = _(this._cards[ocard.type].name);
        card.text = _(this._cards[ocard.type].text);
      }

      return card;
    },

    /*
     * Create a flipped card
     */
    getBackCard() {
      return {
        id: -1,
        color: 0,
        value: 0,
        name: '',
        text: '',
        type: 'back',
        flipped: true,
        extraClass: '',
        colorOverride: ''
      };
    },

    /*
     * Create several card backs
     */
    getNBackCards(n) {
      return Array.apply(null, { length: n }).map((o) => this.getBackCard());
    },

    /*
     * Create and add a new card to a container
     */
    addCard(ocard, container, suffix = '') {
      var card = this.getCard(ocard);
      card.uid = card.id + suffix;
      if ($('bang-card-' + card.uid)) dojo.destroy('bang-card-' + card.uid);

      var div = dojo.place(this.format_block('jstpl_card', card), container);
      if (card.flipped === '' || card.enforceTooltip)
        this.addTooltipHtml(div.id, this.format_block('jstpl_cardTooltip', card));
      dojo.connect(div, 'onclick', (evt) => {
        evt.preventDefault();
        evt.stopPropagation();
        this.onClickCard(card);
      });
      this.centerCardsIfFew();
      return div;
    },

    updateDeckCount(n) {
      $('mainDeckCount').innerHTML = n.args.deckCount;
    },

    /*
     * Given a list of cards, make them selectable
     */
    makeCardSelectable(cards, action, suffix = '') {
      this._action = action;

      // Add unselectable class
      if (action === 'selectCard' || action === 'discardExcess') {
        dojo.query('#hand .bang-card').addClass('unselectable');
        dojo.query('#bang-player-' + this.player_id + ' .bang-card').addClass('unselectable');
      } else {
        if (action !== 'selectDialog') dojo.query('.bang-card').addClass('unselectable');
      }

      this._selectableCards = cards;
      this._selectableCards.forEach((card) => {
        card.uid = card.id + suffix;
        dojo.removeClass('bang-card-' + card.uid, 'unselectable');
        dojo.addClass('bang-card-' + card.uid, 'selectable');
        if (card.mustPlay) {
          dojo.addClass('bang-card-' + card.uid, 'mustplay');
        }
      });

      if (action === 'playCard') {
        this.gamedatas.gamestate.descriptionmyturn = _('You can play a card');
        this.updatePageTitle();
      }
    },

    /*
     * Triggered whenever a player click on a card
     */
    onClickCard(ocard) {
      //if(!this.isCurrentPlayerActive()) return;
      // Is the card in the discard ?
      if ($('bang-card-' + ocard.uid).parentNode.id === 'discard') return this.onClickDiscard();

      // Is the card selectable ?
      var card = this._selectableCards.find((o) => o.id === ocard.id);
      if (!card) return;

      var methodName = 'onClickCard' + this._action.charAt(0).toUpperCase() + this._action.slice(1);
      if (this[methodName] !== undefined) {
        if (this._action === 'playCard' && card.options.confirmationMsg) {
          this.confirmationDialog(_(card.options.confirmationMsg), () => {
            this[methodName](card);
          });
        } else {
          this[methodName](card);
        }
      } else {
        console.error('Trying to call ' + methodName); // Should not happen
      }
    },

    /*
     * Toggle a card : useful for multiple select or whenever we want a confirm button
     */
    toggleCard(card) {
      var domId = 'bang-card-' + card.uid;
      // Already selected, unselect it
      if (this._selectedCards.includes(card.id)) {
        this._selectedCards = this._selectedCards.filter((id) => id !== card.id);
        dojo.removeClass(domId, 'selected');
        this._selectableCards.forEach((c) => {
          let query = '#bang-card-' + c.uid;
          if (this._selectedCards.length > 0) {
            // Do it for not inplay cards only
            query = ':not(.player-inplay .bang-card)' + query;
          }
          dojo.query(query).addClass('selectable').removeClass('unselectable');
        });
      }
      // Not yet selected, add to selection
      else {
        if (this._selectedCards.length >= this._amount) return false;

        this._selectedCards.push(card.id);
        dojo.addClass(domId, 'selected');

        if (this._selectedCards.length > 0) {
          dojo.query('.player-inplay .bang-card').removeClass('selectable').addClass('unselectable');
          if (this._selectedCards.length === this._amount) {
            dojo.query('.bang-card.selectable').removeClass('selectable').addClass('unselectable');
            dojo.query('.bang-card.selected').removeClass('unselectable').addClass('selectable');
          }
        }
      }

      return true;
    },

    onClickCardToSelect(card, buttonName, buttonText, callback) {
      if (!this.toggleCard(card)) return;

      if (this._selectedCards.length < this._amount || this._selectedCards.length === 0) {
        this.removeActionButtons();
        this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
      } else {
        if (!$(buttonName)) {
          this.addActionButton(
              buttonName,
              buttonText,
              callback,
              null,
              false,
              'blue',
          );
        }
      }
    },

    /*
 * notification sent to all players when someone plays a card
 */
    notif_cardPlayed(n) {
      debug('Notif: card played', n);
      var playerId = n.args.player_id,
          target = n.args.target,
          targetPlayer = n.args.player_id2 || null;
      var animationDuration = 1000;

      if (!targetPlayer && target === 'inPlay') {
        targetPlayer = playerId;
        animationDuration = 700;
      }
      if (targetPlayer && target !== 'inPlay') {
        // Slide to player then to discard
        animationDuration = 1600;
      }

      this.notifqueue.setSynchronousDuration(100 + animationDuration);

      var card = this.getCard(n.args.card, true);
      card.uid = card.id + 'slide';
      card.extraClass += ' slide';
      var sourceId = this.getCardAndDestroy(n.args.card, 'player-character-' + playerId);
      if (targetPlayer) {
        var duration = target === 'inPlay' ? animationDuration : animationDuration / 2;
        var targetId = (target === 'inPlay' ? 'player-inplay-' : 'player-character-') + targetPlayer;
        this.slideTemporary('jstpl_card', card, 'board', sourceId, targetId, duration, 0).then(() => {
          // Add the card in front of player
          if (target === 'inPlay') this.addCard(n.args.card, targetId);
          // Put the card in the discard pile
          else this.slideTemporaryToDiscard(n.args.card, targetId, animationDuration / 2, 'discard');
        });
      }
      // Directly to discard
      else {
        this.slideTemporaryToDiscard(n.args.card, sourceId, animationDuration, 'discard');
      }
    },

    /*
     * notification sent to all players when someone gained a card (from deck or from someone else hand/inplay)
     */
    notif_cardsGained(n) {
      this.removeDialog('selectCard');

      debug('Notif: cards gained', n);
      if (n.args.card) n.args.cards = [n.args.card];

      var cards =
          n.args.cards && n.args.cards.length > 0
              ? n.args.cards.map((o) => this.getCard(o))
              : this.getNBackCards(n.args.amount);

      cards.forEach((card, i) => {
        card.uid = card.id + 'slide';
        card.extraClass += ' slide';
        if (n.args.src === 'discard' && !$(`bang-card-${card.id}`)) {
          // Looks like not all cards from discard are added on frontend, need to add it
          this.addCard(card, 'discard');
        }

        let sourceId =
            n.args.src === 'deck' ? 'deck' : this.getCardAndDestroy(card, 'player-character-' + n.args.player_id2);
        let targetId;
        if (n.args.target === 'hand') {
          if (this.player_id === n.args.player_id && !n.args.isSelection) {
            targetId = 'hand';
          } else {
            targetId = 'player-character-' + n.args.player_id;
          }
        } else {
          targetId = 'player-inplay-' + n.args.player_id;
        }
        this.slideTemporary('jstpl_card', card, 'board', sourceId, targetId, 800, 120 * i).then(() => {
          if (targetId === 'hand' && !n.args.isSelection) this.addCard(card, 'hand-cards');
          if (n.args.target === 'inPlay') this.addCard(card, targetId);
        });

        if (n.args.src === 'deck' || n.args.src === 'discard') {
          // Make sure it will pass in front of discard
          dojo.style('bang-card-' + card.uid, 'zIndex', dojo.query('#discard .bang-card').length + i);
        }
      });

      if (n.args.src === 'deck') this.updateDeckCount(n);

      if (n.args.src === 'discard' && n.args.nextCard) {
        this.addCard(n.args.nextCard, 'discard');
      }
    },

    /*
     * notification sent to all players when someone discard a card
     */
    notif_cardLost(n) {
      debug('Notif: card lost', n);
      var sourceId = this.getCardAndDestroy(n.args.card, 'player-character-' + n.args.player_id);
      this.slideTemporaryToDiscard(n.args.card, sourceId);
      this.notifqueue.setSynchronousDuration(n.args.player_id === this.player_id ? 800 : 1200);
    },

    notif_cardLostToDeck(n) {
      debug('Notif: cardLostToDeck', n);
      let card = this.getBackCard();
      card.uid = card.id + 'slide';
      card.extraClass += ' slide';
      let sourceId;
      if (n.args.card) {
        sourceId = this.getCardAndDestroy(n.args.card, 'player-character-' + n.args.player_id);
      } else {
        sourceId = this.getCardAndDestroy(card, 'player-character-' + n.args.player_id);
      }
      this.slideTemporary('jstpl_card', card, 'board', sourceId, 'deck', 800, 120 * i)
      this.notifqueue.setSynchronousDuration(n.args.player_id === this.player_id ? 800 : 1200);
      this.updateDeckCount(n);
    },

    /*
     * Flip card
     */
    notif_flipCard(n) {
      debug('Notif: card flipped', n);
      var card = n.args.card;
      card.flipped = true;
      card.enforceTooltip = true;
      var div = this.addCard(card, 'discard');
      dojo.style(div, 'zIndex', dojo.query('#discard .bang-card').length);
      setTimeout(() => dojo.removeClass('bang-card-' + card.id, 'flipped'), 100);
      this.updateDeckCount(n);
    },

    /*
     * Update playing/reacting option after playing a card
     */
    notif_updateOptions(n) {
      debug('Notif: update options', n);
      this.gamedatas.gamestate.args['_private'] = n.args;
      this.clearPossible();
      var action = this.gamedatas.gamestate.name === 'playCard' ? 'playCard' : 'selectReact';
      this.makeCardSelectable(n.args.cards, action);
    },

    notif_reshuffle(n) {
      debug('Notif: reshuffle', n);
      dojo.query('#discard .bang-card').forEach((card, i) =>
        setTimeout(() => {
          dojo.addClass(card, 'flipped');
          setTimeout(() => dojo.destroy(card), 500);
        }, i * 10),
      );
      this.updateDeckCount(n);
    },
  });
});
