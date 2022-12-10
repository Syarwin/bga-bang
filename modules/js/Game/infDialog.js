/*
 * Informational popping dialog
 */

define(['dojo', 'dojo/_base/declare', 'dojo/fx', 'dojox/fx/ext-dojo/complex'], function (dojo, declare) {
  return declare('bang.informationdialog', null, {
    showInformationDialog(title, paragraphArray, params = {}) {
      const buttonId = 'i_agree_button';
      this._dial = new ebg.popindialog();
      this._dial.create('bang-information-dialog');
      this._dial.setTitle(title);
      let html = `<div class="popin_content">`;

      let nextIsHeader = false;
      for (const p of paragraphArray) {
        if (nextIsHeader) {
          html += '<h2>' + dojo.string.substitute(p, params) + '</h2>'
        } else if (p.length > 0) {
          html += '<p>' + dojo.string.substitute(p, params) + '</p>'
        }
        nextIsHeader = p.length === 0;
      }
      if (params.buttonText) {
        html += `<a href="#" id=${buttonId} class="bgabutton bgabutton_blue"><span>${params.buttonText}</span></a>`
      }
      html += '</div>';

      this._dial.setContent(html);
      this._dial.show();
      this._dial.hideCloseIcon();
      return buttonId;
    },

    showGhostTownDisclaimer() {
      return this.showInformationDialog(_('Welcome to BANG!'), [
        _('Please be aware that you are starting a game with a Ghost Town card. This is a special event card making eliminated players return to the game as ghosts for one turn.'),
        '',
        _('What Ghost Town means to you?'),
        _("Until this event is played no player can be fully eliminated from the game even if they are out of life points. You will be forced to stay at the table until Ghost Town effect is played. If you decide to leave the game before that - you will be penalised. If you do not agree with this - please propose to end this game now."),
        _('Hope you will have a great time!'),
      ], {
        startb: '<b>',
        endb: '</b>',
        buttonText: _('I agree to play with the Ghost Town card'),
      });
    },
  })
})

