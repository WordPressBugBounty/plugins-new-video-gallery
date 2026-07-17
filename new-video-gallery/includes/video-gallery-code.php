<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

/**
 * Gallery Output Code
 */
require_once __DIR__ . '/vg-card-render.php';
//js
wp_enqueue_script('jquery');
wp_enqueue_script('imagesloaded');
wp_enqueue_script('vg-isotope-js');

//video js
wp_enqueue_script('vg-lightgallery-js');
wp_enqueue_script('vg-lightbox-init');

// modern CSS variables columns grid
wp_enqueue_style('vg-frontend-grid-css');
wp_enqueue_style('vg-video-icon-css');
wp_enqueue_style('vg-lightgallery-css');
wp_enqueue_style('vg-lg-transitions');


$video_gallery_id = esc_attr($post_id['id']);

if (!function_exists('is_vg_serialized')) {
	function is_vg_serialized($str)
	{
		return ($str == serialize(false) || @unserialize($str, ['allowed_classes' => false]) !== false);
	}
}



$all_galleries = array('p' => $video_gallery_id, 'post_type' => 'video_gallery', 'orderby' => 'ASC');
$loop = new WP_Query($all_galleries);

while ($loop->have_posts()):
	$loop->the_post();

	$post_id = esc_attr(get_the_ID());


	// Retrieve the meta value using compliant prefix, with fallback to old prefix
	$meta_key = 'nvgall_vg_settings_' . $post_id;
	$meta_val = get_post_meta( $post_id, $meta_key, true );
	if ( empty( $meta_val ) ) {
		$meta_key = 'awl_vg_settings_' . $post_id;
		$meta_val = get_post_meta( $post_id, $meta_key, true );
	}

	// Check if the data is already an array or needs decoding
	if ( is_array( $meta_val ) ) {
		$gallery_settings = $meta_val;
	} else {
		// Decode base64 if it's base64 encoded
		$decodedData = base64_decode($meta_val);

		// Check if the data is serialized
		if (is_vg_serialized($decodedData)) {
			// The data is serialized, so unserialize it safely
			$gallery_settings = unserialize($decodedData, ['allowed_classes' => false]);

			// Convert the unserialized data and save it with the new compliant prefix
			update_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, $gallery_settings);
		} elseif (is_vg_serialized($meta_val)) {
			$gallery_settings = unserialize($meta_val, ['allowed_classes' => false]);
		} else {
			// Assume the data is in JSON format
			$gallery_settings = json_decode($meta_val, true); // Ensure true is passed to get an associative array
		}
	}

	if (isset($gallery_settings['slide-ids']) && is_string($gallery_settings['slide-ids'])) {
		$gallery_settings['slide-ids'] = explode(',', $gallery_settings['slide-ids']);
	}

	$original_ids = isset($gallery_settings['slide-ids']) && is_array($gallery_settings['slide-ids']) ? $gallery_settings['slide-ids'] : array();

	$vg_total_images = (isset($gallery_settings['slide-ids']) && is_array($gallery_settings['slide-ids'])) ? count($gallery_settings['slide-ids']) : 0;

	//columns settings
	if (isset($gallery_settings['gal_thumb_size']))
		$gal_thumb_size = $gallery_settings['gal_thumb_size'];
	else
		$gal_thumb_size = "full";
	if (isset($gallery_settings['col_large_desktops']))
		$col_large_desktops = $gallery_settings['col_large_desktops'];
	else
		$col_large_desktops = "col-lg-4";
	if (isset($gallery_settings['col_desktops']))
		$col_desktops = $gallery_settings['col_desktops'];
	else
		$col_desktops = "col-md-3";
	if (isset($gallery_settings['col_tablets']))
		$col_tablets = $gallery_settings['col_tablets'];
	else
		$col_tablets = "col-sm-4";
	if (isset($gallery_settings['col_phones']))
		$col_phones = $gallery_settings['col_phones'];
	else
		$col_phones = "col-xs-6";

	if (!function_exists('vg_get_column_number')) {
		function vg_get_column_number($class) {
			if (empty($class)) {
				return 4;
			}
			if (is_numeric($class)) {
				return intval($class);
			}
			preg_match('/-(\d+)$/', $class, $matches);
			if (!empty($matches[1])) {
				$val = intval($matches[1]);
				if ($val > 0) {
					return 12 / $val;
				}
			}
			return 4; // default
		}
	}
	
	$cols_lg = vg_get_column_number($col_large_desktops);
	$cols_md = vg_get_column_number($col_desktops);
	$cols_sm = vg_get_column_number($col_tablets);
	$cols_xs = vg_get_column_number($col_phones);

	if (isset($gallery_settings['auto_play']))
		$auto_play = $gallery_settings['auto_play'];
	else
		$auto_play = "true";
	if (isset($gallery_settings['auto_close']))
		$auto_close = $gallery_settings['auto_close'];
	else
		$auto_close = "true";
	$show_lightbox_title = isset($gallery_settings['show_lightbox_title']) ? intval($gallery_settings['show_lightbox_title']) : 1;
	$show_lightbox_desc = 0;
	$show_lightbox_loop = isset($gallery_settings['show_lightbox_loop']) ? intval($gallery_settings['show_lightbox_loop']) : 1;

	$show_lightbox_thumbnails = 0;
	$lightbox_lightgallery_transition = 'lg-slide';




	if (isset($gallery_settings['thumbnail_order']))
		$thumbnail_order = $gallery_settings['thumbnail_order'];
	else
		$thumbnail_order = "ASC";
	$thumbnail_aspect_ratio = '3-2';
	$aspect_ratio_val = '3 / 2';
	if (isset($gallery_settings['video_gallery_option']))
		$video_gallery_option = $gallery_settings['video_gallery_option'];
	else
		$video_gallery_option = "photo_wall";
	if (isset($gallery_settings['video_gallery_api_key']))
		$video_gallery_api_key = $gallery_settings['video_gallery_api_key'];
	else
		$video_gallery_api_key = "";
	if (isset($gallery_settings['video_gallery_channel_link']))
		$video_gallery_channel_link = $gallery_settings['video_gallery_channel_link'];
	else
		$video_gallery_channel_link = "";
	if (isset($gallery_settings['vimeo_gallery_access_token']))
		$vimeo_gallery_access_token = $gallery_settings['vimeo_gallery_access_token'];
	else
		$vimeo_gallery_access_token = "";
	if (isset($gallery_settings['vimeo_gallery_username']))
		$vimeo_gallery_username = $gallery_settings['vimeo_gallery_username'];
	else
		$vimeo_gallery_username = "";
	if (isset($gallery_settings['vimeo_gallery_channel_link']))
		$vimeo_gallery_channel_link = $gallery_settings['vimeo_gallery_channel_link'];
	else
		$vimeo_gallery_channel_link = "";
	if (isset($gallery_settings['twitch_client_id']))
		$twitch_client_id = $gallery_settings['twitch_client_id'];
	else
		$twitch_client_id = "";
	if (isset($gallery_settings['twitch_client_secret']))
		$twitch_client_secret = $gallery_settings['twitch_client_secret'];
	else
		$twitch_client_secret = "";
	if (isset($gallery_settings['twitch_channel_name']))
		$twitch_channel_name = $gallery_settings['twitch_channel_name'];
	else
		$twitch_channel_name = "";
	if (isset($gallery_settings['dailymotion_content_source']))
		$dailymotion_content_source = $gallery_settings['dailymotion_content_source'];
	else
		$dailymotion_content_source = "channel";
	if (isset($gallery_settings['dailymotion_channel_name']))
		$dailymotion_channel_name = $gallery_settings['dailymotion_channel_name'];
	else
		$dailymotion_channel_name = "";
	if (isset($gallery_settings['dailymotion_playlist_id']))
		$dailymotion_playlist_id = $gallery_settings['dailymotion_playlist_id'];
	else
		$dailymotion_playlist_id = "";
	if (isset($gallery_settings['wistia_api_token']))
		$wistia_api_token = $gallery_settings['wistia_api_token'];
	else
		$wistia_api_token = "";
	if (isset($gallery_settings['wistia_content_source']))
		$wistia_content_source = $gallery_settings['wistia_content_source'];
	else
		$wistia_content_source = "all";
	if (isset($gallery_settings['wistia_project_id']))
		$wistia_project_id = $gallery_settings['wistia_project_id'];
	else
		$wistia_project_id = "";
	$enable_live_search = 'false';

	$v_gallery_load_more = "no";
	if ($video_gallery_option !== 'no_api') {
		$vg_limit = 50;
	} else {
		$vg_limit = isset($gallery_settings['vg_limit']) ? intval($gallery_settings['vg_limit']) : 8;
	}
	if (isset($gallery_settings['load_text']))
		$load_text = $gallery_settings['load_text'];
	else
		$load_text = "Load More";
	if (isset($gallery_settings['no_images_text']))
		$no_images_text = $gallery_settings['no_images_text'];
	else
		$no_images_text = "No more videos.";
	if (isset($gallery_settings['load_btn_style']))
		$load_btn_style = $gallery_settings['load_btn_style'];
	else
		$load_btn_style = "solid";
	if (isset($gallery_settings['load_button_color']))
		$load_button_color = $gallery_settings['load_button_color'];
	else
		$load_button_color = "#4f46e5";
	if (isset($gallery_settings['load_text_color']))
		$load_text_color = $gallery_settings['load_text_color'];
	else
		$load_text_color = "#ffffff";
	if (isset($gallery_settings['load_button_hover']))
		$load_button_hover = $gallery_settings['load_button_hover'];
	else
		$load_button_hover = "#4338ca";
	if (isset($gallery_settings['load_btn_pad_x']))
		$load_btn_pad_x = intval($gallery_settings['load_btn_pad_x']);
	else
		$load_btn_pad_x = 32;
	if (isset($gallery_settings['load_btn_pad_y']))
		$load_btn_pad_y = intval($gallery_settings['load_btn_pad_y']);
	else
		$load_btn_pad_y = 12;
	if (isset($gallery_settings['load_btn_radius']))
		$load_btn_radius = intval($gallery_settings['load_btn_radius']);
	else
		$load_btn_radius = 50;
	if (isset($gallery_settings['video_icon']))
		$video_icon = $gallery_settings['video_icon'];
	else
		$video_icon = "true";
	$show_video_platform_tag = 'false';
	$gallery_ad_script = isset($gallery_settings['gallery_ad_script']) ? $gallery_settings['gallery_ad_script'] : '<div style="background: #3b82f6; color: white; padding: 30px; text-align: center; font-weight: bold; border-radius: 8px; font-size: 18px; margin: 15px 0; width: 100%;">
    🎉 IN-GRID ADVERTISEMENT SLOT (WORKING!)
</div>';
	$show_ad = isset($gallery_settings['show_ad']) ? $gallery_settings['show_ad'] : (!empty($gallery_ad_script) ? 'yes' : 'no');
	$isotope_layout_mode = 'fitRows';

	$thumb_spacing = isset($gallery_settings['thumb_spacing']) ? intval($gallery_settings['thumb_spacing']) : 8;
	$thumb_title_hover_mode = isset($gallery_settings['thumb_title_hover_mode']) ? $gallery_settings['thumb_title_hover_mode'] : 'show_hover';
	$thumb_icon_tag_display = isset($gallery_settings['thumb_icon_tag_display']) ? $gallery_settings['thumb_icon_tag_display'] : 'hover';
	$thumb_border_radius = isset($gallery_settings['thumb_border_radius']) ? intval($gallery_settings['thumb_border_radius']) : 8;
	$thumb_border = isset($gallery_settings['thumb_border']) ? intval($gallery_settings['thumb_border']) : 0;

	$image_grayscale = isset($gallery_settings['image_grayscale']) ? intval($gallery_settings['image_grayscale']) : 0;
	$grayscale_percentage = isset($gallery_settings['grayscale_percentage']) ? intval($gallery_settings['grayscale_percentage']) : 80;

	$video_title = isset($gallery_settings['video_title']) ? $gallery_settings['video_title'] : 'hide';
	if (isset($gallery_settings['title_color']))
		$title_color = $gallery_settings['title_color'];
	else
		$title_color = "#383838";
	$video_desc = isset($gallery_settings['video_desc']) ? $gallery_settings['video_desc'] : 'hide';
	if (isset($gallery_settings['desc_color']))
		$desc_color = $gallery_settings['desc_color'];
	else
		$desc_color = "#72777C";
	$api_video_title = isset($gallery_settings['api_video_title']) ? $gallery_settings['api_video_title'] : 'show';
	$api_video_desc = isset($gallery_settings['api_video_desc']) ? $gallery_settings['api_video_desc'] : 'show';

	if (isset($gallery_settings['custom_css']))
		$custom_css = $gallery_settings['custom_css'];
	else
		$custom_css = "";

	$global_settings = get_option('awl_vg_global_settings', array());
	$lazy_loading = isset($global_settings['lazy_loading']) ? $global_settings['lazy_loading'] : 'yes';
	$gallery_loader = isset($global_settings['gallery_loader']) ? $global_settings['gallery_loader'] : 'spinner';
	$gallery_loader_color = isset($global_settings['gallery_loader_color']) ? $global_settings['gallery_loader_color'] : '#4f46e5';
	$global_css = isset($global_settings['global_css']) ? $global_settings['global_css'] : '';

	$style_data = wp_strip_all_tags($custom_css) . "\n" . wp_strip_all_tags($global_css) . "\n";
	$style_data .= ".single-image figure {\n";
	$style_data .= "	margin:0 !important;\n";
	$style_data .= "}\n";
	$style_data .= ".single-image {\n";
	$style_data .= "	position: relative;\n";
	$style_data .= "	left: 0;\n";
	$style_data .= "	top: 0;\n";
	$style_data .= "	float: left;\n";
	$style_data .= "}\n";
	list($lbr, $lbg, $lbb) = sscanf($load_button_color, "#%02x%02x%02x");
	$btn_bg_rgba = "rgba($lbr, $lbg, $lbb, 0.2)";

	list($hlbr, $hlbg, $hlbb) = sscanf($load_button_hover, "#%02x%02x%02x");
	$btn_hover_rgba = "rgba($hlbr, $hlbg, $hlbb, 0.5)";

	$style_data .= "#image_gallery_" . esc_attr($video_gallery_id) . ", #nev_vimeo_api_" . esc_attr($post_id) . ", #nev_twitch_api_" . esc_attr($post_id) . ", #nev_dm_api_" . esc_attr($post_id) . ", #nev_wis_api_" . esc_attr($post_id) . ", .youram-simple_" . esc_attr($post_id) . ", .vimeo-api-gallery-wrapper_" . esc_attr($post_id) . " {\n";
	$style_data .= "	--vg-title-color: " . esc_attr($title_color) . ";\n";
	$style_data .= "	--vg-desc-color: " . esc_attr($desc_color) . ";\n";
	$style_data .= "	--vg-video-icon-display: " . (($video_icon === 'hide' || $video_icon === 'false') ? 'none' : 'flex') . ";\n";
	$style_data .= "	--vg-thumbnail-radius: " . esc_attr($thumb_border_radius) . "px;\n";
	$style_data .= "	--vg-gutter: " . esc_attr($thumb_spacing) . "px;\n";
	$style_data .= "	--vg-radius: " . esc_attr($thumb_border_radius) . "px;\n";
	$style_data .= "	--vg-card-radius: " . esc_attr($thumb_border_radius) . "px;\n";
	$style_data .= "}\n";

	// Play Overlay Behavior CSS rules
	$style_data .= "#image_gallery_" . esc_attr($video_gallery_id) . " .vg-card__overlay, .youram-simple_" . esc_attr($post_id) . " .vg-card__overlay {\n";
	if ($thumb_icon_tag_display === 'always') {
		$style_data .= "	opacity: 1 !important;\n";
	} else {
		$style_data .= "	opacity: 0 !important;\n";
	}
	$style_data .= "}\n";
	if ($thumb_icon_tag_display === 'hover') {
		$style_data .= "#image_gallery_" . esc_attr($video_gallery_id) . " .vg-card:hover .vg-card__overlay, .youram-simple_" . esc_attr($post_id) . " .vg-card:hover .vg-card__overlay {\n";
		$style_data .= "	opacity: 1 !important;\n";
		$style_data .= "}\n";
	}

	// Check if inside editor preview context (Gutenberg or Elementor)
	$is_preview_style = false;
	if ( is_admin() ) {
		$is_preview_style = true;
	}
	if ( class_exists( '\Elementor\Plugin' ) ) {
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			$is_preview_style = true;
		}
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$is_preview_style = true;
	}

	if ( $is_preview_style ) {
		echo '<style type="text/css">' . $style_data . '</style>';
	} else {
		wp_add_inline_style('vg-video-icon-css', $style_data);
	}
	?>

	<?php if ($enable_live_search === 'true') { ?>
		<div class="vg-search-wrapper" style="margin-bottom: 25px; display: flex; justify-content: center; width: 100%;">
			<div style="position: relative; max-width: 480px; width: 100%;">
				<input type="text" class="vg-search-input" placeholder="<?php esc_attr_e('Search videos...', 'new-video-gallery'); ?>" style="width: 100%; padding: 12px 16px 12px 42px; border-radius: 50px; border: 1px solid rgba(0,0,0,0.1); outline: none; transition: all 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.05); font-size: 15px;">
				<svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #94a3b8;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
			</div>
		</div>
	<?php } ?>

<?php if ($video_gallery_option == 'no_api') {
	$has_twitch = isset($gallery_settings['slide-type']) && in_array('t', $gallery_settings['slide-type']);
	if ($has_twitch && !is_ssl() && current_user_can('manage_options') && strpos($_SERVER['HTTP_HOST'], 'localhost') === false && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
		echo '<div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 12px; margin: 10px 0; border-radius: 4px; font-size: 13px; text-align: left; clear: both;">';
		echo '<strong>Twitch Embed Notice (Admins Only):</strong> Twitch requires secure HTTPS contexts for all custom domain embeds (e.g. <code>free.local</code>). Please enable SSL in Local WP by clicking <strong>Trust</strong> in the SSL tab, then access your site via <strong>https://' . esc_html($_SERVER['HTTP_HOST']) . '</strong>.';
		echo '</div>';
	}

?>
		<?php if ($v_gallery_load_more == "no") { ?>
			<div id="image_gallery_<?php echo esc_attr($video_gallery_id); ?>" class="vg-row all-images vg-layout-fixed" style="--vg-cols-lg: <?php echo esc_attr($cols_lg); ?>; --vg-cols-md: <?php echo esc_attr($cols_md); ?>; --vg-cols-sm: <?php echo esc_attr($cols_sm); ?>; --vg-cols-xs: <?php echo esc_attr($cols_xs); ?>;" version="<?php echo esc_attr(VG_PLUGIN_VER); ?>" data-lg-config='<?php echo esc_attr(wp_json_encode(array(
				"loop" => ($show_lightbox_loop === 1),
				"thumbnail" => ($show_lightbox_thumbnails === 1),
				"autoplay" => ($auto_play === "true"),
				"mode" => esc_attr($lightbox_lightgallery_transition)
			))); ?>'>
				<div class="grid-sizer"></div>
				<?php
			if (isset($gallery_settings['slide-ids']) && is_array($gallery_settings['slide-ids']) && count($gallery_settings['slide-ids']) > 0) {
				$count = 0;
				$ad_after_index = intval(floor(count($gallery_settings['slide-ids']) / 2));
				$original_ids = $gallery_settings['slide-ids'];
				echo '<!-- DEBUG VG ADS info: show_ad = ' . esc_attr($show_ad) . ', script_empty = ' . (empty($gallery_ad_script) ? 'yes' : 'no') . ', ad_after_index = ' . intval($ad_after_index) . ', count = ' . count($gallery_settings['slide-ids']) . ' -->';
				if ($thumbnail_order == "DESC") {
					$gallery_settings['slide-ids'] = array_reverse($gallery_settings['slide-ids']);
				}
				if ($thumbnail_order == "RANDOM") {
					shuffle($gallery_settings['slide-ids']);
				}
				foreach ($gallery_settings['slide-ids'] as $attachment_id) {
					echo '<!-- LOOP ITERATION: count = ' . $count . ', attachment_id = ' . $attachment_id . ' -->';
					$attachment_details = get_post($attachment_id);
					if (!$attachment_details) {
						$count++;
						continue;
					}
					$orig_key = array_search($attachment_id, $original_ids);
					if ($orig_key === false) {
						$orig_key = $count;
					}
					$title = isset($gallery_settings['slide-title'][$orig_key]) && !empty($gallery_settings['slide-title'][$orig_key]) ? $gallery_settings['slide-title'][$orig_key] : $attachment_details->post_title;
					$description = isset($gallery_settings['slide-desc'][$orig_key]) && !empty($gallery_settings['slide-desc'][$orig_key]) ? $gallery_settings['slide-desc'][$orig_key] : $attachment_details->post_content;
					$video_type = isset($gallery_settings['slide-type'][$orig_key]) ? $gallery_settings['slide-type'][$orig_key] : '';
					$video_id = isset($gallery_settings['slide-link'][$orig_key]) ? $gallery_settings['slide-link'][$orig_key] : '';
					if ($video_type == 'y') {
						$video_id = vg_extract_youtube_id($video_id);
					} elseif ($video_type == 'v') {
						$video_id = vg_extract_vimeo_id($video_id);
					}
					$poster_type = isset($gallery_settings['poster-type'][$orig_key]) ? $gallery_settings['poster-type'][$orig_key] : '';

					$should_show_ad = false;
					if ($show_ad === 'yes') {
						$should_show_ad = ($count === $ad_after_index);
					}

					if ($should_show_ad && !empty($gallery_ad_script)) {
						echo '<div class="vg-col ' . esc_attr("$col_large_desktops $col_desktops $col_tablets $col_phones") . ' vg-ad-container">' . $gallery_ad_script . '</div>';
					}
					vg_render_manual_card(array(
						'attachment_id'       => $attachment_id,
						'video_type'          => $video_type,
						'video_id'            => $video_id,
						'poster_type'         => $poster_type,
						'title'               => $title,
						'description'         => $description,
						'gallery_settings'    => $gallery_settings,
						'post_id'             => $post_id,
						'video_gallery_id'    => $video_gallery_id,
						'gal_thumb_size'      => $gal_thumb_size,
						'video_title'         => $video_title,
						'video_desc'          => $video_desc,
						'video_icon'          => $video_icon,
						'show_video_platform_tag' => $show_video_platform_tag,
						'thumb_title_hover_mode' => $thumb_title_hover_mode,
						'thumb_border'        => $thumb_border,
						'image_grayscale'     => $image_grayscale,
						'show_lightbox_title' => $show_lightbox_title,
						'show_lightbox_desc'  => $show_lightbox_desc,
						'lazy_loading'        => $lazy_loading,
						'col_classes'         => "$col_large_desktops $col_desktops $col_tablets $col_phones",
					));

					$count++;
				} // end of attachment foreach




			}
			
			else {
				esc_html_e('Sorry! No video gallery found ', 'new-video-gallery');
				echo ": [VDGAL id=" . esc_attr($post_id) . "]";
			} // end of if esle of slides avaialble check into slider
?>
			</div>
		<?php
		}
	}
	if ($video_gallery_option == 'video_yoyube_api') {
		require( VG_PLUGIN_DIR . 'api-templates/youtube-api-gallery.php' );
	}
	if (in_array($video_gallery_option, array('video_vimeo_api', 'video_twitch_api', 'video_dailymotion_api', 'video_wistia_api', 'video_meta_api', 'video_tiktok_api'))) {
		?>
		<div class="vg-pro-notice-frontend" style="border: 2px dashed #e11d48; padding: 25px; border-radius: 8px; text-align: center; background: #fff1f2; margin: 20px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
			<svg viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 36px; height: 36px; margin: 0 auto 12px; display: block;">
				<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
				<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
			</svg>
			<h4 style="margin: 0 0 8px 0; color: #9f1239; font-size: 18px; font-weight: 700;"><?php esc_html_e( 'Premium Gallery Type', 'new-video-gallery' ); ?></h4>
			<p style="margin: 0 0 15px 0; color: #be123c; font-size: 14px;"><?php esc_html_e( 'This gallery uses an external API feed source (Vimeo, Twitch, Wistia, Dailymotion, Meta, or TikTok) which is only supported in the Premium version of Video Gallery.', 'new-video-gallery' ); ?></p>
			<a href="https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/" target="_blank" style="background: #e11d48; color: #fff; text-decoration: none; padding: 8px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; display: inline-block; transition: background 0.2s;"><?php esc_html_e( 'Upgrade to Premium', 'new-video-gallery' ); ?></a>
		</div>
		<?php
	}


endwhile;
wp_reset_postdata();

ob_start();
?>
jQuery(document).ready(function () {
	var $grid = jQuery('#image_gallery_<?php echo esc_js($video_gallery_id); ?>, .youram-simple_<?php echo esc_js($video_gallery_id); ?>, #nev_vimeo_api_<?php echo esc_js($video_gallery_id); ?>, #nev_twitch_api_<?php echo esc_js($video_gallery_id); ?>, #nev_dm_api_<?php echo esc_js($video_gallery_id); ?>, #nev_wis_api_<?php echo esc_js($video_gallery_id); ?>');
	var $loader = jQuery('#vg-loader-<?php echo esc_js($video_gallery_id); ?>');
	var $gallery = $grid;

	// 1. Initialize Isotope
	if (typeof jQuery.fn.isotope !== 'undefined') {
		$grid.isotope({
			itemSelector: '.single-image, .vg-col',
			layoutMode: '<?php echo esc_js($isotope_layout_mode); ?>',
			percentPosition: true,
			masonry: {
				columnWidth: '.grid-sizer'
			},
			transitionDuration: 0
		});
	}

	// 2. Unified reveal and layout function
	var revealGallery = function() {
		$gallery.css('opacity', 1);
		if (typeof jQuery.fn.isotope !== 'undefined') {
			$grid.isotope('layout');
		}
	};

	// 3. Load triggers
	if (typeof jQuery.fn.imagesLoaded !== 'undefined') {
		$gallery.imagesLoaded(function() {
			revealGallery();
		});
		$grid.imagesLoaded().progress(function() {
			if (typeof jQuery.fn.isotope !== 'undefined') {
				$grid.isotope('layout');
			}
		});
	} else {
		revealGallery();
	}

	// 4. Robust layout fallback timers
	if (typeof jQuery.fn.isotope !== 'undefined') {
		setTimeout(function() {
			$grid.isotope('layout');
		}, 500);
		setTimeout(function() {
			$grid.isotope('layout');
		}, 1500);
	}
});
<?php
$script_data = ob_get_clean();

// Check if we are inside Gutenberg blocks editor or Elementor preview editor
$is_preview = false;
if ( is_admin() ) {
	$is_preview = true;
}
if ( class_exists( '\Elementor\Plugin' ) ) {
	if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
		$is_preview = true;
	}
}
if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
	$is_preview = true;
}

if ( $is_preview ) {
	echo '<script type="text/javascript">' . $script_data . '</script>';
} else {
	wp_add_inline_script( 'vg-lightbox-init', $script_data );
}
?>