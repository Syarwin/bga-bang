/*
 * Informational popping dialog
 */

define(['dojo', 'dojo/_base/declare', 'dojo/fx', 'dojox/fx/ext-dojo/complex'], function (dojo, declare) {
  return declare('bang.informationdialog', null, {
    showInformationDialog(title, id, paragraphArray, params = {}) {
      const buttonId = 'i_agree_button';
      let dialog = this.addDialog(id, 'popindialog', 10);
      dialog.create('bang-information-dialog');
      dialog.setTitle(title);
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

      dialog.setContent(html);
      dialog.hideCloseIcon();
      this.showDialog();
      return buttonId;
    },

    showGhostTownDisclaimer() {
      return this.showInformationDialog(_('Welcome to BANG!'), 'ghostTown', [
        _('Please be aware that you are starting a game with a Ghost Town and/or a Dead Man card. These are special event cards making some or all eliminated players return to the game after elimination.'),
        '',
        _('What Ghost Town/Dead Man cards mean to you?'),
        _("Until one or both events are played some players won't be fully eliminated from the game even if they are out of life points. You might be forced to stay at the table until Ghost Town/Dead Man effects are played. If you decide to leave the game before that - you will be penalised. If you do not agree with this - please propose to end this game now."),
        _('Hope you will have a great time!'),
      ], {
        startb: '<b>',
        endb: '</b>',
        buttonText: _('I agree to play with the Ghost Town and/or Dead Man card'),
      });
    },
  })
})

