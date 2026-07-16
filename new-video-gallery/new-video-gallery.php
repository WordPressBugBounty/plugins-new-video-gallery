<?php
/*
Plugin Name: 	Video Gallery
Plugin URI: 	https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/
Description: 	Create beautiful, responsive video galleries for WordPress with YouTube, Vimeo, and self-hosted video support. Features automatic YouTube API integrations, Isotope grid layouts, lightbox playback, load-more pagination, analytics tracking, and customizable styling.
Version: 		2.0.0
Author: 		A WP Life
Author URI: 	http://awplife.com/
License: 		GPL2
License URI:	https://www.gnu.org/licenses/gpl-2.0.html
Domain Path:	/languages
Text Domain:	new-video-gallery
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// If Video Gallery Premium is active, bypass Free version to let Premium take priority
$active_plugins = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
}
foreach ( $active_plugins as $plugin ) {
	if ( strpos( $plugin, 'video-gallery-premium.php' ) !== false ) {
		return;
	}
}

if (!function_exists('vg_extract_youtube_id')) {
	function vg_extract_youtube_id($url) {
		if (empty($url)) {
			return '';
		}
		$url = trim($url);
		if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
			return $url;
		}
		$pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
		if (preg_match($pattern, $url, $matches)) {
			return $matches[1];
		}
		return $url;
	}
}

if (!function_exists('vg_extract_vimeo_id')) {
	function vg_extract_vimeo_id($url) {
		if (empty($url)) {
			return '';
		}
		$url = trim($url);
		if (preg_match('/^\d+$/', $url)) {
			return $url;
		}
		if (preg_match('/(?:vimeo\.com\/|video\/)(\d+)/i', $url, $matches)) {
			return $matches[1];
		}
		return $url;
	}
}









if (!function_exists('vg_get_vimeo_thumbnail_url')) {
	function vg_get_vimeo_thumbnail_url($video_id) {
		$transient_key = 'vg_vimeo_thumb_' . md5($video_id);
		$cached_thumb = get_transient($transient_key);
		if ($cached_thumb !== false) {
			return $cached_thumb;
		}

		$response = wp_safe_remote_get("https://vimeo.com/api/v2/video/$video_id.json");
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$data = json_decode(wp_remote_retrieve_body($response), true);
			if (!empty($data[0]['thumbnail_large'])) {
				$thumbnail_url = esc_url_raw($data[0]['thumbnail_large']);
				set_transient($transient_key, $thumbnail_url, DAY_IN_SECONDS);
				return $thumbnail_url;
			}
		}
		return "";
	}
}



if ( ! class_exists( 'New_Video_Gallery' ) ) {

	class New_Video_Gallery {
		
		protected $protected_plugin_api;
		protected $ajax_plugin_nonce;
		
		public function __construct() {
			$this->_constants();
			$this->_hooks();
		}	
		
		protected function _constants() {
			//Plugin Version
			define( 'VG_PLUGIN_VER', '2.0.0' );
			
			//Plugin Text Domain
			define("VGP_TXTDM","new-video-gallery" );

			//Plugin Name
			define( 'VG_PLUGIN_NAME', 'Video Gallery' );

			//Plugin Slug
			define( 'VG_PLUGIN_SLUG', 'video_gallery' );

			//Plugin Directory Path
			define( 'VG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			//Plugin Directory URL
			define( 'VG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			/**
			 * Create a key for the .htaccess secure download link.
			 * @uses    NONCE_KEY     Defined in the WP root config.php
			 */
			define( 'VG_SECURE_KEY', md5( NONCE_KEY ) );
			
		} // end of constructor function
		
		
		/**
		 * Setup the default filters and actions
		 */
		protected function _hooks() {
			
			//Load text domain
			add_action( 'plugins_loaded', array( $this, '_load_textdomain' ) );
			
			//add Video gallery menu item, change menu filter for multisite
			add_action( 'admin_menu', array( $this, '_srgallery_menu' ), 101 );
			
			//Create Video Gallery Custom Post
			add_action( 'init', array( $this, '_New_Video_Gallery' ));
			
			//Add meta box to custom post
			add_action( 'add_meta_boxes', array( $this, '_admin_add_meta_box' ) );
			 
			//loaded during admin init 
			add_action( 'admin_init', array( $this, '_admin_add_meta_box' ) );
			
			add_action('wp_ajax_video_gallery_js', array(&$this, '_ajax_video_gallery'));
		
			add_action('save_post', array(&$this, '_vg_save_settings'));

			// Custom single gallery sync cron hooks
			add_action( 'nvgall_sync_single_gallery_cron', array( $this, 'run_single_gallery_sync' ) );
			add_action( 'before_delete_post', array( $this, 'clear_gallery_sync_cron_on_delete' ) );

			//Shortcode Compatibility in Text Widgets
			add_filter('widget_text', 'do_shortcode');

			// add gallery cpt shortcode column - manage_{$post_type}_posts_columns
			add_filter( 'manage_video_gallery_posts_columns', array(&$this, 'set_video_gallery_shortcode_column_name') );
			
			// add gallery cpt shortcode column data - manage_{$post_type}_posts_custom_column
			add_action( 'manage_video_gallery_posts_custom_column' , array(&$this, 'custom_video_gallery_shodrcode_data'), 10, 2 );
			
			add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts_in_header') );
						
			//clone gallery ajax call back, its required localize ajax object
			add_action('wp_ajax_vg_clone_gallery', array(&$this, 'vg_clone_gallery'));


			// Admin-side content fetching AJAX actions
			add_action('wp_ajax_nvgall_fetch_youtube_content', array(&$this, 'nvgall_fetch_youtube_content'));


			// Video play tracking AJAX endpoints
			add_action('wp_ajax_vg_track_video_play', array($this, 'vg_track_video_play'));
			add_action('wp_ajax_nopriv_vg_track_video_play', array($this, 'vg_track_video_play'));

			// Admin assets enqueuing hook
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			// Coexistence warning hook
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}// end of hook function

		/**
		 * Display coexistence warning notice when both free and premium versions are active
		 */
		public function admin_notices() {
			if ( defined( 'NVGALL_PLUGIN_VER' ) ) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<?php 
						echo sprintf(
							esc_html__( '%1$sVideo Gallery Premium%2$s and %1$sVideo Gallery Free%2$s are both active. Please deactivate the free version to avoid conflicts and ensure premium features work correctly.', 'new-video-gallery' ),
							'<strong>',
							'</strong>'
						); 
						?>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Enqueue admin scripts and styles for video gallery CPT
		 */
		public function admin_enqueue_scripts( $hook_suffix ) {
			if ( strpos( $hook_suffix, 'vg-our-plugins' ) !== false || strpos( $hook_suffix, 'vg-our-themes' ) !== false ) {
				wp_enqueue_style( 'ig-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null );
				wp_enqueue_style( 'awl-vg-our-plugins-style', VG_PLUGIN_URL . 'assets/css/our-plugins-style.css', array( 'dashicons' ), VG_PLUGIN_VER . '.' . time() );
				return;
			}
			global $post_type;
			if ( 'video_gallery' === $post_type || strpos( $hook_suffix, 'vg-global-settings' ) !== false || strpos( $hook_suffix, 'vg-doc-page' ) !== false || strpos( $hook_suffix, 'vg-analytics' ) !== false ) {
				// Enqueue WP media and color picker
				wp_enqueue_media();
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				
				// Enqueue our styles
				wp_enqueue_style( 'ig-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null );
				wp_enqueue_style( 'awl-vg-admin-style', VG_PLUGIN_URL . 'assets/css/vg-admin-style.css', array( 'dashicons' ), VG_PLUGIN_VER . '.' . time() );
				if ( strpos( $hook_suffix, 'vg-doc-page' ) !== false ) {
					wp_enqueue_style( 'awl-vg-admin-docs-style', VG_PLUGIN_URL . 'assets/css/vg-admin-docs.css', array( 'dashicons' ), VG_PLUGIN_VER . '.' . time() );
				}
				
				// Enqueue our scripts
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( 'awl-vg-uploader.js', VG_PLUGIN_URL . 'assets/js/awl-vg-uploader.js', array( 'jquery', 'jquery-ui-sortable' ), VG_PLUGIN_VER . '.' . time() );
				wp_enqueue_script( 'awl-vg-admin-settings-js', VG_PLUGIN_URL . 'assets/js/vg-admin-settings.js', array( 'jquery', 'wp-color-picker' ), VG_PLUGIN_VER . '.' . time(), true );

				if ( strpos( $hook_suffix, 'vg-analytics' ) !== false ) {
					wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', false );
				}
			}
		}

		
		/**
			* Clone gallery call back
		*/
		public function vg_clone_gallery() {
			check_ajax_referer('vg_clone_gallery_nonce', 'security');

			if (!current_user_can('edit_posts')) {
				wp_send_json_error(array('message' => 'Permission denied'));
			}

			if(isset($_POST['vg_clone_post_id'])) {
				
				$vg_clone_post_id = intval($_POST['vg_clone_post_id']);
				if (!$vg_clone_post_id) {
					wp_send_json_error(array('message' => 'Invalid ID'));
				}

				if (!current_user_can('edit_post', $vg_clone_post_id)) {
					wp_send_json_error(array('message' => 'Permission denied for this post'));
				}

				// get all required data for cloning
				$post_title = get_the_title($vg_clone_post_id)." - Duplicate";
				$post_type = "video_gallery";
				$post_status = "draft";
				
				// get gallery post meta settings for cloning with fallback check
				$gallery_settings = get_post_meta( $vg_clone_post_id, "nvgall_vg_settings_".$vg_clone_post_id, true);
				if ( empty( $gallery_settings ) ) {
					$gallery_settings = get_post_meta( $vg_clone_post_id, "awl_vg_settings_".$vg_clone_post_id, true);
				}
				
				//cloning post
				$vg_cloning_post_array =  array(
					'post_title' => $post_title,
					'post_type' => $post_type,
					'post_status' => $post_status,
				);
				
				$vg_cloned_post_id = wp_insert_post($vg_cloning_post_array);
				if ($vg_cloned_post_id && !is_wp_error($vg_cloned_post_id)) {
					// images post meta settings cloning using compliant key
					add_post_meta( $vg_cloned_post_id, "nvgall_vg_settings_".$vg_cloned_post_id, $gallery_settings);
					wp_send_json_success();
				} else {
					wp_send_json_error(array('message' => 'Failed to clone post'));
				}
			}
			wp_send_json_error(array('message' => 'Invalid request'));
		}
		
		public function enqueue_scripts_in_header() {
			wp_enqueue_script('jquery');
		}
		
		// Video Gallery cpt shortcode column before date columns
		public function set_video_gallery_shortcode_column_name($defaults) {
			$new = array();
			unset($defaults['tags']);	// remove it from the columns list
			foreach($defaults as $key=>$value) {
				if($key=='date') {  // when we find the date column
				   $new['_video_gallery_shodrcode'] = __( 'Shortcode', 'new-video-gallery' );  // put the tags column before it
				   $new['_video_gallery_duplicate'] = __( 'Duplicate', 'new-video-gallery' );  // put the tags column before it
				}
				$new[$key] = $value;
			}
			return $new;  
		}
		
		// Video Gallery cpt shortcode column data
		public function custom_video_gallery_shodrcode_data( $column, $post_id ) {
			switch ( $column ) {
				case '_video_gallery_shodrcode' :
					echo "<input type='text' class='button button-primary' id='shortcode-" . esc_attr($post_id) . "' value='[VDGAL id=" . esc_attr($post_id) . "]' style='font-weight:bold; background-color:#32373C; color:#FFFFFF; text-align:center;' />";
					echo "<input type='button' class='button button-primary' onclick='return GalleryCopyShortcode" . esc_attr($post_id) . "();' readonly value='Copy' style='margin-left:4px;' />";
					echo "<span id='copy-msg-" . esc_attr($post_id) . "' class='button button-primary' style='display:none; background-color:#32CD32; color:#FFFFFF; margin-left:4px; border-radius: 4px;'>" . esc_html__('copied', 'new-video-gallery') . "</span>";
					echo "<script>
						function GalleryCopyShortcode" . esc_attr($post_id) . "() {
							var copyText = document.getElementById('shortcode-" . esc_attr($post_id) . "');
							copyText.select();
							document.execCommand('copy');
							//fade in and out copied message
							jQuery('#copy-msg-" . esc_attr($post_id) . "').fadeIn('1000', 'linear');
							jQuery('#copy-msg-" . esc_attr($post_id) . "').fadeOut(2500,'swing');
						}
						</script>
					";
				break;
				case '_video_gallery_duplicate' :
					echo "<input type='button' class='button button-primary' onclick='return vg_clone_run_$post_id($post_id);' readonly value='Duplicate Gallery' style='margin-left:4px;' />";
					echo "<script>
						function vg_clone_run_$post_id(post_id){
							if(confirm('Do you want to duplicate this Gallery?')){
								var formData = {
									'action': 'vg_clone_gallery',
									'vg_clone_post_id': post_id,
									'security': '" . esc_js(wp_create_nonce('vg_clone_gallery_nonce')) . "'
								};
								jQuery.ajax({
									type: 'post',
									dataType: 'json',
									url: ajaxurl,
									data: formData,
									success: function(response){
										if (response.success) {
											location.href = 'edit.php?post_type=video_gallery';
										} else {
											alert('Failed to clone: ' + (response.data ? response.data.message : 'Unknown error'));
										}
									},
									error: function() {
										alert('Request failed');
									}
								});
							}
						}
						</script>
					";
				break;
				
			}
		}
		
		public function _load_textdomain() {
			load_plugin_textdomain( 'new-video-gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );		
		}		
		
		public function _srgallery_menu() {
			if ( function_exists( 'add_submenu_page' ) ) {
				add_submenu_page( 'edit.php?post_type='.VG_PLUGIN_SLUG, __( 'Video Analytics', 'new-video-gallery' ), __( 'Video Analytics', 'new-video-gallery' ), 'manage_options', 'vg-analytics', array( $this, '_vg_analytics_page') );
				$help_menu = add_submenu_page( 'edit.php?post_type='.VG_PLUGIN_SLUG, __( 'Docs', 'new-video-gallery' ), __( 'Docs', 'new-video-gallery' ), 'manage_options', 'vg-doc-page', array( $this, '_vg_doc_page') );
				add_submenu_page( 'edit.php?post_type='.VG_PLUGIN_SLUG, __( 'Our Plugins', 'new-video-gallery' ), __( 'Our Plugins', 'new-video-gallery' ), 'manage_options', 'vg-our-plugins', array( $this, '_vg_our_plugins_page') );
				add_submenu_page( 'edit.php?post_type='.VG_PLUGIN_SLUG, __( 'Our Themes', 'new-video-gallery' ), __( 'Our Themes', 'new-video-gallery' ), 'manage_options', 'vg-our-themes', array( $this, '_vg_our_themes_page') );
			}
		}
		
		/**
		 * Image Gallery Custom Post
		*/
		public function _New_Video_Gallery() {
			$labels = array(
				'name'               => _x( 'Video Gallery', 'new-video-gallery' ),
				'singular_name'      => _x( 'Video Gallery', 'new-video-gallery' ),
				'menu_name'          => _x( 'Video Gallery', 'new-video-gallery' ),
				'name_admin_bar'     => _x( 'Video Gallery', 'new-video-gallery' ),
				'add_new'            => _x( 'Add Video Gallery','new-video-gallery' ),
				'add_new_item'       => __( 'Add Video Gallery', 'new-video-gallery' ),
				'new_item'           => __( 'New Video Gallery ', 'new-video-gallery' ),
				'edit_item'          => __( 'Edit Video Gallery', 'new-video-gallery' ),
				'view_item'          => __( 'View Video Gallery', 'new-video-gallery' ),
				'all_items'          => __( 'All Video Gallery', 'new-video-gallery' ),
				'search_items'       => __( 'Search Video Gallery', 'new-video-gallery' ),
				'parent_item_colon'  => __( 'Parent Video Gallery:', 'new-video-gallery' ),
				'not_found'          => __( 'Video Gallery Not found.', 'new-video-gallery' ),
				'not_found_in_trash' => __( 'Video Gallery Not found in Trash.', 'new-video-gallery' )
			);

			$args = array(
				'label'               => __( 'Video Gallery', 'new-video-gallery' ),
				'description'         => __( 'Custom Post Type For Video Gallery', 'new-video-gallery' ),
				'labels'              => $labels,
				'supports'            => array( 'title'),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 65,
				'menu_icon'           => 'dashicons-images-alt2',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'query_var'           => false,
				'rewrite'             => false,
				'capability_type'     => 'page',
			);

			register_post_type( 'video_gallery', $args );
		}// end of post type function
		
		/**
		 * Adds Meta Boxes
		 */
		public function _admin_add_meta_box() {
			add_meta_box( '1', __( 'Copy Video Gallery Shortcode', 'new-video-gallery' ), array( &$this, '_vg_shortcode_left_metabox' ), 'video_gallery', 'side', 'high' );
			add_meta_box( '', __('Add Video', 'new-video-gallery'), array(&$this, 'vg_upload_multiple_images'), 'video_gallery', 'normal', 'high' );
		}
		
		// Video gallery copy shortcode meta box under publish button
		public function _vg_shortcode_left_metabox( $post ) { ?>
			<div class="vg-shortcode-container">
				<div class="vg-shortcode-input-wrapper">
					<input type="text" name="VIDEOCopyShortcode" id="VIDEOCopyShortcode" value="<?php echo '[VDGAL id=' . esc_attr( $post->ID ) . ']'; ?>" readonly>
					<button type="button" class="vg-shortcode-copy-btn" onclick="copyToClipboard('#VIDEOCopyShortcode')" title="<?php esc_attr_e('Copy Shortcode', 'new-video-gallery'); ?>">
						<span class="dashicons dashicons-clipboard"></span>
					</button>
				</div>
				<div id="vg-copy-code" class="vg-copy-success-msg" style="display: none;">
					<span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Copied successfully!', 'new-video-gallery' ); ?>
				</div>
				<p class="vg-shortcode-help-text"><?php esc_html_e( 'Copy & Embed this shortcode into any Page, Post, or Text Widget to display the gallery.', 'new-video-gallery' ); ?></p>
			</div>

			<style>
				.vg-shortcode-container {
					padding: 6px 2px;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				}
				.vg-shortcode-input-wrapper {
					position: relative;
					display: flex;
					align-items: center;
					border: 2px dashed #007cba;
					border-radius: 6px;
					background: #f6f7f7;
					overflow: hidden;
					margin-bottom: 12px;
					transition: border-color 0.2s ease-in-out;
				}
				.vg-shortcode-input-wrapper:hover {
					border-color: #005682;
				}
				#VIDEOCopyShortcode {
					border: none !important;
					background: transparent !important;
					box-shadow: none !important;
					height: 48px !important;
					line-height: 48px !important;
					font-size: 16px !important;
					font-weight: 600 !important;
					color: #32373c !important;
					text-align: left !important;
					padding: 0 45px 0 12px !important;
					width: 100% !important;
					margin: 0 !important;
					cursor: text;
				}
				.vg-shortcode-copy-btn {
					position: absolute;
					right: 6px;
					top: 50%;
					transform: translateY(-50%);
					background: #007cba !important;
					border: none !important;
					border-radius: 4px !important;
					color: #fff !important;
					cursor: pointer !important;
					width: 32px !important;
					height: 32px !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					padding: 0 !important;
					transition: background-color 0.15s ease-in-out, transform 0.1s ease !important;
				}
				.vg-shortcode-copy-btn:hover {
					background: #005682 !important;
				}
				.vg-shortcode-copy-btn:active {
					transform: translateY(-50%) scale(0.95);
				}
				.vg-shortcode-copy-btn .dashicons {
					font-size: 18px;
					width: 18px;
					height: 18px;
					line-height: 18px;
					margin: 0;
				}
				.vg-copy-success-msg {
					background-color: #ecfdf5;
					border: 1px solid #a7f3d0;
					color: #065f46;
					padding: 8px 12px;
					border-radius: 6px;
					font-size: 13px;
					font-weight: 500;
					margin-bottom: 12px;
					display: flex;
					align-items: center;
					gap: 6px;
				}
				.vg-copy-success-msg .dashicons {
					color: #059669;
					font-size: 16px;
					width: 16px;
					height: 16px;
				}
				.vg-shortcode-help-text {
					font-size: 12px;
					color: #646970;
					line-height: 1.5;
					margin: 0;
				}
			</style>
			
			<script>
				jQuery( "#vg-copy-code" ).hide();
				function copyToClipboard(element) {
				  var textToCopy = jQuery(element).val();
				  if (navigator.clipboard && navigator.clipboard.writeText) {
				    navigator.clipboard.writeText(textToCopy).then(function() {
				      jQuery( "#VIDEOCopyShortcode" ).select();
				      jQuery( "#vg-copy-code" ).stop(true, true).fadeIn().delay(2000).fadeOut();
				    });
				  } else {
				    var $temp = jQuery("<input>");
				    jQuery("body").append($temp);
				    $temp.val(textToCopy).select();
				    document.execCommand("copy");
				    $temp.remove();
				    jQuery( "#VIDEOCopyShortcode" ).select();
				    jQuery( "#vg-copy-code" ).stop(true, true).fadeIn().delay(2000).fadeOut();
				  }
				}
			</script>
			<?php
		}
		
		public function vg_upload_multiple_images($post) {
			require_once( VG_PLUGIN_DIR . 'admin/video-gallery-settings.php' );
		}
		
		public function _vg_ajax_callback_function($id) {
			$thumbnail = wp_get_attachment_image_src($id, 'medium', true);
			$attachment = get_post( $id ); // $id = attachment id
			$image_type = 'y';
			$poster_type = 'internal';
			?>
			<li class="slide">
				<div class="vg-image-preview">
					<div class="vg-image-controls">
						<div class="vg-move-handle" title="<?php esc_attr_e('Drag to reorder', 'new-video-gallery'); ?>"><span class="dashicons dashicons-move"></span></div>
						<a class="pw-trash-icon remove-slide" name="remove-slide" href="#" id="remove-slide" title="<?php esc_attr_e('Delete banner', 'new-video-gallery'); ?>"><span class="dashicons dashicons-trash"></span></a>
					</div>
					<img class="new-slide" src="<?php echo esc_url($thumbnail[0]); ?>" data-original-src="<?php echo esc_url($thumbnail[0]); ?>" alt="<?php echo esc_html(get_the_title($id)); ?>">
				</div>
				<div class="vg-image-info">
					<input type="hidden" id="slide-ids[]" name="slide-ids[]" value="<?php echo esc_attr( $id ); ?>" />
					<select id="slide-type[]" name="slide-type[]" class="form-control sel_<?php echo esc_attr( $id ); ?>" placeholder="<?php esc_attr_e('Image Title', 'new-video-gallery'); ?>" value="<?php echo esc_attr($image_type); ?>" >
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
					<input type="text" name="slide-link[]" id="slide-link[]" placeholder="<?php echo esc_attr($placeholder); ?>">
					<input type="text" name="slide-title[]" id="slide-title[]" placeholder="<?php esc_attr_e('Video Title', 'new-video-gallery'); ?>" value="<?php echo esc_html(get_the_title($id)); ?>">
					<textarea name="slide-desc[]" id="slide-desc[]" placeholder="<?php esc_attr_e('Video Description', 'new-video-gallery'); ?>"><?php echo esc_html($attachment->post_content); ?></textarea>
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
		}
		
		public function _ajax_video_gallery()
		{
			if (current_user_can('manage_options')) {
				if (isset ($_POST['vg_add_images_nonce']) && wp_verify_nonce($_POST['vg_add_images_nonce'], 'vg_add_images')) {
					$this->_vg_ajax_callback_function($_POST['slideId']);
					wp_die();
				} else {
					print 'Sorry, your nonce did not verify.';
					wp_die();
				}
			}
			wp_die();
		}
		
		public function _vg_save_settings($post_id) {
			if (current_user_can('edit_post', $post_id)) {
				if (isset ($_POST['vg_save_nonce'])) {
					if (isset ($_POST['vg_save_nonce']) && wp_verify_nonce($_POST['vg_save_nonce'], 'vg_save_settings')) {
						
						$video_gallery_option         	= isset($_POST['video_gallery_option']) ? sanitize_text_field( $_POST['video_gallery_option'] ) : 'no_api';
						$video_gallery_api_key 			= isset($_POST['video_gallery_api_key']) ? sanitize_text_field( $_POST['video_gallery_api_key'] ) : '';
						$video_gallery_channel_link  	= isset($_POST['video_gallery_channel_link']) ? sanitize_text_field( $_POST['video_gallery_channel_link'] ) : '';
						$api_video_like  				= isset( $_POST['api_video_like'] ) ? sanitize_text_field( $_POST['api_video_like'] ) : 'true';
						$gal_thumb_size      			= isset( $_POST['gal_thumb_size'] ) ? sanitize_text_field( $_POST['gal_thumb_size'] ) : 'full';
						$gal_youtube_thumb_size      	= isset( $_POST['gal_youtube_thumb_size'] ) ? sanitize_text_field( $_POST['gal_youtube_thumb_size'] ) : 'medium';
						$col_large_desktops        		= isset($_POST['col_large_desktops']) ? sanitize_text_field( $_POST['col_large_desktops'] ) : 'col-lg-4';
						$col_desktops           		= isset($_POST['col_desktops']) ? sanitize_text_field( $_POST['col_desktops'] ) : 'col-md-4';
						$col_tablets           			= isset($_POST['col_tablets']) ? sanitize_text_field( $_POST['col_tablets'] ) : 'col-sm-6';
						$col_phones           			= isset($_POST['col_phones']) ? sanitize_text_field( $_POST['col_phones'] ) : 'col-xs-12';
 
						$video_icon = ($video_gallery_option === 'no_api') ? (isset($_POST['video_icon']) ? sanitize_text_field( $_POST['video_icon'] ) : 'false') : (isset($_POST['api_video_icon']) ? sanitize_text_field( $_POST['api_video_icon'] ) : 'true');
						$video_title           			= isset($_POST['video_title']) ? sanitize_text_field( $_POST['video_title'] ) : 'hide';
						$title_color           			= isset($_POST['title_color']) ? sanitize_text_field( $_POST['title_color'] ) : '#383838';
						$video_desc           			= isset($_POST['video_desc']) ? sanitize_text_field( $_POST['video_desc'] ) : 'hide';
						$desc_color           			= isset($_POST['desc_color']) ? sanitize_text_field( $_POST['desc_color'] ) : '#72777c';
						$auto_play           			= isset($_POST['auto_play']) ? sanitize_text_field( $_POST['auto_play'] ) : 'true';
						$auto_close           			= isset($_POST['auto_close']) ? sanitize_text_field( $_POST['auto_close'] ) : 'true';
						$v_gallery_load_more          	= 'no';
						$vg_limit         			 	= ($video_gallery_option === 'no_api') ? '' : '50';
 
						// Active design settings matching free features
						$thumb_spacing = (isset($_POST['thumb_spacing_toggle']) && $_POST['thumb_spacing_toggle'] === 'yes') ? 8 : 0;
						if ($video_gallery_option === 'no_api') {
							$thumb_border_radius = (isset($_POST['thumb_border_radius_toggle']) && $_POST['thumb_border_radius_toggle'] === 'yes') ? 8 : 0;
						} else {
							$thumb_border_radius = (isset($_POST['api_thumb_border_radius_toggle']) && $_POST['api_thumb_border_radius_toggle'] === 'yes') ? 8 : 0;
						}
						$thumb_border = isset($_POST['thumb_border']) ? intval($_POST['thumb_border']) : 0;

						$show_lightbox_title 			= isset($_POST['show_lightbox_title']) ? intval($_POST['show_lightbox_title']) : 1;
						$show_lightbox_loop 			= isset($_POST['show_lightbox_loop']) ? intval($_POST['show_lightbox_loop']) : 1;
						$old_settings_raw = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
						if (empty($old_settings_raw)) {
							$old_settings_raw = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
						}
						$old_settings = array();
						if (!empty($old_settings_raw)) {
							$old_settings = is_array($old_settings_raw) ? $old_settings_raw : json_decode($old_settings_raw, true);
						}
						$custom_css = isset($_POST['custom_css']) ? sanitize_text_field($_POST['custom_css']) : (isset($old_settings['custom_css']) ? $old_settings['custom_css'] : '');
						$api_video_title       			= isset($_POST['api_video_title']) ? sanitize_text_field($_POST['api_video_title']) : 'show';
						$api_video_desc       			= isset($_POST['api_video_desc']) ? sanitize_text_field($_POST['api_video_desc']) : 'show';
						
						// Ads & Monetization (Free Version Limits: 1 ad in middle)
						// Check if the current user is allowed to save raw HTML/JS scripts (unfiltered_html)
						if ( current_user_can( 'unfiltered_html' ) ) {
							$gallery_ad_script          = isset($_POST['gallery_ad_script']) ? wp_unslash($_POST['gallery_ad_script']) : '';
						} else {
							$gallery_ad_script          = isset($_POST['gallery_ad_script']) ? wp_kses_post(wp_unslash($_POST['gallery_ad_script'])) : '';
						}

						
						$api_thumb_title_desc_align    	= 'center';
						$api_title_char_limit           = isset($_POST['api_title_char_limit']) ? intval($_POST['api_title_char_limit']) : 50;
						$api_desc_char_limit            = isset($_POST['api_desc_char_limit']) ? intval($_POST['api_desc_char_limit']) : 150;
						$thumbnail_order       			= isset($_POST['thumbnail_order']) ? sanitize_text_field( $_POST['thumbnail_order'] ) : 'ASC';
						
						$i = 0;
						$image_ids     = array();
						$image_titles  = array();
						$image_type    = array();
						$image_desc    = array();
						$image_link    = array();
						$poster_type    = array();
						$image_ids_val = isset( $_POST['slide-ids'] ) ? (array) $_POST['slide-ids'] : array();
						$image_ids_val = array_map( 'sanitize_text_field', $image_ids_val );
						
						foreach($image_ids_val as $image_id_raw) {
							$image_id = absint($image_id_raw);
							if (!$image_id) {
								$i++;
								continue;
							}
							
							$slide_title = isset($_POST['slide-title'][ $i ]) ? sanitize_text_field( $_POST['slide-title'][ $i ] ) : '';
							$slide_type  = isset($_POST['slide-type'][ $i ]) ? sanitize_text_field( $_POST['slide-type'][ $i ] ) : '';
							$slide_desc  = isset($_POST['slide-desc'][ $i ]) ? sanitize_text_field( $_POST['slide-desc'][ $i ] ) : '';
							$slide_link  = isset($_POST['slide-link'][ $i ]) ? sanitize_text_field( $_POST['slide-link'][ $i ] ) : '';
							$p_type      = isset($_POST['poster-type'][ $i ]) ? sanitize_text_field( $_POST['poster-type'][ $i ] ) : '';

							$image_ids[]    = $image_id;
							$image_titles[] = $slide_title;
							$image_type[]   = $slide_type;
							$image_desc[]   = $slide_desc;
							$image_link[]   = $slide_link;
							$poster_type[]  = $p_type;

							if (get_post_type($image_id) === 'attachment' && current_user_can('edit_post', $image_id)) {
								$single_image_update = array(
									'ID'           => $image_id,
									'post_title'   => $slide_title,
									'post_content' => $slide_desc,
								);
								wp_update_post( $single_image_update );
							}
							$i++;
						}
							$gallery_settings = array(
								'slide-ids'      				 => $image_ids,
								'slide-title'    				 => $image_titles,
								'slide-type'    				 => $image_type,
								'slide-desc'    				 => $image_desc,
								'slide-link'    				 => $image_link,
								'poster-type'     				 => $poster_type,
								'video_gallery_option'  		 => $video_gallery_option,
								'video_gallery_api_key'  		 => $video_gallery_api_key,
								'video_gallery_channel_link'	 => $video_gallery_channel_link,
								'vimeo_gallery_access_token'  	 => $vimeo_gallery_access_token,
								'vimeo_gallery_username'  		 => $vimeo_gallery_username,
								'vimeo_gallery_channel_link'	 => $vimeo_gallery_channel_link,
								'vimeo_col_large_desktops'		 => $vimeo_col_large_desktops,
								'api_video_like'				 => $api_video_like,
								'gal_thumb_size'   				 => $gal_thumb_size,
								'gal_youtube_thumb_size'   		 => $gal_youtube_thumb_size,
								'gal_vimeo_thumb_size'   		 => $gal_vimeo_thumb_size,
								'gal_wistia_thumb_size'   		 => $gal_wistia_thumb_size,
								'gal_twitch_thumb_size'   		 => $gal_twitch_thumb_size,
								'twitch_client_id'               => $twitch_client_id,
								'twitch_client_secret'           => $twitch_client_secret,
								'twitch_channel_name'            => $twitch_channel_name,
								'twitch_content_source'          => $twitch_content_source,
								'twitch_clips_period'            => $twitch_clips_period,
								'yt_content_source'              => $yt_content_source,
								'yt_selected_playlist_id'        => $yt_selected_playlist_id,
								'yt_selected_playlist_name'      => $yt_selected_playlist_name,
								'vimeo_content_source'           => $vimeo_content_source,
								'vimeo_selected_album_id'        => $vimeo_selected_album_id,
								'vimeo_selected_album_name'      => $vimeo_selected_album_name,
								'col_large_desktops'    		 => $col_large_desktops,
								'col_desktops'        			 => $col_desktops,
								'col_tablets'          			 => $col_tablets,
								'col_phones'          			 => $col_phones,
								'video_icon'          			 => $video_icon,
								'video_title'          			 => $video_title,
								'title_color'          			 => $title_color,
								'video_desc'          			 => $video_desc,
								'desc_color'          			 => $desc_color,
								'auto_play'          			 => $auto_play,
								'auto_close'          			 => $auto_close,
								'v_gallery_load_more'          	 => $v_gallery_load_more,
								'vg_limit'          			 => $vg_limit,
								'thumb_spacing'          		 => $thumb_spacing,
								'thumb_border_radius'          	 => $thumb_border_radius,
								'thumb_border'          		 => $thumb_border,
								'show_lightbox_title' 			 => $show_lightbox_title,
								'show_lightbox_loop' 			 => $show_lightbox_loop,
								'custom_css'         			 => $custom_css,
								'api_video_title'        		 => isset($api_video_title) ? $api_video_title : 'show',
								'api_video_desc'        		 => isset($api_video_desc) ? $api_video_desc : 'show',
								'gallery_ad_script'              => $gallery_ad_script,

								'api_thumb_title_desc_align'     => isset($api_thumb_title_desc_align) ? $api_thumb_title_desc_align : 'center',
								'api_title_char_limit'           => isset($api_title_char_limit) ? $api_title_char_limit : 50,
								'api_desc_char_limit'            => isset($api_desc_char_limit) ? $api_desc_char_limit : 150,
								'thumbnail_order'       		 => $thumbnail_order,
							);				
						$nvgall_video_gallery_shortcode_setting = 'nvgall_vg_settings_' . $post_id;
						update_post_meta($post_id, $nvgall_video_gallery_shortcode_setting, $gallery_settings);
					} else {
						print 'Sorry, your nonce did not verify.';
						exit;
					}
				}
			}
		}// end save setting
		
		/**
		 * Video Gallery Docs Page
		 * Create doc page to help user to setup plugin
		 * @return    void.
		 */
		public function _vg_doc_page() {
			require_once( VG_PLUGIN_DIR . 'admin/docs.php' );
		}

		public function _vg_our_plugins_page() {
			require_once( VG_PLUGIN_DIR . 'includes/our-plugins.php' );
		}

		public function _vg_our_themes_page() {
			require_once( VG_PLUGIN_DIR . 'includes/our-themes.php' );
		}


		/**
		 * Fetch YouTube Channel Avatars
		 */
		private function fetch_youtube_channel_avatars($api_key, $channel_ids) {
			$channel_avatars = array();
			$channel_ids = array_unique(array_filter($channel_ids));
			if (empty($channel_ids) || empty($api_key)) {
				return $channel_avatars;
			}
			
			$ch_details_url = add_query_arg(array(
				'part' => 'snippet',
				'id' => implode(',', $channel_ids),
				'key' => $api_key
			), 'https://www.googleapis.com/youtube/v3/channels');

			$ch_details_response = wp_safe_remote_get($ch_details_url);
			if (!is_wp_error($ch_details_response)) {
				$ch_details_body = json_decode(wp_remote_retrieve_body($ch_details_response), true);
				if (!empty($ch_details_body['items'])) {
					foreach ($ch_details_body['items'] as $ch_item) {
						$ch_id = $ch_item['id'];
						$avatar = '';
						if (isset($ch_item['snippet']['thumbnails']['default']['url'])) {
							$avatar = $ch_item['snippet']['thumbnails']['default']['url'];
						} elseif (isset($ch_item['snippet']['thumbnails']['medium']['url'])) {
							$avatar = $ch_item['snippet']['thumbnails']['medium']['url'];
						}
						if (!empty($avatar)) {
							$channel_avatars[$ch_id] = $avatar;
						}
					}
				}
			}
			return $channel_avatars;
		}

		/**
		 * Fetch YouTube videos server-side and cache
		 */
		public function fetch_youtube_data($api_key, $source_link, $limit, $page_token = '', $post_id = 0) {
			$limit = 50;
			$res_size = 'medium'; // default
			$yt_content_source = 'all';
			$yt_selected_playlist_id = '';
			if ($post_id > 0) {
				$meta_val = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
				if (empty($meta_val)) {
					$meta_val = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
				}
				if (!empty($meta_val)) {
					$gallery_settings = is_array($meta_val) ? $meta_val : json_decode($meta_val, true);
					if (isset($gallery_settings['gal_youtube_thumb_size'])) {
						$res_size = $gallery_settings['gal_youtube_thumb_size'];
					}
				}
			}

			$post_modified = $post_id ? get_the_modified_date('U', $post_id) : '';
			$cache_key = 'vg_yt_cache_' . md5($source_link . '_' . $limit . '_' . $page_token . '_' . $res_size . '_' . $yt_content_source . '_' . $yt_selected_playlist_id . '_' . $post_modified);
			$cached = get_transient($cache_key);
			if ($cached !== false) {
				return $this->sort_api_videos($cached, $post_id);
			}

			if (empty($api_key)) {
				return new WP_Error('missing_api_key', 'YouTube API Key is missing.');
			}

			$is_playlist = false;
			$playlist_ids = array();

			if ($yt_content_source === 'playlist' && !empty($yt_selected_playlist_id)) {
				$is_playlist = true;
				$playlist_ids[] = $yt_selected_playlist_id;
			} else {
				if (strpos($source_link, ',') !== false || preg_match('/^[A-Za-z0-9_-]{10,}$/', $source_link)) {
					$is_playlist = true;
				}

				if (strpos($source_link, ',') !== false) {
					$playlist_ids = array_map('trim', explode(',', $source_link));
				} elseif ($is_playlist) {
					$playlist_ids[] = trim($source_link);
				}
			}

			$videos = array();
			$next_page_token = '';

			if (!empty($playlist_ids)) {
				foreach ($playlist_ids as $pid) {
					$url = add_query_arg(array(
						'part' => 'snippet',
						'playlistId' => $pid,
						'maxResults' => $limit,
						'key' => $api_key
					), 'https://www.googleapis.com/youtube/v3/playlistItems');

					if (!empty($page_token)) {
						$url = add_query_arg('pageToken', $page_token, $url);
					}

					$response = wp_safe_remote_get($url);
					if (is_wp_error($response)) {
						return $response;
					}
					$body = json_decode(wp_remote_retrieve_body($response), true);
					if (empty($body['items'])) {
						continue;
					}
					
					if (isset($body['nextPageToken'])) {
						$next_page_token = $body['nextPageToken'];
					}
					
					$vids_in_page = array();
					foreach ($body['items'] as $item) {
						if (isset($item['snippet']['resourceId']['videoId'])) {
							$vids_in_page[] = $item['snippet']['resourceId']['videoId'];
						}
					}

					if (!empty($vids_in_page)) {
						$stats_url = add_query_arg(array(
							'part' => 'snippet,statistics,contentDetails',
							'id' => implode(',', $vids_in_page),
							'key' => $api_key
						), 'https://www.googleapis.com/youtube/v3/videos');

						$stats_response = wp_safe_remote_get($stats_url);
						if (!is_wp_error($stats_response)) {
							$stats_body = json_decode(wp_remote_retrieve_body($stats_response), true);
							if (!empty($stats_body['items'])) {
								$channel_ids = array();
								foreach ($stats_body['items'] as $video_item) {
									if (isset($video_item['snippet']['channelId'])) {
										$channel_ids[] = $video_item['snippet']['channelId'];
									}
								}
								$channel_avatars = $this->fetch_youtube_channel_avatars($api_key, $channel_ids);

								foreach ($stats_body['items'] as $video_item) {
									$views = isset($video_item['statistics']['viewCount']) ? number_format(intval($video_item['statistics']['viewCount'])) : '0';
									$likes = isset($video_item['statistics']['likeCount']) ? number_format(intval($video_item['statistics']['likeCount'])) : '0';
									$comments = isset($video_item['statistics']['commentCount']) ? number_format(intval($video_item['statistics']['commentCount'])) : '0';
									$publishedAt = isset($video_item['snippet']['publishedAt']) ? $video_item['snippet']['publishedAt'] : '';
									
									$yt_thumb = '';
									if (isset($video_item['snippet']['thumbnails'][$res_size]['url'])) {
										$yt_thumb = $video_item['snippet']['thumbnails'][$res_size]['url'];
									} elseif (isset($video_item['snippet']['thumbnails']['medium']['url'])) {
										$yt_thumb = $video_item['snippet']['thumbnails']['medium']['url'];
									} elseif (isset($video_item['snippet']['thumbnails']['default']['url'])) {
										$yt_thumb = $video_item['snippet']['thumbnails']['default']['url'];
									}

									$ch_id = isset($video_item['snippet']['channelId']) ? $video_item['snippet']['channelId'] : '';
									$creator_avatar = isset($channel_avatars[$ch_id]) ? $channel_avatars[$ch_id] : '';

									$videos[] = array(
										'id' => $video_item['id'],
										'title' => isset($video_item['snippet']['title']) ? $video_item['snippet']['title'] : '',
										'description' => isset($video_item['snippet']['description']) ? $video_item['snippet']['description'] : '',
										'thumbnail' => $yt_thumb,
										'views' => $views,
										'timeAgo' => $this->time_ago($publishedAt),
										'duration' => isset($video_item['contentDetails']['duration']) ? $this->parse_youtube_duration($video_item['contentDetails']['duration']) : '',
										'is_hd' => (isset($video_item['contentDetails']['definition']) && $video_item['contentDetails']['definition'] === 'hd'),
										'channel_title' => isset($video_item['snippet']['channelTitle']) ? $video_item['snippet']['channelTitle'] : '',
										'creator_avatar' => $creator_avatar,
										'likes' => $likes,
										'comments' => $comments
									);
								}
							}
						}
					}
				}
			} else {
				$channel_id = $this->resolve_youtube_channel_id($api_key, $source_link);

				if (!empty($channel_id)) {
					$ch_url = add_query_arg(array(
						'part' => 'contentDetails',
						'id' => $channel_id,
						'key' => $api_key
					), 'https://www.googleapis.com/youtube/v3/channels');

					$ch_response = wp_safe_remote_get($ch_url);
					if (!is_wp_error($ch_response)) {
						$ch_body = json_decode(wp_remote_retrieve_body($ch_response), true);
						if (!empty($ch_body['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
							$uploads_playlist_id = $ch_body['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

							$pl_url = add_query_arg(array(
								'part' => 'snippet',
								'playlistId' => $uploads_playlist_id,
								'maxResults' => $limit,
								'key' => $api_key
							), 'https://www.googleapis.com/youtube/v3/playlistItems');

							if (!empty($page_token)) {
								$pl_url = add_query_arg('pageToken', $page_token, $pl_url);
							}

							$pl_response = wp_safe_remote_get($pl_url);
							if (!is_wp_error($pl_response)) {
								$pl_body = json_decode(wp_remote_retrieve_body($pl_response), true);
								if (!empty($pl_body['items'])) {
									if (isset($pl_body['nextPageToken'])) {
										$next_page_token = $pl_body['nextPageToken'];
									}

									$vids_in_page = array();
									foreach ($pl_body['items'] as $item) {
										if (isset($item['snippet']['resourceId']['videoId'])) {
											$vids_in_page[] = $item['snippet']['resourceId']['videoId'];
										}
									}

									if (!empty($vids_in_page)) {
										$stats_url = add_query_arg(array(
											'part' => 'snippet,statistics,contentDetails',
											'id' => implode(',', $vids_in_page),
											'key' => $api_key
										), 'https://www.googleapis.com/youtube/v3/videos');

										$stats_response = wp_safe_remote_get($stats_url);
										if (!is_wp_error($stats_response)) {
											$stats_body = json_decode(wp_remote_retrieve_body($stats_response), true);
											if (!empty($stats_body['items'])) {
												$channel_ids = array();
												foreach ($stats_body['items'] as $video_item) {
													if (isset($video_item['snippet']['channelId'])) {
														$channel_ids[] = $video_item['snippet']['channelId'];
													}
												}
												$channel_avatars = $this->fetch_youtube_channel_avatars($api_key, $channel_ids);

												foreach ($stats_body['items'] as $video_item) {
													$views = isset($video_item['statistics']['viewCount']) ? number_format(intval($video_item['statistics']['viewCount'])) : '0';
													$likes = isset($video_item['statistics']['likeCount']) ? number_format(intval($video_item['statistics']['likeCount'])) : '0';
													$comments = isset($video_item['statistics']['commentCount']) ? number_format(intval($video_item['statistics']['commentCount'])) : '0';
													$publishedAt = isset($video_item['snippet']['publishedAt']) ? $video_item['snippet']['publishedAt'] : '';
													
													$yt_thumb = '';
													if (isset($video_item['snippet']['thumbnails'][$res_size]['url'])) {
														$yt_thumb = $video_item['snippet']['thumbnails'][$res_size]['url'];
													} elseif (isset($video_item['snippet']['thumbnails']['medium']['url'])) {
														$yt_thumb = $video_item['snippet']['thumbnails']['medium']['url'];
													} elseif (isset($video_item['snippet']['thumbnails']['default']['url'])) {
														$yt_thumb = $video_item['snippet']['thumbnails']['default']['url'];
													}

													$ch_id = isset($video_item['snippet']['channelId']) ? $video_item['snippet']['channelId'] : '';
													$creator_avatar = isset($channel_avatars[$ch_id]) ? $channel_avatars[$ch_id] : '';

													$videos[] = array(
														'id' => $video_item['id'],
														'title' => isset($video_item['snippet']['title']) ? $video_item['snippet']['title'] : '',
														'description' => isset($video_item['snippet']['description']) ? $video_item['snippet']['description'] : '',
														'thumbnail' => $yt_thumb,
														'views' => $views,
														'timeAgo' => $this->time_ago($publishedAt),
														'duration' => isset($video_item['contentDetails']['duration']) ? $this->parse_youtube_duration($video_item['contentDetails']['duration']) : '',
														'is_hd' => (isset($video_item['contentDetails']['definition']) && $video_item['contentDetails']['definition'] === 'hd'),
														'channel_title' => isset($video_item['snippet']['channelTitle']) ? $video_item['snippet']['channelTitle'] : '',
														'creator_avatar' => $creator_avatar,
														'likes' => $likes,
														'comments' => $comments
													);
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}

			$data_to_cache = array(
				'videos' => $videos,
				'nextPageToken' => $next_page_token
			);

			set_transient($cache_key, $data_to_cache, 12 * HOUR_IN_SECONDS);
			return $this->sort_api_videos($data_to_cache, $post_id);
		}



		private function time_ago($dateString) {
			if (empty($dateString)) return '';
			$time = strtotime($dateString);
			if (!$time) return '';
			$diff = time() - $time;
			if ($diff < 60) return sprintf(_n('%d second ago', '%d seconds ago', $diff, 'new-video-gallery'), $diff);
			$diff = round($diff / 60);
			if ($diff < 60) return sprintf(_n('%d minute ago', '%d minutes ago', $diff, 'new-video-gallery'), $diff);
			$diff = round($diff / 60);
			if ($diff < 24) return sprintf(_n('%d hour ago', '%d hours ago', $diff, 'new-video-gallery'), $diff);
			$diff = round($diff / 24);
			if ($diff < 30) return sprintf(_n('%d day ago', '%d days ago', $diff, 'new-video-gallery'), $diff);
			$diff = round($diff / 30);
			if ($diff < 12) return sprintf(_n('%d month ago', '%d months ago', $diff, 'new-video-gallery'), $diff);
			$diff = round($diff / 12);
			return sprintf(_n('%d year ago', '%d years ago', $diff, 'new-video-gallery'), $diff);
		}

		private function parse_youtube_duration($youtube_time) {
			if (empty($youtube_time)) {
				return '';
			}
			try {
				$start = new DateTime('@0');
				$start->add(new DateInterval($youtube_time));
				$seconds = $start->getTimestamp();
				if ($seconds >= 3600) {
					return gmdate('H:i:s', $seconds);
				}
				return gmdate('i:s', $seconds);
			} catch (Exception $e) {
				return '';
			}
		}





		public function resolve_youtube_channel_id($api_key, $source_link) {
			$channel_id = '';
			$username = '';
			$handle = '';
			if (filter_var($source_link, FILTER_VALIDATE_URL)) {
				$path = parse_url($source_link, PHP_URL_PATH);
				$parts = explode('/', trim($path, '/'));
				if (count($parts) >= 2) {
					if ($parts[0] === 'channel') {
						$channel_id = $parts[1];
					} elseif ($parts[0] === 'user') {
						$username = $parts[1];
					} elseif ($parts[0] === 'c' || $parts[0] === 'handle') {
						$handle = $parts[1];
					}
				} elseif (count($parts) === 1 && strpos($parts[0], '@') === 0) {
					$handle = $parts[0];
				}
			} else {
				if (strpos($source_link, '@') === 0) {
					$handle = $source_link;
				} else {
					$channel_id = $source_link;
				}
			}

			if (empty($channel_id) && !empty($username)) {
				$user_url = add_query_arg(array(
					'part' => 'id',
					'forUsername' => $username,
					'key' => $api_key
				), 'https://www.googleapis.com/youtube/v3/channels');

				$user_resp = wp_safe_remote_get($user_url);
				if (!is_wp_error($user_resp)) {
					$user_body = json_decode(wp_remote_retrieve_body($user_resp), true);
					if (!empty($user_body['items'][0]['id'])) {
						$channel_id = $user_body['items'][0]['id'];
					}
				}
			}

			if (empty($channel_id) && !empty($handle)) {
				$handle_url = add_query_arg(array(
					'part' => 'id',
					'forHandle' => $handle,
					'key' => $api_key
				), 'https://www.googleapis.com/youtube/v3/channels');

				$handle_resp = wp_safe_remote_get($handle_url);
				if (!is_wp_error($handle_resp)) {
					$handle_body = json_decode(wp_remote_retrieve_body($handle_resp), true);
					if (!empty($handle_body['items'][0]['id'])) {
						$channel_id = $handle_body['items'][0]['id'];
					}
				}
			}

			return $channel_id;
		}

		public function nvgall_fetch_youtube_content() {
			if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
				wp_send_json_error(array('message' => 'Unauthorized'));
			}

			$api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
			$channel_link = isset($_POST['channel_link']) ? sanitize_text_field($_POST['channel_link']) : '';

			if (empty($api_key)) {
				wp_send_json_error(array('message' => 'YouTube API Key is missing.'));
			}
			if (empty($channel_link)) {
				wp_send_json_error(array('message' => 'YouTube Channel Link is missing.'));
			}

			$channel_id = $this->resolve_youtube_channel_id($api_key, $channel_link);
			if (empty($channel_id)) {
				wp_send_json_error(array('message' => 'Could not resolve Channel ID. Please check the Channel Link.'));
			}

			wp_send_json_success(array('playlists' => array()));
		}



		public function manage_gallery_sync_cron( $post_id, $bg_sync_interval ) {
			wp_clear_scheduled_hook( 'nvgall_sync_single_gallery_cron', array( $post_id ) );

			if ( ! empty( $bg_sync_interval ) && $bg_sync_interval !== 'disabled' ) {
				wp_schedule_event( time() + 10, $bg_sync_interval, 'nvgall_sync_single_gallery_cron', array( $post_id ) );
			}
		}

		public function clear_gallery_sync_cron_on_delete( $post_id ) {
			if ( get_post_type( $post_id ) === 'video_gallery' ) {
				wp_clear_scheduled_hook( 'nvgall_sync_single_gallery_cron', array( $post_id ) );
			}
		}

		public function run_single_gallery_sync( $post_id ) {
			if ( get_post_type( $post_id ) !== 'video_gallery' ) {
				return;
			}

			$meta_val = get_post_meta( $post_id, 'nvgall_vg_settings_' . $post_id, true );
			if ( empty( $meta_val ) ) {
				$meta_val = get_post_meta( $post_id, 'awl_vg_settings_' . $post_id, true );
			}
			if ( empty( $meta_val ) ) {
				return;
			}

			$gallery_settings = is_array( $meta_val ) ? $meta_val : json_decode( $meta_val, true );
			$video_gallery_option = isset( $gallery_settings['video_gallery_option'] ) ? $gallery_settings['video_gallery_option'] : 'no_api';

			if ( $video_gallery_option === 'no_api' ) {
				return;
			}

			$post_modified = get_the_modified_date( 'U', $post_id );
			$limit = isset( $gallery_settings['limit'] ) ? intval( $gallery_settings['limit'] ) : 8;

			if ( $video_gallery_option === 'video_yoyube_api' ) {
				$api_key = isset( $gallery_settings['api_key'] ) ? $gallery_settings['api_key'] : '';
				$source_link = isset( $gallery_settings['source_link'] ) ? $gallery_settings['source_link'] : '';
				$res_size = isset( $gallery_settings['gal_youtube_thumb_size'] ) ? $gallery_settings['gal_youtube_thumb_size'] : 'medium';
				$yt_content_source = 'all';
				$yt_selected_playlist_id = '';
				
				$cache_key = 'vg_yt_cache_' . md5( $source_link . '_' . $limit . '__' . $res_size . '_' . $yt_content_source . '_' . $yt_selected_playlist_id . '_' . $post_modified );
				delete_transient( $cache_key );
				$this->fetch_youtube_data( $api_key, $source_link, $limit, '', $post_id );
			}
		}



		private function sort_api_videos($data, $post_id) {
			if (empty($data['videos']) || !$post_id) {
				return $data;
			}
			$meta_val = get_post_meta($post_id, 'nvgall_vg_settings_' . $post_id, true);
			if (empty($meta_val)) {
				$meta_val = get_post_meta($post_id, 'awl_vg_settings_' . $post_id, true);
			}
			if (!empty($meta_val)) {
				$gallery_settings = is_array($meta_val) ? $meta_val : json_decode($meta_val, true);
				$thumbnail_order = isset($gallery_settings['thumbnail_order']) ? $gallery_settings['thumbnail_order'] : 'ASC';
				if ($thumbnail_order === 'ASC') { // ASC represents Old First (reverse default feed ordering which is Newest First)
					$data['videos'] = array_reverse($data['videos']);
				} elseif ($thumbnail_order === 'RANDOM') {
					shuffle($data['videos']);
				}
			}
			return $data;
		}


		public function _vg_analytics_page() {
			if ( isset( $_GET['action'] ) && $_GET['action'] === 'export_csv' ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( __( 'Permission denied.', 'new-video-gallery' ) );
				}
				check_admin_referer( 'vg_export_csv_action', 'security' );

				$analytics = get_option( 'vg_video_analytics', array() );

				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=video-playback-analytics-' . current_time( 'Y-m-d' ) . '.csv' );

				$output = fopen( 'php://output', 'w' );

				// Add UTF-8 BOM for Excel formatting
				fputs( $output, "\xEF\xBB\xBF" );

				// Column headers
				fputcsv( $output, array(
					__( 'Video Title', 'new-video-gallery' ),
					__( 'Gallery ID', 'new-video-gallery' ),
					__( 'Gallery Name', 'new-video-gallery' ),
					__( 'Platform / Source', 'new-video-gallery' ),
					__( 'Plays', 'new-video-gallery' ),
					__( 'Plays (Desktop)', 'new-video-gallery' ),
					__( 'Plays (Mobile)', 'new-video-gallery' ),
					__( 'Plays (Tablet)', 'new-video-gallery' ),
					__( 'Last Clicked Time', 'new-video-gallery' )
				) );

				if ( ! empty( $analytics ) ) {
					foreach ( $analytics as $item ) {
						$desktop = isset( $item['devices']['desktop'] ) ? intval( $item['devices']['desktop'] ) : 0;
						$mobile  = isset( $item['devices']['mobile'] ) ? intval( $item['devices']['mobile'] ) : 0;
						$tablet  = isset( $item['devices']['tablet'] ) ? intval( $item['devices']['tablet'] ) : 0;
						
						fputcsv( $output, array(
							$item['video_title'],
							$item['gallery_id'],
							$item['gallery_title'],
							strtoupper( $item['source'] ),
							$item['plays'],
							$desktop,
							$mobile,
							$tablet,
							$item['last_played']
						) );
					}
				}

				fclose( $output );
				exit;
			}

			require_once( VG_PLUGIN_DIR . 'admin/video-gallery-analytics.php' );
		}

		private function get_device_type() {
			$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
			if ( empty( $ua ) ) {
				return 'desktop';
			}

			$tablet_regex = '/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i';
			$mobile_regex = '/(mobi|ipod|phone|blackberry|opera mini|fennec|minimo|symbian|psp|nintendo ds)/i';

			if ( preg_match( $tablet_regex, $ua ) ) {
				return 'tablet';
			}
			if ( preg_match( $mobile_regex, $ua ) ) {
				return 'mobile';
			}
			return 'desktop';
		}

		public function vg_track_video_play() {
			check_ajax_referer( 'vg_analytics_nonce', 'security' );

			$gallery_id   = isset( $_POST['gallery_id'] ) ? intval( $_POST['gallery_id'] ) : 0;
			$video_title  = isset( $_POST['video_title'] ) ? sanitize_text_field( wp_unslash( $_POST['video_title'] ) ) : '';
			$video_source = isset( $_POST['video_source'] ) ? sanitize_text_field( $_POST['video_source'] ) : 'local';

			if ( ! empty( $gallery_id ) && ! empty( $video_title ) ) {
				$analytics = get_option( 'vg_video_analytics', array() );

				$gallery_title = get_the_title( $gallery_id );
				if ( empty( $gallery_title ) ) {
					$gallery_title = sprintf( __( 'Gallery #%d', 'new-video-gallery' ), $gallery_id );
				}

				$tracking_key = md5( $gallery_id . '_' . $video_title );

				if ( ! isset( $analytics[ $tracking_key ] ) ) {
					$analytics[ $tracking_key ] = array(
						'gallery_id'    => $gallery_id,
						'gallery_title' => $gallery_title,
						'video_title'   => $video_title,
						'source'        => $video_source,
						'plays'         => 0,
						'last_played'   => '',
						'devices'       => array(
							'desktop' => 0,
							'mobile'  => 0,
							'tablet'  => 0
						)
					);
				} else {
					$analytics[ $tracking_key ]['gallery_title'] = $gallery_title;
				}

				if ( ! isset( $analytics[ $tracking_key ]['devices'] ) ) {
					$analytics[ $tracking_key ]['devices'] = array(
						'desktop' => 0,
						'mobile'  => 0,
						'tablet'  => 0
					);
				}

				$device_type = $this->get_device_type();
				$analytics[ $tracking_key ]['devices'][ $device_type ]++;

				$analytics[ $tracking_key ]['plays']++;
				$analytics[ $tracking_key ]['last_played'] = current_time( 'mysql' );

				update_option( 'vg_video_analytics', $analytics );
				wp_send_json_success( array( 'plays' => $analytics[ $tracking_key ]['plays'] ) );
			}

			wp_send_json_error( 'Invalid request arguments' );
		}


	}
	
	// register sf scripts
	if ( ! function_exists( 'awplife_vg_register_scripts' ) ) {
		function awplife_vg_register_scripts(){
			
			wp_register_script('vg-isotope-js', VG_PLUGIN_URL .'assets/js/isotope.pkgd.js', array('jquery'), '', true);

			//video js
			wp_register_script('vg-lightgallery-js', VG_PLUGIN_URL .'assets/vendor/lightgallery/js/lightgallery-all.min.js', array('jquery'), '1.10.0', true);
			wp_register_script('vg-lightbox-init', VG_PLUGIN_URL .'assets/js/vg-lightbox-init.js', array('jquery', 'vg-lightgallery-js', 'vg-isotope-js'), VG_PLUGIN_VER . '.' . time(), true);
			wp_localize_script('vg-lightbox-init', 'vg_analytics_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('vg_analytics_nonce')
			));

			// modern CSS variables columns grid
			wp_register_style('vg-frontend-grid-css', VG_PLUGIN_URL .'assets/css/vg-frontend-grid.css', array(), VG_PLUGIN_VER . '.' . time());
			wp_register_style('vg-video-icon-css', VG_PLUGIN_URL .'assets/css/video-icon.css');
			wp_register_style('vg-api-frontend-css', VG_PLUGIN_URL .'assets/css/vg-api-frontend.css', array(), VG_PLUGIN_VER . '.' . time());
			wp_register_style('vg-lightgallery-css', VG_PLUGIN_URL .'assets/vendor/lightgallery/css/lightgallery.min.css', array(), '1.10.0');
			wp_register_style('vg-lg-transitions', VG_PLUGIN_URL .'assets/vendor/lightgallery/css/lg-transitions.min.css', array(), '1.10.0');
			
		}	
		add_action( 'init', 'awplife_vg_register_scripts' );
	}
		
	$vg_gallery_object = new New_Video_Gallery();
	require_once( VG_PLUGIN_DIR . 'includes/shortcode.php' );
	
	// Integrations
	require_once( VG_PLUGIN_DIR . 'includes/elementor-widget.php' );
	require_once( VG_PLUGIN_DIR . 'includes/gutenberg-block.php' );
}
?>