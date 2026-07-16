/**
 * Video Gallery Admin Settings JS
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Initialize wpColorPicker
        if ($.fn.wpColorPicker) {
            $('#title_color, #desc_color, #bgc, #load_button_color, #load_text_color, #load_button_hover, #gallery_loader_color, #thumb_border_color, #thumb_card_bg, #vg_icon_badge_custom_color, #api_vg_icon_badge_custom_color').wpColorPicker();
        }

        // Tab switching logic
        $('.awl-vg-tabs-nav .nav-item').on('click', function (e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('.awl-vg-tabs-nav .nav-item').removeClass('active');
            $(this).addClass('active');
            $('.awl-vg-tab-content').removeClass('active');
            $('#' + target).addClass('active');
        });

        // Range slider display logic
        var rangeSlider = function () {
            var slider = $('.range-slider'),
                range = $('.range-slider__range'),
                value = $('.range-slider__value');

            slider.each(function () {
                value.each(function () {
                    var val = $(this).prev().attr('value');
                    $(this).html(val);
                });
                range.on('input', function () {
                    $(this).next(value).html(this.value);
                });
            });
        };
        rangeSlider();

        // Range slider display logic for premium controls
        $('.ig-range').on('input', function () {
            $(this).next('.ig-range-value').find('span').text($(this).val());
        });


        // Sort slides logic (optional helper)
        window.SortSlides = function (order) {
            var $list = $('#remove-slides');
            var $listItems = $list.children('li');
            $listItems.sort(function (a, b) {
                var aTitle = $(a).find('input[name="slide-title[]"]').val().toLowerCase();
                var bTitle = $(b).find('input[name="slide-title[]"]').val().toLowerCase();
                if (order === 'ASC') {
                    return aTitle.localeCompare(bTitle);
                } else {
                    return bTitle.localeCompare(aTitle);
                }
            });
            $list.empty().append($listItems);
            return false;
        };

        // Conditional visibility toggling based on selected gallery option
        function toggleGalleryOptions(optionVal) {
            var $navItem = $('.awl-vg-tabs-nav [data-target="tab-add-videos"]');
            var iconHTML = '<span class="dashicons dashicons-format-video"></span> ';
            $('#meta-gallery').hide();

            // Hide all platform-specific resolution settings by default
            $('.YouTube-yes-config-setting, .Vimeo-yes-config-setting, .Twitch-yes-config-setting, .Wistia-yes-config-setting').hide();

            if (optionVal === 'no_api') {
                // Show local video settings container, hide APIs
                $('#slider-gallery').show();
                $('#youtube-gallery').hide();
                $('#vimeo-gallery').hide();
                $('#twitch-gallery').hide();
                $('#dailymotion-gallery').hide();
                $('#wistia-gallery').hide();
                $('#tiktok-gallery').hide();

                // Show/hide sub-sections
                $('.api-yes-config-setting').not('.awl-vg-tab-content').hide();
                $('.api-both-config-setting').not('.awl-vg-tab-content').hide();
                $('.no-api-config-setting').show(); // Show nav-items and other card elements

                $navItem.html(iconHTML + 'Add Videos');


            } else {
                if (optionVal === 'video_yoyube_api') {
                    // Show YouTube, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').show();
                    $('#vimeo-gallery').hide();
                    $('#twitch-gallery').hide();
                    $('#dailymotion-gallery').hide();
                    $('#wistia-gallery').hide();
                    $('#tiktok-gallery').hide();

                    // Show/hide sub-sections
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.YouTube-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide(); // Hides nav-items and other card elements

                    // Reset default visibility for API sub-settings
                    $('.twitch-no-hd, .twitch-no-avatar, .twitch-no-likes, .twitch-no-comments').show();

                    $navItem.html(iconHTML + 'Add YouTube API');
                } else if (optionVal === 'video_vimeo_api') {
                    // Show Vimeo, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').hide();
                    $('#vimeo-gallery').show();
                    $('#twitch-gallery').hide();
                    $('#dailymotion-gallery').hide();
                    $('#wistia-gallery').hide();
                    $('#tiktok-gallery').hide();

                    // Show/hide sub-sections
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').hide();
                    $('.Vimeo-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide(); // Hides nav-items and other card elements

                    // Reset default visibility for API sub-settings
                    $('.twitch-no-hd, .twitch-no-avatar, .twitch-no-likes, .twitch-no-comments').show();

                    $navItem.html(iconHTML + 'Add Vimeo API');
                } else if (optionVal === 'video_twitch_api') {
                    // Show Twitch, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').hide();
                    $('#vimeo-gallery').hide();
                    $('#twitch-gallery').show();
                    $('#dailymotion-gallery').hide();
                    $('#wistia-gallery').hide();
                    $('#tiktok-gallery').hide();

                    // Show/hide sub-sections
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').hide();
                    $('.Twitch-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide(); // Hides nav-items and other card elements

                    // Twitch doesn't support likes, comments, or HD quality badges
                    $('.twitch-no-hd, .twitch-no-likes, .twitch-no-comments').hide();
                    $('.twitch-no-avatar').show();

                    $navItem.html(iconHTML + 'Add Twitch API');
                } else if (optionVal === 'video_dailymotion_api') {
                    // Show Dailymotion, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').hide();
                    $('#vimeo-gallery').hide();
                    $('#twitch-gallery').hide();
                    $('#dailymotion-gallery').show();
                    $('#wistia-gallery').hide();
                    $('#tiktok-gallery').hide();

                    // Show/hide sub-sections (Dailymotion uses YouTube extra typography controls and metadata tags)
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide();

                    // Dailymotion doesn't support comments, HD quality badges, or creator avatars
                    $('.twitch-no-comments, .twitch-no-hd, .twitch-no-avatar').hide();
                    $('.twitch-no-likes').show();

                    $navItem.html(iconHTML + 'Add Dailymotion API');
                } else if (optionVal === 'video_wistia_api') {
                    // Show Wistia, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').hide();
                    $('#vimeo-gallery').hide();
                    $('#twitch-gallery').hide();
                    $('#dailymotion-gallery').hide();
                    $('#wistia-gallery').show();
                    $('#tiktok-gallery').hide();

                    // Show/hide sub-sections (Wistia uses YouTube extra typography controls and metadata tags)
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.Wistia-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide();

                    // Wistia doesn't support likes, comments, or creator avatars, but supports HD badge
                    $('.twitch-no-likes, .twitch-no-comments, .twitch-no-avatar').hide();
                    $('.twitch-no-hd').show();

                    $navItem.html(iconHTML + 'Add Wistia API');
                } else if (optionVal === 'video_tiktok_api') {
                    // Show TikTok, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').hide();
                    $('#vimeo-gallery').hide();
                    $('#twitch-gallery').hide();
                    $('#dailymotion-gallery').hide();
                    $('#wistia-gallery').hide();
                    $('#tiktok-gallery').show();
                    $('#meta-gallery').hide();

                    // Show/hide sub-sections
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide();

                    // TikTok doesn't support comments or HD quality badges in standard feeds
                    $('.twitch-no-comments, .twitch-no-hd').hide();
                    $('.twitch-no-likes, .twitch-no-avatar').show();

                    $navItem.html(iconHTML + 'Add TikTok API');
                } else if (optionVal === 'video_meta_api') {
                    // Show Meta, hide others
                    $('#slider-gallery').hide();
                    $('#youtube-gallery').hide();
                    $('#vimeo-gallery').hide();
                    $('#twitch-gallery').hide();
                    $('#dailymotion-gallery').hide();
                    $('#wistia-gallery').hide();
                    $('#tiktok-gallery').hide();
                    $('#meta-gallery').show();

                    // Show/hide sub-sections
                    $('.api-yes-config-setting').not('.awl-vg-tab-content').show();
                    $('.api-both-config-setting').not('.awl-vg-tab-content').show();
                    $('.no-api-config-setting').hide();

                    // Meta does not support HD quality badges or creator avatars in standard feeds
                    $('.twitch-no-hd, .twitch-no-avatar').hide();
                    $('.twitch-no-likes, .twitch-no-comments').show();

                    $navItem.html(iconHTML + 'Add Meta API');
                }


            }

            // If the active tab was hidden, fallback to "tab-add-videos" (only on post edit screen)
            if ($('#tab-add-videos').length) {
                var activeTabId = $('.awl-vg-tabs-nav .nav-item.active').data('target');
                if ($('#' + activeTabId).is(':hidden') || $('[data-target="' + activeTabId + '"]').is(':hidden')) {
                    $('.awl-vg-tabs-nav .nav-item').removeClass('active');
                    $('[data-target="tab-add-videos"]').addClass('active');
                    $('.awl-vg-tab-content').removeClass('active');
                    $('#tab-add-videos').addClass('active');
                }
            }
        }

        // Trigger on change of gallery option radio
        $('input[name="video_gallery_option"]').change(function () {
            toggleGalleryOptions($(this).val());
            updateTitlePositionWrapper();
        });

        // Initialize state
        var initialOption = $('input[name="video_gallery_option"]:checked').val();
        toggleGalleryOptions(initialOption);

        // Sub-settings toggle triggers
        function updateTitlePositionWrapper() {
            var galleryOption = $('input[name="video_gallery_option"]:checked').val();
            if (galleryOption !== 'no_api') {
                $('#thumb_title_pos_wrapper').hide();
                $('#thumb_title_align_wrapper').hide();
                $('#thumb_title_hover_mode_wrapper').hide();
                return;
            }

            var showTitle = $('input[name="video_title"]:checked').val() === 'show';
            var showDesc = $('input[name="video_desc"]:checked').val() === 'show';
            var isHover = $('input[name="thumb_title_pos"]:checked').val() === 'hover';

            if (showTitle || showDesc) {
                $('#thumb_title_pos_wrapper').slideDown(200);
                $('#thumb_title_align_wrapper').slideDown(200);
                if (isHover) {
                    $('#thumb_title_hover_mode_wrapper').slideDown(200);
                } else {
                    $('#thumb_title_hover_mode_wrapper').slideUp(200);
                }
            } else {
                $('#thumb_title_pos_wrapper').slideUp(200);
                $('#thumb_title_align_wrapper').slideUp(200);
                $('#thumb_title_hover_mode_wrapper').slideUp(200);
            }
        }

        // Title font settings visibility
        $('input[name="video_title"]').change(function () {
            if ($(this).val() === 'show') {
                $('.title-font-setting-row').css('display', 'inline-flex').hide().fadeIn(200);
            } else {
                $('.title-font-setting-row').fadeOut(200);
            }
            updateTitlePositionWrapper();
        });

        // Description font settings visibility
        $('input[name="video_desc"]').change(function () {
            if ($(this).val() === 'show') {
                $('.desc-font-setting-row').css('display', 'inline-flex').hide().fadeIn(200);
            } else {
                $('.desc-font-setting-row').fadeOut(200);
            }
            updateTitlePositionWrapper();
        });


        // Title & Description Position dependency logic
        $('input[name="thumb_title_pos"]').change(function () {
            updateTitlePositionWrapper();
        });

        // Border dependency logic
        $('input[name="thumb_border"]').change(function () {
            if (parseInt($(this).val(), 10) === 1) {
                $('.ig-border-options').slideDown(200);
            } else {
                $('.ig-border-options').slideUp(200);
            }
        });

        // Grayscale dependency logic
        $('input[name="image_grayscale"]').change(function () {
            if (parseInt($(this).val(), 10) === 1) {
                $('.grayscale_pct_wrapper').css('display', 'inline-flex').hide().fadeIn(200);
            } else {
                $('.grayscale_pct_wrapper').fadeOut(200);
            }
        });

        // Initial dependency checks for new design settings
        var showTitle = $('input[name="video_title"]:checked').val() === 'show';
        var showDesc = $('input[name="video_desc"]:checked').val() === 'show';
        var isHover = $('input[name="thumb_title_pos"]:checked').val() === 'hover';
        var galleryOption = $('input[name="video_gallery_option"]:checked').val();

        if (galleryOption === 'no_api' && (showTitle || showDesc)) {
            $('#thumb_title_pos_wrapper').show();
            $('#thumb_title_align_wrapper').show();
            if (isHover) {
                $('#thumb_title_hover_mode_wrapper').show();
            } else {
                $('#thumb_title_hover_mode_wrapper').hide();
            }
        } else {
            $('#thumb_title_pos_wrapper').hide();
            $('#thumb_title_align_wrapper').hide();
            $('#thumb_title_hover_mode_wrapper').hide();
        }

        if (parseInt($('input[name="thumb_border"]:checked').val(), 10) !== 1) {
            $('.ig-border-options').hide();
        } else {
            $('.ig-border-options').show();
        }

        if (parseInt($('input[name="image_grayscale"]:checked').val(), 10) !== 1) {
            $('.grayscale_pct_wrapper').hide();
        } else {
            $('.grayscale_pct_wrapper').css('display', 'inline-flex');
        }

        // Trigger initial sub-setting checks
        if ($('input[name="video_title"]:checked').val() !== 'show') {
            $('.title-font-setting-row').hide();
        } else {
            $('.title-font-setting-row').css('display', 'inline-flex');
        }
        if ($('input[name="video_desc"]:checked').val() !== 'show') {
            $('.desc-font-setting-row').hide();
        } else {
            $('.desc-font-setting-row').css('display', 'inline-flex');
        }


        // Play Icon & Source Badge Color Options visibility & sync logic
        function updateVideoIconColorVisibility() {
            var iconVisible = $('input[name="video_icon"]:checked').val() === 'true' || $('input[name="api_video_icon"]:checked').val() === 'true';
            var colorMode = $('input[name="vg_icon_badge_color_mode"]:checked').val() || $('input[name="api_vg_icon_badge_color_mode"]:checked').val() || 'source';

            if (iconVisible) {
                $('#vg_icon_badge_color_mode_row, #api_vg_icon_badge_color_mode_row').slideDown(200);
                if (colorMode === 'custom') {
                    $('#vg_icon_badge_custom_color_row, #api_vg_icon_badge_custom_color_row').slideDown(200);
                } else {
                    $('#vg_icon_badge_custom_color_row, #api_vg_icon_badge_custom_color_row').slideUp(200);
                }
            } else {
                $('#vg_icon_badge_color_mode_row, #api_vg_icon_badge_color_mode_row').slideUp(200);
                $('#vg_icon_badge_custom_color_row, #api_vg_icon_badge_custom_color_row').slideUp(200);
            }
        }

        $('input[name="video_icon"]').on('change', function () {
            var val = $(this).val();
            $('input[name="api_video_icon"][value="' + val + '"]').prop('checked', true);
            updateVideoIconColorVisibility();
        });

        $('input[name="api_video_icon"]').on('change', function () {
            var val = $(this).val();
            $('input[name="video_icon"][value="' + val + '"]').prop('checked', true);
            updateVideoIconColorVisibility();
        });

        $('input[name="vg_icon_badge_color_mode"]').on('change', function () {
            var val = $(this).val();
            $('input[name="api_vg_icon_badge_color_mode"][value="' + val + '"]').prop('checked', true);
            updateVideoIconColorVisibility();
        });

        $('input[name="api_vg_icon_badge_color_mode"]').on('change', function () {
            var val = $(this).val();
            $('input[name="vg_icon_badge_color_mode"][value="' + val + '"]').prop('checked', true);
            updateVideoIconColorVisibility();
        });

        // Sync custom color pickers
        $('#vg_icon_badge_custom_color, #api_vg_icon_badge_custom_color').on('change', function () {
            var val = $(this).val();
            $('#vg_icon_badge_custom_color, #api_vg_icon_badge_custom_color').not(this).val(val).trigger('change.wpColorPicker');
        });

        // Sync hover display mode radio buttons
        $('input[name="thumb_icon_tag_display"]').on('change', function () {
            var val = $(this).val();
            $('input[name="api_thumb_icon_tag_display"][value="' + val + '"]').prop('checked', true);
        });

        $('input[name="api_thumb_icon_tag_display"]').on('change', function () {
            var val = $(this).val();
            $('input[name="thumb_icon_tag_display"][value="' + val + '"]').prop('checked', true);
        });

        // Initial check on load
        var initialVideoIcon = $('input[name="video_icon"]:checked').val() === 'true' || $('input[name="api_video_icon"]:checked').val() === 'true';
        var initialColorMode = $('input[name="vg_icon_badge_color_mode"]:checked').val() || $('input[name="api_vg_icon_badge_color_mode"]:checked').val() || 'source';
        if (initialVideoIcon) {
            $('#vg_icon_badge_color_mode_row, #api_vg_icon_badge_color_mode_row').show();
            if (initialColorMode === 'custom') {
                $('#vg_icon_badge_custom_color_row, #api_vg_icon_badge_custom_color_row').show();
            } else {
                $('#vg_icon_badge_custom_color_row, #api_vg_icon_badge_custom_color_row').hide();
            }
        } else {
            $('#vg_icon_badge_color_mode_row, #api_vg_icon_badge_color_mode_row').hide();
            $('#vg_icon_badge_custom_color_row, #api_vg_icon_badge_custom_color_row').hide();
        }

        // Twitch Clips Period display logic
        function toggleTwitchClipsPeriod() {
            var selectedSource = $('input[name="twitch_content_source"]:checked').val();
            if (selectedSource === 'clips') {
                $('.twitch-clips-period-row').show();
            } else {
                $('.twitch-clips-period-row').hide();
            }
        }
        $('input[name="twitch_content_source"]').change(function() {
            toggleTwitchClipsPeriod();
        });
        toggleTwitchClipsPeriod();

        // YouTube/Vimeo Content Source Sub-Toggles
        $('input[name="yt_content_source"]').change(function() {
            if ($(this).val() === 'playlist') {
                $('.yt-playlist-select-row').show();
            } else {
                $('.yt-playlist-select-row').hide();
            }
        });

        $('input[name="vimeo_content_source"]').change(function() {
            if ($(this).val() === 'album') {
                $('.vimeo-album-select-row').show();
            } else {
                $('.vimeo-album-select-row').hide();
            }
        });



        if ($('#vg-vimeo-fetch-status').text().indexOf('Connected') !== -1) {
            var accessToken = $('#vimeo_gallery_access_token').val();
            var username = $('#vimeo_gallery_username').val();
            if (accessToken && username) {
                var $select = $('#vimeo_selected_album_id');
                var currentVal = $select.val();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'nvgall_fetch_vimeo_content',
                        access_token: accessToken,
                        username: username
                    },
                    success: function(response) {
                        if (response.success && response.data.albums) {
                            $select.empty();
                            $.each(response.data.albums, function(i, album) {
                                $select.append('<option value="' + album.id + '">' + album.title + ' (' + album.count + ' videos)</option>');
                            });
                            if (currentVal) {
                                $select.val(currentVal);
                            }
                        }
                    }
                });
            }
        }

        // AJAX Fetch Content triggers
        $('#vg-fetch-youtube-content').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $status = $('#vg-youtube-fetch-status');
            var apiKey = $('#video_gallery_api_key').val();
            var channelLink = $('#video_gallery_channel_link').val();

            if (!apiKey || !channelLink) {
                $status.html('<span class="vg-fetch-status--error">Please enter both API Key and Channel Link.</span>');
                return;
            }

            $btn.addClass('vg-fetch-btn--loading').prop('disabled', true);
            $status.html('<span>🔄 Verifying credentials...</span>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'nvgall_fetch_youtube_content',
                    api_key: apiKey,
                    channel_link: channelLink
                },
                success: function(response) {
                    $btn.removeClass('vg-fetch-btn--loading').prop('disabled', false);
                    if (response.success) {
                        $status.html('<span class="vg-fetch-status--success">✅ Connected: YouTube channel verified.</span>');
                        $btn.hide();
                        $('#vg-disconnect-youtube').show();

                        // Reveal options (except playlist selection)
                        $('.yt-fetch-dependent-options').not('.yt-playlist-select-row').show();
                    } else {
                        $status.html('<span class="vg-fetch-status--error">Error: ' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    $btn.removeClass('vg-fetch-btn--loading').prop('disabled', false);
                    $status.html('<span class="vg-fetch-status--error">AJAX error occurred while verifying.</span>');
                }
            });
        });



        // Dynamic placeholder changer for slide type selection
        $(document).on('change', 'select[name="slide-type[]"]', function () {
            var val = $(this).val();
            var $input = $(this).closest('.slide').find('input[name="slide-link[]"]');
            if (val === 'y' || val === 'v' || val === 't' || val === 'd' || val === 'w' || val === 'tk') {
                $input.attr('placeholder', 'Video id or link');
            } else if (val === 'f') {
                $input.attr('placeholder', 'Video Link from media');
            } else if (val === 'image') {
                $input.attr('placeholder', 'Add link');
            }
        });

        // Enforce maximum 50 limit for API galleries on keyup/change
        $('#vg_limit').on('input change', function () {
            var val = parseInt($(this).val(), 10);
            var galleryOption = $('input[name="video_gallery_option"]:checked').val();
            if (galleryOption && galleryOption !== 'no_api') {
                if (val > 50) {
                    $(this).val(50);
                } else if (val < 1 || isNaN(val)) {
                    $(this).val(1);
                }
            }
        });



        revealSettingsPage();
    });

    $(document).ajaxComplete(function () {
        if ($.fn.wpColorPicker) {
            $('#title_color, #desc_color, #bgc, #load_button_color, #load_text_color, #load_button_hover, #gallery_loader_color, #vg_icon_badge_custom_color, #api_vg_icon_badge_custom_color').wpColorPicker();
        }
    });

    // Settings Page Loader Transition
    function revealSettingsPage() {
        $('.vg-settings-loader').fadeOut(250, function () {
            $(this).remove();
            $('.vg-settings-main-content').fadeIn(250);
        });
    }
    if (document.readyState === 'complete') {
        revealSettingsPage();
    } else {
        $(window).on('load', revealSettingsPage);
    }
})(jQuery);
