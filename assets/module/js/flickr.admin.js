/**
 * @file custom_module.js
 * Custom Module main JavaScript file.
 *
 * You will need to get rid of the comments.
 * For how to add this file to your module, see https://drupal.org/node/304255
 */

// The first and last lines are a closure that allow us to use the `$()`
// shortcut, instead of the longer `jQuery()` function.
(function ($) {

    // Implementing a Drupal JavaScript behavior allow us to run code when the
    // page is ready and every time elements are added via the Drupal AJAX API.
    Drupal.behaviors.flickr = {
        // The `context` and `settings` arguments are specific to when and where
        // this is being executed.
        attach: function (context, settings) {
            $("#admin-photoset-gallery").justifiedGallery({
                "rowHeight": 150,
            });
            $("#photo-to-added-gallery").lightGallery({
                thumbnail: true,
                selector: "a"
            }).justifiedGallery({
                "rowHeight": 150,
            });

        }
    };
})(jQuery);
