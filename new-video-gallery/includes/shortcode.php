<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * video Gallery Premium Shortcode
 */
add_shortcode('VDGAL', 'awl_video_gallery_shortcode');
// Shortcode function with robust attribute handling
function awl_video_gallery_shortcode($atts) {
	// Normalize attributes, default 'id' to current post ID if not provided
	$post_id = shortcode_atts(array(
		'id' => get_the_ID(),
	), $atts);

	ob_start();
	
	require( VG_PLUGIN_DIR . 'includes/video-gallery-code.php' );	
	
	return ob_get_clean();
}
?>