define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.eventTrait', null, {
    constructor() {
      this._notifications.push(['newEvent', 1]);
    },

    notif_newEvent(n)
    {
      //add nextEvent card
      dojo.style(dojo.query('#eventNext .bang-card'), 'zIndex', 1);
      this.addEventCard(n.args.eventNext, 'eventNext')

      this.slideEventCard(n.args.eventActive);
      this.updateEventCount(n.args.eventsDeck);
    },

    slideEventCard(card) {
      var tempCard = this.getCard(card, true);
      tempCard.uid = tempCard.id + 'discard';
      tempCard.extraClass = 'slide';

      this.slideTemporary('jstpl_eventCard', tempCard, 'board', 'eventNext', 'eventActive', 700, 0).then(() => {
        var div = this.addEventCard(card, 'eventActive');
        dojo.style(div, 'zIndex', dojo.query('#eventActive .bang-card').length);
        dojo.style(div, 'transformStyle', "initial");
      });
    },

    addEventCard(ocard, container, suffix = '') {
      var card = this.getCard(ocard);
      card.uid = card.id + suffix;
      if ($('bang-card-' + card.uid)) dojo.destroy('bang-card-' + card.uid);

      var div = dojo.place(this.format_block('jstpl_eventCard', card), container);
      if (card.flipped === '' || card.enforceTooltip)
        this.addTooltipHtml(div.id, this.format_block('jstpl_eventCardTooltip', card));
      return div;
    },

    updateEventCount(count) {
      $('eventsDeck').innerHTML = count;
    }

  });
});
