(function ($) {
  'use strict';

  Drupal.behaviors.example_share = {
    attach: function (context, settings) {
      $('a.share__link:not(.share__link--email)', context).once('example_share').click(function () {
        window.open($(this).attr('href'), 'title', 'width=670, height=300');
        return false;
      });
    }
  };

}(jQuery));
