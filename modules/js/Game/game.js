var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui'], (dojo, declare) => {
  return declare('customgame.game', ebg.core.gamegui, {
    /*
     * Constructor
     */
    constructor() {
      this._notifications = [];
      this._activeStates = [];
    },

    /*
     * [Undocumented] Override BGA framework functions to call onLoadingComplete when loading is done
     */
    setLoader(value, max) {
      this.inherited(arguments);
      if (!this.isLoadingComplete && value >= 100) {
        this.isLoadingComplete = true;
        this.onLoadingComplete();
      }
    },

    onLoadingComplete() {
      debug('Loading complete');
    },

    /*
     * Setup:
     */
    setup(gamedatas) {
      // Create a new div for buttons to avoid BGA auto clearing it
      dojo.place("<div id='customActions' style='display:inline-block'></div>", $('generalactions'), 'after');

      this.setupNotifications();
      this.initPreferences();
    },

    /*
     * Detect if spectator or replay
     */
    isReadOnly() {
      return this.isSpectator || typeof g_replayFrom != 'undefined' || g_archive_mode;
    },

    /*
     * Make an AJAX call with automatic lock
     */
    takeAction(action, data, reEnterStateOnError, checkAction = true) {
      if (checkAction && !this.checkAction(action)) return false;

      data = data || {};
      if (data.lock === undefined) {
        data.lock = true;
      } else if (data.lock === false) {
        delete data.lock;
      }
      let promise = new Promise((resolve, reject) => {
        this.ajaxcall(
          '/' + this.game_name + '/' + this.game_name + '/' + action + '.html',
          data,
          this,
          (data) => resolve(data),
          (isError, message, code) => {
            if (isError) reject(message, code);
          },
        );
      });

      if (reEnterStateOnError) {
        promise.catch(() => this.onEnteringState(this.gamedatas.gamestate.name, this.gamedatas.gamestate));
      }

      return promise;
    },

    /*
     * onEnteringState:
     * 	this method is called each time we are entering into a new game state.
     *
     * params:
     *  - str stateName : name of the state we are entering
     *  - mixed args : additional information
     */
    onEnteringState(stateName, args) {
      debug('Entering state: ' + stateName, args);

      if (this._activeStates.includes(stateName) && !this.isCurrentPlayerActive() && stateName != 'react') return;

      // Call appropriate method
      var methodName = 'onEnteringState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },

    /*
     * onLeavingState:
     * 	this method is called each time we are leaving a game state.
     *
     * params:
     *  - str stateName : name of the state we are leaving
     */
    onLeavingState(stateName) {
      debug('Leaving state: ' + stateName);
      this.clearPossible();
    },
    clearPossible() {},

    /*
     * setupNotifications
     */
    setupNotifications() {
      console.log(this._notifications);
      this._notifications.forEach((notif) => {
        var functionName = 'notif_' + notif[0];

        dojo.subscribe(notif[0], this, functionName);
        if (notif[1] !== undefined) {
          if (notif[1] === null) {
            this.notifqueue.setSynchronous(notif[0]);
          } else {
            this.notifqueue.setSynchronous(notif[0], notif[1]);

            // xxxInstant notification runs same function without delay
            dojo.subscribe(notif[0] + 'Instant', this, functionName);
            this.notifqueue.setSynchronous(notif[0] + 'Instant', 10);
          }
        }

        if (notif[2] != undefined) {
          this.notifqueue.setIgnoreNotificationCheck(notif[0], notif[2]);
        }
      });
    },

    /*
     * Add a timer on an action button :
     * params:
     *  - buttonId : id of the action button
     *  - time : time before auto click
     *  - pref : 0 is disabled (auto-click), 1 if normal timer, 2 if no timer and show normal button
     */

    startActionTimer(buttonId, time, pref) {
      var button = $(buttonId);
      var isReadOnly = this.isReadOnly();
      if (button == null || isReadOnly || pref == 2) {
        debug('Ignoring startActionTimer(' + buttonId + ')', 'readOnly=' + isReadOnly, 'prefValue=' + prefValue);
        return;
      }

      // If confirm disabled, click on button
      if (pref == 0) {
        button.click();
        return;
      }

      this._actionTimerLabel = button.innerHTML;
      this._actionTimerSeconds = time;
      this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button == null) {
          this.stopActionTimer();
        } else if (this._actionTimerSeconds-- > 1) {
          button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
        } else {
          debug('Timer ' + buttonId + ' execute');
          button.click();
        }
      };
      this._actionTimerFunction();
      this._actionTimerId = window.setInterval(this._actionTimerFunction, 1000);
      debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
    },

    stopActionTimer() {
      if (this._actionTimerId != null) {
        debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
      }
    },

    /*
     * Play a given sound that should be first added in the tpl file
     */
    playSound(sound, disableNextMoveSound = true, delay = 0) {
      if (sound) {
        if (disableNextMoveSound) {
          this.disableNextMoveSound();
        }
        setTimeout(() => {
          playSound(this.game_name + '_' + sound);
        }, delay);
      }
    },

    /*
     * Add a blue/grey button if it doesn't already exists
     */
    addPrimaryActionButton(id, text, callback) {
      if (!$(id)) this.addActionButton(id, text, callback, 'customActions', false, 'blue');
    },

    addSecondaryActionButton(id, text, callback) {
      if (!$(id)) this.addActionButton(id, text, callback, 'customActions', false, 'gray');
    },

    addDangerActionButton(id, text, callback) {
      if (!$(id)) this.addActionButton(id, text, callback, 'customActions', false, 'red');
    },

    clearActionButtons() {
      this.removeActionButtons();
      dojo.empty('customActions');
    },

    initPreferencesObserver() {
      dojo.query('.preference_control, preference_fontrol').on('change', (e) => {
        var match = e.target.id.match(/^preference_[fc]ontrol_(\d+)$/);
        if (!match) {
          return;
        }
        var pref = match[1];
        var newValue = e.target.value;
        this.prefs[pref].value = newValue;
        if (this.prefs[pref].attribute) {
          $('ebd-body').setAttribute('data-' + this.prefs[pref].attribute, newValue);
        }

        $('preference_control_' + pref).value = newValue;
        if ($('preference_fontrol_' + pref)) {
          $('preference_fontrol_' + pref).value = newValue;
        }
        data = { pref: pref, lock: false, value: newValue, player: this.player_id };
        this.takeAction('actChangePref', data, false, false);
        this.onPreferenceChange(pref, newValue);
      });
    },

    onPreferenceChange(pref, newValue) {},

    // Init preferences will setup local preference and put the corresponding data-attribute on overall-content div if needed
    initPreferences() {
      // Attach data attribute on overall-content div
      Object.keys(this.prefs).forEach((prefId) => {
        let pref = this.prefs[prefId];
        if (pref.attribute) {
          $('ebd-body').setAttribute('data-' + pref.attribute, pref.value);
        }
      });

      if (!this.isReadOnly() && this.gamedatas.localPrefs) {
        // Create local prefs
        Object.keys(this.gamedatas.localPrefs).forEach((prefId) => {
          let pref = this.gamedatas.localPrefs[prefId];
          pref.id = prefId;
          let selectedValue = this.gamedatas.prefs.find((pref2) => pref2.pref_id == pref.id).pref_value;
          pref.value = selectedValue;
          this.prefs[prefId] = pref;
          this.place('tplPreferenceSelect', pref, 'local-prefs-container');
        });
      }

      this.initPreferencesObserver();
      this.setupSettings();
    },

    tplPreferenceSelect(pref) {
      let values = Object.keys(pref.values)
        .map(
          (val) =>
            `<option value='${val}' ${pref.value == val ? 'selected="selected"' : ''}>${_(
              pref.values[val].name,
            )}</option>`,
        )
        .join('');

      return `
        <div class="preference_choice">
          <div class="row-data row-data-large">
            <div class="row-label">${_(pref.name)}</div>
            <div class="row-value">
              <select id="preference_control_${
                pref.id
              }" class="preference_control game_local_preference_control" style="display: block;">
                ${values}
              </select>
            </div>
          </div>
        </div>
      `;
    },

    onPreferenceChange(pref, newValue) {},

    /************************
     ******* SETTINGS ********
     ************************/
    setupSettings() {
      dojo.connect($('show-settings'), 'onclick', () => this.toggleSettings());
      this.addTooltip('show-settings', '', _('Display some settings about the game.'));
      let container = $('settings-controls-container');

      this.settings = {};
      Object.keys(this._settingsConfig).forEach((settingName) => {
        let config = this._settingsConfig[settingName];
        if (config.type == 'pref') {
          // Pref type => just move the user pref around
          dojo.place($('preference_control_' + config.prefId).parentNode.parentNode, container);
          return;
        }

        let suffix = settingName.charAt(0).toUpperCase() + settingName.slice(1);
        let value = this.getConfig(this.game_name + suffix, config.default);
        this.settings[settingName] = value;

        // Slider type => create DOM and initialize noUiSlider
        if (config.type == 'slider') {
          this.place('tplSettingSlider', { desc: config.name, id: settingName }, container);
          config.sliderConfig.start = [value];
          noUiSlider.create($('setting-' + settingName), config.sliderConfig);
          $('setting-' + settingName).noUiSlider.on('slide', (arg) =>
            this.changeSetting(settingName, parseInt(arg[0])),
          );
        }
        // Select type => create a select
        else if (config.type == 'select') {
          config.id = settingName;
          this.place('tplSettingSelect', config, container);
          $('setting-' + settingName).addEventListener('change', () => {
            let newValue = $('setting-' + settingName).value;
            this.changeSetting(settingName, newValue);
            if (config.attribute) {
              $('ebd-body').setAttribute('data-' + config.attribute, newValue);
            }
          });
        }

        if (config.attribute) {
          $('ebd-body').setAttribute('data-' + config.attribute, value);
        }
        this.changeSetting(settingName, value);
      });
    },

    changeSetting(settingName, value) {
      let suffix = settingName.charAt(0).toUpperCase() + settingName.slice(1);
      this.settings[settingName] = value;
      localStorage.setItem(this.game_name + suffix, value);
      let methodName = 'onChange' + suffix + 'Setting';
      if (this[methodName]) {
        this[methodName](value);
      }
    },

    tplSettingSlider(setting) {
      return `
      <div class='row-data row-data-large'>
        <div class='row-label'>${setting.desc}</div>
        <div class='row-value'>
          <div id="setting-${setting.id}"></div>
        </div>
      </div>
      `;
    },

    tplSettingSelect(setting) {
      let values = Object.keys(setting.values)
        .map(
          (val) =>
            `<option value='${val}' ${this.settings[setting.id] == val ? 'selected="selected"' : ''}>${_(
              setting.values[val],
            )}</option>`,
        )
        .join('');

      return `
        <div class="preference_choice">
          <div class="row-data row-data-large">
            <div class="row-label">${_(setting.name)}</div>
            <div class="row-value">
              <select id="setting-${
                setting.id
              }" class="preference_control game_local_preference_control" style="display: block;">
                ${values}
              </select>
            </div>
          </div>
        </div>
      `;
    },

    updatePlayerOrdering() {
      this.inherited(arguments);
      dojo.place('player_board_config', 'player_boards', 'first');
    },

    toggleSettings() {
      dojo.toggleClass('settings-controls-container', 'settingsControlsHidden');

      // Hacking BGA framework
      if (dojo.hasClass('ebd-body', 'mobile_version')) {
        dojo.query('.player-board').forEach((elt) => {
          if (elt.style.height != 'auto') {
            dojo.style(elt, 'min-height', elt.style.height);
            elt.style.height = 'auto';
          }
        });
      }
    },

    /* Helper to work with local storage */
    getConfig(value, v) {
      return localStorage.getItem(value) == null || isNaN(localStorage.getItem(value))
        ? v
        : localStorage.getItem(value);
    },

    /*
     * slideTemporary: a wrapper of slideTemporaryObject using Promise
     */
    slideTemporary(template, data, container, sourceId, targetId, duration, delay) {
      return new Promise((resolve, reject) => {
        var animation = this.slideTemporaryObject(
          this.format_block(template, data),
          container,
          sourceId,
          targetId,
          duration,
          delay,
        );
        setTimeout(() => {
          resolve();
        }, duration + delay);
      });
    },

    forEachPlayer(callback) {
      this.getPlayersWithHiddenRoles().forEach(callback);
    },

    forEachPlayerWithCharacter(callback) {
      const playersWithCharacters = this.getPlayersWithHiddenRoles().filter((player) => {
        return player.character !== null;
      });
      playersWithCharacters.forEach(callback);
    },

    getPlayersWithHiddenRoles() {
      var players = Object.values(this.gamedatas.players);
      players.forEach((player) => {
        if (player.role === null) player.role = 'hidden';
      });
      return players;
    },

    /*
     * Return a span with a colored 'You'
     */
    coloredYou() {
      var color = this.gamedatas.players[this.player_id].color;
      var color_bg = '';
      if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
        color_bg = 'background-color:#' + this.gamedatas.players[this.player_id].color_back + ';';
      }
      var you =
        '<span style="font-weight:bold;color:#' +
        color +
        ';' +
        color_bg +
        '">' +
        __('lang_mainsite', 'You') +
        '</span>';
      return you;
    },

    coloredPlayerName(name) {
      const player = Object.values(this.gamedatas.players).find((player) => player.name == name);
      if (player == undefined) return '<!--PNS--><span class="playername">' + name + '</span><!--PNE-->';

      const color = player.color;
      const color_bg = player.color_back
        ? 'background-color:#' + this.gamedatas.players[this.player_id].color_back + ';'
        : '';
      return (
        '<!--PNS--><span class="playername" style="color:#' + color + ';' + color_bg + '">' + name + '</span><!--PNE-->'
      );
    },

    /*
     * Overwrite to allow to more player coloration than player_name and player_name2
     */
    format_string_recursive(log, args) {
      try {
        if (log && args) {
          if (args.msgYou && args.player_id == this.player_id) log = args.msgYou;

          let player_keys = Object.keys(args).filter((key) => key.substr(0, 11) == 'player_name');
          player_keys.forEach((key) => {
            args[key] = this.coloredPlayerName(args[key]);
          });

          args.You = this.coloredYou();

          if (args.card_name && args.card) {
            args.card_name =
              _(args.card_name) +
              ' (' +
              args.card.value +
              '<span class="card-copy-color" data-color="' +
              args.card.color +
              '"></span>)';
          }
        }
      } catch (e) {
        console.error(log, args, 'Exception thrown', e.stack);
      }

      return this.inherited(arguments);
    },

    place(tplMethodName, object, container) {
      if ($(container) == null) {
        console.error('Trying to place on null container', container);
        return;
      }

      if (this[tplMethodName] == undefined) {
        console.error('Trying to create a non-existing template', tplMethodName);
        return;
      }

      return dojo.place(this[tplMethodName](object), container);
    },

    destroyDialog() {
      this._dial.destroy();
      this._dial = null;
    },
  });
});
