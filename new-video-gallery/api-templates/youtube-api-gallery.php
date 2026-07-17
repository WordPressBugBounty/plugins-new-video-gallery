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
$gallery_ad_script = isset($gallery_settings['gallery_ad_script']) ? $gallery_settings['gallery_ad_script'] : '<div style="background: #3b82f6; color: white; padding: 30px; text-align: center; font-weight: bold; border-radius: 8px; font-size: 18px; margin: 15px 0; width: 100%;">
    🎉 IN-GRID ADVERTISEMENT SLOT (WORKING!)
</div>';
$show_ad = isset($gallery_settings['show_ad']) ? $gallery_settings['show_ad'] : (!empty($gallery_ad_script) ? 'yes' : 'no');
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
            $ad_after_index = intval(floor(count($result['videos']) / 2));
            echo '<!-- DEBUG VG ADS info (YouTube API): show_ad = ' . esc_attr($show_ad) . ', script_empty = ' . (empty($gallery_ad_script) ? 'yes' : 'no') . ', ad_after_index = ' . intval($ad_after_index) . ', count = ' . count($result['videos']) . ' -->';
            foreach ($result['videos'] as $video) {
                $should_show_ad = false;
                if ($show_ad === 'yes') {
                    $should_show_ad = ($api_ad_count === $ad_after_index);
                }
                if ($should_show_ad && !empty($gallery_ad_script)) {
                    echo '<!-- DEBUG VG ADS: Injected YouTube API ad container -->';
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
</div>

