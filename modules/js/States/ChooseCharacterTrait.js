define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.chooseCharacterTrait', null, {
    constructor() {
      this._notifications.push(['characterChosen', 1]);
    },

    onEnteringStateChooseCharacter(args) {
      debug('Choosing character', args);

      if (!this.isCurrentPlayerActive()) return;
      this.gamedatas.gamestate.args.cards = args.characters[this.player_id];

      this.dialogChooseCharacter();
    },

    dialogChooseCharacter() {
      var args = this.gamedatas.gamestate.args;
      this._dial = new customgame.modal('selectCard', {
        autoShow: true,
        title: _('Choose a character'),
        class: 'bang_popin',
        closeIcon: 'fa-times',
        openAnimation: true,
        openAnimationTarget: 'buttonShowCharacters',
        contentsTpl: jstpl_dialog,
      });
      args.cards.forEach((card) => this.addCharacterCard(card));
      this._selectableCards = args.cards;
      $('dialog-title-container').innerHTML = $('pagemaintitletext').innerHTML;
    },

    onClickConfirmCharacter(characterId) {
      if (this._dial != null) this.destroyDialog();
      this.clearActionButtons();
      this.takeAction('actChooseCharacter', {
        character: characterId,
      });
    },

    addCharacterCard(card) {
      var div = dojo.place(this.format_block('jstpl_character', card), 'dialog-card-container');
      this.addTooltipHtml(div.id, this.format_block('jstpl_characterTooltip', card));
      dojo.connect(div, 'onclick', (evt) => {
        evt.preventDefault();
        evt.stopPropagation();
        this.onClickConfirmCharacter(card.characterId);
      });
      this.centerCardsIfFew();
      return div;
    },

    notif_characterChosen(n) {
      debug('Notif: character chosen', n);
      const newData = n.args.character;
      this.gamedatas.players[n.args.player_id] = {...this.gamedatas.players[n.args.player_id], ...newData};
      let player = this.gamedatas.players[n.args.player_id];
      dojo.destroy('bang-player-' + player.id);
      dojo.place(this.format_block('jstpl_player', player), 'board');
      this.updatePlayersStatus();
      this.setupCharacter(player);
      // TODO: If help is opened and this notification is received - we have a bug: fadeOutAnimation() in modal.js is rejected
      // if ($('dialog-players')) {
      //   dojo.place(this.format_block('jstpl_helpDialogCharacter', player), 'dialog-players');
      // }
      if (player.id === this.player_id || this.isSpectator) {
        $('player-distance-' + player.id).classList.add('current');
      }
    },
  });
});
