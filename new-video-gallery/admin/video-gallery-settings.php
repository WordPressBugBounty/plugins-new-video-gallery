<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_id = esc_attr($post->ID);

if ( ! function_exists( 'is_vg_serialized' ) ) {
	function is_vg_serialized($str)
	{
		return ($str == serialize(false) || @unserialize($str, ['allowed_classes' => false]) !== false);
	}
}

if ( ! function_exists( 'selected' ) ) {
	function selected( $val1, $val2, $echo = true ) {
		$result = ( (string) $val1 === (string) $val2 ) ? ' selected="selected"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'checked' ) ) {
	function checked( $val1, $val2, $echo = true ) {
		$result = ( (string) $val1 === (string) $val2 ) ? ' checked="checked"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

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

if (!function_exists('vg_settings_get_column_number')) {
	function vg_settings_get_column_number($col_class) {
		if (empty($col_class)) {
			return 4;
		}
		if (is_numeric($col_class)) {
			return intval($col_class);
		}
		if (strpos($col_class, '-') !== false) {
			$parts = explode('-', $col_class);
			$num = intval(end($parts));
			if ($num > 0) {
				return 12 / $num;
			}
		}
		return 4;
	}
}

$col_large_desktops_val = vg_settings_get_column_number(isset($gallery_settings['col_large_desktops']) ? $gallery_settings['col_large_desktops'] : 'col-lg-3');
$col_desktops_val = vg_settings_get_column_number(isset($gallery_settings['col_desktops']) ? $gallery_settings['col_desktops'] : 'col-md-3');
$col_tablets_val = vg_settings_get_column_number(isset($gallery_settings['col_tablets']) ? $gallery_settings['col_tablets'] : 'col-sm-4');
$col_phones_val = vg_settings_get_column_number(isset($gallery_settings['col_phones']) ? $gallery_settings['col_phones'] : 'col-xs-6');
$gallery_layout_mode = isset($gallery_settings['gallery_layout_mode']) ? $gallery_settings['gallery_layout_mode'] : 'masonry';
$bg_sync_interval = isset($gallery_settings['bg_sync_interval']) ? $gallery_settings['bg_sync_interval'] : 'disabled';

// Set layout option
if ( isset( $gallery_settings['video_gallery_option'] ) ) {
	$video_gallery_option = $gallery_settings['video_gallery_option'];
} else {
	$video_gallery_option = 'no_api';
}

$thumb_spacing = isset($gallery_settings['thumb_spacing']) ? intval($gallery_settings['thumb_spacing']) : 8;
$thumb_title_pos = isset($gallery_settings['thumb_title_pos']) ? $gallery_settings['thumb_title_pos'] : 'hover';
$thumb_title_hover_mode = isset($gallery_settings['thumb_title_hover_mode']) ? $gallery_settings['thumb_title_hover_mode'] : 'show_hover';
$thumb_icon_tag_display = isset($gallery_settings['thumb_icon_tag_display']) ? $gallery_settings['thumb_icon_tag_display'] : 'hover';
$thumb_title_desc_align = isset($gallery_settings['thumb_title_desc_align']) ? $gallery_settings['thumb_title_desc_align'] : 'center';
$thumb_border_radius = isset($gallery_settings['thumb_border_radius']) ? intval($gallery_settings['thumb_border_radius']) : 8;
$thumb_border = isset($gallery_settings['thumb_border']) ? intval($gallery_settings['thumb_border']) : 0;

$image_grayscale = isset($gallery_settings['image_grayscale']) ? intval($gallery_settings['image_grayscale']) : 0;
$grayscale_percentage = isset($gallery_settings['grayscale_percentage']) ? intval($gallery_settings['grayscale_percentage']) : 80;
?>

<div class="awl-vg-settings-wrapper">
	<!-- Settings Page Loader -->
	<div class="vg-settings-loader">
		<div class="vg-settings-loader-spinner"></div>
	</div>

	<div class="vg-settings-main-content" style="display: none; padding: 20px;">
		<!-- Gallery Type Selector -->
		<div class="video-gallery-type-selector">
			<label style="display:block; margin: 0;">
				<input type="radio" name="video_gallery_option" value="no_api" <?php checked($video_gallery_option, 'no_api'); ?> style="display:none;">
				<div class="video-gallery-type-card no_api">
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-local-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'Video Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0;">
				<input type="radio" name="video_gallery_option" value="video_yoyube_api" <?php checked($video_gallery_option, 'video_yoyube_api'); ?> style="display:none;">
				<div class="video-gallery-type-card video_yoyube_api">
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-youtube-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.113C19.52 3.545 12 3.545 12 3.545s-7.52 0-9.388.505a3.003 3.003 0 0 0-2.11 2.113C0 8.037 0 12 0 12s0 3.963.502 5.837a3.003 3.003 0 0 0 2.11 2.113c1.868.502 9.388.502 9.388.502s7.52 0 9.388-.502a3.003 3.003 0 0 0 2.11-2.113C24 15.963 24 12 24 12s0-3.963-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'YouTube API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0; position: relative; cursor: not-allowed;">
				<input type="radio" name="video_gallery_option" value="video_vimeo_api" <?php checked($video_gallery_option, 'video_vimeo_api'); ?> style="display:none;" disabled>
				<div class="video-gallery-type-card video_vimeo_api" style="position: relative; pointer-events: none; opacity: 0.65;">
					<span class="vg-pro-badge" style="position: absolute; top: 8px; right: 8px; background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; z-index: 10;">Pro</span>
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-vimeo-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M22.396 7.42c-.068 1.5-1.112 3.555-3.13 6.163-2.083 2.7-3.844 4.05-5.286 4.05-.89 0-1.644-.82-2.26-2.46L9.67 7.75c-.616-2.257-1.284-3.385-2.003-3.385-.154 0-.683.325-1.588.975l-.956-1.213C6.35 3.01 7.6 1.95 8.875.926c1.37-1.1 2.397-1.685 3.08-1.758 1.608-.18 2.602.946 2.984 3.376.417 2.664.7 4.316.852 4.954.49 2.052.923 3.08 1.298 3.08.273 0 .785-.436 1.537-1.31.752-.87 1.156-1.537 1.21-2 .102-1.077-.272-1.616-1.12-1.616-.407 0-.825.093-1.25.28l.84-2.7c1.782-.524 3.09-.434 3.926.27.84.7 1.216 1.768 1.13 3.2z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'Vimeo API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0; position: relative; cursor: not-allowed;">
				<input type="radio" name="video_gallery_option" value="video_twitch_api" <?php checked($video_gallery_option, 'video_twitch_api'); ?> style="display:none;" disabled>
				<div class="video-gallery-type-card video_twitch_api" style="position: relative; pointer-events: none; opacity: 0.65;">
					<span class="vg-pro-badge" style="position: absolute; top: 8px; right: 8px; background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; z-index: 10;">Pro</span>
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-twitch-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'Twitch API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0; position: relative; cursor: not-allowed;">
				<input type="radio" name="video_gallery_option" value="video_dailymotion_api" <?php checked($video_gallery_option, 'video_dailymotion_api'); ?> style="display:none;" disabled>
				<div class="video-gallery-type-card video_dailymotion_api" style="position: relative; pointer-events: none; opacity: 0.65;">
					<span class="vg-pro-badge" style="position: absolute; top: 8px; right: 8px; background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; z-index: 10;">Pro</span>
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-dailymotion-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M19.78 5.43c-1.33-1.32-3.1-2.05-4.98-2.05h-5.6C5.55 3.38 2.62 6.3 2.62 9.92v4.16c0 3.62 2.93 6.54 6.58 6.54h5.6c1.88 0 3.65-.73 4.98-2.05c1.33-1.33 2.06-3.1 2.06-4.99V9.92c0-1.89-.73-3.66-2.06-4.99zm-4.98 10.72h-5.6c-1.54 0-2.8-1.25-2.8-2.79V9.92c0-1.54 1.26-2.79 2.8-2.79h5.6c1.54 0 2.8 1.25 2.8 2.79v3.44c0 1.54-1.26 2.79-2.8 2.79z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'Dailymotion API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0; position: relative; cursor: not-allowed;">
				<input type="radio" name="video_gallery_option" value="video_wistia_api" <?php checked($video_gallery_option, 'video_wistia_api'); ?> style="display:none;" disabled>
				<div class="video-gallery-type-card video_wistia_api" style="position: relative; pointer-events: none; opacity: 0.65;">
					<span class="vg-pro-badge" style="position: absolute; top: 8px; right: 8px; background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; z-index: 10;">Pro</span>
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-wistia-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.5l-5-3.5 5-3.5v7zM17 13h-3v-2h3v2z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'Wistia API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0; position: relative; cursor: not-allowed;">
				<input type="radio" name="video_gallery_option" value="video_tiktok_api" <?php checked($video_gallery_option, 'video_tiktok_api'); ?> style="display:none;" disabled>
				<div class="video-gallery-type-card video_tiktok_api" style="position: relative; pointer-events: none; opacity: 0.65;">
					<span class="vg-pro-badge" style="position: absolute; top: 8px; right: 8px; background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; z-index: 10;">Pro</span>
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-tiktok-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.94-1.74-.22-.2-.41-.43-.6-.67v6.62c.03 2.24-.85 4.58-2.6 6.01-1.76 1.48-4.27 2.05-6.51 1.55-2.22-.44-4.22-1.92-5.18-3.99-1.04-2.15-1.02-4.85.12-6.97.94-1.83 2.73-3.23 4.81-3.66 1.11-.25 2.29-.18 3.39.14v4.1c-.8-.28-1.72-.34-2.52-.02-1.01.37-1.8 1.29-2.02 2.34-.29 1.19-.04 2.53.72 3.48.74.96 1.99 1.46 3.2 1.34 1.3-.07 2.53-.94 2.92-2.17.26-.74.24-1.57.25-2.35.02-3.67 0-7.34.01-11.01v-.01z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'TikTok API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>
			<label style="display:block; margin: 0; position: relative; cursor: not-allowed;">
				<input type="radio" name="video_gallery_option" value="video_meta_api" <?php checked($video_gallery_option, 'video_meta_api'); ?> style="display:none;" disabled>
				<div class="video-gallery-type-card video_meta_api" style="position: relative; pointer-events: none; opacity: 0.65;">
					<span class="vg-pro-badge" style="position: absolute; top: 8px; right: 8px; background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; z-index: 10;">Pro</span>
					<span class="card-check-icon dashicons dashicons-yes"></span>
					<svg class="selector-icon selector-meta-svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; margin-bottom: 8px; display: inline-block; vertical-align: middle;"><path d="M12 2C6.477 2 2 6.477 2 12c0 5.011 3.667 9.154 8.5 9.872V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v7.002C18.333 21.162 22 17.022 22 12c0-5.523-4.477-10-10-10z"/></svg>
					<div class="selector-title"><?php esc_html_e( 'Meta API Gallery', 'new-video-gallery' ); ?></div>
				</div>
			</label>


		</div>

		<!-- Navigation Tabs -->
		<div class="awl-vg-tabs-nav">
			<a href="#" class="nav-item active" data-target="tab-add-videos">
				<span class="dashicons dashicons-format-video"></span> <?php esc_html_e('Videos & Source', 'new-video-gallery'); ?>
			</a>
			<a href="#" class="nav-item" data-target="tab-config">
				<span class="dashicons dashicons-grid-view"></span> <?php esc_html_e('Grid Layout', 'new-video-gallery'); ?>
			</a>
			<a href="#" class="nav-item no-api-config-setting" data-target="tab-card-design">
				<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('Card & Frame Styles', 'new-video-gallery'); ?>
			</a>
			<a href="#" class="nav-item" data-target="tab-typography">
				<span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e('Badges & Typography', 'new-video-gallery'); ?>
			</a>
			<a href="#" class="nav-item" data-target="tab-lightbox">
				<span class="dashicons dashicons-format-image"></span> <?php esc_html_e('Lightbox Popup', 'new-video-gallery'); ?>
			</a>
			<a href="#" class="nav-item" data-target="tab-pro-features" style="background: #10b981; color: #ffffff !important; border-radius: 4px; font-weight: 600;">
				<span class="dashicons dashicons-star-filled" style="color: #fff !important;"></span> <?php esc_html_e('Pro Features', 'new-video-gallery'); ?>
			</a>

		</div>

		<!-- Tabs Content Wrapper -->
		<div class="awl-vg-tabs-content-wrapper">
			
			<!-- Tab 1: Add Videos -->
			<div class="awl-vg-tab-content active" id="tab-add-videos">

				
				<!-- Local Video List Uploader -->
				<div id="slider-gallery">
					<div class="file-upload">
						<div class="image-upload-wrap">
							<input type="hidden" id="add-new-slider" name="add-new-slider" value="Upload Image" />
							<div class="drag-text">
								<span class="dashicons dashicons-cloud-upload" style="font-size: 45px; width:45px; height:45px; color: var(--vg-primary); margin: 0 auto 10px auto; display: block;"></span>
								<h3><?php esc_html_e( 'ADD VIDEO BANNER / POSTER', 'new-video-gallery' ); ?></h3>
								<?php wp_nonce_field('vg_add_images', 'vg_add_images_nonce'); ?>
							</div>
						</div>
					</div>

					<div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
						<div class="gg-button-group">
							<button type="button" class="vg-btn vg-btn-secondary" onclick="return SortSlides('ASC');">
								<span class="dashicons dashicons-sort"></span> <?php esc_html_e('Sort A-Z', 'new-video-gallery'); ?>
							</button>
							<button type="button" class="vg-btn vg-btn-secondary" onclick="return SortSlides('DESC');">
								<span class="dashicons dashicons-sort"></span> <?php esc_html_e('Sort Z-A', 'new-video-gallery'); ?>
							</button>
						</div>
						<input type="button" id="remove-all-slides" name="remove-all-slides" class="vg-btn vg-btn-danger" value="<?php esc_attr_e( 'Delete All Videos', 'new-video-gallery' ); ?>">
					</div>

					<ul id="remove-slides" class="sbox vgp-listitems">
					<?php
					if (isset($gallery_settings['slide-ids']) && is_array($gallery_settings['slide-ids'])) {
						$count = 0;
						foreach($gallery_settings['slide-ids'] as $id) {
							$thumbnail = wp_get_attachment_image_src($id, 'medium', true);
							$attachment = get_post( $id );
							$image_link = isset($gallery_settings['slide-link'][$count]) ? $gallery_settings['slide-link'][$count] : '';
							$image_type = isset($gallery_settings['slide-type'][$count]) ? $gallery_settings['slide-type'][$count] : 'y';
							$image_desc = isset($gallery_settings['slide-desc'][$count]) ? $gallery_settings['slide-desc'][$count] : '';
							$poster_type = isset($gallery_settings['poster-type'][$count]) ? $gallery_settings['poster-type'][$count] : 'internal';
							?>
							<li class="slide">
								<div class="vg-image-preview">
									<div class="vg-image-controls">
										<div class="vg-move-handle" title="<?php esc_attr_e('Drag to reorder', 'new-video-gallery'); ?>"><span class="dashicons dashicons-move"></span></div>
										<a class="pw-trash-icon remove-single-slide" name="remove-slide" href="#" id="remove-slide" title="<?php esc_attr_e('Delete banner', 'new-video-gallery'); ?>"><span class="dashicons dashicons-trash"></span></a>
									</div>
									<?php
									$preview_url = $thumbnail[0];
									if ($poster_type === 'youtube' && $image_type === 'y') {
										$video_id = vg_extract_youtube_id($image_link);
										$preview_url = "https://img.youtube.com/vi/$video_id/hqdefault.jpg";
									} elseif ($poster_type === 'vimeo' && $image_type === 'v') {
										$video_id = vg_extract_vimeo_id($image_link);
										if (function_exists('vg_get_vimeo_thumbnail_url')) {
											$preview_url = vg_get_vimeo_thumbnail_url($video_id);
										}
									}
									?>
									<img class="new-slide" src="<?php echo esc_url($preview_url); ?>" data-original-src="<?php echo esc_url($thumbnail[0]); ?>" alt="<?php echo esc_html(get_the_title($id)); ?>">
								</div>
								<div class="vg-image-info">
									<input type="hidden" id="slide-ids[]" name="slide-ids[]" value="<?php echo esc_attr($id); ?>" />
									
									<select id="slide-type[]" name="slide-type[]" class="form-control sel_<?php echo esc_attr($id); ?>" style="width: 100%;" value="<?php echo esc_attr($image_type); ?>" >
										<option value="y" <?php selected($image_type, 'y'); ?>><?php esc_html_e( 'YouTube', 'new-video-gallery' ); ?></option>
										<option value="v" <?php selected($image_type, 'v'); ?>><?php esc_html_e( 'Vimeo', 'new-video-gallery' ); ?></option>
										<option value="image" <?php selected($image_type, 'image'); ?>><?php esc_html_e( 'Image Only', 'new-video-gallery' ); ?></option>
										<option value="t" <?php selected($image_type, 't'); ?> disabled><?php esc_html_e( 'Twitch (Pro)', 'new-video-gallery' ); ?></option>
										<option value="d" <?php selected($image_type, 'd'); ?> disabled><?php esc_html_e( 'Dailymotion (Pro)', 'new-video-gallery' ); ?></option>
										<option value="w" <?php selected($image_type, 'w'); ?> disabled><?php esc_html_e( 'Wistia (Pro)', 'new-video-gallery' ); ?></option>
										<option value="tk" <?php selected($image_type, 'tk'); ?> disabled><?php esc_html_e( 'TikTok (Pro)', 'new-video-gallery' ); ?></option>
										<option value="f" <?php selected($image_type, 'f'); ?> disabled><?php esc_html_e( 'Local Video (Pro)', 'new-video-gallery' ); ?></option>
									</select>
									
									<?php
									$placeholder = 'Video id or link';
									if ($image_type === 'f') {
										$placeholder = 'Video Link from media';
									} elseif ($image_type === 'image') {
										$placeholder = 'Add link';
									}
									?>
									<input type="text" name="slide-link[]" id="slide-link[]" style="width: 100%;" placeholder="<?php echo esc_attr($placeholder); ?>" value="<?php echo esc_attr($image_link); ?>">
									<input type="text" name="slide-title[]" id="slide-title[]" style="width: 100%;" placeholder="<?php esc_attr_e('Video Title', 'new-video-gallery'); ?>" value="<?php echo esc_attr(get_the_title($id)); ?>">
									<textarea name="slide-desc[]" id="slide-desc[]" placeholder="<?php esc_attr_e('Video Description', 'new-video-gallery'); ?>" style="height: 100px;width: 100%;"><?php echo esc_html($attachment->post_content); ?></textarea>
									
									<?php
									$is_fetched = ($poster_type === 'youtube' || $poster_type === 'vimeo' || $poster_type === 'twitch' || $poster_type === 'dailymotion' || $poster_type === 'wistia' || $poster_type === 'tiktok');
									?>
									<input type="hidden" class="poster-type-field poster-type-field_<?php echo esc_attr($id); ?>" name="poster-type[]" value="<?php echo esc_attr($poster_type); ?>">
									<div class="vg-poster-btn-group-wrapper vg-poster-btn-group-wrapper_<?php echo esc_attr($id); ?>" style="margin-top: 10px; display: none;">
										<span style="font-weight: 500; font-size: 12px; display: block; margin-bottom: 5px; color: #444;"><?php esc_html_e( 'Poster Image Source:', 'new-video-gallery' ); ?></span>
										<div class="vg-poster-btn-group" style="display: flex; gap: 8px;">
											<button type="button" class="button vg-btn-fetch-poster" style="flex: 1;<?php echo $is_fetched ? ' display: none;' : ''; ?>"><?php esc_html_e( 'Fetch Poster', 'new-video-gallery' ); ?></button>
											<button type="button" class="button vg-btn-revert-poster" style="flex: 1;<?php echo !$is_fetched ? ' display: none;' : ''; ?>"><?php esc_html_e( 'Revert to Uploaded', 'new-video-gallery' ); ?></button>
										</div>
									</div>
								</div>
							</li>
							<script>
								(function($) {
									var togglePosterGroup = function() {
										var slide = $(".sel_<?php echo esc_js($id); ?>").val();
										if (slide == 'y' || slide == 'v' || slide == 't' || slide == 'd' || slide == 'w' || slide == 'tk') {
											$(".vg-poster-btn-group-wrapper_<?php echo esc_js($id); ?>").show();
										} else {
											$(".vg-poster-btn-group-wrapper_<?php echo esc_js($id); ?>").hide();
											$(".poster-type-field_<?php echo esc_js($id); ?>").val('internal');
										}
									};
									togglePosterGroup();
									$(".sel_<?php echo esc_js($id); ?>").change(togglePosterGroup);
								})(jQuery);
							</script>
							<?php 
							$count++; 
						}
					}
					?>
					</ul>
				</div>

				<!-- YouTube API KEY and details -->
				<div id="youtube-gallery" class="awl-vg-card">
					<h3><?php esc_html_e('YouTube API Configuration', 'new-video-gallery'); ?></h3>
					<?php
					$video_gallery_api_key = isset($gallery_settings['video_gallery_api_key']) ? $gallery_settings['video_gallery_api_key'] : 'AIzaSyDLlnSIppxQEjiy4Rt5mYJDDHQQI-ynPwQ';
					$video_gallery_channel_link = isset($gallery_settings['video_gallery_channel_link']) ? $gallery_settings['video_gallery_channel_link'] : 'https://www.youtube.com/channel/UCqj36njQUT_HCvw6eHzN-hw';
					$yt_content_source = isset($gallery_settings['yt_content_source']) ? $gallery_settings['yt_content_source'] : 'all';
					$yt_selected_playlist_name = isset($gallery_settings['yt_selected_playlist_name']) ? $gallery_settings['yt_selected_playlist_name'] : '';
					$show_yt_options = (!empty($video_gallery_api_key) && !empty($video_gallery_channel_link));
					?>

					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'YouTube API Key', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Enter your YouTube API Key to load feeds.', 'new-video-gallery' ); ?> <a target="_blank" href="https://www.youtube.com/watch?v=44OBOSBd73M"><?php esc_html_e( 'Get API Key Video Guide', 'new-video-gallery' ); ?></a></p> 
						</div>
						<div class="awl-vg-setting-field">
							<input type="text" class="form-control" id="video_gallery_api_key" name="video_gallery_api_key" value="<?php echo esc_attr( $video_gallery_api_key ); ?>" style="width: 100%;">
						</div>
					</div>

					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'YouTube Channel Link', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Enter the full YouTube channel link or playlist ID.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<input type="text" class="form-control" id="video_gallery_channel_link" name="video_gallery_channel_link" value="<?php echo esc_attr($video_gallery_channel_link); ?>" style="width: 100%;">
							
							<div style="margin-top: 15px; display: flex; align-items: center; gap: 10px;">
								<button type="button" class="vg-btn vg-fetch-btn" id="vg-fetch-youtube-content" style="<?php echo $show_yt_options ? 'display: none;' : ''; ?>">
									<span class="dashicons dashicons-update vg-fetch-icon"></span> <?php esc_html_e( 'Fetch Available Playlists', 'new-video-gallery' ); ?>
								</button>
								<button type="button" class="vg-btn vg-disconnect-btn" id="vg-disconnect-youtube" style="background: #ea4335; color: #fff; <?php echo !$show_yt_options ? 'display: none;' : ''; ?>">
									<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Disconnect', 'new-video-gallery' ); ?>
								</button>
								<div id="vg-youtube-fetch-status" class="vg-fetch-status" style="font-weight: 500;">
									<?php if ($show_yt_options) {
										if ($yt_content_source === 'playlist' && !empty($yt_selected_playlist_name)) {
											echo '<span class="vg-fetch-status--success">✅ Connected: Playlist - ' . esc_html($yt_selected_playlist_name) . '</span>';
										} else {
											echo '<span class="vg-fetch-status--success">✅ Connected: All Channel Videos</span>';
										}
									} ?>
								</div>
							</div>
						</div>
					</div>

					<div class="awl-vg-setting-row yt-fetch-dependent-options" style="<?php echo $show_yt_options ? '' : 'display: none;'; ?>">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Content Source', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Choose to show all channel uploads or a specific playlist.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$yt_content_source = isset($gallery_settings['yt_content_source']) ? $gallery_settings['yt_content_source'] : 'all';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="yt_source_all" name="yt_content_source" value="all" <?php checked($yt_content_source, 'all'); ?>>
								<label for="yt_source_all"><?php esc_html_e( 'All Channel Videos', 'new-video-gallery' ); ?></label>
								
								<input type="radio" class="form-control" id="yt_source_playlist" name="yt_content_source" value="playlist" <?php checked($yt_content_source, 'playlist'); ?> disabled>
								<label for="yt_source_playlist" style="position: relative; opacity: 0.65; padding-right: 38px; cursor: not-allowed;"><?php esc_html_e( 'Specific Playlist', 'new-video-gallery' ); ?> <span class="vg-pro-badge" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: #10b981; color: #fff; font-size: 8px; font-weight: bold; padding: 2px 5px; border-radius: 3px; text-transform: uppercase; line-height: 1;">Pro</span></label>
							</div>
						</div>
					</div>

					<div class="awl-vg-setting-row yt-playlist-select-row yt-fetch-dependent-options" style="<?php echo ($show_yt_options && $yt_content_source === 'playlist') ? '' : 'display: none;'; ?>">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Select Playlist', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the playlist to display.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$yt_selected_playlist_id = isset($gallery_settings['yt_selected_playlist_id']) ? $gallery_settings['yt_selected_playlist_id'] : '';
							$yt_selected_playlist_name = isset($gallery_settings['yt_selected_playlist_name']) ? $gallery_settings['yt_selected_playlist_name'] : '';
							?>
							<input type="hidden" id="yt_selected_playlist_name" name="yt_selected_playlist_name" value="<?php echo esc_attr($yt_selected_playlist_name); ?>">
							<select id="yt_selected_playlist_id" name="yt_selected_playlist_id" class="selectbox_settings" style="width: 300px;">
								<?php if (!empty($yt_selected_playlist_id)) { ?>
									<option value="<?php echo esc_attr($yt_selected_playlist_id); ?>" selected><?php echo esc_html($yt_selected_playlist_name ?: $yt_selected_playlist_id); ?></option>
								<?php } else { ?>
									<option value=""><?php esc_html_e( 'Click Fetch button above first', 'new-video-gallery' ); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>


			<!-- Tab 2: Gallery Configuration -->
			<div class="awl-vg-tab-content" id="tab-config">
				<div class="awl-vg-card">
					<h3><?php esc_html_e('Grid Layout & Alignment', 'new-video-gallery'); ?></h3>

					<!-- Background Sync Interval -->
					<div class="awl-vg-setting-row api-yes-config-setting" style="<?php echo ($video_gallery_option !== 'no_api') ? '' : 'display:none;'; ?>">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'API Background Sync Syncing', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select how often to fetch playlist/feed changes automatically in the background.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<select id="bg_sync_interval" name="bg_sync_interval" class="selectbox_settings" style="width: 300px;">
								<option value="disabled" <?php selected($bg_sync_interval, 'disabled'); ?>><?php esc_html_e( 'Disabled (Cache on save only)', 'new-video-gallery' ); ?></option>
								<option value="twicedaily" <?php selected($bg_sync_interval, 'twicedaily'); ?>><?php esc_html_e( 'Every 12 Hours (Twice Daily)', 'new-video-gallery' ); ?></option>
								<option value="daily" <?php selected($bg_sync_interval, 'daily'); ?>><?php esc_html_e( 'Every 24 Hours (Daily)', 'new-video-gallery' ); ?></option>
								<option value="hourly" <?php selected($bg_sync_interval, 'hourly'); ?>><?php esc_html_e( 'Every Hour (Hourly)', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Thumbnail Spacing -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Thumbnail Spacing (Gap)', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Adjust the gap in pixels between thumbnails in the grid to 8px.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$is_spacing_enabled = ($thumb_spacing > 0) ? 'yes' : 'no';
							?>
							<div class="vg-segmented-control">
								<input type="radio" id="thumb_spacing_yes" name="thumb_spacing_toggle" value="yes" <?php checked($is_spacing_enabled, 'yes'); ?>>
								<label for="thumb_spacing_yes"><?php esc_html_e('Yes', 'new-video-gallery'); ?></label>
								<input type="radio" id="thumb_spacing_no" name="thumb_spacing_toggle" value="no" <?php checked($is_spacing_enabled, 'no'); ?>>
								<label for="thumb_spacing_no"><?php esc_html_e('No', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Columns large desktops -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Columns on Large Desktops', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the number of columns to show on extra-large desktop screens.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<select id="col_large_desktops" name="col_large_desktops" class="selectbox_settings" style="width: 300px;">
								<?php foreach (array(1, 2, 3, 4, 6) as $i) { ?>
									<option value="<?php echo $i; ?>" <?php selected($col_large_desktops_val, $i); ?>><?php echo $i; ?> <?php echo ($i === 1) ? esc_html__( 'Column', 'new-video-gallery' ) : esc_html__( 'Columns', 'new-video-gallery' ); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<!-- Columns desktops -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Columns on Desktops', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the number of columns to show on standard computer screens.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<select id="col_desktops" name="col_desktops" class="selectbox_settings" style="width: 300px;">
								<?php foreach (array(1, 2, 3, 4, 6) as $i) { ?>
									<option value="<?php echo $i; ?>" <?php selected($col_desktops_val, $i); ?>><?php echo $i; ?> <?php echo ($i === 1) ? esc_html__( 'Column', 'new-video-gallery' ) : esc_html__( 'Columns', 'new-video-gallery' ); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<!-- Columns tablets -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Columns on Tablets', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the number of columns to show on tablet devices.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<select id="col_tablets" name="col_tablets" class="selectbox_settings" style="width: 300px;">
								<?php foreach (array(1, 2, 3, 4, 6) as $i) { ?>
									<option value="<?php echo $i; ?>" <?php selected($col_tablets_val, $i); ?>><?php echo $i; ?> <?php echo ($i === 1) ? esc_html__( 'Column', 'new-video-gallery' ) : esc_html__( 'Columns', 'new-video-gallery' ); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<!-- Columns phones -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Columns on Phones', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the number of columns to show on mobile phone screens.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<select id="col_phones" name="col_phones" class="selectbox_settings" style="width: 300px;">
								<?php foreach (array(1, 2, 3, 4) as $i) { ?>
									<option value="<?php echo $i; ?>" <?php selected($col_phones_val, $i); ?>><?php echo $i; ?> <?php echo ($i === 1) ? esc_html__( 'Column', 'new-video-gallery' ) : esc_html__( 'Columns', 'new-video-gallery' ); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<!-- Thumbnail sorting order -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Thumbnail Sorting Order', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Select the order of videos in the gallery (Old First, New First, or Random).', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$thumbnail_order = isset($gallery_settings['thumbnail_order']) ? $gallery_settings['thumbnail_order'] : 'ASC';
							?>
							<div class="vg-segmented-control">
								<input type="radio" name="thumbnail_order" id="thumbnail_order_asc" value="ASC" <?php checked($thumbnail_order, 'ASC'); ?>>
								<label for="thumbnail_order_asc"><?php esc_html_e('Old First', 'new-video-gallery'); ?></label>
								<input type="radio" name="thumbnail_order" id="thumbnail_order_desc" value="DESC" <?php checked($thumbnail_order, 'DESC'); ?>>
								<label for="thumbnail_order_desc"><?php esc_html_e('New First', 'new-video-gallery'); ?></label>
								<input type="radio" name="thumbnail_order" id="thumbnail_order_random" value="RANDOM" <?php checked($thumbnail_order, 'RANDOM'); ?>>
								<label for="thumbnail_order_random"><?php esc_html_e('Random', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Gallery Layout Mode (Locked) -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Grid Layout Mode', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Choose between a fluid Masonry grid or a Fixed grid with equal-height cards.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<select id="gallery_layout_mode" class="selectbox_settings" style="width: 300px; background-color: #f6f7f7; cursor: not-allowed;" disabled>
								<option value="fixed" selected><?php esc_html_e( 'Fixed Grid (Equal Heights)', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Frontend Live Search (Locked) -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Enable Live Search', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Add a real-time keyword search bar above the gallery grid.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
								<input type="radio" class="form-control" id="enable_live_search_yes" value="true" disabled>
								<label for="enable_live_search_yes"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="enable_live_search_no" value="false" checked disabled>
								<label for="enable_live_search_no"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>
				</div>

				<div class="awl-vg-card">
					<h3><?php esc_html_e('Video Thumbnail Resolution', 'new-video-gallery'); ?></h3>

					<!-- Thumbnail Resolution -->
					<div class="awl-vg-setting-row no-api-config-setting">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Thumbnail Image Resolution', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Choose the image size of manual/local uploaded thumbnails in the grid.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$gal_thumb_size = isset($gallery_settings['gal_thumb_size']) ? $gallery_settings['gal_thumb_size'] : 'full';
							?>
							<select id="gal_thumb_size" name="gal_thumb_size" class="selectbox_settings" style="width: 300px;">
								<option value="thumbnail" <?php selected($gal_thumb_size, 'thumbnail'); ?>><?php esc_html_e( 'Thumbnail – 150 × 150', 'new-video-gallery' ); ?></option>
								<option value="medium" <?php selected($gal_thumb_size, 'medium'); ?>><?php esc_html_e( 'Medium – 300 × 169', 'new-video-gallery' ); ?></option>
								<option value="large" <?php selected($gal_thumb_size, 'large'); ?>><?php esc_html_e( 'Large – 840 × 473', 'new-video-gallery' ); ?></option>
								<option value="full" <?php selected($gal_thumb_size, 'full'); ?>><?php esc_html_e( 'Full Size – 1280 × 720', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>

					<!-- YouTube API Thumbnail Resolution -->
					<div class="awl-vg-setting-row YouTube-yes-config-setting">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'YouTube Poster Resolution', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the resolution of fetched YouTube thumbnail images.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$gal_youtube_thumb_size = isset($gallery_settings['gal_youtube_thumb_size']) ? $gallery_settings['gal_youtube_thumb_size'] : 'medium';
							?>
							<select id="gal_youtube_thumb_size" name="gal_youtube_thumb_size" class="selectbox_settings" style="width: 300px;">
								<option value="default" <?php selected($gal_youtube_thumb_size, 'default'); ?>><?php esc_html_e( 'Default – 120 × 90', 'new-video-gallery' ); ?></option>
								<option value="medium" <?php selected($gal_youtube_thumb_size, 'medium'); ?>><?php esc_html_e( 'Medium – 320 × 180', 'new-video-gallery' ); ?></option>
								<option value="high" <?php selected($gal_youtube_thumb_size, 'high'); ?>><?php esc_html_e( 'High – 480 × 360', 'new-video-gallery' ); ?></option>
								<option value="standard" <?php selected($gal_youtube_thumb_size, 'standard'); ?>><?php esc_html_e( 'Standard – 640 × 480', 'new-video-gallery' ); ?></option>
								<option value="maxres" <?php selected($gal_youtube_thumb_size, 'maxres'); ?>><?php esc_html_e( 'Max Resolution – 1280 × 720', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Vimeo API Thumbnail Resolution -->
					<div class="awl-vg-setting-row Vimeo-yes-config-setting">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Vimeo Poster Resolution', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the resolution of fetched Vimeo thumbnail images.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$gal_vimeo_thumb_size = isset($gallery_settings['gal_vimeo_thumb_size']) ? $gallery_settings['gal_vimeo_thumb_size'] : 'medium';
							?>
							<select id="gal_vimeo_thumb_size" name="gal_vimeo_thumb_size" class="selectbox_settings" style="width: 300px;">
								<option value="small" <?php selected($gal_vimeo_thumb_size, 'small'); ?>><?php esc_html_e( 'Small – 295 × 166', 'new-video-gallery' ); ?></option>
								<option value="medium" <?php selected($gal_vimeo_thumb_size, 'medium'); ?>><?php esc_html_e( 'Medium – 640 × 360', 'new-video-gallery' ); ?></option>
								<option value="large" <?php selected($gal_vimeo_thumb_size, 'large'); ?>><?php esc_html_e( 'Large – 960 × 540', 'new-video-gallery' ); ?></option>
								<option value="huge" <?php selected($gal_vimeo_thumb_size, 'huge'); ?>><?php esc_html_e( 'Huge – 1280 × 720', 'new-video-gallery' ); ?></option>
								<option value="original" <?php selected($gal_vimeo_thumb_size, 'original'); ?>><?php esc_html_e( 'Original – 1920 × 1080', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Twitch API Thumbnail Resolution -->
					<div class="awl-vg-setting-row Twitch-yes-config-setting">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Twitch Poster Resolution', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the resolution of fetched Twitch thumbnail images.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$gal_twitch_thumb_size = isset($gallery_settings['gal_twitch_thumb_size']) ? $gallery_settings['gal_twitch_thumb_size'] : 'medium';
							?>
							<select id="gal_twitch_thumb_size" name="gal_twitch_thumb_size" class="selectbox_settings" style="width: 300px;">
								<option value="small" <?php selected($gal_twitch_thumb_size, 'small'); ?>><?php esc_html_e( 'Small – 320 × 180', 'new-video-gallery' ); ?></option>
								<option value="medium" <?php selected($gal_twitch_thumb_size, 'medium'); ?>><?php esc_html_e( 'Medium – 640 × 360', 'new-video-gallery' ); ?></option>
								<option value="large" <?php selected($gal_twitch_thumb_size, 'large'); ?>><?php esc_html_e( 'Large – 1280 × 720', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Wistia API Thumbnail Resolution -->
					<div class="awl-vg-setting-row Wistia-yes-config-setting">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Wistia Poster Resolution', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Select the resolution of fetched Wistia thumbnail images.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$gal_wistia_thumb_size = isset($gallery_settings['gal_wistia_thumb_size']) ? $gallery_settings['gal_wistia_thumb_size'] : 'medium';
							?>
							<select id="gal_wistia_thumb_size" name="gal_wistia_thumb_size" class="selectbox_settings" style="width: 300px;">
								<option value="small" <?php selected($gal_wistia_thumb_size, 'small'); ?>><?php esc_html_e( 'Small – 320 × 180', 'new-video-gallery' ); ?></option>
								<option value="medium" <?php selected($gal_wistia_thumb_size, 'medium'); ?>><?php esc_html_e( 'Medium – 640 × 360', 'new-video-gallery' ); ?></option>
								<option value="large" <?php selected($gal_wistia_thumb_size, 'large'); ?>><?php esc_html_e( 'Large – 960 × 540', 'new-video-gallery' ); ?></option>
								<option value="original" <?php selected($gal_wistia_thumb_size, 'original'); ?>><?php esc_html_e( 'Original – 1920 × 1080', 'new-video-gallery' ); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<!-- Tab 3: Card & Frame Styles -->
			<div class="awl-vg-tab-content" id="tab-card-design">
				<div class="awl-vg-card">
					<h3><?php esc_html_e('Thumbnail Images & Badges', 'new-video-gallery'); ?></h3>

					<!-- Thumbnail Corner Radius -->
					<div class="awl-vg-setting-row" id="thumb_radius_row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Thumbnail Corner Radius', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Set the border-radius roundness of thumbnail cards to 8px.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$is_radius_enabled = ($thumb_border_radius > 0) ? 'yes' : 'no';
							?>
							<div class="vg-segmented-control">
								<input type="radio" id="thumb_radius_yes" name="thumb_border_radius_toggle" value="yes" <?php checked($is_radius_enabled, 'yes'); ?>>
								<label for="thumb_radius_yes"><?php esc_html_e('Yes', 'new-video-gallery'); ?></label>
								<input type="radio" id="thumb_radius_no" name="thumb_border_radius_toggle" value="no" <?php checked($is_radius_enabled, 'no'); ?>>
								<label for="thumb_radius_no"><?php esc_html_e('No', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Grayscale Effect -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Grayscale Effect', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Display thumbnail images in black and white, turning to full color on hover.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field" style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
							<div class="vg-segmented-control">
								<input type="radio" id="gray_yes" name="image_grayscale" value="1" <?php checked($image_grayscale, 1); ?> />
								<label for="gray_yes"><?php esc_html_e('Yes', 'new-video-gallery'); ?></label>
								<input type="radio" id="gray_no" name="image_grayscale" value="0" <?php checked($image_grayscale, 0); ?> />
								<label for="gray_no"><?php esc_html_e('No', 'new-video-gallery'); ?></label>
							</div>

							<div class="grayscale_pct_wrapper" style="display: <?php echo ($image_grayscale == 0) ? 'none' : 'inline-flex'; ?>; align-items: center; gap: 8px;">
								<span style="font-weight: 500; font-size: 13px; color: #72777c;"><?php esc_html_e('Grayscale Amount:', 'new-video-gallery'); ?></span>
								<input type="number" id="grayscale_percentage" name="grayscale_percentage" min="0" max="100" value="<?php echo esc_attr($grayscale_percentage); ?>" class="selectbox_settings" style="width: 80px; padding: 6px 10px; text-align: center;" />
								<span style="font-size: 13px; color: #72777c;">%</span>
							</div>
						</div>
					</div>

					<!-- Video icon visibility -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Play Icon Overlay', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Show a play button overlay icon on top of thumbnail images.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$video_icon = isset($gallery_settings['video_icon']) ? $gallery_settings['video_icon'] : 'false';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="video_icon_yes" name="video_icon" value="true" <?php checked($video_icon, 'true'); ?>>
								<label for="video_icon_yes"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="video_icon_no" name="video_icon" value="false" <?php checked($video_icon, 'false'); ?>>
								<label for="video_icon_no"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Icon & Source Badge Display -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Icon & Tag Hover Behavior', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Show play overlays and platform tags at all times, or only on mouse hover.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$thumb_icon_tag_display = isset($gallery_settings['thumb_icon_tag_display']) ? $gallery_settings['thumb_icon_tag_display'] : 'hover';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="thumb_icon_tag_display_hover" name="thumb_icon_tag_display" value="hover" <?php checked($thumb_icon_tag_display, 'hover'); ?>>
								<label for="thumb_icon_tag_display_hover"><?php esc_html_e( 'Show on Hover', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="thumb_icon_tag_display_always" name="thumb_icon_tag_display" value="always" <?php checked($thumb_icon_tag_display, 'always'); ?>>
								<label for="thumb_icon_tag_display_always"><?php esc_html_e( 'Always Show', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Thumbnail Aspect Ratio (Locked) -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Thumbnail Aspect Ratio', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Crop all thumbnail images to a consistent aspect ratio shape.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$thumbnail_aspect_ratio = isset($gallery_settings['thumbnail_aspect_ratio']) ? $gallery_settings['thumbnail_aspect_ratio'] : 'auto';
							?>
							<select class="form-control" id="thumbnail_aspect_ratio" style="min-width: 250px; background-color: #f6f7f7; cursor: not-allowed;" disabled>
								<option value="3-2" selected><?php esc_html_e('3:2 (Classic Photo/DSLR)', 'new-video-gallery'); ?></option>
							</select>
						</div>
					</div>

					<!-- Platform Source Tag (Locked) -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Platform Source Tag', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Show a small platform source badge (YouTube, Vimeo, Twitch, Local) on the thumbnail.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
								<input type="radio" class="form-control" id="show_video_platform_tag_yes" value="true" checked disabled>
								<label for="show_video_platform_tag_yes"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="show_video_platform_tag_no" value="false" disabled>
								<label for="show_video_platform_tag_no"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Icon & Badge Color Mode (Locked) -->
					<div class="awl-vg-setting-row" id="vg_icon_badge_color_mode_row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Icon & Badge Color Mode', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Choose color mode for the play button and platform source badge: Source Color (brand-specific) or Custom Color.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
								<input type="radio" class="form-control" id="vg_icon_badge_color_mode_source" value="source" checked disabled>
								<label for="vg_icon_badge_color_mode_source"><?php esc_html_e( 'Source Color', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="vg_icon_badge_color_mode_custom" value="custom" disabled>
								<label for="vg_icon_badge_color_mode_custom"><?php esc_html_e( 'Custom Color', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Icon & Badge Custom Color Picker (Locked) -->
					<div class="awl-vg-setting-row" id="vg_icon_badge_custom_color_row" style="opacity: 0.65; pointer-events: none;">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Icon & Badge Custom Color', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Pick a custom background color for the play overlay icon and source badge.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$vg_icon_badge_custom_color = isset($gallery_settings['vg_icon_badge_custom_color']) ? $gallery_settings['vg_icon_badge_custom_color'] : '#4f46e5';
							?>
							<input type="text" value="<?php echo esc_attr($vg_icon_badge_custom_color); ?>" style="background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; padding: 6px; border-radius: 4px;" disabled>
						</div>
					</div>
				</div>

				<div class="awl-vg-card" style="margin-top: 30px;">
					<h3><?php esc_html_e('Card Styling & Backgrounds', 'new-video-gallery'); ?></h3>

					<!-- Thumbnail Border -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Thumbnail Card Border', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Enable a custom card-style outline border around thumbnail cards.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control">
								<input type="radio" id="thumb_border_yes" name="thumb_border" value="1" <?php checked($thumb_border, 1); ?>>
								<label for="thumb_border_yes"><?php esc_html_e('Yes', 'new-video-gallery'); ?></label>
								<input type="radio" id="thumb_border_no" name="thumb_border" value="0" <?php checked($thumb_border, 0); ?>>
								<label for="thumb_border_no"><?php esc_html_e('No', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<div class="ig-border-options" <?php echo ($thumb_border == 0) ? 'style="display:none;"' : ''; ?>>
						<!-- Border Thickness -->
						<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
							<div class="awl-vg-setting-label">
								<h4><?php esc_html_e('Border Thickness', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
								<p><?php esc_html_e('Set the card border padding width in pixels.', 'new-video-gallery'); ?></p>
							</div>
							<div class="awl-vg-setting-field">
								<div style="display: flex; align-items: center; gap: 10px;">
									<input type="number" value="8" style="width: 100px; padding: 6px 10px; background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; border-radius: 4px;" disabled>
									<span style="font-size: 13px; color: #72777c;">px</span>
								</div>
							</div>
						</div>

						<!-- Border Color & Opacity -->
						<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
							<div class="awl-vg-setting-label">
								<h4><?php esc_html_e('Border Color & Opacity', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
								<p><?php esc_html_e('Select color and opacity for the card border.', 'new-video-gallery'); ?></p>
							</div>
							<div class="awl-vg-setting-field" style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<span style="font-size: 13px; color: #72777c; font-weight: 500;"><?php esc_html_e('Color:', 'new-video-gallery'); ?></span>
									<input type="text" value="#eef2f6" style="background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; padding: 6px; border-radius: 4px;" disabled>
								</div>
								<div style="display: flex; align-items: center; gap: 8px;">
									<span style="font-size: 13px; color: #72777c; font-weight: 500;"><?php esc_html_e('Opacity (%):', 'new-video-gallery'); ?></span>
									<input type="number" value="100" style="width: 80px; padding: 6px 10px; text-align: center; background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; border-radius: 4px;" disabled>
								</div>
							</div>
						</div>

						<!-- Card Background Color & Opacity -->
						<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
							<div class="awl-vg-setting-label">
								<h4><?php esc_html_e('Card Background & Opacity', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
								<p><?php esc_html_e('Select background color and opacity for card elements.', 'new-video-gallery'); ?></p>
							</div>
							<div class="awl-vg-setting-field" style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<span style="font-size: 13px; color: #72777c; font-weight: 500;"><?php esc_html_e('Color:', 'new-video-gallery'); ?></span>
									<input type="text" value="#ffffff" style="background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; padding: 6px; border-radius: 4px;" disabled>
								</div>
								<div style="display: flex; align-items: center; gap: 8px;">
									<span style="font-size: 13px; color: #72777c; font-weight: 500;"><?php esc_html_e('Opacity (%):', 'new-video-gallery'); ?></span>
									<input type="number" value="100" style="width: 80px; padding: 6px 10px; text-align: center; background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; border-radius: 4px;" disabled>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Tab 4: Badges & Typography -->
			<div class="awl-vg-tab-content" id="tab-typography">
				<div class="awl-vg-card no-api-config-setting">
					<h3><?php esc_html_e('Card Content & Typography', 'new-video-gallery'); ?></h3>

					<!-- Video Title Settings -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Video Title', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Toggle visibility of video titles, select text color, and slider font size.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field" style="display: flex; align-items: center; flex-wrap: wrap; gap: 20px;">
							<?php
							$video_title = isset($gallery_settings['video_title']) ? $gallery_settings['video_title'] : 'hide';
							?>
							<div class="vg-segmented-control" style="margin-right: 10px;">
								<input type="radio" class="form-control" id="video_title_show" name="video_title" value="show" <?php checked($video_title, 'show'); ?>>
								<label for="video_title_show"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="video_title_hide" name="video_title" value="hide" <?php checked($video_title, 'hide'); ?>>
								<label for="video_title_hide"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>

							<!-- Title Typography Inline Wrapper -->
							<div class="title-font-setting-row" style="display: inline-flex; align-items: center; gap: 20px; flex-wrap: wrap;">
								<!-- Font Color -->
								<div class="inline-setting-item" style="display: flex; align-items: center; gap: 8px;">
									<span style="font-weight: 500; font-size: 13px; color: var(--vg-text);"><?php esc_html_e( 'Color:', 'new-video-gallery' ); ?></span>
									<?php
									$title_color = isset($gallery_settings['title_color']) ? $gallery_settings['title_color'] : '#383838';
									?>
									<input type="text" id="title_color" name="title_color" value="<?php echo esc_attr($title_color); ?>" default-color="#383838">
								</div>

								<!-- Font Size -->
								<div class="inline-setting-item" style="display: flex; align-items: center; gap: 8px; opacity: 0.65; pointer-events: none;">
									<span style="font-weight: 500; font-size: 13px; color: var(--vg-text);"><?php esc_html_e( 'Size:', 'new-video-gallery' ); ?> <span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 8px; font-weight: bold; padding: 1px 4px; border-radius: 3px; text-transform: uppercase; margin-left: 2px; display: inline-block; vertical-align: middle;">Pro</span></span>
									<div class="range-slider" style="margin: 0; min-width: 150px;">
										<input type="range" class="range-slider__range" value="18" min="10" max="32" step="1" disabled>
										<span class="range-slider__value">18</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Video Description Settings -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Video Description', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Toggle visibility of video descriptions, select text color, and slider font size.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field" style="display: flex; align-items: center; flex-wrap: wrap; gap: 20px;">
							<?php
							$video_desc = isset($gallery_settings['video_desc']) ? $gallery_settings['video_desc'] : 'hide';
							?>
							<div class="vg-segmented-control" style="margin-right: 10px;">
								<input type="radio" class="form-control" id="video_desc_show" name="video_desc" value="show" <?php checked($video_desc, 'show'); ?>>
								<label for="video_desc_show"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="video_desc_hide" name="video_desc" value="hide" <?php checked($video_desc, 'hide'); ?>>
								<label for="video_desc_hide"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>

							<!-- Description Typography Inline Wrapper -->
							<div class="desc-font-setting-row" style="display: inline-flex; align-items: center; gap: 20px; flex-wrap: wrap;">
								<!-- Font Color -->
								<div class="inline-setting-item" style="display: flex; align-items: center; gap: 8px;">
									<span style="font-weight: 500; font-size: 13px; color: var(--vg-text);"><?php esc_html_e( 'Color:', 'new-video-gallery' ); ?></span>
									<?php
									$desc_color = isset($gallery_settings['desc_color']) ? $gallery_settings['desc_color'] : '#72777C';
									?>
									<input type="text" id="desc_color" name="desc_color" value="<?php echo esc_attr($desc_color); ?>" default-color="#72777C">
								</div>

								<!-- Font Size -->
								<div class="inline-setting-item" style="display: flex; align-items: center; gap: 8px; opacity: 0.65; pointer-events: none;">
									<span style="font-weight: 500; font-size: 13px; color: var(--vg-text);"><?php esc_html_e( 'Size:', 'new-video-gallery' ); ?> <span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 8px; font-weight: bold; padding: 1px 4px; border-radius: 3px; text-transform: uppercase; margin-left: 2px; display: inline-block; vertical-align: middle;">Pro</span></span>
									<div class="range-slider" style="margin: 0; min-width: 150px;">
										<input type="range" class="range-slider__range" value="14" min="10" max="24" step="1" disabled>
										<span class="range-slider__value">14</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Title & Description Display Mode on Image -->
					<div id="thumb_title_hover_mode_wrapper" <?php echo (($video_title === 'show' || $video_desc === 'show') && $thumb_title_pos == 'hover') ? '' : 'style="display:none;"'; ?>>
						<div class="awl-vg-setting-row" id="thumb_title_hover_mode_row">
							<div class="awl-vg-setting-label">
								<h4><?php esc_html_e('Overlay Visibility Hover Mode', 'new-video-gallery'); ?></h4>
								<p><?php esc_html_e('When positioned on image, choose to show overlay on hover, hide it on hover, or always show it.', 'new-video-gallery'); ?></p>
							</div>
							<div class="awl-vg-setting-field">
								<div class="vg-segmented-control">
									<input type="radio" id="title_mode_show_hover" name="thumb_title_hover_mode" value="show_hover" <?php checked($thumb_title_hover_mode, 'show_hover'); ?>>
									<label for="title_mode_show_hover"><?php esc_html_e('Show on Hover', 'new-video-gallery'); ?></label>
									<input type="radio" id="title_mode_hide_hover" name="thumb_title_hover_mode" value="hide_hover" <?php checked($thumb_title_hover_mode, 'hide_hover'); ?>>
									<label for="title_mode_hide_hover"><?php esc_html_e('Hide on Hover', 'new-video-gallery'); ?></label>
									<input type="radio" id="title_mode_always" name="thumb_title_hover_mode" value="always" <?php checked($thumb_title_hover_mode, 'always'); ?>>
									<label for="title_mode_always"><?php esc_html_e('Always Show', 'new-video-gallery'); ?></label>
								</div>
							</div>
						</div>
					</div>

					<!-- Title & Description Position (Locked) -->
					<div id="thumb_title_pos_wrapper" <?php echo ($video_title === 'show' || $video_desc === 'show') ? '' : 'style="display:none;"'; ?>>
						<div class="awl-vg-setting-row" id="thumb_title_pos_row">
							<div class="awl-vg-setting-label">
								<h4><?php esc_html_e('Title & Description Position', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
								<p><?php esc_html_e('Choose whether the title & description appear on top of the image overlay, or below the card.', 'new-video-gallery'); ?></p>
							</div>
							<div class="awl-vg-setting-field">
								<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
									<input type="radio" id="title_pos_hover" value="hover" disabled>
									<label for="title_pos_hover" style="min-width: 140px;"><?php esc_html_e('On Image', 'new-video-gallery'); ?></label>
									<input type="radio" id="title_pos_below" value="below" checked disabled>
									<label for="title_pos_below" style="min-width: 140px;"><?php esc_html_e('Below Image', 'new-video-gallery'); ?></label>
								</div>
							</div>
						</div>
					</div>

					<!-- Title & Description Alignment (Locked) -->
					<div id="thumb_title_align_wrapper" <?php echo ($video_title === 'show' || $video_desc === 'show') ? '' : 'style="display:none;"'; ?>>
						<div class="awl-vg-setting-row" id="thumb_title_align_row">
							<div class="awl-vg-setting-label">
								<h4><?php esc_html_e('Title & Description Alignment', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
								<p><?php esc_html_e('Choose left, center, or right horizontal text alignment.', 'new-video-gallery'); ?></p>
							</div>
							<div class="awl-vg-setting-field">
								<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
									<input type="radio" id="title_align_left" value="left" disabled>
									<label for="title_align_left"><?php esc_html_e('Left', 'new-video-gallery'); ?></label>
									<input type="radio" id="title_align_center" value="center" checked disabled>
									<label for="title_align_center"><?php esc_html_e('Center', 'new-video-gallery'); ?></label>
									<input type="radio" id="title_align_right" value="right" disabled>
									<label for="title_align_right"><?php esc_html_e('Right', 'new-video-gallery'); ?></label>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- API Feed Extra Metadata Settings -->
				<div class="api-both-config-setting awl-vg-card" style="margin-top: 30px;">
					<h3><?php esc_html_e('API Feed Extra Metadata Settings', 'new-video-gallery'); ?></h3>


					<!-- API Video Title Display -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Video Title', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Show or hide video titles in the API feed.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$api_video_title = isset($gallery_settings['api_video_title']) ? $gallery_settings['api_video_title'] : 'show';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="api_video_title_show" name="api_video_title" value="show" <?php checked($api_video_title, 'show'); ?>>
								<label for="api_video_title_show"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="api_video_title_hide" name="api_video_title" value="hide" <?php checked($api_video_title, 'hide'); ?>>
								<label for="api_video_title_hide"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- API Title Character Limit -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Title Character Limit', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Limit the length of video titles (in characters). Leave empty or set to 0 for no limit.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$api_title_char_limit = isset($gallery_settings['api_title_char_limit']) ? intval($gallery_settings['api_title_char_limit']) : 50;
							?>
							<input type="number" class="selectbox_settings" style="width: 100px; padding: 6px 10px;" id="api_title_char_limit" name="api_title_char_limit" min="0" value="<?php echo esc_attr($api_title_char_limit); ?>">
						</div>
					</div>

					<!-- API Video Description Display -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Video Description', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Show or hide video descriptions in the API feed.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$api_video_desc = isset($gallery_settings['api_video_desc']) ? $gallery_settings['api_video_desc'] : 'show';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="api_video_desc_show" name="api_video_desc" value="show" <?php checked($api_video_desc, 'show'); ?>>
								<label for="api_video_desc_show"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="api_video_desc_hide" name="api_video_desc" value="hide" <?php checked($api_video_desc, 'hide'); ?>>
								<label for="api_video_desc_hide"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- API Description Character Limit -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Description Character Limit', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Limit the length of video descriptions (in characters). Leave empty or set to 0 for no limit.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$api_desc_char_limit = isset($gallery_settings['api_desc_char_limit']) ? intval($gallery_settings['api_desc_char_limit']) : 150;
							?>
							<input type="number" class="selectbox_settings" style="width: 100px; padding: 6px 10px;" id="api_desc_char_limit" name="api_desc_char_limit" min="0" value="<?php echo esc_attr($api_desc_char_limit); ?>">
						</div>
					</div>

					<!-- Thumbnail Corner Radius -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Thumbnail Corner Radius', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Set the border-radius roundness of thumbnail cards to 8px.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$is_radius_enabled = ($thumb_border_radius > 0) ? 'yes' : 'no';
							?>
							<div class="vg-segmented-control">
								<input type="radio" id="api_thumb_radius_yes" name="api_thumb_border_radius_toggle" value="yes" <?php checked($is_radius_enabled, 'yes'); ?>>
								<label for="api_thumb_radius_yes"><?php esc_html_e('Yes', 'new-video-gallery'); ?></label>
								<input type="radio" id="api_thumb_radius_no" name="api_thumb_border_radius_toggle" value="no" <?php checked($is_radius_enabled, 'no'); ?>>
								<label for="api_thumb_radius_no"><?php esc_html_e('No', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Play Icon Overlay -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Play Icon Overlay', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Show a play button overlay icon on top of thumbnail images.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$video_icon = isset($gallery_settings['video_icon']) ? $gallery_settings['video_icon'] : 'true';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="api_video_icon_yes" name="api_video_icon" value="true" <?php checked($video_icon, 'true'); ?>>
								<label for="api_video_icon_yes"><?php esc_html_e( 'Show', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="api_video_icon_no" name="api_video_icon" value="false" <?php checked($video_icon, 'false'); ?>>
								<label for="api_video_icon_no"><?php esc_html_e( 'Hide', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Icon & Source Badge Display -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Icon & Source Badge Display', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Choose whether play overlay icons and source badges show only on hover or are always visible.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$thumb_icon_tag_display = isset($gallery_settings['thumb_icon_tag_display']) ? $gallery_settings['thumb_icon_tag_display'] : 'hover';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="api_thumb_icon_tag_display_hover" name="api_thumb_icon_tag_display" value="hover" <?php checked($thumb_icon_tag_display, 'hover'); ?>>
								<label for="api_thumb_icon_tag_display_hover"><?php esc_html_e( 'Show on Hover', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="api_thumb_icon_tag_display_always" name="api_thumb_icon_tag_display" value="always" <?php checked($thumb_icon_tag_display, 'always'); ?>>
								<label for="api_thumb_icon_tag_display_always"><?php esc_html_e( 'Always Show', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- API Title & Description Alignment (Locked) -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Title & Description Alignment', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e('Choose left, center, or right horizontal text alignment for the API cards.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
								<input type="radio" id="api_title_align_left" value="left" disabled>
								<label for="api_title_align_left"><?php esc_html_e('Left', 'new-video-gallery'); ?></label>
								<input type="radio" id="api_title_align_center" value="center" checked disabled>
								<label for="api_title_align_center"><?php esc_html_e('Center', 'new-video-gallery'); ?></label>
								<input type="radio" id="api_title_align_right" value="right" disabled>
								<label for="api_title_align_right"><?php esc_html_e('Right', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Play Icon & Source Badge Color Mode (API) (Locked) -->
					<div class="awl-vg-setting-row" id="api_vg_icon_badge_color_mode_row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Icon & Badge Color Mode', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Choose color mode for the play button and platform source badge: Source Color (brand-specific) or Custom Color.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control" style="pointer-events: none; opacity: 0.65;">
								<input type="radio" class="form-control" id="api_vg_icon_badge_color_mode_source" value="source" checked disabled>
								<label for="api_vg_icon_badge_color_mode_source"><?php esc_html_e( 'Source Color', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="api_vg_icon_badge_color_mode_custom" value="custom" disabled>
								<label for="api_vg_icon_badge_color_mode_custom"><?php esc_html_e( 'Custom Color', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Play Icon & Source Badge Custom Color Picker (API) (Locked) -->
					<div class="awl-vg-setting-row" id="api_vg_icon_badge_custom_color_row" style="opacity: 0.65; pointer-events: none;">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Icon & Badge Custom Color', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Pick a custom background color for the play overlay icon and source badge.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$vg_icon_badge_custom_color = isset($gallery_settings['vg_icon_badge_custom_color']) ? $gallery_settings['vg_icon_badge_custom_color'] : '#4f46e5';
							?>
							<input type="text" value="<?php echo esc_attr($vg_icon_badge_custom_color); ?>" style="background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; padding: 6px; border-radius: 4px;" disabled>
						</div>
					</div>




				</div>

				<!-- Ads & Monetization Settings -->
				<div class="awl-vg-card" style="margin-top: 30px;">
					<h3><?php esc_html_e('Ads & Monetization Settings', 'new-video-gallery'); ?></h3>

					<!-- Ad Script -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label" style="align-items: flex-start; margin-top: 10px;">
							<h4><?php esc_html_e('Ad Script / HTML Code', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Paste your Google AdSense code or custom image banner HTML here. It will be injected exactly in the middle of the gallery grid.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$gallery_ad_script = isset($gallery_settings['gallery_ad_script']) ? $gallery_settings['gallery_ad_script'] : '';
							?>
							<textarea name="gallery_ad_script" class="form-control" rows="5" style="width: 100%; border: 1px solid #dcdcde; border-radius: 4px; padding: 10px;"><?php echo esc_textarea($gallery_ad_script); ?></textarea>
							<div class="vg-pro-notice" style="margin-top: 8px; font-size: 12px; color: #64748b;">
								<span style="font-weight: 600; color: #10b981;"><?php esc_html_e('Free Version Limit:', 'new-video-gallery'); ?></span> 
								<?php esc_html_e('Exactly 1 ad is inserted in the middle of the gallery. Custom ad frequency settings (e.g. ads after every 2, 3, or 4 videos) and script tag execution require ', 'new-video-gallery'); ?>
								<a href="https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/" target="_blank" style="color: #4f46e5; font-weight: 500; text-decoration: none;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></a>.
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Tab 4: Lightbox Settings -->
			<div class="awl-vg-tab-content" id="tab-lightbox">
				<div class="awl-vg-card">
					<h3><?php esc_html_e('Lightbox & Overlay Settings', 'new-video-gallery'); ?></h3>

					<!-- Auto Play -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Auto Play Videos', 'new-video-gallery' ); ?></h4>
							<p><?php esc_html_e( 'Start playing videos automatically when they open in the popup.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field">
							<?php
							$auto_play = isset($gallery_settings['auto_play']) ? $gallery_settings['auto_play'] : 'true';
							?>
							<div class="vg-segmented-control">
								<input type="radio" class="form-control" id="auto_play_yes" name="auto_play" value="true" <?php checked($auto_play, 'true'); ?>>
								<label for="auto_play_yes"><?php esc_html_e( 'Yes', 'new-video-gallery' ); ?></label>
								<input type="radio" class="form-control" id="auto_play_no" name="auto_play" value="false" <?php checked($auto_play, 'false'); ?>>
								<label for="auto_play_no"><?php esc_html_e( 'No', 'new-video-gallery' ); ?></label>
							</div>
						</div>
					</div>

					<!-- Loop Videos in Lightbox -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Loop Videos in Lightbox', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Start over from the first video after the last one is viewed.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php $show_lightbox_loop = isset($gallery_settings['show_lightbox_loop']) ? $gallery_settings['show_lightbox_loop'] : 1; ?>
							<div class="vg-segmented-control">
								<input type="radio" id="lb_loop_yes" name="show_lightbox_loop" value="1" <?php checked($show_lightbox_loop, 1); ?>>
								<label for="lb_loop_yes"><?php esc_html_e('Yes', 'new-video-gallery'); ?></label>
								<input type="radio" id="lb_loop_no" name="show_lightbox_loop" value="0" <?php checked($show_lightbox_loop, 0); ?>>
								<label for="lb_loop_no"><?php esc_html_e('No', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Show Title in Lightbox -->
					<div class="awl-vg-setting-row">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Title in Lightbox', 'new-video-gallery'); ?></h4>
							<p><?php esc_html_e('Show the video title at the bottom of the popup player.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<?php $show_lightbox_title = isset($gallery_settings['show_lightbox_title']) ? $gallery_settings['show_lightbox_title'] : 1; ?>
							<div class="vg-segmented-control">
								<input type="radio" id="lb_title_yes" name="show_lightbox_title" value="1" <?php checked($show_lightbox_title, 1); ?>>
								<label for="lb_title_yes"><?php esc_html_e('Show', 'new-video-gallery'); ?></label>
								<input type="radio" id="lb_title_no" name="show_lightbox_title" value="0" <?php checked($show_lightbox_title, 0); ?>>
								<label for="lb_title_no"><?php esc_html_e('Hide', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Show Description in Lightbox -->
					<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Description in Lightbox', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e('Show the video description text inside the popup player. (Note: Only a single line of description is displayed on tablets/PCs, and it is hidden on mobile screens.)', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control">
								<input type="radio" id="lb_desc_yes" value="1" disabled>
								<label for="lb_desc_yes"><?php esc_html_e('Show', 'new-video-gallery'); ?></label>
								<input type="radio" id="lb_desc_no" value="0" checked disabled>
								<label for="lb_desc_no"><?php esc_html_e('Hide', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Show Lightbox Thumbnails -->
					<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Lightbox Thumbnails', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e('Show a list of small video thumbnails inside the popup.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<div class="vg-segmented-control">
								<input type="radio" id="lb_thumbs_yes" value="1" disabled>
								<label for="lb_thumbs_yes"><?php esc_html_e('Show', 'new-video-gallery'); ?></label>
								<input type="radio" id="lb_thumbs_no" value="0" checked disabled>
								<label for="lb_thumbs_no"><?php esc_html_e('Hide', 'new-video-gallery'); ?></label>
							</div>
						</div>
					</div>

					<!-- Lightbox Transition Effect -->
					<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e('Lightbox Transition', 'new-video-gallery'); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e('Choose the animation style when switching videos in the popup.', 'new-video-gallery'); ?></p>
						</div>
						<div class="awl-vg-setting-field">
							<select class="selectbox_settings" style="max-width: 250px; width: 100%; background-color: #f6f7f7; cursor: not-allowed;" disabled>
								<option value="lg-slide" selected><?php esc_html_e('Slide (Default)', 'new-video-gallery'); ?></option>
							</select>
						</div>
					</div>

					<!-- Lightbox Overlay Color & Opacity -->
					<div class="awl-vg-setting-row" style="pointer-events: none; opacity: 0.65;">
						<div class="awl-vg-setting-label">
							<h4><?php esc_html_e( 'Overlay Color & Opacity', 'new-video-gallery' ); ?><span class="vg-pro-badge" style="background: #10b981; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; display: inline-block; vertical-align: middle;">Pro</span></h4>
							<p><?php esc_html_e( 'Choose the background color and transparency for the lightbox popup.', 'new-video-gallery' ); ?></p> 
						</div>
						<div class="awl-vg-setting-field" style="display: flex; align-items: center; flex-wrap: wrap; gap: 20px;">
							<!-- Color Picker -->
							<div class="inline-setting-item" style="display: flex; align-items: center; gap: 8px;">
								<span style="font-weight: 500; font-size: 13px; color: var(--vg-text);"><?php esc_html_e( 'Color:', 'new-video-gallery' ); ?></span>
								<input type="text" value="#000000" style="background-color: #f6f7f7; cursor: not-allowed; border: 1px solid #ddd; padding: 6px; border-radius: 4px;" disabled>
							</div>

							<!-- Opacity Slider -->
							<div class="inline-setting-item" style="display: flex; align-items: center; gap: 8px;">
								<span style="font-weight: 500; font-size: 13px; color: var(--vg-text);"><?php esc_html_e( 'Opacity:', 'new-video-gallery' ); ?></span>
								<div class="range-slider" style="margin: 0; min-width: 150px;">
									<input type="range" class="range-slider__range" value="0.9" min="0" max="1" step="0.1" disabled>
									<span class="range-slider__value">0.9</span>
								</div>
							</div>
						</div>
					</div>
				</div>



				</div>
			</div>

			<!-- Tab 6: Pro Features -->
			<div class="awl-vg-tab-content" id="tab-pro-features">
				<!-- Hero Upgrade Banner -->
				<div class="vg-pro-tab-hero">
					<h2><?php esc_html_e('Upgrade to Video Gallery Premium', 'new-video-gallery'); ?></h2>
					<p><?php esc_html_e('Unlock automated video platform feeds, advanced masonry layouts, live dashboard tracking analytics, infinite customization options, and premium customer support.', 'new-video-gallery'); ?></p>
					<div class="vg-pro-tab-hero-actions">
						<a href="https://awplife.com/demo/video-gallery-premium/" target="_blank" class="vg-pro-cta-btn vg-pro-cta-btn--demo">
							<span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Live Demo', 'new-video-gallery'); ?>
						</a>
						<a href="https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/" target="_blank" class="vg-pro-cta-btn vg-pro-cta-btn--purchase">
							<span class="dashicons dashicons-cart"></span> <?php esc_html_e('Purchase Now', 'new-video-gallery'); ?>
						</a>
					</div>
				</div>

				<!-- Features Grid -->
				<div class="vg-pro-features-grid">
					<!-- Feature 1 -->
					<div class="vg-pro-feature-card">
						<div class="vg-pro-feature-icon">
							<span class="dashicons dashicons-rss"></span>
						</div>
						<h4><?php esc_html_e('Vimeo API Import Feeds', 'new-video-gallery'); ?></h4>
						<p><?php esc_html_e('Auto-import feeds from Vimeo Showcase Albums and channels in addition to YouTube feeds.', 'new-video-gallery'); ?></p>
					</div>

					<!-- Feature 2 -->
					<div class="vg-pro-feature-card">
						<div class="vg-pro-feature-icon">
							<span class="dashicons dashicons-video-alt3"></span>
						</div>
						<h4><?php esc_html_e('Self-Hosted Video support', 'new-video-gallery'); ?></h4>
						<p><?php esc_html_e('Upload and stream local HTML5 MP4/WebM videos directly from the WordPress media library.', 'new-video-gallery'); ?></p>
					</div>

					<!-- Feature 3 -->
					<div class="vg-pro-feature-card">
						<div class="vg-pro-feature-icon">
							<span class="dashicons dashicons-admin-appearance"></span>
						</div>
						<h4><?php esc_html_e('Advanced Visual Styling', 'new-video-gallery'); ?></h4>
						<p><?php esc_html_e('Configure specific hover scale animations, custom backgrounds, card borders, and responsive grid shadows.', 'new-video-gallery'); ?></p>
					</div>

					<!-- Feature 4 -->
					<div class="vg-pro-feature-card">
						<div class="vg-pro-feature-icon">
							<span class="dashicons dashicons-format-gallery"></span>
						</div>
						<h4><?php esc_html_e('Custom Lightbox Opacity', 'new-video-gallery'); ?></h4>
						<p><?php esc_html_e('Select precise background overlay colors, slider thumbnails, custom transitions, and descriptions.', 'new-video-gallery'); ?></p>
					</div>

					<!-- Feature 5 -->
					<div class="vg-pro-feature-card">
						<div class="vg-pro-feature-icon">
							<span class="dashicons dashicons-money-alt"></span>
						</div>
						<h4><?php esc_html_e('Advanced In-Grid Ads', 'new-video-gallery'); ?></h4>
						<p><?php esc_html_e('Inject multiple ad codes and select customized banner frequency intervals inside the video loop.', 'new-video-gallery'); ?></p>
					</div>

					<!-- Feature 6 -->
					<div class="vg-pro-feature-card">
						<div class="vg-pro-feature-icon">
							<span class="dashicons dashicons-controls-play"></span>
						</div>
						<h4><?php esc_html_e('AJAX Load More Button', 'new-video-gallery'); ?></h4>
						<p><?php esc_html_e('Add an interactive Load More trigger button to load more API video items dynamically.', 'new-video-gallery'); ?></p>
					</div>
				</div>

				<!-- Comparison Table -->
				<div class="vg-comparison-wrapper">
					<h3 class="vg-comparison-title"><?php esc_html_e('Free vs. Premium Comparison', 'new-video-gallery'); ?></h3>
					<table class="vg-comparison-table">
						<thead>
							<tr>
								<th style="width: 40%;"><?php esc_html_e('Feature Description', 'new-video-gallery'); ?></th>
								<th style="width: 30%;"><?php esc_html_e('Free Version', 'new-video-gallery'); ?></th>
								<th style="width: 30%;"><?php esc_html_e('Premium Version', 'new-video-gallery'); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong><?php esc_html_e('Video Sources', 'new-video-gallery'); ?></strong></td>
								<td><?php esc_html_e('YouTube, Vimeo, Custom Images/Links', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('YouTube, Vimeo, Custom Links & Local HTML5 Videos', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Automated API Import Feeds', 'new-video-gallery'); ?></strong></td>
								<td><?php esc_html_e('YouTube Channel / Uploads Feed', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('YouTube Channels & Vimeo Channels/Showcases', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Interactive Page Builders', 'new-video-gallery'); ?></strong></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Gutenberg Block & Elementor Widget', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Gutenberg Block & Elementor Widget', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Grid Slide Count Limits', 'new-video-gallery'); ?></strong></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Unlimited Videos', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Unlimited Videos', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Lightbox Popup Player', 'new-video-gallery'); ?></strong></td>
								<td><?php esc_html_e('Standard Lightgallery popups', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Lightgallery + custom color, opacity, transitions & descriptions', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('In-Grid Ad Monetization', 'new-video-gallery'); ?></strong></td>
								<td><?php esc_html_e('1 Middle-Grid Ad banner (HTML only)', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Multiple Ads with custom injection frequencies (Google AdSense/JS)', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Custom Card Aesthetics', 'new-video-gallery'); ?></strong></td>
								<td><?php esc_html_e('Spacing, Rounded corners & Grayscale filter', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Advanced Backgrounds, border color/width & hover glow', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Playback Analytics Tracker', 'new-video-gallery'); ?></strong></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Logs & Graphs (YouTube, Vimeo, Images)', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Advanced Tracking logs (YouTube, Vimeo, Local)', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Lazy Load Optimization', 'new-video-gallery'); ?></strong></td>
								<td><span class="dashicons dashicons-no-alt" style="color: #ef4444;"></span></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Yes', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Gallery Loading Icon', 'new-video-gallery'); ?></strong></td>
								<td><span class="dashicons dashicons-no-alt" style="color: #ef4444;"></span></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Yes', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Custom CSS Field', 'new-video-gallery'); ?></strong></td>
								<td><span class="dashicons dashicons-no-alt" style="color: #ef4444;"></span></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Yes', 'new-video-gallery'); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e('Customer Support & Updates', 'new-video-gallery'); ?></strong></td>
								<td><?php esc_html_e('WordPress.org community forums', 'new-video-gallery'); ?></td>
								<td><span class="dashicons dashicons-yes" style="color: #10b981;"></span> <?php esc_html_e('Priority Helpdesk support & 1-Click updates', 'new-video-gallery'); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- Footer CTA -->
				<div class="vg-pro-cta" style="display: flex; flex-direction: column; align-items: center; gap: 15px; margin-top: 40px; text-align: center; width: 100%;">
					<div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
						<a href="https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/" target="_blank" class="vg-btn vg-btn-primary" style="height: auto; padding: 12px 30px; font-size: 15px; font-weight: 700;">
							<span class="dashicons dashicons-cart" style="margin-right: 5px;"></span> <?php esc_html_e('Buy Pro', 'new-video-gallery'); ?>
						</a>
						<a href="https://awplife.com/demo/video-gallery-premium/" target="_blank" class="vg-btn vg-btn-secondary" style="height: auto; padding: 12px 30px; font-size: 15px; font-weight: 700;">
							<span class="dashicons dashicons-visibility" style="margin-right: 5px;"></span> <?php esc_html_e('Pro Live Demo', 'new-video-gallery'); ?>
						</a>
					</div>
					<p style="margin: 15px 0 0 0; font-size: 13px; color: var(--vg-text-muted); font-weight: 500;"><?php esc_html_e('One-time payment. Lifetime updates. 100% Satisfaction.', 'new-video-gallery'); ?></p>
				</div>
			</div>

		</div>
	</div>

<?php
	// Nonce field for securing settings save post transactions
	wp_nonce_field( 'vg_save_settings', 'vg_save_nonce' );
?>
<script>
(function($) {
	// Helper to extract YouTube video ID from URL
	function vgExtractYoutubeId(url) {
		var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
		var match = url.match(regExp);
		return (match && match[2].length === 11) ? match[2] : url;
	}

	// Helper to extract Vimeo video ID from URL
	function vgExtractVimeoId(url) {
		var regExp = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
		var match = url.match(regExp);
		return match ? match[3] : url;
	}

	// Helper to extract Dailymotion video ID from URL
	function vgExtractDailymotionId(url) {
		var regExp = /^.+dailymotion.com\/(video|hub)\/([^_?#&]+)[^#]*$/;
		var match = url.match(regExp);
		if (match) {
			return match[2];
		}
		var regExpShort = /^.+dai.ly\/([^_?#&]+)[^#]*$/;
		var matchShort = url.match(regExpShort);
		return matchShort ? matchShort[1] : url;
	}

	// Helper to extract Wistia video ID from URL
	function vgExtractWistiaId(url) {
		var matchParam = url.match(/[?&](?:wmediaid|wvideo)=([a-zA-Z0-9]+)/i);
		if (matchParam) return matchParam[1];
		var match = url.match(/(?:wistia\.com|wi\.st)\/(?:medias|embed)\/([a-zA-Z0-9]+)/i);
		if (match) return match[1];
		var matchIframe = url.match(/fast\.wistia\.(?:net|com)\/embed\/(?:iframe|playlists)\/([a-zA-Z0-9]+)/i);
		if (matchIframe) return matchIframe[1];
		return url;
	}

	// Click handler for Fetch Poster button
	$(document).on('click', '.vg-btn-fetch-poster', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $li = $btn.closest('li.slide');
		var slideType = $li.find('select[name="slide-type[]"]').val();
		var videoUrl = $li.find('input[name="slide-link[]"]').val() || '';
		var posterType = 'internal';

		if (slideType === 'y') {
			var ytId = vgExtractYoutubeId(videoUrl);
			if (ytId) {
				posterType = 'youtube';
				var posterUrl = 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg';
				$li.find('.new-slide').attr('src', posterUrl);
				$btn.hide();
				$li.find('.vg-btn-revert-poster').show();
			}
		} else if (slideType === 'v') {
			var vimId = vgExtractVimeoId(videoUrl);
			if (vimId) {
				posterType = 'vimeo';
				$btn.addClass('button-disabled').text('Fetching...');
				$.getJSON('https://vimeo.com/api/v2/video/' + vimId + '.json')
					.done(function(data) {
						$btn.removeClass('button-disabled').text('Fetch Poster');
						if (data && data[0] && data[0].thumbnail_large) {
							$li.find('.new-slide').attr('src', data[0].thumbnail_large);
							$btn.hide();
							$li.find('.vg-btn-revert-poster').show();
						}
					})
					.fail(function() {
						$btn.removeClass('button-disabled').text('Fetch Poster');
						$li.find('.new-slide').attr('src', 'https://vimeo.com/api/v2/video/' + vimId + '.json');
						$btn.hide();
						$li.find('.vg-btn-revert-poster').show();
					});
			}
		} else if (slideType === 't') {
			posterType = 'twitch';
			$btn.addClass('button-disabled').text('Fetching...');
			$.post(ajaxurl, {
				action: 'nvgall_fetch_twitch_poster',
				post_id: $('#post_ID').val(),
				twitch_url: videoUrl
			}, function(response) {
				$btn.removeClass('button-disabled').text('Fetch Poster');
				if (response.success && response.data.thumbnail_url) {
					$li.find('.new-slide').attr('src', response.data.thumbnail_url);
					$btn.hide();
					$li.find('.vg-btn-revert-poster').show();
				} else {
					alert(response.data.message || 'Failed to fetch Twitch poster.');
				}
			});
		} else if (slideType === 'd') {
			var dmId = vgExtractDailymotionId(videoUrl);
			if (dmId) {
				posterType = 'dailymotion';
				$btn.addClass('button-disabled').text('Fetching...');
				$.getJSON('https://api.dailymotion.com/video/' + dmId + '?fields=thumbnail_720_url')
					.done(function(data) {
						$btn.removeClass('button-disabled').text('Fetch Poster');
						if (data && data.thumbnail_720_url) {
							$li.find('.new-slide').attr('src', data.thumbnail_720_url);
							$btn.hide();
							$li.find('.vg-btn-revert-poster').show();
						}
					})
					.fail(function() {
						$btn.removeClass('button-disabled').text('Fetch Poster');
					});
			}
		} else if (slideType === 'w') {
			var wisId = vgExtractWistiaId(videoUrl);
			if (wisId) {
				posterType = 'wistia';
				$btn.addClass('button-disabled').text('Fetching...');
				$.getJSON('https://fast.wistia.com/oembed?url=' + encodeURIComponent('https://home.wistia.com/medias/' + wisId))
					.done(function(data) {
						$btn.removeClass('button-disabled').text('Fetch Poster');
						if (data && data.thumbnail_url) {
							$li.find('.new-slide').attr('src', data.thumbnail_url);
							$btn.hide();
							$li.find('.vg-btn-revert-poster').show();
						}
					})
					.fail(function() {
						$btn.removeClass('button-disabled').text('Fetch Poster');
					});
			}
		}

		// Update hidden input value
		$li.find('.poster-type-field').val(posterType);
	});

	// Click handler for Revert button
	$(document).on('click', '.vg-btn-revert-poster', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $li = $btn.closest('li.slide');
		var originalSrc = $li.find('.new-slide').attr('data-original-src');

		// Restore original src
		if (originalSrc) {
			$li.find('.new-slide').attr('src', originalSrc);
		}

		// Update hidden input value
		$li.find('.poster-type-field').val('internal');

		// Toggle buttons visibility
		$btn.hide();
		$li.find('.vg-btn-fetch-poster').show();
	});
})(jQuery);
</script>