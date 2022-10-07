define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('bang.eventTrait', null, {
    constructor() {
      this._notifications.push(['newEvent', 1]);
    },

    notif_newEvent(n)
    {
      var eventActiveCard = n.args.eventActive;
      this.gamedatas.eventActive = eventActiveCard;

      this.updateColorOverride(eventActiveCard.colorOverride);
      if (n.args.eventNext) {
        this.addEventCard(n.args.eventNext, 'eventNext')
      }
      this.slideEventCard(eventActiveCard);
      this.updateEventCount(n.args.eventsDeck);
    },

    slideEventCard(card) {
      var tempCard = this.getCard(card);
      tempCard.uid = tempCard.id;
      tempCard.extraClass = 'slide';

      dojo.destroy('bang-card-' + card.id +'event');
      this.slideTemporary('jstpl_eventCard', tempCard, 'board', 'eventNext', 'eventActive', 700, 0).then(() => {
        dojo.query('#eventActive .bang-card').forEach((card) => dojo.destroy(card));
        this.addEventCard(card, 'eventActive');
      });
    },

    addEventCard(ocard, container, suffix = 'event') {
      var card = this.getCard(ocard);
      card.uid = card.id + suffix;
      if ($('bang-card-' + card.uid)) dojo.destroy('bang-card-' + card.uid);

      var div = dojo.place(this.format_block('jstpl_eventCard', card), container);
      this.addTooltipHtml(div.id, this.format_block('jstpl_eventCardTooltip', card));
    },

    updateEventCount(count) {
      $('eventsDeck').innerHTML = count || '';
    },

    updateColorOverride(col) {
      dojo.query('.card-copy-color,.card-copy-color-override').forEach((n) => n.setAttribute('data-color-override', col || ''));
    }

  });
});
