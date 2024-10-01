<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

/*
 * Gallery Output Code
 */

//js
wp_enqueue_script('jquery');
wp_enqueue_script('awl-vg-youram-simple-js', VG_PLUGIN_URL . 'assets/js/youram-simple.min.js', array('jquery'), '', true);

// custom bootstrap css
wp_enqueue_style('awl-video-youram-simple-css', VG_PLUGIN_URL . 'assets/css/youram-simple.min.css');
?>
<div id="your-page-column" class="not-a-part-of-youram-plugin">
	<div id="yram" class="youram-simple_<?php echo esc_attr($post_id); ?>"></div>
</div>
<style>
	.youram-simple_<?php echo esc_attr($post_id); ?> {
		text-align: center;
	}

	.youram-simple_<?php echo esc_attr($post_id); ?> .yl-load-more-button {
		background-color: #1e73be !important;
		color: #ffffff !important;
	}
</style>

<script>
	jQuery(document).ready(function () {
		jQuery(".youram-simple_<?php echo esc_js($post_id); ?>").youramSimple({
			apiKey: "<?php echo esc_js($video_gallery_api_key); ?>",
			sourceLink: "<?php echo esc_js($video_gallery_channel_link); ?>",
			maxResults: "8",
			videoDisplayMode: "popup",
			loadMoreText: 'Load More',
			themeColor: "rgb(255, 0, 0)",
		});

	});
</script>