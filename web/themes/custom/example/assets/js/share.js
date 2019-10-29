(function ($) {
  'use strict';

  Drupal.behaviors.sonova_share = {
    attach: function (context, settings) {
      $('a.share__link:not(.share__link--email)', context).once('sonova_share').click(function () {
        window.open($(this).attr('href'), 'title', 'width=670, height=300');
        return false;
      });
    }
  };

}(jQuery));
