<?php
/**
 * Shared Card Rendering Functions
 * 
 * Single source of truth for all video card HTML output.
 * Used by manual galleries (video-gallery-code.php) and
 * all API galleries (youtube, vimeo, twitch, dailymotion, wistia, meta, tiktok).
 *
 * @package Video_Gallery_Premium
 */

if (!defined('ABSPATH')) exit;

/**
 * Render a single card for the manual (no_api) video gallery.
 *
 * @param array $args {
 *     @type int    $attachment_id       WP attachment ID
 *     @type string $video_type          'y','v','t','d','w','f','tk','image'
 *     @type string $video_id            Extracted video ID
 *     @type string $poster_type         'youtube','vimeo','twitch','dailymotion','wistia','tiktok','default','fetched'
 *     @type string $title               Video title
 *     @type string $description         Video description
 *     @type array  $gallery_settings    Full gallery settings array
 *     @type int    $post_id             Gallery post ID
 *     @type string $video_gallery_id    Gallery ID for CSS selectors
 *     @type string $gal_thumb_size      'thumbnail','medium','large','full'
 *     @type string $video_title         'show' or 'hide'
 *     @type string $video_desc          'show' or 'hide'
 *     @type string $video_icon          'true','false','hide'
 *     @type string $show_video_platform_tag  'true' or 'false'
 *     @type string $thumb_title_pos     'hover','below'
 *     @type string $thumb_title_hover_mode  'show_hover','always_show'
 *     @type int    $thumb_border        1 or 0
 *     @type int    $image_grayscale     1 or 0
 *     @type int    $show_lightbox_title 1 or 0
 *     @type int    $show_lightbox_desc  1 or 0
 *     @type string $gallery_layout_mode 'masonry' or 'fixed'
 *     @type string $lazy_loading        'yes' or 'no'
 *     @type string $col_classes         Column classes string (e.g. 'col-lg-4 col-md-3 col-sm-4 col-xs-6')
 * }
 */
function vg_render_manual_card($args) {
	// Extract args with defaults
	$attachment_id       = $args['attachment_id'];
	$video_type          = $args['video_type'];
	$video_id            = $args['video_id'];
	$poster_type         = isset($args['poster_type']) ? $args['poster_type'] : '';
	$title               = $args['title'];
	$description         = $args['description'];
	$gallery_settings    = $args['gallery_settings'];
	$post_id             = $args['post_id'];
	$video_gallery_id    = isset($args['video_gallery_id']) ? $args['video_gallery_id'] : $post_id;
	$gal_thumb_size      = isset($args['gal_thumb_size']) ? $args['gal_thumb_size'] : 'full';
	$video_title         = isset($args['video_title']) ? $args['video_title'] : 'hide';
	$video_desc          = isset($args['video_desc']) ? $args['video_desc'] : 'hide';
	$video_icon          = isset($args['video_icon']) ? $args['video_icon'] : 'true';
	$show_video_platform_tag = 'false';
	$thumb_title_pos     = 'below';
	$thumb_title_hover_mode = 'show_hover';
	$thumb_border        = isset($args['thumb_border']) ? intval($args['thumb_border']) : 0;
	$image_grayscale     = isset($args['image_grayscale']) ? intval($args['image_grayscale']) : 0;
	$show_lightbox_title = isset($args['show_lightbox_title']) ? intval($args['show_lightbox_title']) : 1;
	$show_lightbox_desc  = isset($args['show_lightbox_desc']) ? intval($args['show_lightbox_desc']) : 1;
	$gallery_layout_mode = 'fixed';
	$lazy_loading        = isset($args['lazy_loading']) ? $args['lazy_loading'] : 'yes';
	$col_classes         = isset($args['col_classes']) ? $args['col_classes'] : '';

	// --- Resolve thumbnail URL ---
	$thumb    = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
	$medium   = wp_get_attachment_image_src($attachment_id, 'medium', true);
	$large    = wp_get_attachment_image_src($attachment_id, 'large', true);
	$full     = wp_get_attachment_image_src($attachment_id, 'full', true);

	if ($gal_thumb_size === 'thumbnail') {
		$thumbnail_url = $thumb[0];
	} elseif ($gal_thumb_size === 'medium') {
		$thumbnail_url = $medium[0];
	} elseif ($gal_thumb_size === 'large') {
		$thumbnail_url = $large[0];
	} else {
		$thumbnail_url = $full[0];
	}

	// Platform-specific poster type overrides
	if ($poster_type === 'youtube' && $video_type === 'y') {
		$thumbnail_url = "https://img.youtube.com/vi/$video_id/hqdefault.jpg";
	}
	if ($poster_type === 'vimeo' && $video_type === 'v' && function_exists('vg_get_vimeo_thumbnail_url')) {
		$thumbnail_url = vg_get_vimeo_thumbnail_url($video_id);
	}


	// Lightbox thumbnail (use medium for non-API poster types)
	$lightbox_thumb_url = $thumbnail_url;
	if ($video_type === 'image' || $video_type === 'f' || $poster_type === 'default' || $poster_type === 'fetched') {
		if (!empty($medium[0])) {
			$lightbox_thumb_url = $medium[0];
		}
	}

	// --- Lightbox attributes ---
	$lightbox_href = '#';
	$data_html = '';
	$is_lightbox = true;

	if ($video_type === 'y') {
		$lightbox_href = 'https://www.youtube.com/watch?v=' . esc_attr($video_id);
	} elseif ($video_type === 'v') {
		$lightbox_href = 'https://vimeo.com/' . esc_attr($video_id);
	} elseif ($video_type === 't') {
		$lightbox_href = '#';
		$parent_domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : wp_parse_url(home_url(), PHP_URL_HOST);
		if (strpos($parent_domain, ':') !== false) {
			$parent_domain = explode(':', $parent_domain)[0];
		}
		if (strpos($video_id, 'clip_') === 0) {
			$real_clip_id = substr($video_id, 5);
			$embed_url = 'https://clips.twitch.tv/embed?clip=' . esc_attr($real_clip_id) . '&parent=' . esc_attr($parent_domain) . '&autoplay=false';
		} else {
			$real_video_id = is_numeric($video_id) ? 'v' . $video_id : $video_id;
			$embed_url = 'https://player.twitch.tv/?video=' . esc_attr($real_video_id) . '&parent=' . esc_attr($parent_domain) . '&autoplay=false';
		}
		$data_html = esc_attr("<iframe src='" . esc_url($embed_url) . "' frameborder='0' allowfullscreen='true' scrolling='no' height='100%' width='100%' style='position:absolute;top:0;left:0;'></iframe><video class='lg-html5' style='display:none;' muted playsinline></video>");
	} elseif ($video_type === 'd') {
		$lightbox_href = '#';
		$data_html = esc_attr("<iframe src='https://www.dailymotion.com/embed/video/" . esc_attr($video_id) . "?autoplay=1' frameborder='0' allowfullscreen='true' scrolling='no' height='100%' width='100%' style='position:absolute;top:0;left:0;'></iframe><video class='lg-html5' style='display:none;' muted playsinline></video>");
	} elseif ($video_type === 'w') {
		$lightbox_href = '#';
		$data_html = esc_attr("<iframe src='https://fast.wistia.net/embed/iframe/" . esc_attr($video_id) . "?autoPlay=true' frameborder='0' allowfullscreen='true' scrolling='no' height='100%' width='100%' style='position:absolute;top:0;left:0;'></iframe><video class='lg-html5' style='display:none;' muted playsinline></video>");
	} elseif ($video_type === 'f') {
		$lightbox_href = '#';
		$data_html = esc_attr("<video class='lg-video-object lg-html5' controls><source src='" . esc_url($video_id) . "' type='video/mp4'></video>");
	} elseif ($video_type === 'tk') {
		$lightbox_href = '#';
		$embed_url = 'https://www.tiktok.com/embed/v2/' . esc_attr($video_id);
		$data_html = esc_attr("<iframe src='" . esc_url($embed_url) . "' frameborder='0' allowfullscreen='true' scrolling='no' height='100%' width='100%' style='position:absolute;top:0;left:0;'></iframe><video class='lg-html5' style='display:none;' muted playsinline></video>");



	} elseif ($video_type === 'image') {
		if (!empty($video_id)) {
			$lightbox_href = $video_id;
			$is_lightbox = false;
		} else {
			$lightbox_href = $full[0];
		}
	}

	// Sub-HTML for lightbox captions
	$sub_html = '';
	if ($show_lightbox_title) {
		$sub_html .= '<h4>' . esc_attr($title) . '</h4>';
	}
	if ($show_lightbox_desc) {
		$sub_html .= '<p>' . esc_attr($description) . '</p>';
	}

	// Platform class
	$platform_classes = array(
		'y' => 'vg-card--youtube', 'v' => 'vg-card--vimeo', 't' => 'vg-card--twitch',
		'd' => 'vg-card--dailymotion', 'w' => 'vg-card--wistia', 'f' => 'vg-card--local',
		'tk' => 'vg-card--tiktok', 'image' => 'vg-card--image'
	);
	$platform_class = isset($platform_classes[$video_type]) ? $platform_classes[$video_type] : '';

	// --- OUTPUT ---
	?>
	<div class="vg_result_<?php echo esc_attr($video_gallery_id); ?> single-image <?php echo esc_attr($col_classes); ?>">
		<div class="vg-card vg-card--simple <?php echo ($thumb_border == 1) ? 'vg-has-card-border' : ''; ?> <?php echo $platform_class; ?>">

			<div class="vg-card__media">
				<a class="<?php echo $is_lightbox ? 'vgp-trigger' : ''; ?>" href="<?php echo esc_url($lightbox_href); ?>" <?php if (!empty($data_html)) { echo 'data-html="' . $data_html . '"'; } ?> data-thumb="<?php echo esc_url($lightbox_thumb_url); ?>" data-sub-html="<?php echo esc_attr($sub_html); ?>" <?php if (!$is_lightbox) { echo 'target="_blank"'; } ?>>
					<img class="vg-card__img <?php echo ($image_grayscale == 1) ? 'vg-grayscale' : ''; ?>" <?php echo ($lazy_loading === 'yes') ? 'loading="lazy"' : ''; ?> src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_html($title); ?>">
					<?php if ($video_icon != "hide") { ?>
						<div class="vg-card__overlay">
							<button class="vg-card__play video_icon_<?php echo esc_attr($post_id); ?>" aria-label="<?php echo ($video_type === 'image') ? 'View Image' : 'Play video'; ?>">
								<?php if ($video_type === 'image') { ?>
									<?php if ($is_lightbox) { ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #ffffff;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
									<?php } else { ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #ffffff;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
									<?php } ?>
								<?php } else { ?>
									<svg viewBox="0 0 24 24" fill="none" style="color: #ffffff;"><polygon points="6,3 20,12 6,21" fill="currentColor"/></svg>
								<?php } ?>
							</button>
						</div>
					<?php } ?>
					<?php if ($show_video_platform_tag === 'true') {
						echo vg_get_platform_badge_html($video_type, $is_lightbox);
					} ?>
					<?php if ($thumb_title_pos === 'hover' && (($video_title === 'show' && !empty(trim($title))) || ($video_desc === 'show' && !empty(trim($description))))) { 
						$overlay_mode_class = 'vg-overlay-' . str_replace('_', '-', $thumb_title_hover_mode);
						$overlay_class = 'vg-overlay-bottom ' . $overlay_mode_class;
					?>
						<div class="vg-overlay <?php echo esc_attr($overlay_class); ?>">
							<div style="display: flex; flex-direction: column; width: 100%; text-align: var(--vg-title-desc-align, center);">
								<?php if ($video_title === 'show' && !empty(trim($title))) { ?>
									<span class="vg-item-title"><?php echo esc_html($title); ?></span>
								<?php } ?>
								<?php if ($video_desc === 'show' && !empty(trim($description))) { ?>
									<span class="vg-item-desc" style="color: rgba(255,255,255,0.85); font-size: 12px; margin-top: 4px; display: block; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?php echo esc_html($description); ?></span>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</a>
			</div>

			<?php if ($thumb_title_pos === 'below') { ?>
				<?php if ($video_title === 'show' && !empty(trim($title))) { ?>
					<div class="vg-card__title-wrap">
						<h3 class="vg-card__title"><?php echo esc_html($title); ?></h3>
					</div>
				<?php } ?>
				<?php if ($video_desc === 'show' && !empty(trim($description))) { ?>
					<div class="vg-card__desc-wrap">
						<p class="vg-card__desc"><?php echo esc_html($description); ?></p>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
	<?php
}


/**
 * Render a single card for any API gallery.
 *
 * @param array $video {
 *     Video data from the API fetch function.
 *     @type string $id             Video ID
 *     @type string $title          Video title
 *     @type string $description    Video description
 *     @type string $thumbnail      Thumbnail URL
 *     @type string $duration       Formatted duration (e.g. "12:34")
 *     @type bool   $is_hd          Whether the video is HD
 *     @type string $views          Formatted view count
 *     @type string $likes          Formatted like count
 *     @type string $comments       Formatted comment count
 *     @type string $timeAgo        Human-readable time ago
 *     @type string $channel_title  Channel/creator name
 *     @type string $creator_avatar Creator avatar URL
 *     @type string $embed_url      Pre-built embed URL (Twitch)
 * }
 * @param array $settings {
 *     Display settings.
 *     @type string $platform             'youtube','vimeo','twitch','dailymotion','wistia','meta','tiktok'
 *     @type int    $post_id              Gallery post ID
 *     @type string $video_icon           'true','false','hide'
 *     @type int    $show_lightbox_title  1 or 0
 *     @type int    $show_lightbox_desc   1 or 0
 *     @type string $show_video_duration  'true' or 'false'
 *     @type string $show_video_hd_badge  'true' or 'false'
 *     @type string $show_video_channel_title 'true' or 'false'
 *     @type string $show_video_creator_avatar 'true' or 'false'
 *     @type string $show_video_platform_tag  'true' or 'false'
 *     @type string $api_video_title      'show' or 'hide'
 *     @type string $api_video_desc       'show' or 'hide'
 *     @type string $api_video_view       'true' or 'false'
 *     @type string $api_video_like       'true' or 'false'
 *     @type string $api_video_comments   'true' or 'false'
 *     @type string $api_video_publish_time 'true' or 'false'
 *     @type int    $api_title_char_limit  Character limit for title (0 = unlimited)
 *     @type int    $api_desc_char_limit   Character limit for description (0 = unlimited)
 *     @type string $lazy_loading         'yes' or 'no'
 *     @type string $meta_content_source  'facebook_page' or 'instagram_business' (Meta only)
 *     @type string $parent_domain        Parent domain for Twitch embeds
 * }
 */
function vg_render_api_card($video, $settings) {
	$platform     = $settings['platform'];
	$post_id      = $settings['post_id'];
	$video_icon   = isset($settings['video_icon']) ? $settings['video_icon'] : 'true';
	$show_lb_title = isset($settings['show_lightbox_title']) ? intval($settings['show_lightbox_title']) : 1;
	$show_lb_desc  = isset($settings['show_lightbox_desc']) ? intval($settings['show_lightbox_desc']) : 1;
	$api_video_title     = isset($settings['api_video_title']) ? $settings['api_video_title'] : 'show';
	$api_video_desc      = isset($settings['api_video_desc']) ? $settings['api_video_desc'] : 'show';
	$title_char_limit    = isset($settings['api_title_char_limit']) ? intval($settings['api_title_char_limit']) : 50;
	$desc_char_limit     = isset($settings['api_desc_char_limit']) ? intval($settings['api_desc_char_limit']) : 150;
	$lazy_loading        = isset($settings['lazy_loading']) ? $settings['lazy_loading'] : 'yes';

	// --- Lightbox sub-html ---
	$sub_html = '';
	if ($show_lb_title) {
		$sub_html .= '<h4>' . esc_attr($video['title']) . '</h4>';
	}
	if ($show_lb_desc) {
		$sub_html .= '<p>' . esc_attr($video['description']) . '</p>';
	}

	// --- Build href and data-html based on platform ---
	$lightbox_href = '#';
	$data_html = '';

	if ($platform === 'youtube') {
		$lightbox_href = 'https://www.youtube.com/watch?v=' . esc_attr($video['id']);
	}

	// Platform card class
	$card_class = 'vg-card--youtube';

	// Display title/desc with char limits
	$display_title = ($title_char_limit > 0) ? wp_html_excerpt($video['title'], $title_char_limit, '...') : $video['title'];
	$display_desc = ($desc_char_limit > 0) ? wp_html_excerpt($video['description'], $desc_char_limit, '...') : $video['description'];

	$show_title_flag = ($api_video_title !== 'hide' && !empty(trim($display_title)));
	$show_desc_flag = ($api_video_desc !== 'hide' && !empty(trim($display_desc)));
	$has_body = ($show_title_flag || $show_desc_flag);

	// --- OUTPUT ---
	?>
	<div class="video-item_<?php echo esc_attr($post_id); ?> vg-col">
		<div class="vg-card <?php echo $card_class; ?>">
			<div class="vg-card__media">
				<a class="vgp-trigger" href="<?php echo !empty($data_html) ? '#' : esc_url($lightbox_href); ?>" <?php if (!empty($data_html)) { echo 'data-html="' . $data_html . '"'; } ?> data-thumb="<?php echo esc_url($video['thumbnail']); ?>" data-sub-html="<?php echo esc_attr($sub_html); ?>">
					<img <?php echo ($lazy_loading === 'yes') ? 'loading="lazy"' : ''; ?> src="<?php echo esc_url($video['thumbnail']); ?>" alt="<?php echo esc_attr($video['title']); ?>">
					<?php if ($video_icon !== 'false' && $video_icon !== 'hide') { ?>
						<div class="vg-card__overlay">
							<button class="vg-card__play" aria-label="Play video">
								<svg viewBox="0 0 24 24" fill="none" style="color: #ffffff;"><polygon points="6,3 20,12 6,21" fill="currentColor"/></svg>
							</button>
						</div>
					<?php } ?>
				</a>
			</div>
			<?php if ($has_body) { ?>
			<div class="vg-card__body">
				<?php if ($show_title_flag) { ?>
					<h3 class="vg-card__title"><?php echo esc_html($display_title); ?></h3>
				<?php } ?>
				<?php if ($show_desc_flag) { ?>
					<p class="vg-card__desc"><?php echo esc_html($display_desc); ?></p>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php
}


/**
 * Get platform badge HTML for manual video gallery cards.
 */
function vg_get_platform_badge_html($video_type, $is_lightbox = true) {
	$badges = array(
		'y'  => '<span class="vg-card__source-badge vg-source--youtube"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M15.665 4.113a2.003 2.003 0 00-1.418-1.424C12.926 2.25 8 2.25 8 2.25s-4.926 0-6.247.439A2.003 2.003 0 00.335 4.113C0 5.434 0 8 0 8s0 2.566.335 3.887a2.003 2.003 0 001.418 1.424C3.074 13.75 8 13.75 8 13.75s4.926 0 6.247-.439a2.003 2.003 0 001.418-1.424C16 10.566 16 8 16 8s0-2.566-.335-3.887zM6.5 10.5V5.5L10.5 8l-4 2.5z"/></svg>YouTube</span>',
		'v'  => '<span class="vg-card__source-badge vg-source--vimeo"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M15.992 4.248c-.073 1.648-1.223 3.907-3.44 6.786C10.216 14.065 8.268 15.75 6.62 15.75c-1.04 0-1.924-.96-2.652-2.88-.488-1.788-.975-3.575-1.463-5.363-.527-1.928-1.092-2.892-1.695-2.892-.133 0-.6.28-1.4.838L0 5.88c.92-.81 1.828-1.62 2.724-2.436 1.24-1.08 2.164-1.652 2.772-1.716 1.452-.16 2.352.852 2.7 3.036.372 2.328.632 3.78.78 4.356.432 1.92.908 2.88 1.428 2.88.404 0 1.01-.636 1.818-1.908.808-1.272 1.244-2.244 1.312-2.916.144-1.236-.356-1.854-1.5-1.854-.532 0-1.076.12-1.632.372 1.084-3.54 3.152-5.262 6.204-5.16 2.256.072 3.312 1.532 3.168 4.38z"/></svg>Vimeo</span>',
		't'  => '<span class="vg-card__source-badge vg-source--twitch"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>Twitch</span>',
		'd'  => '<span class="vg-card__source-badge vg-source--dailymotion"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M19.78 5.43c-1.33-1.32-3.1-2.05-4.98-2.05h-5.6C5.55 3.38 2.62 6.3 2.62 9.92v4.16c0 3.62 2.93 6.54 6.58 6.54h5.6c1.88 0 3.65-.73 4.98-2.05c1.33-1.33 2.06-3.1 2.06-4.99V9.92c0-1.89-.73-3.66-2.06-4.99zm-4.98 10.72h-5.6c-1.54 0-2.8-1.25-2.8-2.79V9.92c0-1.54 1.26-2.79 2.8-2.79h5.6c1.54 0 2.8 1.25 2.8 2.79v3.44c0 1.54-1.26 2.79-2.8 2.79z"/></svg>Dailymotion</span>',
		'w'  => '<span class="vg-card__source-badge vg-source--wistia"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.5l-5-3.5 5-3.5v7zM17 13h-3v-2h3v2z"/></svg>Wistia</span>',
		'f'  => '<span class="vg-card__source-badge vg-source--self"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polygon points="10 11 15 14 10 17 10 11"></polygon></svg>Local</span>',
		'tk' => '<span class="vg-card__source-badge vg-source--tiktok"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.94-1.74-.22-.2-.41-.43-.6-.67v6.62c.03 2.24-.85 4.58-2.6 6.01-1.76 1.48-4.27 2.05-6.51 1.55-2.22-.44-4.22-1.92-5.18-3.99-1.04-2.15-1.02-4.85.12-6.97.94-1.83 2.73-3.23 4.81-3.66 1.11-.25 2.29-.18 3.39.14v4.1c-.8-.28-1.72-.34-2.52-.02-1.01.37-1.8 1.29-2.02 2.34-.29 1.19-.04 2.53.72 3.48.74.96 1.99 1.46 3.2 1.34 1.3-.07 2.53-.94 2.92-2.17.26-.74.24-1.57.25-2.35.02-3.67 0-7.34.01-11.01v-.01z"/></svg>TikTok</span>'
	);

	if ($video_type === 'image') {
		if ($is_lightbox) {
			return '<span class="vg-card__source-badge vg-source--image"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>Image</span>';
		} else {
			return '<span class="vg-card__source-badge vg-source--link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>Link</span>';
		}
	}

	return isset($badges[$video_type]) ? $badges[$video_type] : '';
}


/**
 * Get platform badge HTML for API gallery cards.
 */
function vg_get_api_platform_badge($platform, $meta_content_source = '') {
	$badges = array(
		'youtube'     => '<span class="vg-card__source-badge vg-source--youtube"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M15.665 4.113a2.003 2.003 0 00-1.418-1.424C12.926 2.25 8 2.25 8 2.25s-4.926 0-6.247.439A2.003 2.003 0 00.335 4.113C0 5.434 0 8 0 8s0 2.566.335 3.887a2.003 2.003 0 001.418 1.424C3.074 13.75 8 13.75 8 13.75s4.926 0 6.247-.439a2.003 2.003 0 001.418-1.424C16 10.566 16 8 16 8s0-2.566-.335-3.887zM6.5 10.5V5.5L10.5 8l-4 2.5z"/></svg>YouTube</span>',
		'vimeo'       => '<span class="vg-card__source-badge vg-source--vimeo"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M15.992 4.248c-.073 1.648-1.223 3.907-3.44 6.786C10.216 14.065 8.268 15.75 6.62 15.75c-1.04 0-1.924-.96-2.652-2.88-.488-1.788-.975-3.575-1.463-5.363-.527-1.928-1.092-2.892-1.695-2.892-.133 0-.6.28-1.4.838L0 5.88c.92-.81 1.828-1.62 2.724-2.436 1.24-1.08 2.164-1.652 2.772-1.716 1.452-.16 2.352.852 2.7 3.036.372 2.328.632 3.78.78 4.356.432 1.92.908 2.88 1.428 2.88.404 0 1.01-.636 1.818-1.908.808-1.272 1.244-2.244 1.312-2.916.144-1.236-.356-1.854-1.5-1.854-.532 0-1.076.12-1.632.372 1.084-3.54 3.152-5.262 6.204-5.16 2.256.072 3.312 1.532 3.168 4.38z"/></svg>Vimeo</span>',
		'twitch'      => '<span class="vg-card__source-badge vg-source--twitch"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>Twitch</span>',
		'dailymotion' => '<span class="vg-card__source-badge vg-source--dailymotion"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M19.78 5.43c-1.33-1.32-3.1-2.05-4.98-2.05h-5.6C5.55 3.38 2.62 6.3 2.62 9.92v4.16c0 3.62 2.93 6.54 6.58 6.54h5.6c1.88 0 3.65-.73 4.98-2.05c1.33-1.33 2.06-3.1 2.06-4.99V9.92c0-1.89-.73-3.66-2.06-4.99zm-4.98 10.72h-5.6c-1.54 0-2.8-1.25-2.8-2.79V9.92c0-1.54 1.26-2.79 2.8-2.79h5.6c1.54 0 2.8 1.25 2.8 2.79v3.44c0 1.54-1.26 2.79-2.8 2.79z"/></svg>Dailymotion</span>',
		'wistia'      => '<span class="vg-card__source-badge vg-source--wistia"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.5l-5-3.5 5-3.5v7zM17 13h-3v-2h3v2z"/></svg>Wistia</span>',
		'tiktok'      => '<span class="vg-card__source-badge vg-source--tiktok"><svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px; vertical-align: middle;"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.94-1.74-.22-.2-.41-.43-.6-.67v6.62c.03 2.24-.85 4.58-2.6 6.01-1.76 1.48-4.27 2.05-6.51 1.55-2.22-.44-4.22-1.92-5.18-3.99-1.04-2.15-1.02-4.85.12-6.97.94-1.83 2.73-3.23 4.81-3.66 1.11-.25 2.29-.18 3.39.14v4.1c-.8-.28-1.72-.34-2.52-.02-1.01.37-1.8 1.29-2.02 2.34-.29 1.19-.04 2.53.72 3.48.74.96 1.99 1.46 3.2 1.34 1.3-.07 2.53-.94 2.92-2.17.26-.74.24-1.57.25-2.35.02-3.67 0-7.34.01-11.01v-.01z"/></svg>TikTok</span>'
	);

	if ($platform === 'meta') {
		if ($meta_content_source === 'facebook_page') {
			return '<span class="vg-card__source-badge vg-source--facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12c0 5.011 3.667 9.154 8.5 9.872V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v7.002C18.333 21.162 22 17.022 22 12c0-5.523-4.477-10-10-10z"/></svg>Facebook</span>';
		} else {
			return '<span class="vg-card__source-badge vg-source--instagram"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>Instagram</span>';
		}
	}

	return isset($badges[$platform]) ? $badges[$platform] : '';
}
