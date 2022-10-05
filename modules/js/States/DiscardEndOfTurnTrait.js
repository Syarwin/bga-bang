define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.discardEndOfTurnTrait', null, {
    constructor() {
      //      this._notifications.push(      );
    },

    onClickEndOfTurn() {
      this.takeAction('actEndTurn');
    },

    onClickCancelEndTurn() {
      this.takeAction('actCancelEndTurn');
    },

    onEnteringStateDiscardExcess(args) {
      debug('Discard excess', args);
      this._amount = args.amount;
      this._selectedCards = [];
      this.makeCardSelectable(args._private, 'discardExcess');
    },

    onClickCardDiscardExcess(card) {
      this.onClickCardToSelect(card, 'buttonConfirmDiscardExcess', _('Confirm discard'), 'onClickConfirmDiscardExcess');
    },

    onClickConfirmDiscardExcess() {
      this.takeAction('actDiscardExcess', {
        cards: this._selectedCards.join(';'),
      });
    },

    /** End of Life discard **/

    onEnteringStatePreEliminate(args) {
      debug('Discard before dying', args);
      this._amount = args.amount;
      this._selectedCards = [];
      this.makeCardSelectable(args._private, 'discardEliminate');
    },

    onClickCardDiscardEliminate(card) {
      this.onClickCardToSelect(card, 'buttonConfirmDiscardEliminate', _('Confirm discard order'), 'onClickConfirmDiscardEliminate');
    },

    onClickConfirmDiscardEliminate() {
      this.takeAction('actDiscardEliminate', {
        cards: this._selectedCards.join(';'),
      });
    },

    /** Vice Penalty Discard **/

    onEnteringStateVicePenalty(args) {
      debug('Discard because killing vice', args);
      this._amount = args.amount;
      this._selectedCards = [];
      this.makeCardSelectable(args._private, 'discardVicePenalty');
    },

    onClickCardDiscardVicePenalty(card) {
      if (!this.toggleCard(card)) return;

      if (this._selectedCards.length < this._amount) {
        this.removeActionButtons();
        this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
      } else {
        this.addActionButton(
          'buttonConfirmDiscardVicePenalty',
          _('Confirm discard order'),
          'onClickConfirmDiscardVicePenalty',
          null,
          false,
          'blue',
        );
      }
    },

    onClickConfirmDiscardVicePenalty() {
      this.takeAction('actDiscardVicePenalty', {
        cards: this._selectedCards.join(';'),
      });
    },
  });
});
