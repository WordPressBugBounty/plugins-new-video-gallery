(function($) {
    'use strict';

    /**
     * LightGallery Initializer for Video Gallery Premium
     */
    function initVideoGalleries(forceReinit) {
        // Target all possible gallery containers
        $('.all-images, [class*="youram-simple_"]').each(function() {
            var $container = $(this);
            if ($container.data('lightGallery')) {
                if (forceReinit && !$('body').hasClass('lg-on')) {
                    try {
                        $container.data('lightGallery').destroy(true);
                    } catch(err) {
                    }
                    $container.removeData('lightGallery');
                    $container.off('.lg');
                    $container.find('.vgp-trigger').off('.lg .lgcustom');
                } else {
                    return; // Already initialized
                }
            }

            // For triggers with data-html (custom iframes), remove href so lightGallery
            // uses the data-html path in isVideo() instead of trying to parse href as a URL.
            // Our e.preventDefault() handler on .vgp-trigger prevents any page reload.
            $container.find('.vgp-trigger').each(function() {
                var $trigger = $(this);
                var href = $trigger.attr('href');
                var dataHtml = $trigger.attr('data-html');
                if (dataHtml) {
                    // Remove href so lightGallery sees no src and falls through to data-html
                    $trigger.removeAttr('href');
                } else if ((!href || href === '#' || href === '') && !$trigger.attr('data-src')) {
                    $trigger.attr('data-src', 'html5-video');
                }
            });

            var lgConfig = $container.data('lg-config') || {};
            var autoplayEnabled = lgConfig.autoplay === true;

            var options = {
                selector: '.vgp-trigger',
                loop: lgConfig.loop !== false,
                thumbnail: lgConfig.thumbnail !== false,
                loadYoutubeThumbnail: false,
                loadVimeoThumbnail: false,
                autoplayFirstVideo: autoplayEnabled,
                hash: false,
                download: false,
                share: false,
                fullScreen: true,
                autoplayControls: true,
                zoom: false,
                actualSize: false,
                rotate: false,
                flipHorizontal: false,
                flipVertical: false,
                mode: lgConfig.mode || 'lg-slide',
                speed: 600,
                closable: true,
                escKey: true,
                keyPress: true,
                controls: true,
                showAfterLoad: true,
                videojs: false,
                videoMaxWidth: '1140px',
                youtubePlayerParams: {
                    modestbranding: 1,
                    showinfo: 0,
                    rel: 0,
                    controls: 1,
                    vq: 'hd1080',
                    mute: autoplayEnabled ? 1 : 0
                },
                vimeoPlayerParams: {
                    byline: 0,
                    portrait: 0
                },
                toogleThumb: true,
                pullCaptionUp: true,
                thumbWidth: 100,
                thumbContHeight: 100,
                thumbMargin: 5
            };

            $container.lightGallery(options);

            $container.on('onAfterOpen.lg', function() {
                window.activeLgConfig = lgConfig;
            });


        });
    }

    // Inject premium entry animation styles for Load More items
    if (!$('#vg-item-entry-styles').length) {
        $('<style id="vg-item-entry-styles">' +
            '@keyframes vgItemAppear {' +
            '  from {' +
            '    opacity: 0;' +
            '    transform: translateY(24px) scale(0.96);' +
            '  }' +
            '  to {' +
            '    opacity: 1;' +
            '    transform: translateY(0) scale(1);' +
            '  }' +
            '}' +
            '.vg-item-new {' +
            '  opacity: 0 !important;' +
            '  animation: vgItemAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;' +
            '}' +
          '</style>').appendTo('head');
    }

    var firstLoadDone = false;

    function setupMutationObserver() {
        if (typeof MutationObserver === 'undefined') return;

        var observer = new MutationObserver(function(mutations) {
            var needsReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        var node = mutation.addedNodes[i];
                        if (node.nodeType === 1) { // Element node
                            var $node = $(node);
                            if ($node.hasClass('vg-col') || $node.hasClass('single-image') || $node.find('.vgp-trigger').length > 0) {
                                needsReinit = true;
                                break;
                            }
                        }
                    }
                }
            });

            if (needsReinit) {
                // Apply staggered animation to newly appended items
                if (firstLoadDone) {
                    var $newItems = $('.vg-col, .single-image').not('.vg-item-animated, .vg-item-new');
                    if ($newItems.length > 0) {
                        $newItems.each(function(index) {
                            var $item = $(this);
                            $item.css({
                                'animation-delay': (index * 50) + 'ms',
                                'opacity': '0'
                            });
                            $item.addClass('vg-item-new');
                            $item.one('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function() {
                                $item.removeClass('vg-item-new').addClass('vg-item-animated').css({
                                    'animation-delay': '',
                                    'opacity': ''
                                });
                            });
                        });
                    }
                }
                
                initVideoGalleries(true);
                // Delayed safety re-check
                setTimeout(function() {
                    initVideoGalleries(true);
                }, 150);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    $(document).ready(function() {
        // Mark existing items as already animated to prevent initial load animation
        $('.vg-col, .single-image').addClass('vg-item-animated');
        firstLoadDone = true;
        initVideoGalleries();
        setupMutationObserver();
    });

    $(document).on('click', '.vgp-trigger', function() {
        if (typeof vg_analytics_ajax === 'undefined') {
            return;
        }

        var $trigger = $(this);
        var $gallery = $trigger.closest('.vg-row');
        if (!$gallery.length) {
            $gallery = $trigger.closest('[id*="nev_vimeo_api_"]');
        }
        if (!$gallery.length) {
            $gallery = $trigger.closest('[class*="youram-simple_"]');
        }

        var galleryId = '';
        if ($gallery.length) {
            var idAttr = $gallery.attr('id');
            if (idAttr) {
                var match = idAttr.match(/\d+/);
                if (match) {
                    galleryId = match[0];
                }
            } else {
                var classAttr = $gallery.attr('class');
                if (classAttr) {
                    var matchClass = classAttr.match(/youram-simple_(\d+)/);
                    if (matchClass) {
                        galleryId = matchClass[1];
                    }
                }
            }
        }

        var title = '';
        if ($trigger.closest('.vg-card').length) {
            var $titleEl = $trigger.closest('.vg-card').find('.vg-card__title, .vg-item-title');
            if ($titleEl.length) {
                title = $titleEl.first().text();
            }
        }

        if (!title) {
            title = $trigger.find('img').attr('alt') || $trigger.attr('title') || 'Untitled Video';
        }

        var source = 'local';
        var $card = $trigger.closest('.vg-card');
        if ($card.hasClass('vg-card--youtube')) {
            source = 'youtube';
        } else if ($card.hasClass('vg-card--vimeo')) {
            source = 'vimeo';
        } else if ($card.hasClass('vg-card--twitch')) {
            source = 'twitch';
        } else if ($card.hasClass('vg-card--dailymotion')) {
            source = 'dailymotion';
        } else if ($card.hasClass('vg-card--wistia')) {
            source = 'wistia';
        } else if ($card.hasClass('vg-card--local')) {
            source = 'local';
        } else if ($card.hasClass('vg-card--tiktok')) {
            source = 'tiktok';
        } else if ($card.hasClass('vg-card--reels')) {
            source = 'reels';
        } else {
            if ($gallery.attr('id') && $gallery.attr('id').indexOf('dm') !== -1) {
                source = 'dailymotion';
            } else if ($gallery.attr('id') && $gallery.attr('id').indexOf('wis') !== -1) {
                source = 'wistia';
            } else if ($gallery.attr('id') && $gallery.attr('id').indexOf('vimeo') !== -1) {
                source = 'vimeo';
            } else if ($gallery.attr('id') && $gallery.attr('id').indexOf('twitch') !== -1) {
                source = 'twitch';
            } else if ($gallery.hasClass('youram-simple_' + galleryId) || ($gallery.attr('id') && $gallery.attr('id').indexOf('yram') !== -1)) {
                source = 'youtube';
            }
        }

        if (galleryId && title) {
            $.ajax({
                url: vg_analytics_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'vg_track_video_play',
                    security: vg_analytics_ajax.nonce,
                    gallery_id: galleryId,
                    video_title: title.trim(),
                    video_source: source
                }
            });
        }
    });

    // Live Search input filter
    $(document).on('input', '.vg-search-input', function() {
        var $input = $(this);
        var query = $input.val().toLowerCase().trim();
        var $galleryWrapper = $input.closest('.vg-search-wrapper').next('.vg-row');
        if (!$galleryWrapper.length) {
            $galleryWrapper = $input.closest('.vg-search-wrapper').siblings('.vg-row');
        }

        if (typeof $.fn.isotope !== 'undefined' && $galleryWrapper.data('isotope')) {
            $galleryWrapper.isotope({
                filter: function() {
                    var $item = $(this);
                    var textToSearch = '';

                    var $title = $item.find('.vg-card__title, .vg-item-title, .image-gallery-caption-title');
                    if ($title.length) textToSearch += ' ' + $title.text();

                    var $desc = $item.find('.vg-card__desc, .vg-item-desc, .image-gallery-caption-text');
                    if ($desc.length) textToSearch += ' ' + $desc.text();

                    var $img = $item.find('img');
                    if ($img.length) textToSearch += ' ' + ($img.attr('alt') || '');

                    textToSearch = textToSearch.toLowerCase();
                    return query === '' || textToSearch.indexOf(query) !== -1;
                }
            });
        } else {
            $galleryWrapper.find('.vg-col, .single-image').each(function() {
                var $item = $(this);
                var textToSearch = '';

                var $title = $item.find('.vg-card__title, .vg-item-title, .image-gallery-caption-title');
                if ($title.length) textToSearch += ' ' + $title.text();

                var $desc = $item.find('.vg-card__desc, .vg-item-desc, .image-gallery-caption-text');
                if ($desc.length) textToSearch += ' ' + $desc.text();

                var $img = $item.find('img');
                if ($img.length) textToSearch += ' ' + ($img.attr('alt') || '');

                textToSearch = textToSearch.toLowerCase();

                if (query === '' || textToSearch.indexOf(query) !== -1) {
                    $item.fadeIn(300);
                } else {
                    $item.fadeOut(300);
                }
            });
        }
    });

    // Equal Heights logic for Fixed Grid layout
    function vgEqualizeFixedGridHeights() {
        $('.vg-layout-fixed').each(function() {
            var $gallery = $(this);
            var $cards = $gallery.find('.vg-card');

            // Reset heights to measure them naturally first
            $cards.css('height', 'auto');

            var maxHeight = 0;
            $cards.each(function() {
                var h = $(this).outerHeight();
                if (h > maxHeight) {
                    maxHeight = h;
                }
            });

            if (maxHeight > 0) {
                $cards.css('height', maxHeight + 'px');
            }

            // Recalculate Isotope layout if it's initialized on this grid
            if (typeof $.fn.isotope !== 'undefined' && $gallery.data('isotope')) {
                $gallery.isotope('layout');
            }
        });
    }

    $(document).ready(function() {
        // Initial run
        vgEqualizeFixedGridHeights();

        // Run after delays to ensure lazy-loaded or cached images are rendered
        setTimeout(vgEqualizeFixedGridHeights, 150);
        setTimeout(vgEqualizeFixedGridHeights, 400);
        setTimeout(vgEqualizeFixedGridHeights, 800);
        setTimeout(vgEqualizeFixedGridHeights, 1500);

        // Bind image load events
        $('.vg-layout-fixed img').on('load', function() {
            vgEqualizeFixedGridHeights();
        });
    });

    $(window).on('load resize', function() {
        vgEqualizeFixedGridHeights();
    });



    $(document).ajaxComplete(function() {
        setTimeout(vgEqualizeFixedGridHeights, 150);
        setTimeout(vgEqualizeFixedGridHeights, 500);
    });

    // Prevent default action on all vgp-trigger links since they only open lightbox overlays
    $(document).on('click', '.vgp-trigger', function(e) {
        e.preventDefault();
    });

})(jQuery);
