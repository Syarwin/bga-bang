/*
 * Class to manage all dialog windows appearing and showing them in order.
 * If there's just one dialog - it will be shown on showDialog(). Otherwise - the top priority one is appearing
 */
define(['dojo', 'dojo/_base/declare'], function (dojo, declare) {
  return declare('bang.dialogManager', null, {
    addDialog(id, type, priority, params = {}) {
      if (type === 'modal') {
        this._dial[id] = {
          dialog: new customgame.modal(id, params),
        };
      } else if (type === 'popindialog') {
        this._dial[id] = {
          dialog: new ebg.popindialog(),
        };
      } else {
        console.error('Unknown dialog type: ', type);
        return null;
      }
      this._dial[id]['priority'] = priority;
      return this._dial[id].dialog;
    },

    // Showing a dialog which is highest in priority
    showDialog() {
      const minPriority = Math.min(...Object.values(this._dial).map((modal) => {
        return modal.priority
      }));

      Object.values(this._dial).find((modal) => {
        return modal.priority === minPriority;
      }).dialog.show();
    },

    addAndShowDialog(id, type, priority, params = {}) {
      this.addDialog(id, type, priority, params);
      this.showDialog();
    },

    removeDialog(id) {
      if (this._dial[id]) {
        this._dial[id].dialog.destroy();
        delete this._dial[id];

        if (Object.keys(this._dial).length > 0) {
          this.showDialog();
        }
      }
    },
  })
})

