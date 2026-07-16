<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

global $vg_gallery_object;

// Load assets
wp_enqueue_script('jquery');
wp_enqueue_script('vg-lightgallery-js');
wp_enqueue_script('vg-lightbox-init');
wp_enqueue_style('vg-lightgallery-css');
wp_enqueue_style('vg-lg-transitions');
wp_enqueue_style('vg-api-frontend-css');

$result = $vg_gallery_object->fetch_youtube_data($video_gallery_api_key, $video_gallery_channel_link, $vg_limit, '', $post_id);

$api_title_char_limit = isset($gallery_settings['api_title_char_limit']) ? intval($gallery_settings['api_title_char_limit']) : 50;
$api_desc_char_limit = isset($gallery_settings['api_desc_char_limit']) ? intval($gallery_settings['api_desc_char_limit']) : 150;
$gallery_ad_frequency = isset($gallery_settings['gallery_ad_frequency']) ? intval($gallery_settings['gallery_ad_frequency']) : 0;
$gallery_ad_script = isset($gallery_settings['gallery_ad_script']) ? $gallery_settings['gallery_ad_script'] : '';
?>
<div id="your-page-column" class="not-a-part-of-youram-plugin">
    <div id="yram" class="vg-row youram-simple_<?php echo esc_attr($post_id); ?> <?php echo (isset($gallery_settings['gallery_layout_mode']) && $gallery_settings['gallery_layout_mode'] === 'fixed') ? 'vg-layout-fixed' : ''; ?>" style="--vg-cols-lg: <?php echo esc_attr($cols_lg); ?>; --vg-cols-md: <?php echo esc_attr($cols_md); ?>; --vg-cols-sm: <?php echo esc_attr($cols_sm); ?>; --vg-cols-xs: <?php echo esc_attr($cols_xs); ?>;" data-lg-config='<?php echo esc_attr(wp_json_encode(array(
        "loop" => ($show_lightbox_loop === 1),
        "thumbnail" => ($show_lightbox_thumbnails === 1),
        "autoplay" => ($auto_play === "true"),
        "mode" => esc_attr($lightbox_lightgallery_transition)
    ))); ?>'>
        <?php
        if (!is_wp_error($result) && !empty($result['videos'])) {
            ?>
            <div class="grid-sizer"></div>
            <?php
require_once __DIR__ . '/../includes/vg-card-render.php';
$global_settings = get_option('awl_vg_global_settings', array());
$api_settings = array(
    'platform'                => 'youtube',
    'post_id'                 => $post_id,
    'video_icon'              => $video_icon,
    'show_lightbox_title'     => $show_lightbox_title,
    'show_lightbox_desc'      => $show_lightbox_desc,
    'api_video_title'         => $api_video_title,
    'api_video_desc'          => $api_video_desc,
    'api_title_char_limit'    => $api_title_char_limit,
    'api_desc_char_limit'     => $api_desc_char_limit,
    'lazy_loading'            => isset($global_settings['lazy_loading']) ? $global_settings['lazy_loading'] : 'yes',
);
            $api_ad_count = 0;
            foreach ($result['videos'] as $video) {
                if ($gallery_ad_frequency > 0 && $api_ad_count > 0 && $api_ad_count % $gallery_ad_frequency === 0 && !empty($gallery_ad_script)) {
                    echo '<div class="vg-col vg-ad-container">' . $gallery_ad_script . '</div>';
                }
                vg_render_api_card($video, $api_settings);
                $api_ad_count++;
            }
        } elseif (is_wp_error($result)) {
            if (current_user_can('manage_options')) {
                echo '<div class="notice notice-error"><p>' . esc_html__('YouTube API Error (visible to admins only): ', 'new-video-gallery') . esc_html($result->get_error_message()) . '</p></div>';
            }
        }
        ?>
    </div>
    <?php if ($v_gallery_load_more == 'yes' && !is_wp_error($result) && !empty($result['nextPageToken'])) { ?>
        <div style="clear:both;"></div>
        <div class="vg-load-more-container" style="text-align: center; margin-top: 20px;">
            <button id="loadMoreBtn_<?php echo esc_attr($post_id); ?>" class="btn vg-load-more-btn <?php echo esc_attr($load_btn_style); ?>" data-next-token="<?php echo esc_attr($result['nextPageToken']); ?>"><?php echo esc_html($load_text); ?></button>
        </div>
    <?php } ?>
</div>
<script>
    jQuery(document).ready(function () {
        var postId = "<?php echo esc_js($post_id); ?>";

        jQuery(document).on("click", "#loadMoreBtn_" + postId, function () {
            var $btn = jQuery(this);
            var pageToken = $btn.attr("data-next-token");
            if (!pageToken) return;
            
            $btn.html('<span class="vg-btn-spinner"></span>')
                .prop('disabled', true)
                .css({'opacity': '0.8', 'cursor': 'default'});
            
            jQuery.ajax({
                url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
                type: "POST",
                data: {
                    action: "nvgall_load_more_youtube",
                    security: "<?php echo esc_js(wp_create_nonce('vg_load_more_nonce')); ?>",
                    post_id: postId,
                    page_token: pageToken
                },
                success: function (response) {
                    if (response.success) {
                        var $grid = jQuery(".youram-simple_" + postId);
                        var $items = jQuery('<div>' + response.data.html + '</div>').find('.vg-col');
                        if ($items.length > 0) {
                            $grid.append($items);
                            if (typeof jQuery.fn.isotope !== 'undefined' && $grid.data('isotope')) {
                                if (typeof jQuery.fn.imagesLoaded !== 'undefined') {
                                    $grid.imagesLoaded(function() {
                                        $grid.isotope('appended', $items);
                                        $grid.isotope('layout');
                                    });
                                } else {
                                    $grid.isotope('appended', $items);
                                    $grid.isotope('layout');
                                }
                            }
                        }
                        if (response.data.nextPageToken) {
                            $btn.attr("data-next-token", response.data.nextPageToken);
                            $btn.html("<?php echo esc_js($load_text); ?>")
                                .prop("disabled", false)
                                .css({'opacity': '', 'cursor': ''});
                        } else {
                            $btn.html("<?php echo esc_js($no_images_text); ?>")
                                .prop("disabled", true)
                                .css({'opacity': '0.6', 'cursor': 'default'});
                        }
                    } else {
                        $btn.html("<?php echo esc_js($no_images_text); ?>")
                            .prop("disabled", true)
                            .css({'opacity': '0.6', 'cursor': 'default'});
                    }
                },
                error: function () {
                    $btn.html("<?php echo esc_js($load_text); ?>")
                        .prop("disabled", false)
                        .css({'opacity': '', 'cursor': ''});
                }
            });
        });


    });
</script>
