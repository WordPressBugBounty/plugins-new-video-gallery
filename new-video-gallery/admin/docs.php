<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap vgp-docs-wrap">
    <!-- Sidebar Navigation -->
    <div class="vgp-docs-sidebar">
        <div class="vgp-sidebar-logo">
            <span class="dashicons dashicons-video-alt3"></span>
            <span><?php esc_html_e('Mastery Guide', 'new-video-gallery'); ?></span>
        </div>
        <ul class="vgp-toc">
            <li><a href="#quick-start" class="active"><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Quick Start Guide', 'new-video-gallery'); ?></a></li>
            <li><a href="#tab-sources"><span class="dashicons dashicons-playlist-video"></span> <?php esc_html_e('1. Video Sources', 'new-video-gallery'); ?></a></li>
            <li><a href="#tab-columns"><span class="dashicons dashicons-layout"></span> <?php esc_html_e('2. Responsive Columns', 'new-video-gallery'); ?></a></li>
            <li><a href="#tab-styling"><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('3. Card & Typography', 'new-video-gallery'); ?></a></li>
            <li><a href="#tab-lightbox-pop"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('4. Lightbox Popup', 'new-video-gallery'); ?></a></li>
            <li><a href="#tab-monetization"><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('5. Ads & Monetization', 'new-video-gallery'); ?></a></li>
            <li><a href="#tab-builders"><span class="dashicons dashicons-layout"></span> <?php esc_html_e('6. Page Builders', 'new-video-gallery'); ?></a></li>
            <li><a href="#section-deployment"><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('Deployment & Shorts', 'new-video-gallery'); ?></a></li>
        </ul>
        <div class="vgp-sidebar-btn-group" style="padding: 20px 20px 0 20px; display: flex; flex-direction: column; gap: 10px; border-top: 1px solid rgba(255,255,255,0.08); margin-top: 15px;">
            <a href="https://wordpress.org/support/plugin/new-video-gallery/reviews/#new-post" target="_blank" class="vgp-sidebar-btn rate-btn" onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background-color: #3b82f6; color: #fff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; text-align: center; transition: background 0.2s;">
                <span class="dashicons dashicons-star-filled" style="font-size: 16px; width: 16px; height: 16px; margin: 0; line-height: 16px; color: #ffca08;"></span>
                <?php esc_html_e('Rate This Plugin', 'new-video-gallery'); ?>
            </a>
            <a href="https://awplife.com/demo/video-gallery-premium/" target="_blank" class="vgp-sidebar-btn demo-btn" onmouseover="this.style.backgroundColor='#059669'" onmouseout="this.style.backgroundColor='#10b981'" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background-color: #10b981; color: #fff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; text-align: center; transition: background 0.2s;">
                <span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; margin: 0; line-height: 16px; color: #fff;"></span>
                <?php esc_html_e('Pro Live Demo', 'new-video-gallery'); ?>
            </a>
            <a href="https://awplife.com/wordpress-plugins/video-gallery-wordpress-plugin/" target="_blank" class="vgp-sidebar-btn buy-btn" onmouseover="this.style.backgroundColor='#be123c'" onmouseout="this.style.backgroundColor='#e11d48'" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background-color: #e11d48; color: #fff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; text-align: center; transition: background 0.2s;">
                <span class="dashicons dashicons-cart" style="font-size: 16px; width: 16px; height: 16px; margin: 0; line-height: 16px; color: #fff;"></span>
                <?php esc_html_e('Buy Pro', 'new-video-gallery'); ?>
            </a>
        </div>
    </div>

    <!-- Content Area -->
    <div class="vgp-docs-content">
        <header class="vgp-main-header">
            <h1>
                <?php esc_html_e('Video Gallery: Complete Encyclopedia', 'new-video-gallery'); ?> 
                <span class="vgp-version-badge">v<?php echo esc_html(VG_PLUGIN_VER); ?></span>
            </h1>
            <p><?php esc_html_e('Comprehensive step-by-step master tutorial covering every configuration setting, monetization option, layout configuration, and page builder widget.', 'new-video-gallery'); ?></p>
        </header>

        <!-- Section: Quick Start Guide -->
        <section id="quick-start" class="vgp-info-section">
            <h2><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Quick Start: Create Your First Gallery', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <h3><?php esc_html_e('How to Create a Manual Video Gallery', 'new-video-gallery'); ?></h3>
                <p><?php esc_html_e('Follow these steps to manually upload and curate your own list of video slides (YouTube, Vimeo, and custom image banners):', 'new-video-gallery'); ?></p>
                <ol class="vgp-bullet-list" style="list-style-type: decimal; padding-left: 20px;">
                    <li><?php esc_html_e('Navigate to the WordPress Admin Menu and click on **Video Gallery** -> **Add Video Gallery**.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Enter a descriptive title for your gallery at the top of the editor page.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Under **Tab 1: Videos & Source**, ensure that the **Video Gallery** option card is selected (active by default).', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Scroll down and click the dashed area **ADD VIDEO BANNER / POSTER**. Upload or select an image from the Media Library.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Inside the newly generated slide card, select the **Video Type** (YouTube, Vimeo, or Image Only).', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Paste your video link or video ID in the **Video ID or link** field.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Enter your metadata like **Video Title** and **Video Description** inside the card text inputs.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Select **Fetch Poster** to automatically pull video thumbnails, or leave it to use your uploaded image.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Drag and drop slides using the drag handle on the left to re-sequence them.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Configure columns and styling under the tabs, then click the **Publish** button to save and generate your shortcode.', 'new-video-gallery'); ?></li>
                </ol>
            </div>
        </section>

        <!-- Section: Video Sources -->
        <section id="tab-sources" class="vgp-info-section">
            <h2><span class="dashicons dashicons-playlist-video"></span> <?php esc_html_e('Step 1: Content & Links (Video Sources)', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <h3><?php esc_html_e('Manual Video Uploads & Links', 'new-video-gallery'); ?></h3>
                <p><?php esc_html_e('Manually build your video gallery by pasting specific platform URLs. The plugin automatically parses video IDs, fetches custom platform cover posters, and generates playback handlers:', 'new-video-gallery'); ?></p>
                
                <table class="vgp-settings-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Source Type', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Status', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Link Format & How it Works', 'new-video-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('YouTube', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Paste any watch URL (e.g. youtube.com/watch?v=...) or short URL (youtu.be/...). The plugin extracts the ID and fetches the standard YouTube thumbnail.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Vimeo', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Paste vimeo.com/{video_id} URLs. The plugin query fetches cover posters automatically.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Image Only', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Allows creating static image slides linked to custom external URLs.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Local Hosted', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Upload MP4 or WebM files through the WordPress Media Library. Requires upgrading to the Premium version.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Twitch, TikTok, Wistia, Dailymotion', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Inject custom platform players seamlessly using specialized API endpoints. Requires Premium.', 'new-video-gallery'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <h3 style="margin-top: 25px;"><?php esc_html_e('YouTube API Feed Imports', 'new-video-gallery'); ?></h3>
                <p><?php esc_html_e('Automatically sync videos from a YouTube channel by providing API details:', 'new-video-gallery'); ?></p>
                <ul class="vgp-bullet-list" style="list-style-type: disc; padding-left: 20px;">
                    <li><strong><?php esc_html_e('YouTube API Key (Active)', 'new-video-gallery'); ?></strong>: <?php esc_html_e('Enables connection to the Google YouTube API to fetch video lists securely.', 'new-video-gallery'); ?></li>
                    <li><strong><?php esc_html_e('YouTube Channel Link (Active)', 'new-video-gallery'); ?></strong>: <?php esc_html_e('Provide the channel link or uploads playlist link. The plugin automatically fetches and caches uploads.', 'new-video-gallery'); ?></li>
                    <li><strong><?php esc_html_e('Specific Playlist Import (Premium)', 'new-video-gallery'); ?></strong>: <?php esc_html_e('Allows selecting individual playlists instead of all uploads.', 'new-video-gallery'); ?></li>
                </ul>
            </div>
        </section>

        <!-- Section: Responsive Columns -->
        <section id="tab-columns" class="vgp-info-section">
            <h2><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Step 2: Responsive Columns (Layout & Design)', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <h3><?php esc_html_e('Responsive Columns & Dimensions', 'new-video-gallery'); ?></h3>
                <p><?php esc_html_e('Configure how many video thumbnail columns are rendered in the gallery grid across different viewport widths:', 'new-video-gallery'); ?></p>
                <table class="vgp-settings-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Setting', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Status', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Description & Options', 'new-video-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('Extra Large Screens Columns', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Controls the column count (1 to 6) displayed on screens wider than 1400px.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Desktop Columns', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Controls the column count (1 to 6) displayed on standard desktop displays (1200px - 1400px).', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Tablets Columns', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Controls the column count (1 to 6) displayed on tablet screens (768px - 1200px).', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Mobile Columns', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Controls the column count (1 to 4) displayed on mobile viewports (under 768px).', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Thumbnail Sorting Order', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Sort items as Old First (ASC), New First (DESC), or Shuffle (Random).', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Grid Layout Mode', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Toggle between Fixed heights and fluid Masonry grid mode. Free version is locked to Fixed.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Live Search Box', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Adds an instant interactive search input above the gallery grid. Requires Premium.', 'new-video-gallery'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Card & Typography -->
        <section id="tab-styling" class="vgp-info-section">
            <h2><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('Step 3: Card Aesthetics & Typography Settings', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <p><?php esc_html_e('Customize individual card styles, thumbnail qualities, border frames, and typography colors:', 'new-video-gallery'); ?></p>
                
                <table class="vgp-settings-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Setting', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Status', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Impact & Description', 'new-video-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('Spacing Toggle (Gap)', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Toggles 8px margin spacing gaps between thumbnails in the grid.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Card Border Radius', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Toggles rounded corner borders (8px) on thumbnail cards.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Thumbnail Card Outline Border', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Adds a custom card border wrapper around image tiles.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Grayscale Filters', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Apply black-and-white filters to thumbnail cards, resolving to normal color on hover.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Play Icon Overlay Toggle', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Show or hide the play icon indicator overlay on media covers.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Typography Text Toggles', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Show or hide the video Title or Description underneath cards.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Typography Colors', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Pick specific HEX colors for the title and description fonts.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Border Thickness & Custom Backgrounds', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Specify custom card border padding, border colors, opacity, and custom background fills. Requires Premium.', 'new-video-gallery'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Lightbox Settings -->
        <section id="tab-lightbox-pop" class="vgp-info-section">
            <h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Step 4: Lightbox popup player Configurations', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <p><?php esc_html_e('Control the behavior of the overlay lightbox popup player:', 'new-video-gallery'); ?></p>
                
                <table class="vgp-settings-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Setting', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Status', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Impact & Description', 'new-video-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('Auto Play Videos', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('If set to Yes, the YouTube/Vimeo/Self-hosted video starts playing automatically on click.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Loop Videos', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Determines whether users can continuously click through the gallery elements in a loop.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Lightbox Titles', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Shows the video title in the lightbox footer caption.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Overlay Color, Transitions & Thumbs', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Advanced lightbox setups like lightbox thumbnails sliders, transitions, custom descriptions, and background opacity require Premium.', 'new-video-gallery'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Grid Monetization -->
        <section id="tab-monetization" class="vgp-info-section">
            <h2><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Step 5: Grid Monetization (Ads & Banners)', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <p><?php esc_html_e('Monetize your video gallery grids by injecting advertising code directly between your video thumbnails:', 'new-video-gallery'); ?></p>
                
                <table class="vgp-settings-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Setting', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Status', 'new-video-gallery'); ?></th>
                            <th><?php esc_html_e('Description & How to Setup', 'new-video-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('Ad Script / HTML Code', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Paste your custom ad script (like Google AdSense code blocks) or HTML image banner markup (e.g. <a><img src="..."></a>). Script execution is permitted securely for site administrators.', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Middle-Grid Injection', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #16a34a; font-weight: 600;"><?php esc_html_e('Active', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Exactly one ad banner is injected dynamically in the middle of your grid (calculated automatically based on half of the total video items).', 'new-video-gallery'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Custom Ad Injection Frequency', 'new-video-gallery'); ?></strong></td>
                            <td><span style="color: #ea4335; font-weight: 600;"><?php esc_html_e('Premium', 'new-video-gallery'); ?></span></td>
                            <td><?php esc_html_e('Lets you specify multiple ads and ad intervals (e.g. show ads after every 3rd or 4th thumbnail). Requires Premium.', 'new-video-gallery'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Page Builders -->
        <section id="tab-builders" class="vgp-info-section">
            <h2><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Step 6: Page Builder Integrations (Gutenberg & Elementor)', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <h3><?php esc_html_e('1. Gutenberg Block Integration', 'new-video-gallery'); ?></h3>
                <p><?php esc_html_e('Embed your video galleries in the WordPress Block Editor (Gutenberg) without utilizing shortcodes:', 'new-video-gallery'); ?></p>
                <ol class="vgp-bullet-list" style="list-style-type: decimal; padding-left: 20px; margin-bottom: 25px;">
                    <li><?php esc_html_e('Open or edit any Page or Post using the block editor.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Click the block inserter **"+"** button or type `/video` to locate blocks.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Select and add the block **Video Gallery Block**.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('In the Block options panel on the right sidebar, use the **Select Gallery** dropdown to choose your gallery.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('The block automatically loads a live visual preview of your grid layout inside the editor.', 'new-video-gallery'); ?></li>
                </ol>

                <h3><?php esc_html_e('2. Elementor Widget Integration', 'new-video-gallery'); ?></h3>
                <p><?php esc_html_e('Add your galleries within Elementor layouts:', 'new-video-gallery'); ?></p>
                <ol class="vgp-bullet-list" style="list-style-type: decimal; padding-left: 20px;">
                    <li><?php esc_html_e('Edit any page or template with the Elementor Editor.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('In the left widgets search bar, search for **Video Gallery**.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Drag the widget block and place it into any grid column on the layout canvas.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('Under the **Gallery Source Settings** section on the left content panel, select your custom gallery in the dropdown.', 'new-video-gallery'); ?></li>
                    <li><?php esc_html_e('The gallery will immediately render and initialize the scripts and lightgallery popup players.', 'new-video-gallery'); ?></li>
                </ol>
            </div>
        </section>

        <!-- Section: Deployment & Shorts -->
        <section id="section-deployment" class="vgp-info-section">
            <h2><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('Deployment & Shorts', 'new-video-gallery'); ?></h2>
            
            <div class="vgp-tutorial-card">
                <p><?php esc_html_e('Publish your custom video galleries anywhere on your site by copying the generated shortcode from the gallery editor sidebar:', 'new-video-gallery'); ?></p>
                
                <div class="vgp-code-sample">
                    <code>[VDGAL id=<?php esc_html_e('XXXX', 'new-video-gallery'); ?>]</code>
                    <button class="vgp-code-btn" onclick="navigator.clipboard.writeText('[VDGAL id=XXXX]')"><?php esc_html_e('Copy Code', 'new-video-gallery'); ?></button>
                </div>
                <p style="margin-top: 10px; font-size: 11.5px; opacity: 0.8; font-style: italic;">
                    * <?php esc_html_e('Replace "XXXX" with your actual Gallery Post ID (e.g. 142).', 'new-video-gallery'); ?>
                </p>
            </div>
        </section>

        <footer class="vgp-content-footer">
            <p><?php esc_html_e('Documentation synchronized with Video Gallery Version', 'new-video-gallery'); ?> <?php echo esc_html(VG_PLUGIN_VER); ?></p>
        </footer>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Smooth scrolling & active navigation class trigger
    $('.vgp-toc a').on('click', function(e) {
        $('.vgp-toc a').removeClass('active');
        $(this).addClass('active');
        
        var target = $(this).attr('href');
        if ($(target).length > 0) {
            e.preventDefault();
            $('.vgp-docs-content').animate({
                scrollTop: $(target).offset().top - $('.vgp-docs-content').offset().top + $('.vgp-docs-content').scrollTop() - 10
            }, 500);
        }
    });

    // Auto-update active tab on scroll
    $('.vgp-docs-content').on('scroll', function() {
        var scrollPos = $(this).scrollTop();
        $('.vgp-info-section').each(function() {
            var target = $(this).attr('id');
            var top = $(this).offset().top - $('.vgp-docs-content').offset().top + $('.vgp-docs-content').scrollTop();
            if (scrollPos >= top - 50 && scrollPos < top + $(this).outerHeight() - 50) {
                $('.vgp-toc a').removeClass('active');
                $('.vgp-toc a[href="#' + target + '"]').addClass('active');
            }
        });
    });
});
</script>