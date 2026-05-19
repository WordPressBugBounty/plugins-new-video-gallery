<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Gallery Output Code
 */
// js
wp_enqueue_script('imagesloaded');
wp_enqueue_script('awlife-vg-isotope-js');

// video js
wp_enqueue_script('awlife-vg-scale-fix-js');
wp_enqueue_script('awlife-vg-video-lightning-js');
wp_enqueue_script('awlife-vg-jqvl-page-js');


// custom bootstrap css
wp_enqueue_style('awlife-bootstrap-css');
wp_enqueue_style('awlife-icon-css');


$video_gallery_id = esc_attr($post_id['id']);

$all_galleries = array(
	'p' => $video_gallery_id,
	'post_type' => 'video_gallery',
	'orderby' => 'ASC',
);
$loop = new WP_Query($all_galleries);

while ($loop->have_posts()):
	$loop->the_post();

	$post_id = esc_attr(get_the_ID());

	if (!function_exists('nvgall_is_serialized')) {
		function nvgall_is_serialized($str)
		{
			return ($str === serialize(false) || @unserialize($str) !== false);
		}
	}

	if (!function_exists('nvg_is_serialized')) {
		function nvg_is_serialized($str)
		{
			return nvgall_is_serialized($str);
		}
	}

	if (!function_exists('is_sr_serialized')) {
		function is_sr_serialized($str)
		{
			return nvgall_is_serialized($str);
		}
	}

	// Retrieve the data with prefix compliance and absolute fallback safeguards
	$encodedData = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
	if (empty($encodedData)) {
		$encodedData = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
	}

	// Decode the base64 encoded data
	$decodedData = base64_decode($encodedData);

	// Check if the data is serialized
	if (nvgall_is_serialized($decodedData)) {

		// The data is serialized, so unserialize it
		$gallery_settings = unserialize($decodedData, ['allowed_classes' => false]);
		// Convert the unserialized data to JSON and save it back in the new prefix key
		$jsonEncodedData = json_encode($gallery_settings);
		update_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, $jsonEncodedData);

		// Now, to use the newly saved format, fetch and decode again
		$encodedData = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
		$gallery_settings = json_decode(($encodedData), true);

	}
	else {
		// Assume the data is in JSON format and fetch the new key first, fallback to the legacy key
		$jsonData = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
		if (empty($jsonData)) {
			$jsonData = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
		}
		// Decode the JSON string into an associative array
		$gallery_settings = json_decode($jsonData, true); // Ensure true is passed to get an associative array
	}

	// columns settings
	$gal_thumb_size = $gallery_settings['gal_thumb_size'] ?? 'full';
	$col_large_desktops = $gallery_settings['col_large_desktops'] ?? 'col-lg-4';
	$col_desktops = $gallery_settings['col_desktops'] ?? 'col-md-4';
	$col_tablets = $gallery_settings['col_tablets'] ?? 'col-sm-6';
	$col_phones = $gallery_settings['col_phones'] ?? 'col-xs-12';
	$width = $gallery_settings['width'] ?? '';
	$height = $gallery_settings['height'] ?? '';
	$video_icon = $gallery_settings['video_icon'] ?? 'play';
	$auto_play = $gallery_settings['auto_play'] ?? 'false';
	$auto_close = $gallery_settings['auto_close'] ?? 'false';
	$close_button = $gallery_settings['close_button'] ?? 'true';
	$z_index = $gallery_settings['z_index'] ?? 'default';
	if ($z_index == 'default') {
		$z_index_value = 999999;
	}
	else {
		$z_index_value = $gallery_settings['z_index_custom_value'] ?? '';
	}
	// start the video gallery contents
	if (isset($gallery_settings['video_gallery_option']))
		$video_gallery_option = $gallery_settings['video_gallery_option'];
	else
		$video_gallery_option = "no_api";
	if (isset($gallery_settings['video_gallery_api_key']))
		$video_gallery_api_key = $gallery_settings['video_gallery_api_key'];
	else
		$video_gallery_api_key = "";
	if (isset($gallery_settings['video_gallery_channel_link']))
		$video_gallery_channel_link = $gallery_settings['video_gallery_channel_link'];
	else
		$video_gallery_channel_link = "";
?>

	<?php
	if ($video_gallery_option == 'no_api') { ?>
		<div id="image_gallery_<?php echo esc_attr($video_gallery_id); ?>" class="row all-images">
			<?php
		if (isset($gallery_settings['slide-ids']) && count($gallery_settings['slide-ids']) > 0) {
			$count = 0;
			foreach ($gallery_settings['slide-ids'] as $attachment_id) {
				$thumb = wp_get_attachment_image_src($attachment_id, 'thumb', true);
				$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
				$medium = wp_get_attachment_image_src($attachment_id, 'medium', true);
				$large = wp_get_attachment_image_src($attachment_id, 'large', true);
				$full = wp_get_attachment_image_src($attachment_id, 'full', true);
				$attachment_details = get_post($attachment_id);
				$src = $attachment_details->guid;
				$title = $attachment_details->post_title;
				$description = $attachment_details->post_content;
				$video_type = $gallery_settings['slide-type'][$count] ?? 'y';
				$video_id = $gallery_settings['slide-link'][$count] ?? '';
				$poster_type = $gallery_settings['poster-type'][$count] ?? 'internal';

				// set thumbnail size
				if ($gal_thumb_size == 'thumbnail') {
					$thumbnail_url = $thumbnail[0];
				}
				if ($gal_thumb_size == 'medium') {
					$thumbnail_url = $medium[0];
				}
				if ($gal_thumb_size == 'large') {
					$thumbnail_url = $large[0];
				}
				if ($gal_thumb_size == 'full') {
					$thumbnail_url = $full[0];
				}
				if ($poster_type == 'youtube' && $video_type == 'y') {
					$thumbnail_url = "https://img.youtube.com/vi/$video_id/hqdefault.jpg";
				}
?>
					<div
						class="single-image <?php echo esc_attr($col_large_desktops); ?> <?php echo esc_attr($col_desktops); ?> <?php echo esc_attr($col_tablets); ?> <?php echo esc_attr($col_phones); ?>">
						<figure>
							<div class="video-gal-icon">
								<img class="img-thumbnail vid-<?php echo esc_attr($video_gallery_id); ?>"
									src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_html($title); ?>"
									data-video-id="<?php echo esc_attr($video_type); ?>-<?php echo esc_attr($video_id); ?>">
								<?php if ($video_type == "y" && $video_icon != "true") { ?>
									<i class="video_icon_<?php echo esc_attr($post_id); ?>">
										<img src="<?php echo esc_url(NVGALL_PLUGIN_URL . 'assets/img/p-youtube.png'); ?>">
									</i>
								<?php
				}
				if ($video_type == "v" && $video_icon != "true") { ?>
									<i class="video_icon_<?php echo esc_attr($post_id); ?>">
										<img src="<?php echo esc_url(NVGALL_PLUGIN_URL . 'assets/img/p-vimeo.png'); ?>">
									</i>
								<?php
				}?>
							</div>
							<div>
								<?php if ($title) { ?>
									<div class="vg-title">
										<?php echo esc_html($title); ?>
									</div>
								<?php
				}?>
								<?php if ($description) { ?>
									<div class="vg-desc">
										<?php echo esc_html($description); ?>
									</div>
								<?php
				}?>
							</div>
						</figure>
					</div>
					<?php
				$count++;
			} // end of attachment foreach
		}
		else {
			esc_html_e('Sorry! No video gallery found ', 'new-video-gallery');
		} // end of if esle of slides avaialble check into slider
?>
		</div>
	<?php
	}
	if ($video_gallery_option == 'video_yoyube_api') {
		$youtube_gallery_path = NVGALL_PLUGIN_DIR . 'include/youtube-api-gallery.php';
		if ( file_exists( $youtube_gallery_path ) ) {
			require $youtube_gallery_path;
		}
	}
endwhile;
wp_reset_postdata();

// Dynamic styles using standard wp_add_inline_style()
$custom_css = "";
if ($close_button == 'false') {
	$custom_css .= ".video-close { display: none !important; }\n";
}
$custom_css .= "
.single-image .vg-title {
	font-size: 25px;
	font-weight: bold;
	text-align: center;
	padding: 5px;
	line-height: 1.3;
	word-wrap: break-word;
	overflow-wrap: break-word;
}
.single-image .vg-desc {
	font-size: 15px;
	padding: 5px;
}
.single-image {
	padding-top: 20px;
	overflow: visible !important;
}
";
wp_add_inline_style('awlife-bootstrap-css', $custom_css);

// Dynamic scripts using standard wp_add_inline_script()
$autoplay_val = ($auto_play === 'true') ? 'true' : 'false';
$autoclose_val = ($auto_close === 'true') ? 'true' : 'false';
$custom_js = "
jQuery(document).ready(function () {
	// isotope effect function
	// Method 1 - Initialize Isotope, then trigger layout after each image loads.
	var \$grid = jQuery('.all-images').isotope({
		// options...
		itemSelector: '.single-image',
	});
	// layout Isotope after each image loads
	\$grid.imagesLoaded().progress(function () {
		\$grid.isotope('layout');
	});
	// Re-layout after fonts load AND text reflow so container height is correct
	document.fonts.ready.then(function () {
		requestAnimationFrame(function () {
			\$grid.isotope('layout');
		});
	});
	// Ultimate fallback: re-layout after everything is fully loaded
	jQuery(window).on('load', function () {
		\$grid.isotope('layout');
	});

	//video lighting js
	videoLightning({
		elements: [
			{
				\".vid-" . esc_js($video_gallery_id) . "\": {
					width: '" . esc_js($width) . "',
					height: '" . esc_js($height) . "',
					autoplay: " . $autoplay_val . ",
					autoclose: " . $autoclose_val . ",
					zindex: '" . esc_js($z_index_value) . "',
					autohide: 2,
				}
			}
		]
	});

	// Move video lightbox wrappers to <body> to escape
	// the post content stacking context (fixes sidebar/header overlap)
	setTimeout(function () {
		jQuery('.video-wrapper').each(function () {
			var \$wrapper = jQuery(this);
			// Save reference to the original target before moving
			var target = \$wrapper.closest('.video-target')[0];
			\$wrapper.appendTo('body');

			// Proxy close button / backdrop clicks back to the original target
			// so VideoLightning's internal close handler still fires
			\$wrapper.on('mouseup', function (e) {
				var \$clicked = jQuery(e.target);
				if (\$clicked.hasClass('video-close') || \$clicked.hasClass('video-wrapper')) {
					if (target) {
						target.dispatchEvent(new MouseEvent('mouseup', {
							bubbles: true, button: 0, which: 1
						}));
					}
				}
			});
		});
	}, 100);
});
";
wp_add_inline_script('awlife-vg-jqvl-page-js', $custom_js);
?>