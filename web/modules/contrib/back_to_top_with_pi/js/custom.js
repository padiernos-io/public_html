(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.backtotop_scrollbar = {
        attach: function (context, settings) {
            $(once('backtotop_scrollbar', 'body')).each(function (element) {
                (function ($) {
                    "use strict";
                    $(document).ready(function () {
                        "use strict";
                        $('.bttwpi-progressbar-container').each(function () {
                            var progressBarWrap = $(this);
                            var progressBarWrapId = progressBarWrap.attr('id');
                            var scrollType = progressBarWrap.data('scroll-type');
                            $('head').append('<style type="text/css">' + progressBarWrap.data('attr') + '</style>');
                            progressBarWrap.removeAttr('data-attr');
                            var progressBarPath = document.querySelector('#' + progressBarWrapId + ' path');
                            var pathLength = progressBarPath.getTotalLength();
                            progressBarPath.style.transition = progressBarPath.style.WebkitTransition = 'none';
                            progressBarPath.style.strokeDasharray = pathLength + ' ' + pathLength;
                            progressBarPath.style.strokeDashoffset = pathLength;
                            progressBarPath.getBoundingClientRect();
                            progressBarPath.style.transition = progressBarPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';
                            var updateProgressBar = function () {
                                var scroll = $(window).scrollTop();
                                var height = $(document).height() - $(window).height();
                                var progress = pathLength - (scroll * pathLength / height);
                                progressBarPath.style.strokeDashoffset = progress;
                            }
                            updateProgressBar();
                            $(window).scroll(updateProgressBar);
                            var offset = 500;
                            var duration = 850;
                            jQuery(window).on('scroll', function () {
                                if (jQuery(this).scrollTop() > offset) {
                                    progressBarWrap.addClass('active-progress');
                                } else {
                                    progressBarWrap.removeClass('active-progress');
                                }
                            });
                            progressBarWrap.on('click', function (event) {
                                event.preventDefault();
                                jQuery('html, body').animate({
                                    scrollTop: 0
                                }, duration);
                                return FALSE;
                            });
                            if (scrollType == 'percentage') {
                                $(window).on('scroll', function () {
                                    var doc_height = $(document).height(),
                                    win_scrolltop = $(window).scrollTop(),
                                    win_height = $(window).height(),
                                    percent = win_scrolltop / (doc_height - win_height) * 100,
                                    percent = Math.round(percent);
                                    progressBarWrap.attr('data-before', percent + '%');
                                });
                            }
                        });
                    });
                })(jQuery);
            });
        }
    }
}(jQuery, Drupal, drupalSettings));
