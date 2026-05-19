<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// ISO 8601 duration parser helper - Defined at top to prevent runtime Call to Undefined Function errors
if (!function_exists('nvgall_parse_iso8601_duration')) {
	function nvgall_parse_iso8601_duration($duration) {
		try {
			$interval = new DateInterval($duration);
			$hours = $interval->h;
			$minutes = $interval->i;
			$seconds = $interval->s;

			if ($hours > 0) {
				return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
			}
			return sprintf('%d:%02d', $minutes, $seconds);
		} catch (Exception $e) {
			return '';
		}
	}
}

/*
 * Gallery Output Code - Secure Server-Side Cached YouTube API Integration
 */
wp_enqueue_style('awlife-video-youram-simple-css');

$source_link = trim($video_gallery_channel_link);

$playlist_id = '';
$channel_id = '';
$username = '';

// 1. URL parsing to guarantee absolute backward compatibility for all configured input styles
if (strpos($source_link, 'list=') !== false) {
	$parsed_url = parse_url($source_link);
	if (isset($parsed_url['query'])) {
		parse_str($parsed_url['query'], $query_params);
		if (isset($query_params['list'])) {
			$playlist_id = sanitize_text_field($query_params['list']);
		}
	}
	if (empty($playlist_id)) {
		preg_match('/[?&]list=([A-Za-z0-9_-]+)/', $source_link, $matches);
		if (!empty($matches[1])) {
			$playlist_id = sanitize_text_field($matches[1]);
		}
	}
} elseif (preg_match('/^PL[A-Za-z0-9_-]+$/', $source_link)) {
	$playlist_id = $source_link;
} elseif (preg_match('/\/channel\/([A-Za-z0-9_-]+)/', $source_link, $matches)) {
	$channel_id = sanitize_text_field($matches[1]);
} elseif (preg_match('/\/user\/([A-Za-z0-9_-]+)/', $source_link, $matches)) {
	$username = sanitize_text_field($matches[1]);
} elseif (preg_match('/^UC[A-Za-z0-9_-]+$/', $source_link)) {
	$channel_id = $source_link;
} else {
	// Fallback alias username lookup
	$username = sanitize_text_field(basename(rtrim($source_link, '/')));
}

// 2. Fetch Channel Uploads Playlist ID if needed, with Transient caching
if (empty($playlist_id) && (!empty($channel_id) || !empty($username))) {
	$transient_key_uploads = 'nvg_yt_uploads_' . md5($source_link . $video_gallery_api_key);
	$playlist_id = get_transient($transient_key_uploads);

	if ($playlist_id === false) {
		if (!empty($channel_id)) {
			$api_url = "https://www.googleapis.com/youtube/v3/channels?part=contentDetails&id=" . urlencode($channel_id) . "&key=" . urlencode($video_gallery_api_key);
		} else {
			$api_url = "https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=" . urlencode($username) . "&key=" . urlencode($video_gallery_api_key);
		}

		$response = wp_safe_remote_get($api_url);
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$body = json_decode(wp_remote_retrieve_body($response), true);
			if (!empty($body['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
				$playlist_id = $body['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
				set_transient($transient_key_uploads, $playlist_id, DAY_IN_SECONDS);
			}
		}
	}
}

// 3. Fetch Playlist Items, video details and views count with Transient caching
$videos = array();
$next_page_token = '';
if (!empty($playlist_id)) {
	$transient_key_videos = 'nvg_yt_videos_' . md5($playlist_id . $video_gallery_api_key);
	$cached_data = get_transient($transient_key_videos);

	if ($cached_data !== false) {
		if (is_array($cached_data)) {
			if (isset($cached_data['videos'])) {
				$videos = $cached_data['videos'];
				$next_page_token = $cached_data['next_page_token'] ?? '';
			} else {
				$videos = $cached_data;
				$next_page_token = '';
			}
		}
	} else {
		$api_url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=" . urlencode($playlist_id) . "&maxResults=8&key=" . urlencode($video_gallery_api_key);

		$response = wp_safe_remote_get($api_url);
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$body = json_decode(wp_remote_retrieve_body($response), true);
			$next_page_token = $body['nextPageToken'] ?? '';
			if (!empty($body['items'])) {
				$videos = array();
				$video_ids = array();

				foreach ($body['items'] as $item) {
					if (!empty($item['snippet']['resourceId']['videoId'])) {
						$v_id = $item['snippet']['resourceId']['videoId'];
						$video_ids[] = $v_id;
						$videos[$v_id] = array(
							'id' => $v_id,
							'title' => $item['snippet']['title'] ?? '',
							'thumbnail' => $item['snippet']['thumbnails']['medium']['url'] ?? ($item['snippet']['thumbnails']['high']['url'] ?? ($item['snippet']['thumbnails']['default']['url'] ?? '')),
							'views' => 0,
							'duration' => ''
						);
					}
				}

				// Fetch statistics (views) and contentDetails (duration) for these video IDs
				if (!empty($video_ids)) {
					$ids_string = implode(',', $video_ids);
					$stats_url = "https://www.googleapis.com/youtube/v3/videos?part=statistics,contentDetails&id=" . urlencode($ids_string) . "&key=" . urlencode($video_gallery_api_key);

					$stats_response = wp_safe_remote_get($stats_url);
					if (!is_wp_error($stats_response) && wp_remote_retrieve_response_code($stats_response) === 200) {
						$stats_body = json_decode(wp_remote_retrieve_body($stats_response), true);
						if (!empty($stats_body['items'])) {
							foreach ($stats_body['items'] as $stat_item) {
								$v_id = $stat_item['id'];
								if (isset($videos[$v_id])) {
									// View count format
									if (isset($stat_item['statistics']['viewCount'])) {
										$views_count = intval($stat_item['statistics']['viewCount']);
										$videos[$v_id]['views'] = number_format($views_count);
									}
									// ISO 8601 duration string parse
									if (isset($stat_item['contentDetails']['duration'])) {
										$duration_raw = $stat_item['contentDetails']['duration'];
										$videos[$v_id]['duration'] = nvgall_parse_iso8601_duration($duration_raw);
									}
								}
							}
						}
					}
				}

				// Cache the videos for 12 hours
				$cache_payload = array(
					'videos' => $videos,
					'next_page_token' => $next_page_token
				);
				set_transient($transient_key_videos, $cache_payload, 12 * HOUR_IN_SECONDS);
			}
		}
	}
}
?>

<div id="yram_<?php echo esc_attr($post_id); ?>" class="youram-simple yl-grid yl-simple-thumbnails yl-4-col-grid" data-next-page-token="<?php echo esc_attr($next_page_token); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
	<div class="yl-font-controller">
		<div class="yl-wrapper">
			<div class="yl-inline-container"></div>
			<div class="yl-item-container">
				<?php
				if (!empty($videos)) {
					foreach ($videos as $video) {
						$thumbnail_url = $video['thumbnail'];
						$title = $video['title'];
						$video_id = $video['id'];
						$duration = $video['duration'];
						$views = $video['views'];
						?>
						<div class="yl-item-wrapper">
							<div class="yl-item" id="yl-video-<?php echo esc_attr($video_id); ?>">
								<a class="yl-focus" href="https://www.youtube.com/watch?v=<?php echo esc_attr($video_id); ?>" target="_blank">
									<div class="yl-thumbnail">
										<img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>">
										<?php if (!empty($duration)) { ?>
											<div class="yl-duration"><svg class="nvg-yt-icon" width="12" height="12" viewBox="0 0 16 12" fill="#fff" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:3px;display:none;"><path d="M15.8 1.9c-.2-.7-.7-1.3-1.4-1.5C13.2 0 8 0 8 0S2.8 0 1.6.4C.9.6.4 1.2.2 1.9 0 3.1 0 6 0 6s0 2.9.2 4.1c.2.7.7 1.3 1.4 1.5C2.8 12 8 12 8 12s5.2 0 6.4-.4c.7-.2 1.2-.8 1.4-1.5.2-1.2.2-4.1.2-4.1s0-2.9-.2-4.1zM6.4 8.5V3.5L10.6 6 6.4 8.5z"/></svg><?php echo esc_html($duration); ?></div>
										<?php } ?>
										<div class="yl-play-overlay"></div>
										<div class="yl-play-overlay-fixed">
											<div class="yl-play-icon-holder"><svg class="nvg-yt-icon" width="18" height="14" viewBox="0 0 16 12" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M15.8 1.9c-.2-.7-.7-1.3-1.4-1.5C13.2 0 8 0 8 0S2.8 0 1.6.4C.9.6.4 1.2.2 1.9 0 3.1 0 6 0 6s0 2.9.2 4.1c.2.7.7 1.3 1.4 1.5C2.8 12 8 12 8 12s5.2 0 6.4-.4c.7-.2 1.2-.8 1.4-1.5.2-1.2.2-4.1.2-4.1s0-2.9-.2-4.1zM6.4 8.5V3.5L10.6 6 6.4 8.5z"/></svg></div>
										</div>
									</div>
									<br>
									<div class="yl-view-bucket" data-views="<?php echo esc_attr($views); ?>">
										<div class="yl-view-wrapper">
											<div class="yl-view-count"><?php echo esc_html($views); ?> <span>views</span></div>
										</div>
									</div>
								</a>
								<div class="yl-text">
									<div class="yl-title-description-wrapper">
										<div class="yl-title"><?php echo esc_html($title); ?></div>
									</div>
									<div class="yl-separator-for-grid"></div>
									<div class="yl-view-string"><?php echo esc_html($views); ?> <span>views</span></div>
								</div>
							</div>
						</div>
						<?php
					}
				} else {
					?>
					<div class="col-md-12" style="text-align: center; padding: 30px;">
						<p style="color: #d9534f; font-weight: bold;">
							<?php esc_html_e('No videos found or YouTube API connection error. Please verify your YouTube API Key and Channel URL.', 'new-video-gallery'); ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<br>
			<?php if (!empty($next_page_token)) { ?>
				<div class="yl-load-more-button" data-text="Load More">Load More</div>
			<?php } else { ?>
				<div class="yl-load-more-button yl-loading" style="display: none;" data-text="All done..">All done..</div>
			<?php } ?>
		</div>
	</div>
</div>

<?php
// Enqueue the Youram Simple JS (bundled with Magnific Popup)
wp_enqueue_script('awlife-vg-youram-simple-js');

// Add inline script to initialize Magnific Popup and handle Load More AJAX
$inline_js = "
jQuery(document).ready(function ($) {
	function initMagnificPopup(containerId) {
		$('#' + containerId).magnificPopup({
			delegate: 'a.yl-focus',
			gallery: {
				enabled: true
			},
			type: 'iframe',
			iframe: {
				markup: '<div class=\"mfp-iframe-scaler\"><button title=\"Close (Esc)\" type=\"button\" class=\"mfp-close\">×</button><iframe class=\"mfp-iframe\" frameborder=\"0\" allowfullscreen></iframe></div><div class=\"mfp-preloader\">Loading...</div>',
				patterns: {
					youtube: {
						index: 'youtube.com',
						id: 'v=',
						src: '//www.youtube.com/embed/%id%?autoplay=1'
					}
				}
			}
		});
	}

	var containerId = 'yram_' + " . intval($post_id) . ";
	var \$container = $('#' + containerId);
	
	// Initial setup
	initMagnificPopup(containerId);

	// Load More Action
	\$container.on('click', '.yl-load-more-button', function() {
		var \$btn = \$(this);
		if (\$btn.hasClass('yl-loading')) {
			return;
		}

		var nextToken = \$container.attr('data-next-page-token');
		var postId = \$container.attr('data-post-id');

		if (!nextToken) {
			\$btn.text('All done..').addClass('yl-loading');
			return;
		}

		\$btn.addClass('yl-loading').text('loading...');

		$.ajax({
			url: '" . esc_url(admin_url('admin-ajax.php')) . "',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'nvgall_load_more_youtube_videos',
				post_id: postId,
				next_page_token: nextToken
			},
			success: function(response) {
				\$btn.removeClass('yl-loading');
				if (response.success) {
					// Append new elements to the container
					\$container.find('.yl-item-container').append(response.data.html);
					
					// Update next page token
					if (response.data.next_page_token) {
						\$container.attr('data-next-page-token', response.data.next_page_token);
						\$btn.text('Load More');
					} else {
						\$container.removeAttr('data-next-page-token');
						\$btn.text('All done..').addClass('yl-loading');
					}
				} else {
					\$btn.text('Error loading videos');
				}
			},
			error: function() {
				\$btn.removeClass('yl-loading').text('Error loading videos');
			}
		});
	});
});
";
wp_add_inline_script('awlife-vg-youram-simple-js', $inline_js);
?>


<style>
	/* ---- Anchor reset: neutralise <a> styling ---- */
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus,
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus:hover,
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus:focus,
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus:active,
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus:visited {
		text-decoration: none !important;
		color: inherit !important;
		outline: none !important;
	}
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus {
		display: inline-block;
		float: left;
		width: 100%;
		padding: 0;
		cursor: pointer;
		box-sizing: border-box;
	}

	/* ---- Hide view-bucket and stray <br> inside anchor ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-view-bucket {
		display: none !important;
	}
	#yram_<?php echo esc_attr($post_id); ?> a.yl-focus br {
		display: none !important;
	}

	/* ---- Grid item card ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-item {
		background-color: #fbfbfb;
		box-shadow: 0 0 2px rgba(0,0,0,.2);
		overflow: hidden;
	}

	/* ---- Text area under thumbnail (grid / simple-thumbnails mode) ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-text {
		width: 100% !important;
		float: left;
		padding: 5% 5% !important;
		box-sizing: border-box;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-title {
		color: #383838;
		font-size: .8em;
		font-weight: bold;
		height: 3.1em;
		max-height: 3.1em;
		line-height: 1.57em;
		overflow: hidden;
		font-family: 'B612', sans-serif;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-title-description-wrapper {
		height: auto !important;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-separator-for-grid {
		display: none !important;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-view-string {
		display: none !important;
	}

	/* ---- Fixed play icon (always visible, circle shape) ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-play-overlay-fixed {
		display: block !important;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-play-icon-holder {
		width: 2.6em !important;
		height: 2.6em !important;
		border-radius: 100% !important;
		padding: 0.1em 0.25em !important;
		box-sizing: border-box !important;
		border: 3px solid #fff !important;
	}

	/* ---- Hover: fill icon with theme colour only, NO dark overlay, NO duration shift ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-focus:hover .yl-play-icon-holder {
		background-color: rgb(255, 0, 0) !important;
	}

	/* ---- Duration badge (static, bottom-right) ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-duration {
		display: inline-block;
		right: 6%;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-duration i {
		display: none;
	}

	/* ---- Load More button ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-load-more-button {
		background-color: #1e73be !important;
		color: #ffffff !important;
		margin: 20px auto !important;
		display: block !important;
		clear: both !important;
		width: 20%;
		text-align: center;
		padding: 0.6em;
		box-sizing: border-box;
		font-family: 'B612', sans-serif;
		font-size: 1em;
		box-shadow: 0 0 2px rgba(0,0,0,.2);
		cursor: pointer;
		transition: all .3s;
	}
	#yram_<?php echo esc_attr($post_id); ?> .yl-load-more-button:hover {
		background: linear-gradient(to right, rgba(30,115,190,.8), #1e73be 30%) !important;
	}

	/* ---- Float clear for item container ---- */
	#yram_<?php echo esc_attr($post_id); ?> .yl-item-container::after {
		content: "";
		display: table;
		clear: both;
	}

	/* ---- Responsive ---- */
	@media (max-width: 600px) {
		#yram_<?php echo esc_attr($post_id); ?> .yl-item-wrapper {
			width: 100% !important;
			margin-right: 0 !important;
			float: none !important;
			clear: both !important;
		}
		#yram_<?php echo esc_attr($post_id); ?> .yl-load-more-button {
			width: 100% !important;
		}
	}
	@media (min-width: 601px) and (max-width: 900px) {
		#yram_<?php echo esc_attr($post_id); ?> .yl-item-wrapper {
			width: 48.5% !important;
			margin-right: 3% !important;
			float: left !important;
		}
		#yram_<?php echo esc_attr($post_id); ?> .yl-item-wrapper:nth-child(2n) {
			margin-right: 0 !important;
		}
		#yram_<?php echo esc_attr($post_id); ?> .yl-item-wrapper:nth-child(2n+1) {
			clear: both !important;
		}
	}
</style>