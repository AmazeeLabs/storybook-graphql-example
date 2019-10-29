(function ($, window, Drupal) {
  $('.items-view-mode--grid.items-view-mode--masonry .teasers').on('macy.start', function () {
    var macy = Macy({
      container: '.items-view-mode--grid.items-view-mode--masonry .teasers',
      margin: 24,
      columns: 3,
      waitForImages: true,
    });

    macy.runOnImageLoad(function () {
      macy.recalculate(true, true);
    });
  });

  Drupal.behaviors.masonryLayout = {
    attach: function attach(context, settings) {

      // Check if Masonry is needed in the page.
      if ($('.items-view-mode--grid.items-view-mode--masonry .teasers').length) {
        $('.items-view-mode--grid.items-view-mode--masonry .teasers').trigger('macy.start');
      }

    }
  };
})(jQuery, window, Drupal);
