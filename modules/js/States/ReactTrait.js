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
      if (args._private !== undefined) {
        this.makeCardSelectable(args._private.cards, 'selectReact');

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
      if (card.amount === 1 && this._amount == null) {
        this._selectedCards = [card.id];
        this.onClickConfirmReact();
      }
      // React with several cards
      else {
        if (this._amount == null) this._amount = card.amount;

        // Toggle the card
        if (!this.toggleCard(card)) return;

        if (this._selectedCards.length < this._amount) {
          if (this._selectedCards.length === 0) this._amount = null;
          this.clearActionButtons();
          this.onUpdateActionButtons(
            this.gamedatas.gamestate.name,
            this.gamedatas.gamestate.args,
            this._selectedCards.length === 0,
          );
          if (this._selectedCards.length === 1) {
            this.addPrimaryActionButton('buttonPlayerOneCard', _('Play single card'), () => this.onClickConfirmReact());
          }
        } else {
          this.clearActionButtons();
          this.onUpdateActionButtons(
            this.gamedatas.gamestate.name,
            this.gamedatas.gamestate.args,
            this._selectedCards.length === 0,
          );
          this.addPrimaryActionButton('buttonConfirmReact', _('Confirm react'), () => this.onClickConfirmReact());
        }
      }
    },

    onClickConfirmReact(isBeer = false) {
      if (!isBeer && this._amount > 1 && this._selectedCards.length === 1) {
        this.confirmationDialog(
          _(
            'Attention: this BANG! requires 2 Missed! to be cancelled. Are you sure you want to play just a single one?',
          ),
          () => {
            this.takeActReactAction();
          },
        );
      } else {
        this.takeActReactAction();
      }
    },

    takeActReactAction() {
      this.takeAction('actReact', {
        cards: this._selectedCards.join(';'),
      });
    },

    onClickPass() {
      this.takeAction('actPass');
    },

    onClickPassRussianRoulette() {
      this.takeAction('actPassEndRussianRoulette');
    },
    /*
     * React state : active player can play cards from his hand in reaction
     */
    onEnteringStateReactBeer(args) {
      this._amount = args.n;
      this._selectedCards = [];
      if (args._private != undefined) {
        if (args._private.character != null) this.makeCharacterAbilityUsable(args._private.character);

        this.makeCardSelectable(args._private.cards, 'selectReactBeer');
        this.addDangerActionButton('buttonConfirmPass', _('Pass and die'), () => this.onClickPass());
      }
    },

    onClickCardSelectReactBeer(card) {
      // Toggle the card
      if (!this.toggleCard(card)) return;

      this.clearActionButtons();
      if (this._selectedCards.length === 0) {
        let SID_KETCHUM = 9;
        this.addDangerActionButton('buttonConfirmPass', _('Pass and die'), () => this.onClickPass());
        if (this.gamedatas.players[this.player_id].characterId === SID_KETCHUM) {
          this.makeCharacterAbilityUsable(SID_KETCHUM);
        }
      } else {
        this.addPrimaryActionButton('buttonConfirmReact', _('Confirm'), () => this.onClickConfirmReact(true));
      }
    },
  });
});
