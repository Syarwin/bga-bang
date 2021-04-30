function truncate(str, n) {
  return str.length > n ? str.substr(0, n - 1) + '&hellip;' : str;
}

define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.playerTrait', null, {
    constructor() {
      this._notifications.push(
        ['updateHP', 200],
        ['updateHand', 200],
        ['playerEliminated', 1000],
        ['updatePlayers', 100],
        ['showMessage', 1],
      );
    },

    setupPlayerBoards() {
      this.forEachPlayer((player) => {
        let isCurrent = player.id == this.player_id;

        if (player.role == null) player.role = 'hidden';
        player.handCount = isCurrent ? player.hand.length : player.hand;
        player.powers = '<p>' + player.powers.join('</p><p>') + '</p>';
        player.newNo = player.no;
        player.shortName = truncate(player.name, 11);

        player.background = player.color_back ? '#' + player.color_back : 'transparent';
        dojo.place(this.format_block('jstpl_player', player), 'board');
        this.addTooltipHtml('player-character-' + player.id, this.format_block('jstpl_characterTooltip', player));
        player.inPlay.forEach((card) => this.addCard(card, 'player-inplay-' + player.id));
        dojo.connect($('player-character-' + player.id), 'onclick', (evt) => {
          evt.preventDefault();
          evt.stopPropagation();
          this.onClickPlayer(player.id);
        });

        dojo.place(this.format_block('jstpl_player_board_data', player), 'overall_player_board_' + player.id);

        if (isCurrent) {
          let role = this.getRole(player.role);
          dojo.place(this.format_block('jstpl_hand', role), 'board');
          player.hand.forEach((card) => this.addCard(card, 'hand-cards'));
          this.addTooltip('role-card', role['role-text'], '');

          dojo.place(jstpl_helpIcon, 'bang-player-board-' + player.id);
        }
      });

      this.updatePlayers();
    },

    /*
     * notification sent to all players when a player loses or gains hp
     */
    notif_updateHP(n) {
      debug('Notif: hp changed', n);
      var currentHp = dojo.attr('bang-player-' + n.args.player_id, 'data-bullets');
      dojo.query('#bang-player-' + n.args.player_id + ' .bullet').forEach((bullet, id) => {
        if ((currentHp <= id && id < n.args.hp) || (n.args.hp <= id && id < currentHp)) {
          dojo.removeClass(bullet, 'pulse');
          bullet.offsetWidth;
          dojo.addClass(bullet, 'pulse');
        }
      });
      this.gamedatas.players[n.args.player_id].hp = n.args.hp;
      dojo.attr('bang-player-' + n.args.player_id, 'data-bullets', n.args.hp);
      dojo.attr('bang-player-board-' + n.args.player_id, 'data-bullets', n.args.hp);
    },

    /*
     * notification sent to all players when someone is eliminated
     * WARNING : standard BGA notification when a player is elimnated from the table
     */
    notif_playerEliminated(n) {
      debug('Notif: player eliminated', n);
      dojo.addClass('bang-player-' + n.args.who_quits, 'eliminated');
    },

    // TODO
    notif_updatePlayers(n) {
      debug('Notif: update players', n);
      this.gamedatas.players = n.args.players;
      this.updatePlayers();
    },

    notif_showMessage(n) {
      debug('Notif: show message', n);
      this.showMessage(n.log, 'info');
    },

    /*
     * Called to setup player board, called at setup and when someone is eliminated
     */
    updatePlayers() {
      var players = Object.values(this.gamedatas.players);
      var nPlayers = players.length;
      var playersAlive = players.reduce((carry, player) => carry + (player.eliminated ? 0 : 1), 0);
      var playersEliminated = nPlayers - playersAlive;
      var newNo = 0;
      players
        .sort((a, b) => a.no - b.no)
        .forEach((player) => {
          if (!player.eliminated) player.newNo = newNo++;
        });
      var currentPlayerNo = players.reduce(
        (carry, player) => (player.id == this.player_id && !player.eliminated ? player.newNo : carry),
        0,
      );

      players.forEach((player) => {
        this.displayRoleIfPublic(player);
        if (player.eliminated) {
          dojo.addClass('overall_player_board_' + player.id, 'eliminated');

          if ($('bang-player-' + player.id)) dojo.destroy('bang-player-' + player.id);

          if (player.id == this.player_id && $('hand')) dojo.destroy('hand');
        } else {
          player.newNo = (player.newNo + playersAlive - currentPlayerNo) % playersAlive;
          dojo.attr('bang-player-' + player.id, 'data-no', player.newNo);
        }
      });

      dojo.attr('board', 'data-players', playersAlive);
    },

    displayRoleIfPublic(player) {
      var role = this.getRole(player.role);
      if (role != null && !$('player-role-' + player.id)) {
        dojo.place(this.format_block('jstpl_player_board_role', player), 'player_board_' + player.id);
        this.addTooltip('player-role-' + player.id, role['role-name'], '');
      }
    },

    /*
     * Highlight with border whose turn it is (might be different to who is active)
     */
    updateCurrentTurnPlayer(playerId) {
      dojo.query('div.bang-player-container').style('border', '1px solid rgba(50,50,50,0.8)');
      dojo
        .query('#bang-player-' + playerId + ' .bang-player-container')
        .style('border', '2px solid #' + this.gamedatas.players[playerId].color);
    },

    /*
     * Update player status (active/inactive)
     */
    updatePlayersStatus() {
      var args = this.gamedatas.gamestate;

      dojo.query('.bang-player').addClass('inactive');
      var activePlayers = args.type == 'activeplayer' ? [args.active_player] : [];
      if (args.type == 'multipleactiveplayer') activePlayers = args.multiactive;
      activePlayers.forEach((playerId) => dojo.removeClass('bang-player-' + playerId, 'inactive'));
      if (args.name == 'playCard') this.updateCurrentTurnPlayer(args.active_player);
    },

    /*
     * getRole: factory function that return a role
     */
    getRole(roleId) {
      const roles = {
        0: {
          role: 0,
          'role-name': _('Sheriff'),
          'role-text': _('Kill all the Outlaws and the Renegade!'),
        },
        1: {
          role: 1,
          'role-name': _('Vice'),
          'role-text': _('Protect the Sheriff! Kill all the Outlaws and the Renegade!'),
        },
        2: {
          role: 2,
          'role-name': _('Outlaw'),
          'role-text': _('Kill the Sheriff!'),
        },
        3: {
          role: 3,
          'role-name': _('Renegade'),
          'role-text': _('Be the last one in play!'),
        },
      };
      return roleId == 'hidden' ? null : roles[roleId];
    },

    /*
     * notification sent to all players when the hand count of a player changed
     */
    notif_updateHand(n) {
      debug('Notif: update handcount of player', n);
      this.setHandCount(n.args.player_id, n.args.total);
    },

    /*
     * Change player cards in hand counter
     */
    setHandCount(playerId, newHandCount) {
      //      var currentHandCount = parseInt(dojo.attr('bang-player-' + playerId, 'data-hand')),
      //        newHandCount = currentHandCount + parseInt(amount);
      dojo.attr('bang-player-' + playerId, 'data-hand', newHandCount);
      dojo.attr('bang-player-board-' + playerId, 'data-hand', newHandCount);
      this.gamedatas.players[playerId].handCount = newHandCount;
    },

    displayPlayersHelp() {
      new customgame.modal('playersHelp', {
        autoShow: true,
        title: _('Players informations'),
        class: 'bang_popin',
        closeIcon: 'fa-times',
        openAnimation: true,
        openAnimationTarget: 'help-icon',
        contentsTpl: jstpl_helpDialog,
        verticalAlign: 'flex-start',
      });

      [0, 2, 2, 3, 1, 2, 1].forEach((roleId, i) => {
        if (i >= Object.keys(this.gamedatas.players).length) return;

        if ($('dialog-role-count-' + roleId)) {
          $('dialog-role-count-' + roleId).innerHTML = parseInt($('dialog-role-count-' + roleId).innerHTML) + 1;
        } else {
          dojo.place(this.format_block('jstpl_helpDialogRole', this.getRole(roleId)), 'dialog-roles');
        }
      });

      this.forEachPlayer((player) => {
        dojo.place(this.format_block('jstpl_helpDialogCharacter', player), 'dialog-players');
      });
    },

    /********************
     *** TARGET_PLAYER ***
     ********************/

    /*
     * Make some players selectable with either action button or directly on the board
     */
    makePlayersSelectable(players, append = false) {
      if (!append) {
        this.gamedatas.gamestate.descriptionmyturn = _('You must choose a player');
        this.updatePageTitle();
        this.removeActionButtons();
        this.addActionButton(
          'buttonCancel',
          _('Undo'),
          () => this.onClickCancelCardSelected(this._selectableCards),
          null,
          false,
          'gray',
        );
      }

      this._selectablePlayers = players;
      this._selectablePlayers.forEach((playerId) => {
        this.addActionButton('buttonSelectPlayer' + playerId, this.gamedatas.players[playerId].name, () =>
          this.onClickPlayer(playerId),
        );
        dojo.addClass('bang-player-' + playerId, 'selectable');
      });
    },

    /*
     * Triggered when a player click on a player's board or action button
     */
    onClickPlayer(playerId) {
      if (!this._selectablePlayers.includes(playerId)) return;

      if (this._action == 'drawCard') {
        this.onClickDraw(playerId);
      } else {
        this._selectedOptionType = 'player';
        this._selectedPlayer = playerId;
        const CARD_JAIL = 17;
        if (this._selectedCard.type === CARD_JAIL && playerId === this.gamedatas.playerTurn) {
          this.confirmationDialog(_('Are you sure you want to put yourself to Jail?'), () => {
            this.onSelectOption();
          });
        } else {
          this.onSelectOption();
        }
      }
    },

    /******************
     *** TARGET_CARD ***
     ******************/
    /*
     * Make some players' cards selectable with sometimes the deck
     */
    makePlayersCardsSelectable(playersIds) {
      this.removeActionButtons();
      this.gamedatas.gamestate.descriptionmyturn = _("You must choose a card in play or a player's hand");
      this.updatePageTitle();
      var oldSelectableCards = this._selectableCards;
      this.addActionButton(
        'buttonCancel',
        _('Undo'),
        () => this.onClickCancelCardSelected(oldSelectableCards),
        null,
        false,
        'gray',
      );

      var cards = [];
      this._selectablePlayers = playersIds.filter((playerId) => {
        return this.gamedatas.players[playerId].handCount > 0;
      });
      playersIds.forEach((playerId) => {
        dojo.addClass('bang-player-' + playerId, 'selectable');
        dojo.query('#bang-player-' + playerId + ' .bang-card').forEach((div) => {
          cards.push({
            id: dojo.attr(div, 'data-id'),
            playerId: playerId,
          });
        });
      });
      this.makeCardSelectable(cards, 'selectOption');
    },

    onClickCardSelectOption(card) {
      this._selectedPlayer = card.playerId;
      this._selectedOptionType = 'inPlay';
      this._selectedOptionArg = card.id;
      this.onSelectOption();
    },
  });
});
