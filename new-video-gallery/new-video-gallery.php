<?php
/** @package New Video Gallery
 * Plugin Name:       Video Gallery – YouTube API, Vimeo & Link Gallery
 * Plugin URI:        https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/
 * Description:       Create YouTube Vimeo Video Galleries Into WordPress Blog
 * Version:           1.7.1
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            A WP Life
 * Author URI:        https://profiles.wordpress.org/awordpresslife
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       new-video-gallery
 * Domain Path:       /languages
 * License:           GPL2
 * New Video Gallery is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.
 *
 * New Video Gallery is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with New Video Gallery. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html.
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('New_Video_Gallery')) {

	class New_Video_Gallery
	{

		protected $protected_plugin_api;
		protected $ajax_plugin_nonce;

		public function __construct()
		{
			$this->_constants();
			$this->_hooks();
		}

		protected function _constants()
		{
			// Plugin Version
			define('NVGALL_PLUGIN_VER', '1.7.1');

			// Plugin Text Domain
			define('NVGALL_TXTDM', 'new-video-gallery');

			// Plugin Name
			define('NVGALL_PLUGIN_NAME', 'New Video Gallery');

			// Plugin Slug
			define('NVGALL_PLUGIN_SLUG', 'video_gallery');

			// Plugin Directory Path
			define('NVGALL_PLUGIN_DIR', plugin_dir_path(__FILE__));

			// Plugin Directory URL
			define('NVGALL_PLUGIN_URL', plugin_dir_url(__FILE__));

		} // end of constructor function


		/**
		 * Setup the default filters and actions
		 *
		 * @uses      add_action()  To add various actions
		 * @access    private
		 * @since     0.0.5
		 * @return    void
		 */
		protected function _hooks()
		{

			// Create Video Gallery Custom Post
			add_action('init', array($this, '_nvgall_cpt_register'));

			// Add meta box to custom post
			add_action('add_meta_boxes', array($this, '_nvgall_admin_add_meta_box'));

			add_action('wp_ajax_video_gallery_js', array(&$this, '_nvgall_ajax_video_gallery'));

			add_action('save_post', array(&$this, '_nvgall_save_settings'));

			// Register admin assets enqueue hook
			add_action('admin_enqueue_scripts', array($this, '_nvgall_admin_enqueue_assets'));

			// Register frontend assets enqueue hook
			add_action('wp_enqueue_scripts', array($this, '_nvgall_frontend_enqueue_assets'));

			// add video gallery cpt shortcode column - manage_{$post_type}_posts_columns
			add_filter('manage_video_gallery_posts_columns', array(&$this, 'nvgall_set_shortcode_column_name'));

			// add video gallery cpt shortcode column data - manage_{$post_type}_posts_custom_column
			add_action('manage_video_gallery_posts_custom_column', array(&$this, 'nvgall_custom_shortcode_data'), 10, 2);

			// Register admin footer scripts for post list view
			add_action('admin_footer', array($this, '_nvgall_admin_footer_scripts'));

			add_action('wp_ajax_nvgall_load_more_youtube_videos', array($this, 'nvgall_load_more_youtube_videos_ajax'));
			add_action('wp_ajax_nopriv_nvgall_load_more_youtube_videos', array($this, 'nvgall_load_more_youtube_videos_ajax'));

		} // end of hook function

		// Video Gallery table cpt shortcode column before date columns
		public function nvgall_set_shortcode_column_name($defaults)
		{
			$new = array();
			unset($defaults['tags']); // remove it from the columns list

			foreach ($defaults as $key => $value) {
				if ($key == 'date') { // when we find the date column
					$new['video_gallery_shortcode'] = __('Shortcode', 'new-video-gallery'); // put the tags column before it
				}
				$new[$key] = $value;
			}
			return $new;
		}

		// Video Gallery cpt shortcode column data
		public function nvgall_custom_shortcode_data($column, $post_id)
		{
			switch ($column) {
				case 'video_gallery_shortcode':
					echo "<input type='text' class='button button-primary' id='video-gallery-shortcode-" . esc_attr($post_id) . "' value='[VDGAL id=" . esc_attr($post_id) . "]' style='font-weight:bold; background-color:#32373C; color:#FFFFFF; text-align:center;' />";
					echo "<input type='button' class='button button-primary' onclick='nvgCopyListShortcode(" . esc_attr($post_id) . ");' value='Copy' style='margin-left:4px;' />";
					echo "<span id='copy-msg-" . esc_attr($post_id) . "' class='button button-primary' style='display:none; background-color:#32CD32; color:#FFFFFF; margin-left:4px; border-radius: 4px;'>copied</span>";
					break;
			}
		}
		/**
		 * video Gallery Custom Post
		 * Create gallery post type in admin dashboard.
		 *
		 * @access    private
		 */
		public function _nvgall_cpt_register()
		{
			$labels = array(
				'name' => __('Video Gallery', 'new-video-gallery'),
				'singular_name' => __('Video Gallery', 'new-video-gallery'),
				'menu_name' => __('Video Gallery', 'new-video-gallery'),
				'name_admin_bar' => __('Video Gallery', 'new-video-gallery'),
				'add_new' => __('Add Video Gallery', 'new-video-gallery'),
				'add_new_item' => __('Add New Video Gallery', 'new-video-gallery'),
				'new_item' => __('New Video Gallery ', 'new-video-gallery'),
				'edit_item' => __('Edit Video Gallery', 'new-video-gallery'),
				'view_item' => __('View Video Gallery', 'new-video-gallery'),
				'all_items' => __('All Video Gallery', 'new-video-gallery'),
				'search_items' => __('Search Video Gallery', 'new-video-gallery'),
				'parent_item_colon' => __('Parent Video Gallery:', 'new-video-gallery'),
				'not_found' => __('Video Gallery Not found.', 'new-video-gallery'),
				'not_found_in_trash' => __('Video Gallery Not found in Trash.', 'new-video-gallery'),
			);

			$args = array(
				'label' => __('Video Gallery', 'new-video-gallery'),
				'description' => __('Custom Post Type For Video Gallery', 'new-video-gallery'),
				'labels' => $labels,
				'supports' => array('title'),
				'taxonomies' => array(),
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 65,
				'menu_icon' => 'dashicons-images-alt2',
				'show_in_admin_bar' => true,
				'show_in_nav_menus' => true,
				'can_export' => true,
				'has_archive' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'page',
			);

			register_post_type('video_gallery', $args);
		} //end _New_Video_Gallery()

		/**
		 * Adds Meta Boxes
		 */
		public function _nvgall_admin_add_meta_box()
		{
			// Syntax: add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
			add_meta_box('vg_shortcode_metabox', __('Copy Video Gallery Shortcode', 'new-video-gallery'), array(&$this, '_nvgall_shortcode_left_metabox'), 'video_gallery', 'side', 'default');
			add_meta_box('vg_upload_images_metabox', __('Add Video', 'new-video-gallery'), array(&$this, 'nvgall_upload_multiple_images'), 'video_gallery', 'normal', 'default');
		}

		// Video gallery copy shortcode meta box under publish button
		public function _nvgall_shortcode_left_metabox($post)
		{ ?>
			<p class="input-text-wrap">
				<input type="text" name="VIDEOCopyShortcode" id="VIDEOCopyShortcode"
					value="<?php echo '[VDGAL id=' . esc_attr($post->ID) . ']'; ?>" readonly
					style="height: 60px; text-align: center; width:100%;  font-size: 24px; border: 2px dashed;">
			<p id="vg-copy-code">
				<?php esc_html_e('Shortcode copied to clipboard!', 'new-video-gallery'); ?>
			</p>
			<p style="margin-top: 10px">
				<?php esc_html_e('Copy & Embed shotcode into any Page/ Post / Text Widget to display gallery.', 'new-video-gallery'); ?>
			</p>
			</p>
			<span onclick="nvgCopyToClipboard('#VIDEOCopyShortcode')" class="vg-copy dashicons dashicons-clipboard"></span>
			<style>
				.vg-copy {
					position: absolute;
					top: 9px;
					right: 24px;
					font-size: 26px;
					cursor: pointer;
				}
			</style>
			<script>
				jQuery("#vg-copy-code").hide();
				function nvgCopyToClipboard(element) {
					var $temp = jQuery("<input>");
					jQuery("body").append($temp);
					$temp.val(jQuery(element).val()).select();
					document.execCommand("copy");
					$temp.remove();
					jQuery("#VIDEOCopyShortcode").select();
					jQuery("#vg-copy-code").fadeIn();
				}
			</script>
			<?php
		}

		public function _nvgall_admin_enqueue_assets($hook)
		{
			$screen = get_current_screen();
			if ( is_object( $screen ) && 'video_gallery' === $screen->post_type ) {
				// Admin Metabox and Uploader Assets
				wp_enqueue_script('media-upload');
				wp_enqueue_script('awlife-vg-uploader.js', NVGALL_PLUGIN_URL . 'assets/js/awlife-vg-uploader.js', array('jquery'));
				wp_enqueue_style('awlife-vg-uploader-css', NVGALL_PLUGIN_URL . 'assets/css/awlife-vg-uploader.css');
				wp_enqueue_style('awlife-metabox-css', NVGALL_PLUGIN_URL . 'assets/css/metabox.css');
				wp_enqueue_media();

				// Settings Panel Visual Assets
				wp_enqueue_style('awlife-em-css', NVGALL_PLUGIN_URL . 'assets/css/toogle-button.css');
				wp_enqueue_style('awlife-bootstrap-css', NVGALL_PLUGIN_URL . 'assets/css/bootstrap.css');
				wp_enqueue_style('awlife-styles-css', NVGALL_PLUGIN_URL . 'assets/css/styles.css');
			}
		}

		public function _nvgall_frontend_enqueue_assets()
		{
			// Register YouTube API Gallery Assets
			wp_register_script('awlife-vg-youram-simple-js', NVGALL_PLUGIN_URL . 'assets/js/youram-simple.min.js', array('jquery'), NVGALL_PLUGIN_VER, true);
			wp_register_style('awlife-video-youram-simple-css', NVGALL_PLUGIN_URL . 'assets/css/youram-simple.min.css', array(), NVGALL_PLUGIN_VER);

			// Register Video Gallery Assets
			wp_register_script('awlife-vg-isotope-js', NVGALL_PLUGIN_URL . 'assets/js/isotope.pkgd.js', array('jquery'), NVGALL_PLUGIN_VER, false);
			wp_register_script('awlife-vg-scale-fix-js', NVGALL_PLUGIN_URL . 'assets/js/video-js/scale.fix.js', array('jquery'), NVGALL_PLUGIN_VER, true);
			wp_register_script('awlife-vg-video-lightning-js', NVGALL_PLUGIN_URL . 'assets/js/video-js/videoLightning.js', array('jquery'), NVGALL_PLUGIN_VER, true);
			wp_register_script('awlife-vg-jqvl-page-js', NVGALL_PLUGIN_URL . 'assets/js/video-js/jqvl-page.js', array('jquery'), NVGALL_PLUGIN_VER, true);
			wp_register_style('awlife-bootstrap-css', NVGALL_PLUGIN_URL . 'assets/css/video-gallery-bootstrap.css', array(), NVGALL_PLUGIN_VER);
			wp_register_style('awlife-icon-css', NVGALL_PLUGIN_URL . 'assets/css/video-icon.css', array(), NVGALL_PLUGIN_VER);
		}

		public function _nvgall_admin_footer_scripts()
		{
			$screen = get_current_screen();
			if ( is_object( $screen ) && 'edit-video_gallery' === $screen->id ) {
				?>
				<script>
				function nvgCopyListShortcode(postId) {
					var copyText = document.getElementById('video-gallery-shortcode-' + postId);
					if (!copyText) return;
					copyText.select();
					
					if (navigator.clipboard) {
						navigator.clipboard.writeText(copyText.value).then(function() {
							nvgShowCopyMsg(postId);
						}).catch(function() {
							nvgFallbackCopy(postId);
						});
					} else {
						nvgFallbackCopy(postId);
					}
				}
				
				function nvgFallbackCopy(postId) {
					try {
						document.execCommand('copy');
						nvgShowCopyMsg(postId);
					} catch (err) {
						console.error('Fallback copy failed', err);
					}
				}

				function nvgShowCopyMsg(postId) {
					jQuery('#copy-msg-' + postId).fadeIn(1000, 'linear');
					jQuery('#copy-msg-' + postId).fadeOut(2500, 'swing');
				}
				</script>
				<?php
			}
		}

		public function nvgall_upload_multiple_images($post)
		{
			$settings_path = NVGALL_PLUGIN_DIR . 'include/video-gallery-settings.php';
			if ( file_exists( $settings_path ) ) {
				require_once $settings_path;
			}
		}
		public function _nvgall_ajax_callback_function($id)
		{
			$image_type  = '';
			$poster_type = '';
			$thumbnail   = wp_get_attachment_image_src($id, 'medium', true);
			$attachment  = get_post($id); // $id = attachment id

			ob_start();
?>
			<li class="slide">
				<img class="new-slide" src="<?php echo esc_url($thumbnail[0]); ?>"
					alt="<?php echo esc_html(get_the_title($id)); ?>" style="height: 150px; width: 100%; border-radius: 8px;">
				<input type="hidden" id="slide-ids[]" name="slide-ids[]" value="<?php echo esc_html($id); ?>" />
				<select id="slide-type[]" name="slide-type[]" style="width: 100%;" placeholder="Image Title"
					value="<?php echo esc_html($image_type); ?>">
					<option value="y" <?php
			if ($image_type == 'y') {
				echo 'selected=selected';
			}
?>>
						<?php esc_html_e('YouTube', 'new-video-gallery'); ?>
					</option>
					<option value="v" <?php
			if ($image_type == 'v') {
				echo 'selected=selected';
			}
?>>
						<?php esc_html_e('Vimeo', 'new-video-gallery'); ?>
					</option>
				</select>
				<input type="text" name="slide-link[]" id="slide-link[]" style="width: 100%;"
					placeholder="Enter YouTube / Vimeo Video ID">
				<input type="text" name="slide-title[]" id="slide-title[]" style="width: 100%;" placeholder="Video Title"
					value="<?php echo esc_html(get_the_title($id)); ?>">
				<textarea name="slide-desc[]" id="slide-desc[]" placeholder="Video Description"
					style="height: 100px; width: 100%;"><?php echo esc_html($attachment->post_content); ?></textarea>
				<select id="poster-type[]" name="poster-type[]" style="width: 100%;"
					value="<?php echo esc_html($poster_type); ?>">
					<optgroup label="Select Poster Option">
						<option value="internal">
							<?php esc_html_e('Use Above Poster', 'new-video-gallery'); ?>
						</option>
						<option value="youtube">
							<?php esc_html_e('Fetch YouTube Poster', 'new-video-gallery'); ?>
						</option>
					</optgroup>
				</select>
				<input type="button" name="remove-slide" id="remove-slide" style="width: 100%;" class="button" value="Delete">
			</li>
			<?php
			return ob_get_clean();
		}

		public function _nvgall_ajax_video_gallery()
		{
			if (current_user_can('manage_options')) {
				if (isset($_POST['vg_add_images_nonce']) && wp_verify_nonce(wp_unslash($_POST['vg_add_images_nonce']), 'vg_add_images')) {
					$slide_id = isset($_POST['slideId']) ? absint(wp_unslash($_POST['slideId'])) : 0;
					$html     = $this->_nvgall_ajax_callback_function($slide_id);
					echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			wp_die();
		}

		public function _nvgall_save_settings($post_id)
		{
			// Restrict execution to video_gallery post saves only
			if (get_post_type($post_id) !== 'video_gallery') {
				return;
			}

			if (current_user_can('manage_options')) {
				if (isset($_POST['vg_save_nonce']) && wp_verify_nonce(wp_unslash($_POST['vg_save_nonce']), 'vg_save_settings')) {
					$video_gallery_option = isset($_POST['video_gallery_option']) ? sanitize_text_field(wp_unslash($_POST['video_gallery_option'])) : '';
					$video_gallery_api_key = isset($_POST['video_gallery_api_key']) ? sanitize_text_field(wp_unslash($_POST['video_gallery_api_key'])) : '';
					$video_gallery_channel_link = isset($_POST['video_gallery_channel_link']) ? sanitize_text_field(wp_unslash($_POST['video_gallery_channel_link'])) : '';
					$gal_thumb_size = isset($_POST['gal_thumb_size']) ? sanitize_text_field(wp_unslash($_POST['gal_thumb_size'])) : '';
					$col_large_desktops = isset($_POST['col_large_desktops']) ? sanitize_text_field(wp_unslash($_POST['col_large_desktops'])) : '';
					$col_desktops = isset($_POST['col_desktops']) ? sanitize_text_field(wp_unslash($_POST['col_desktops'])) : '';
					$col_tablets = isset($_POST['col_tablets']) ? sanitize_text_field(wp_unslash($_POST['col_tablets'])) : '';
					$col_phones = isset($_POST['col_phones']) ? sanitize_text_field(wp_unslash($_POST['col_phones'])) : '';
					$width = isset($_POST['width']) ? sanitize_text_field(wp_unslash($_POST['width'])) : '';
					$height = isset($_POST['height']) ? sanitize_text_field(wp_unslash($_POST['height'])) : '';
					$video_icon = isset($_POST['video_icon']) ? sanitize_text_field(wp_unslash($_POST['video_icon'])) : '';
					$auto_play = isset($_POST['auto_play']) ? sanitize_text_field(wp_unslash($_POST['auto_play'])) : '';
					$auto_close = isset($_POST['auto_close']) ? sanitize_text_field(wp_unslash($_POST['auto_close'])) : '';
					$z_index = isset($_POST['z_index']) ? sanitize_text_field(wp_unslash($_POST['z_index'])) : '';
					$z_index_custom_value = isset($_POST['z_index_custom_value']) ? sanitize_text_field(wp_unslash($_POST['z_index_custom_value'])) : '';

					$image_ids = array();
					$image_titles = array();
					$image_type = array();
					$slide_link = array();
					$image_descs = array();
					$video_poster = array();

					$image_ids_val = isset($_POST['slide-ids']) ? array_map('sanitize_text_field', wp_unslash((array)$_POST['slide-ids'])) : array();


					foreach ($image_ids_val as $i => $image_id) {
						$img_id = isset($_POST['slide-ids'][$i]) ? sanitize_text_field(wp_unslash($_POST['slide-ids'][$i])) : '';
						$title = isset($_POST['slide-title'][$i]) ? sanitize_text_field(wp_unslash($_POST['slide-title'][$i])) : '';
						$type = isset($_POST['slide-type'][$i]) ? sanitize_text_field(wp_unslash($_POST['slide-type'][$i])) : '';
						$link = isset($_POST['slide-link'][$i]) ? sanitize_text_field(wp_unslash($_POST['slide-link'][$i])) : '';
						$desc = isset($_POST['slide-desc'][$i]) ? sanitize_text_field(wp_unslash($_POST['slide-desc'][$i])) : '';
						$poster = isset($_POST['poster-type'][$i]) ? sanitize_text_field(wp_unslash($_POST['poster-type'][$i])) : '';

						$image_ids[] = $img_id;
						$image_titles[] = $title;
						$image_type[] = $type;
						$slide_link[] = $link;
						$image_descs[] = $desc;
						$video_poster[] = $poster;

						$single_image_update = array(
							'ID' => $img_id,
							'post_title' => $title,
							'post_content' => $desc,
						);
						// Temporarily unhook to prevent infinite recursive loop on attachment update
						remove_action('save_post', array($this, '_nvgall_save_settings'));
						wp_update_post($single_image_update);
						add_action('save_post', array($this, '_nvgall_save_settings'));
					}

					$gallery_settings = array(
						'slide-ids' => $image_ids,
						'slide-title' => $image_titles,
						'slide-type' => $image_type,
						'slide-link' => $slide_link,
						'slide-desc' => $image_descs,
						'poster-type' => $video_poster,
						'video_gallery_option' => $video_gallery_option,
						'video_gallery_api_key' => $video_gallery_api_key,
						'video_gallery_channel_link' => $video_gallery_channel_link,
						'gal_thumb_size' => $gal_thumb_size,
						'col_large_desktops' => $col_large_desktops,
						'col_desktops' => $col_desktops,
						'col_tablets' => $col_tablets,
						'col_phones' => $col_phones,
						'width' => $width,
						'height' => $height,
						'video_icon' => $video_icon,
						'auto_play' => $auto_play,
						'auto_close' => $auto_close,
						'z_index' => $z_index,
						'z_index_custom_value' => $z_index_custom_value,
					);

					$nvgall_video_gallery_shortcode_setting = 'nvgall_vg_settings_' . $post_id;
					update_post_meta($post_id, $nvgall_video_gallery_shortcode_setting, wp_json_encode($gallery_settings));
				}
			}
		}

		private function nvgall_parse_iso8601_duration_helper($duration) {
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

		public function nvgall_load_more_youtube_videos_ajax()
		{
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
			$next_page_token = isset($_POST['next_page_token']) ? sanitize_text_field(wp_unslash($_POST['next_page_token'])) : '';

			if (empty($post_id)) {
				wp_send_json_error(array('message' => 'Invalid Post ID'));
			}

			// Retrieve the data with prefix compliance and absolute fallback safeguards
			$encodedData = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
			if (empty($encodedData)) {
				$encodedData = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
			}

			// Try base64 decoding first
			$decodedData = base64_decode($encodedData);

			if (function_exists('nvgall_is_serialized') && nvgall_is_serialized($decodedData)) {
				$gallery_settings = unserialize($decodedData, ['allowed_classes' => false]);
			} else {
				$jsonData = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
				if (empty($jsonData)) {
					$jsonData = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
				}
				$gallery_settings = json_decode($jsonData, true);
			}

			if (empty($gallery_settings)) {
				wp_send_json_error(array('message' => 'Settings not found'));
			}

			$video_gallery_api_key = $gallery_settings['video_gallery_api_key'] ?? '';
			$video_gallery_channel_link = $gallery_settings['video_gallery_channel_link'] ?? '';

			if (empty($video_gallery_api_key) || empty($video_gallery_channel_link)) {
				wp_send_json_error(array('message' => 'API Key or Channel URL is missing'));
			}

			$source_link = trim($video_gallery_channel_link);
			$playlist_id = '';
			$channel_id = '';
			$username = '';

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
				$username = sanitize_text_field(basename(rtrim($source_link, '/')));
			}

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

			if (empty($playlist_id)) {
				wp_send_json_error(array('message' => 'Playlist ID not found'));
			}

			$api_url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=" . urlencode($playlist_id) . "&maxResults=8&key=" . urlencode($video_gallery_api_key);
			if (!empty($next_page_token)) {
				$api_url .= "&pageToken=" . urlencode($next_page_token);
			}

			$videos = array();
			$new_next_page_token = '';

			$response = wp_safe_remote_get($api_url);
			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				$body = json_decode(wp_remote_retrieve_body($response), true);
				$new_next_page_token = $body['nextPageToken'] ?? '';
				if (!empty($body['items'])) {
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
											$videos[$v_id]['duration'] = $this->nvgall_parse_iso8601_duration_helper($duration_raw);
										}
									}
								}
							}
						}
					}
				}
			}

			ob_start();
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
			}
			$html = ob_get_clean();

			wp_send_json_success(array(
				'html' => $html,
				'next_page_token' => $new_next_page_token
			));
		}

	}

	$nvgall_gallery_object = new New_Video_Gallery();
	$shortcode_path = NVGALL_PLUGIN_DIR . 'include/shortcode.php';
	if ( file_exists( $shortcode_path ) ) {
		require_once $shortcode_path;
	}
}
?>