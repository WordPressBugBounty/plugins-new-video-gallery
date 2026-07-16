<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Register Gutenberg Block for Video Gallery Premium
 */
add_action('init', 'awl_video_gallery_register_gutenberg_block');
function awl_video_gallery_register_gutenberg_block() {
    if (!function_exists('register_block_type')) {
        return;
    }

    // Register editor-only override styles (force visibility in editor preview)
    wp_register_style('awl-vg-block-editor-css', false);
    wp_add_inline_style('awl-vg-block-editor-css', '
        .vg-gallery-loader { display: none !important; }
        [class^="vg_result_"], [class^="video-item_"] { opacity: 1 !important; }
        .vg-skeleton { background: none !important; }
        .vg-skeleton.vg-loading::after { display: none !important; }
        .vg-skeleton img { opacity: 1 !important; }
        .vg-row { opacity: 1 !important; }
        .vg-row .single-image { opacity: 1 !important; animation: none !important; }
    ');
    
    wp_register_script(
        'awl-vg-gutenberg-block-js',
        VG_PLUGIN_URL . 'assets/js/gutenberg-block.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'jquery'),
        VG_PLUGIN_VER,
        true
    );

    register_block_type('new-video-gallery/video-gallery-block', array(
        'api_version'     => 3,
        'editor_script'   => 'awl-vg-gutenberg-block-js',
        'editor_style'    => array('vg-frontend-grid-css', 'vg-api-frontend-css', 'vg-video-icon-css', 'vg-lightgallery-css', 'awl-vg-block-editor-css'),
        'render_callback' => 'awl_video_gallery_block_render',
        'attributes'      => array(
            'galleryId' => array(
                'type'    => 'string',
                'default' => '',
            ),
        ),
    ));
}

add_action('enqueue_block_editor_assets', 'awl_video_gallery_gutenberg_localize');
function awl_video_gallery_gutenberg_localize() {
    $all_galleries = get_posts(array(
        'post_type'      => 'video_gallery',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));
    
    $galleries_data = array();
    if (!empty($all_galleries)) {
        foreach ($all_galleries as $g) {
            $galleries_data[] = array(
                'id'    => $g->ID,
                'title' => $g->post_title ? $g->post_title : __('(no title)', 'new-video-gallery'),
            );
        }
    }
    
    wp_localize_script('awl-vg-gutenberg-block-js', 'vgp_gutenberg_data', array(
        'galleries'  => $galleries_data,
    ));
}

/**
 * Gutenberg Block Render Callback
 * Used for both frontend and ServerSideRender editor preview.
 */
function awl_video_gallery_block_render($attributes) {
    $gallery_id = isset($attributes['galleryId']) ? (int)$attributes['galleryId'] : 0;
    if ($gallery_id) {
        $is_admin_preview = false;
        if ( is_admin() ) {
            $is_admin_preview = true;
        }
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            $is_admin_preview = true;
        }

        if ( $is_admin_preview ) {
            $modified_time = get_post_modified_time( 'U', true, $gallery_id );
            $cache_key = 'vg_blk_prev_' . $gallery_id . '_' . $modified_time . '_' . VG_PLUGIN_VER;
            
            $cached_html = get_transient( $cache_key );
            if ( $cached_html !== false ) {
                return $cached_html;
            }
            
            $rendered_html = do_shortcode('[VDGAL id=' . $gallery_id . ']');
            set_transient( $cache_key, $rendered_html, 1 * HOUR_IN_SECONDS );
            return $rendered_html;
        }

        return do_shortcode('[VDGAL id=' . $gallery_id . ']');
    }
    return '';
}
