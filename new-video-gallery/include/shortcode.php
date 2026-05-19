<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/**
 * video Gallery Shortcode
 *
 * @access    public
 * @since     3.0
 *
 * @return    Create Frontend Gallery Output
 */

// Register the primary shortcode tag and its legacy alias
add_shortcode( 'video_gallery', 'nvgall_video_gallery_shortcode' );
add_shortcode( 'VDGAL', 'nvgall_video_gallery_shortcode' );

function nvgall_video_gallery_shortcode( $atts ) {
	// Parse attributes safely with defaults to prevent PHP warnings on missing parameters
	$atts = shortcode_atts(
		array(
			'id' => 0,
		),
		$atts,
		'video_gallery'
	);

	// Backward compatibility mapping for video-gallery-code.php which expects $post_id['id']
	$post_id = array(
		'id' => absint( $atts['id'] ),
	);

	ob_start();

	// Output code file using secure absolute path instead of relative path
	$template_path = NVGALL_PLUGIN_DIR . 'include/video-gallery-code.php';
	if ( file_exists( $template_path ) ) {
		require $template_path;
	}

	return ob_get_clean();
}

// Backward Compatibility Wrapper Alias to prevent fatal errors on direct custom calls
if ( ! function_exists( 'awl_video_gallery_shortcode' ) ) {
	function awl_video_gallery_shortcode( $atts ) {
		return nvgall_video_gallery_shortcode( $atts );
	}
}

