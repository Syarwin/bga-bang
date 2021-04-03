define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.reactTrait', null, {
    constructor() {
      this._notifications.push(['preselection', 10]);
    },

    /*
     * React state : active player can play cards from his hand in reaction
     */
    onEnteringStateReact(args) {
      this._amount = null;
      this._selectedCards = [];
      if (args._private != undefined) {
        this.makeCardSelectable(args._private.cards, 'selectReact');

        // Button for barrel
        args._private.cards.forEach((card) => {
          if ($('bang-card-' + card.id).parentNode.id != 'hand-cards') {
            this.addPrimaryActionButton('buttonUseBarrel', _('Use barrel'), () => this.onClickCardSelectReact(card));
          }
        });

        if (args._private.selection) this.preSelectCards(args._private.selection);
      }
      this.gamedatas.gamestate.descriptionmyturn = args.msgActive;
      this.gamedatas.gamestate.description = args.msgInactive;
      this.updatePageTitle();
    },

    preSelectCards(cards) {
      dojo.query('.bang-card.preselected').removeClass('preselected');
      cards.forEach((cardId) => dojo.addClass('bang-card-' + cardId, 'preselected'));
      if (cards.length > 0) {
        this.addSecondaryActionButton('buttonCancelPreselection', _('Cancel pre-selection'), () =>
          this.takeAction('actCancelPreSelection'),
        );
      } else if ($('buttonCancelPreselection')) {
        dojo.destroy('buttonCancelPreselection');
      }
    },

    notif_preselection(n) {
      debug('Changing pre-selected cards', n);
      this.preSelectCards(n.args.cards);
    },

    onClickCardSelectReact(card) {
      // React with single card
      if (card.amount == 1 && this._amount == null) {
        this._selectedCards = [card.id];
        this.onClickConfirmReact();
      }
      // React with several cards
      else {
        if (this._amount == null) this._amount = card.amount;

        // Toggle the card
        if (!this.toggleCard(card)) return;

        if (this._selectedCards.length < this._amount) {
          if (this._selectedCards.length == 0) this._amount = null;
          this.removeActionButtons();
          this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
        } else {
          this.addActionButton('buttonConfirmReact', _('Confirm react'), 'onClickConfirmReact', null, false, 'blue');
        }
      }
    },

    onClickConfirmReact() {
      this.takeAction('actReact', {
        cards: this._selectedCards.join(';'),
      });
    },

    onClickPass() {
      this.takeAction('actPass');
    },
  });
});
