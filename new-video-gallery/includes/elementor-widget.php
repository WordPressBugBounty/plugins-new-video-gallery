<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Register all VGP frontend assets early so they're available in any context.
 * The scripts are already registered in new-video-gallery.php via awplife_vg_register_scripts.
 */

/**
 * Force-enqueue gallery assets on Elementor preview pages.
 */
add_action('elementor/preview/enqueue_styles', 'awl_vgp_elementor_enqueue_preview_assets');
function awl_vgp_elementor_enqueue_preview_assets() {
    wp_enqueue_style('vg-frontend-grid-css');
    wp_enqueue_style('vg-api-frontend-css');
    wp_enqueue_style('vg-lightgallery-css');
    
    wp_enqueue_script('jquery');
    wp_enqueue_script('imagesloaded');
    wp_enqueue_script('vg-isotope-js');
    wp_enqueue_script('vg-lightgallery-js');
    wp_enqueue_script('vg-lightbox-init');
}

/**
 * Register Elementor Widget for Video Gallery Premium
 */
add_action('elementor/widgets/register', 'awl_video_gallery_register_elementor_widget');
function awl_video_gallery_register_elementor_widget($widgets_manager) {
    if (class_exists('\Elementor\Widget_Base')) {

        class Elementor_Video_Gallery_Widget extends \Elementor\Widget_Base {

            public function get_name() {
                return 'video_gallery_premium_widget';
            }

            public function get_title() {
                return esc_html__('Video Gallery Premium', 'new-video-gallery');
            }

            public function get_icon() {
                return 'eicon-video-playlist';
            }

            public function get_categories() {
                return array('general');
            }

            public function get_keywords() {
                return array('video', 'gallery', 'youtube', 'vimeo', 'playlist', 'premium');
            }

            public function get_style_depends() {
                return array('vg-frontend-grid-css', 'vg-api-frontend-css', 'vg-lightgallery-css');
            }

            public function get_script_depends() {
                return array('imagesloaded', 'vg-isotope-js', 'vg-lightgallery-js', 'vg-lightbox-init');
            }

            protected function register_controls() {
                $this->start_controls_section(
                    'section_content',
                    array(
                        'label' => esc_html__('Gallery Source Settings', 'new-video-gallery'),
                        'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                    )
                );

                // Fetch Galleries List
                $all_galleries = get_posts(array(
                    'post_type'      => 'video_gallery',
                    'posts_per_page' => -1,
                    'post_status'    => 'any',
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ));

                $gallery_options = array('' => esc_html__('-- Select Gallery --', 'new-video-gallery'));
                if (!empty($all_galleries)) {
                    foreach ($all_galleries as $g) {
                        $gallery_options[$g->ID] = $g->post_title ? $g->post_title . ' (ID: ' . $g->ID . ')' : esc_html__('(no title)', 'new-video-gallery') . ' (ID: ' . $g->ID . ')';
                    }
                }

                $this->add_control(
                    'gallery_id',
                    array(
                        'label'     => esc_html__('Select Gallery', 'new-video-gallery'),
                        'type'      => \Elementor\Controls_Manager::SELECT,
                        'options'   => $gallery_options,
                        'default'   => '',
                    )
                );

                $this->end_controls_section();
            }

            protected function render() {
                $settings = $this->get_settings_for_display();
                
                if (empty($settings['gallery_id'])) {
                    echo '<div style="padding:20px; border:1px dashed #ccc; text-align:center;">' . esc_html__('Please select a Video Gallery.', 'new-video-gallery') . '</div>';
                    return;
                }

                $gallery_id = (int)$settings['gallery_id'];

                // Detect Elementor editor/preview context
                $is_elementor_editor = false;
                if (class_exists('\Elementor\Plugin')) {
                    if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
                        $is_elementor_editor = true;
                    }
                }

                if ($is_elementor_editor) {
                    // In Elementor editor: inject CSS <link> tags directly into the HTML output
                    // because wp_enqueue_style() calls are ignored during AJAX widget re-renders.
                    $css_files = array(
                        VG_PLUGIN_URL . 'assets/css/vg-frontend-grid.css',
                        VG_PLUGIN_URL . 'assets/css/vg-api-frontend.css',
                        VG_PLUGIN_URL . 'assets/vendor/lightgallery/css/lightgallery.min.css',
                    );
                    $ver = VG_PLUGIN_VER;
                    foreach ($css_files as $css_url) {
                        $css_url_versioned = esc_url($css_url) . '?ver=' . esc_attr($ver);
                        echo '<link rel="stylesheet" href="' . $css_url_versioned . '" type="text/css" media="all" />' . "\n";
                    }
                }

                // Render the gallery shortcode
                echo do_shortcode('[VDGAL id=' . $gallery_id . ']');

                if ($is_elementor_editor) {
                    // In Elementor editor: inject inline JS to initialize Isotope and lightgallery
                    ?>
                    <script type="text/javascript">
                    (function() {
                        function vgInitGallery() {
                            if (typeof jQuery === 'undefined') return;
                            var $ = jQuery;
                            var $gallery = $('.youram-simple_<?php echo esc_js($gallery_id); ?>');
                            if (!$gallery.length) return;

                            // Initialize LightGallery if available
                            if (typeof $.fn.lightGallery !== 'undefined') {
                                $gallery.lightGallery({
                                    selector: '.vgp-trigger',
                                    youtubePlayerParams: { modestbranding: 1, showinfo: 0, rel: 0, controls: 1 },
                                    vimeoPlayerParams: { byline: 0, portrait: 0, color: '1ab7ea' },
                                    download: false
                                });
                            }
                            
                            // Initialize Isotope if available and masonry is needed
                            if (typeof $.fn.isotope !== 'undefined') {
                                if ($gallery.hasClass('vg-layout-masonry')) {
                                    if (typeof $.fn.imagesLoaded !== 'undefined') {
                                        $gallery.imagesLoaded(function() {
                                            $gallery.isotope({
                                                itemSelector: '.single-image',
                                                layoutMode: 'masonry',
                                                transitionDuration: '0.4s'
                                            });
                                        });
                                    }
                                }
                            }
                        }

                        // Try immediately and also after a short delay
                        vgInitGallery();
                        setTimeout(vgInitGallery, 500);
                        setTimeout(vgInitGallery, 1500);
                    })();
                    </script>
                    <?php
                }
            }
        }

        // Register widget instance
        $widgets_manager->register(new \Elementor_Video_Gallery_Widget());
    }
}
