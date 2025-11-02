(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.responsive_gallery = {
        attach: function (context, settings) {
            if($(".rg-grid").length) {
                $(".rg-grid").imagesLoaded(function () {
                    $(".rg-grid").masonry({
                        itemSelector: ".rg-grid-item"
                    });
                });
            }
        }
    }
}(jQuery, Drupal, drupalSettings));
