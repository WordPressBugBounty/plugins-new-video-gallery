<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Clear analytics handler
if ( isset( $_POST['vg_clear_analytics'] ) && check_admin_referer( 'vg_clear_analytics_action' ) ) {
	update_option( 'vg_video_analytics', array() );
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Analytics logs cleared successfully!', 'new-video-gallery' ) . '</p></div>';
}

$analytics = get_option( 'vg_video_analytics', array() );

// Calculate KPIs
$total_plays = 0;
$unique_videos = count( $analytics );
$top_video_title = esc_html__( 'None', 'new-video-gallery' );
$top_video_plays = 0;
$gallery_plays = array();

$total_desktop_plays = 0;
$total_mobile_plays = 0;
$total_tablet_plays = 0;

$platform_plays = array(
	'youtube'     => 0,
	'vimeo'       => 0,
	'local'       => 0
);

foreach ( $analytics as $key => $item ) {
	$g_id = isset( $item['gallery_id'] ) ? intval( $item['gallery_id'] ) : 0;
	$current_gallery_title = '';
	if ( $g_id > 0 ) {
		$post_status = get_post_status( $g_id );
		if ( $post_status ) {
			$current_gallery_title = get_the_title( $g_id );
		}
	}
	
	if ( ! empty( $current_gallery_title ) ) {
		$item['gallery_title'] = $current_gallery_title;
		$analytics[ $key ]['gallery_title'] = $current_gallery_title;
	} else if ( empty( $item['gallery_title'] ) ) {
		$item['gallery_title'] = sprintf( __( 'Gallery #%d', 'new-video-gallery' ), $g_id );
		$analytics[ $key ]['gallery_title'] = $item['gallery_title'];
	}

	$plays_val = intval( $item['plays'] );
	$total_plays += $plays_val;
	
	if ( $plays_val > $top_video_plays ) {
		$top_video_plays = $plays_val;
		$top_video_title = $item['video_title'];
	}
	
	if ( ! isset( $gallery_plays[ $g_id ] ) ) {
		$gallery_plays[ $g_id ] = array(
			'title' => $item['gallery_title'],
			'plays' => 0
		);
	}
	$gallery_plays[ $g_id ]['plays'] += $plays_val;

	// Platform count logic
	$src_key = isset($item['source']) ? strtolower($item['source']) : 'local';
	if ( array_key_exists( $src_key, $platform_plays ) ) {
		$platform_plays[ $src_key ] += $plays_val;
	} else {
		$platform_plays['local'] += $plays_val;
	}

	// Device count logic
	$desktop = isset( $item['devices']['desktop'] ) ? intval( $item['devices']['desktop'] ) : 0;
	$mobile  = isset( $item['devices']['mobile'] ) ? intval( $item['devices']['mobile'] ) : 0;
	$tablet  = isset( $item['devices']['tablet'] ) ? intval( $item['devices']['tablet'] ) : 0;

	if ( $plays_val > 0 && ($desktop + $mobile + $tablet) === 0 ) {
		// Fallback for legacy records
		$desktop = $plays_val;
	}

	$total_desktop_plays += $desktop;
	$total_mobile_plays  += $mobile;
	$total_tablet_plays  += $tablet;
}

$top_gallery_title = esc_html__( 'None', 'new-video-gallery' );
$top_gallery_plays = 0;
foreach ( $gallery_plays as $g ) {
	if ( $g['plays'] > $top_gallery_plays ) {
		$top_gallery_plays = $g['plays'];
		$top_gallery_title = $g['title'];
	}
}

// Sort videos by plays DESC for top videos list
uasort( $analytics, function( $a, $b ) {
	return intval( $b['plays'] ) - intval( $a['plays'] );
} );
?>

<style>
	.vg-analytics-wrap {
		margin: 20px 20px 0 0;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
	}
	.vg-analytics-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		background: #ffffff;
		padding: 24px;
		border-radius: 12px;
		border: 1px solid rgba(0, 0, 0, 0.05);
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
		margin-bottom: 24px;
	}
	.vg-analytics-header-title h2 {
		font-size: 22px;
		font-weight: 700;
		color: #0f172a;
		margin: 0 0 6px 0;
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.vg-analytics-header-title h2 .dashicons {
		font-size: 26px;
		width: 26px;
		height: 26px;
		color: #4f46e5;
	}
	.vg-analytics-header-title p {
		font-size: 13px;
		color: #64748b;
		margin: 0;
	}
	.vg-analytics-actions {
		display: flex;
		gap: 12px;
		align-items: center;
	}
	.vg-btn-primary {
		background: #4f46e5 !important;
		color: #ffffff !important;
		text-decoration: none !important;
		padding: 10px 18px !important;
		border-radius: 8px !important;
		font-weight: 600 !important;
		font-size: 13px !important;
		display: inline-flex !important;
		align-items: center !important;
		gap: 8px !important;
		box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15) !important;
		border: none !important;
		cursor: pointer !important;
		transition: background 0.15s ease-in-out, transform 0.1s ease !important;
	}
	.vg-btn-primary:hover {
		background: #4338ca !important;
		color: #ffffff !important;
	}
	.vg-btn-primary:active {
		transform: scale(0.97);
	}
	.vg-btn-danger {
		background: #fef2f2 !important;
		color: #ef4444 !important;
		border: 1px solid #fee2e2 !important;
		padding: 10px 18px !important;
		border-radius: 8px !important;
		font-weight: 600 !important;
		font-size: 13px !important;
		cursor: pointer !important;
		display: inline-flex !important;
		align-items: center !important;
		gap: 8px !important;
		transition: background 0.15s ease-in-out, color 0.15s ease-in-out, transform 0.1s ease !important;
	}
	.vg-btn-danger:hover {
		background: #ef4444 !important;
		color: #ffffff !important;
		border-color: #ef4444 !important;
	}
	.vg-btn-danger:active {
		transform: scale(0.97);
	}
	.vg-analytics-kpi-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 20px;
		margin-bottom: 30px;
	}
	.vg-analytics-kpi-card {
		background: #ffffff;
		border: 1px solid rgba(0, 0, 0, 0.05);
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		position: relative;
		overflow: hidden;
	}
	.vg-analytics-kpi-card::before {
		content: "";
		position: absolute;
		top: 0;
		left: 0;
		width: 4px;
		height: 100%;
		background: #cbd5e1;
	}
	.vg-analytics-kpi-card.kpi-total::before { background: #4f46e5; }
	.vg-analytics-kpi-card.kpi-tracked::before { background: #06b6d4; }
	.vg-analytics-kpi-card.kpi-top-video::before { background: #10b981; }
	.vg-analytics-kpi-card.kpi-top-gallery::before { background: #f59e0b; }

	.vg-analytics-kpi-card h3 {
		font-size: 11px;
		font-weight: 700;
		color: #64748b;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin: 0 0 10px 0;
	}
	.vg-analytics-kpi-card .vg-kpi-value {
		font-size: 28px;
		font-weight: 700;
		color: #0f172a;
		margin: 0 0 5px 0;
	}
	.vg-analytics-kpi-card .vg-kpi-subtitle {
		font-size: 12px;
		color: #94a3b8;
		margin: 0;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.vg-analytics-charts-grid {
		display: grid;
		grid-template-columns: 1fr;
		gap: 24px;
		margin-bottom: 30px;
	}
	@media (min-width: 1024px) {
		.vg-analytics-charts-grid {
			grid-template-columns: 3fr 2fr 2fr;
		}
	}
	.vg-analytics-card {
		background: #ffffff;
		border: 1px solid rgba(0, 0, 0, 0.05);
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
	}
	.vg-analytics-card h4 {
		font-size: 16px;
		font-weight: 700;
		color: #1e293b;
		margin: 0 0 20px 0;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.vg-chart-container {
		position: relative;
		height: 240px;
		width: 100%;
	}
	.vg-source-badge {
		display: inline-flex;
		align-items: center;
		padding: 2px 8px;
		border-radius: 4px;
		font-size: 10px;
		font-weight: 700;
		text-transform: uppercase;
		line-height: 1.2;
	}
	.vg-badge-youtube { background: #fee2e2; color: #dc2626; }
	.vg-badge-vimeo { background: #e0f2fe; color: #0284c7; }
	.vg-badge-twitch { background: #f3e8ff; color: #9146FF; }
	.vg-badge-dailymotion { background: #e2f0fd; color: #0066dc; }
	.vg-badge-wistia { background: #f1f5f9; color: #4e5766; }
	.vg-badge-tiktok { background: #fee2e2; color: #ff0050; }
	.vg-badge-reels { background: #fdf2f8; color: #db2777; }
	.vg-badge-local { background: #f0fdf4; color: #16a34a; }
	
	.vg-table-search {
		display: flex;
		justify-content: space-between;
		margin-bottom: 15px;
	}
	.vg-table-search input {
		width: 250px;
		padding: 8px 12px;
		border-radius: 6px;
		border: 1px solid rgba(0,0,0,0.1);
	}
	.vg-table-search input:focus {
		border-color: #4f46e5;
		outline: none;
		box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
	}
	.vg-analytics-card table.wp-list-table {
		border-collapse: separate;
		border-spacing: 0;
		border-radius: 8px;
		border: 1px solid #e2e8f0 !important;
		overflow: hidden;
	}
	.vg-analytics-card table.wp-list-table thead th {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
		color: #475569;
		text-transform: uppercase;
		font-size: 11px;
		letter-spacing: 0.5px;
		padding: 12px 16px;
	}
	.vg-analytics-card table.wp-list-table tbody td {
		padding: 14px 16px;
		vertical-align: middle;
		font-size: 13px;
		color: #334155;
		border-bottom: 1px solid #f1f5f9;
	}
	.vg-analytics-card table.wp-list-table tbody tr:last-child td {
		border-bottom: none;
	}
	.vg-analytics-card table.wp-list-table tbody tr:hover td {
		background: #f8fafc;
	}
</style>

<div class="vg-analytics-wrap">
	<div class="vg-analytics-header">
		<div class="vg-analytics-header-title">
			<h2><span class="dashicons dashicons-chart-line"></span> <?php esc_html_e( 'Video Playback Insights & Analytics', 'new-video-gallery' ); ?></h2>
			<p><?php esc_html_e( 'Monitor views, device shares, and top-performing video highlights across all active player embeds.', 'new-video-gallery' ); ?></p>
		</div>
		<div class="vg-analytics-actions">
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'edit.php?post_type=' . VG_PLUGIN_SLUG . '&page=vg-analytics&action=export_csv' ), 'vg_export_csv_action', 'security' ) ); ?>" class="vg-btn-primary">
				<span class="dashicons dashicons-download" style="margin-top: -1px;"></span>
				<?php esc_html_e( 'Export to CSV', 'new-video-gallery' ); ?>
			</a>
			<form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to clear all analytics log records?', 'new-video-gallery'); ?>');" style="margin: 0;">
				<?php wp_nonce_field( 'vg_clear_analytics_action' ); ?>
				<button type="submit" name="vg_clear_analytics" class="vg-btn-danger">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Reset Analytics Data', 'new-video-gallery' ); ?>
				</button>
			</form>
		</div>
	</div>

	<!-- KPIs Grid -->
	<div class="vg-analytics-kpi-grid">
		<div class="vg-analytics-kpi-card kpi-total">
			<h3><?php esc_html_e( 'Total Plays', 'new-video-gallery' ); ?></h3>
			<div class="vg-kpi-value"><?php echo number_format_i18n( $total_plays ); ?></div>
			<div class="vg-kpi-subtitle"><?php esc_html_e( 'Across all galleries', 'new-video-gallery' ); ?></div>
		</div>
		<div class="vg-analytics-kpi-card kpi-tracked">
			<h3><?php esc_html_e( 'Tracked Videos', 'new-video-gallery' ); ?></h3>
			<div class="vg-kpi-value"><?php echo number_format_i18n( $unique_videos ); ?></div>
			<div class="vg-kpi-subtitle"><?php esc_html_e( 'Unique video titles', 'new-video-gallery' ); ?></div>
		</div>
		<div class="vg-analytics-kpi-card kpi-top-video">
			<h3><?php esc_html_e( 'Top Video', 'new-video-gallery' ); ?></h3>
			<div class="vg-kpi-value" style="font-size: 20px; padding: 6px 0;"><?php echo esc_html( wp_html_excerpt( $top_video_title, 20, '...' ) ); ?></div>
			<div class="vg-kpi-subtitle"><?php printf( esc_html__( '%d plays recorded', 'new-video-gallery' ), $top_video_plays ); ?></div>
		</div>
		<div class="vg-analytics-kpi-card kpi-top-gallery">
			<h3><?php esc_html_e( 'Top Gallery', 'new-video-gallery' ); ?></h3>
			<div class="vg-kpi-value" style="font-size: 20px; padding: 6px 0;"><?php echo esc_html( wp_html_excerpt( $top_gallery_title, 20, '...' ) ); ?></div>
			<div class="vg-kpi-subtitle"><?php printf( esc_html__( '%d plays recorded', 'new-video-gallery' ), $top_gallery_plays ); ?></div>
		</div>
	</div>

	<!-- Chart.js Graphs Grid -->
	<div class="vg-analytics-charts-grid">
		<!-- Top Videos Horizontal Bar Chart -->
		<div class="vg-analytics-card">
			<h4>
				<span class="dashicons dashicons-chart-bar" style="margin-top: 1px;"></span>
				<?php esc_html_e( 'Top Performing Videos', 'new-video-gallery' ); ?>
			</h4>
			<div class="vg-chart-container">
				<?php if ( empty( $analytics ) ) : ?>
					<p style="color: #94a3b8; font-style: italic;"><?php esc_html_e( 'No playback data recorded yet.', 'new-video-gallery' ); ?></p>
				<?php else : ?>
					<canvas id="vg-top-videos-chart"></canvas>
				<?php endif; ?>
			</div>
		</div>

		<!-- Platform Distribution Doughnut Chart -->
		<div class="vg-analytics-card">
			<h4>
				<span class="dashicons dashicons-admin-site" style="margin-top: 1px;"></span>
				<?php esc_html_e( 'Platform Distribution', 'new-video-gallery' ); ?>
			</h4>
			<div class="vg-chart-container">
				<?php if ( $total_plays === 0 ) : ?>
					<p style="color: #94a3b8; font-style: italic;"><?php esc_html_e( 'No playback data recorded yet.', 'new-video-gallery' ); ?></p>
				<?php else : ?>
					<canvas id="vg-platform-chart"></canvas>
				<?php endif; ?>
			</div>
		</div>

		<!-- Device Share Doughnut Chart -->
		<div class="vg-analytics-card">
			<h4>
				<span class="dashicons dashicons-desktop" style="margin-top: 1px;"></span>
				<?php esc_html_e( 'Device Share', 'new-video-gallery' ); ?>
			</h4>
			<div class="vg-chart-container">
				<?php if ( $total_plays === 0 ) : ?>
					<p style="color: #94a3b8; font-style: italic;"><?php esc_html_e( 'No playback data recorded yet.', 'new-video-gallery' ); ?></p>
				<?php else : ?>
					<canvas id="vg-device-chart"></canvas>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Detailed Logs Table -->
	<div class="vg-analytics-card" style="margin-bottom: 40px;">
		<div class="vg-table-search">
			<h4>
				<span class="dashicons dashicons-list-view" style="margin-top: 1px;"></span>
				<?php esc_html_e( 'Detailed Playback Log', 'new-video-gallery' ); ?>
			</h4>
			<input type="text" id="vg-analytics-search" placeholder="<?php esc_attr_e( 'Filter table...', 'new-video-gallery' ); ?>">
		</div>

		<table class="wp-list-table widefat fixed striped" style="border: 0; box-shadow: none;">
			<thead>
				<tr>
					<th style="font-weight: 700; width: 35%;"><?php esc_html_e( 'Video Title', 'new-video-gallery' ); ?></th>
					<th style="font-weight: 700; width: 25%;"><?php esc_html_e( 'Gallery Source', 'new-video-gallery' ); ?></th>
					<th style="font-weight: 700; width: 15%;"><?php esc_html_e( 'Feed Type', 'new-video-gallery' ); ?></th>
					<th style="font-weight: 700; width: 10%;"><?php esc_html_e( 'Plays', 'new-video-gallery' ); ?></th>
					<th style="font-weight: 700; width: 15%;"><?php esc_html_e( 'Last Clicked', 'new-video-gallery' ); ?></th>
				</tr>
			</thead>
			<tbody id="vg-analytics-tbody">
				<?php
				if ( empty( $analytics ) ) {
					echo '<tr><td colspan="5" style="text-align: center; color: #94a3b8;">' . esc_html__( 'No playback logs recorded.', 'new-video-gallery' ) . '</td></tr>';
				} else {
					foreach ( $analytics as $item ) {
						$src_val = isset($item['source']) ? $item['source'] : 'local';
						$display_src = $src_val;
						if ( $src_val === 'youtube' ) {
							$source_class = 'vg-badge-youtube';
							$display_src = 'YouTube';
						} elseif ( $src_val === 'vimeo' ) {
							$source_class = 'vg-badge-vimeo';
							$display_src = 'Vimeo';
						} else {
							$source_class = 'vg-badge-local';
							$display_src = 'Image Only';
						}
						?>
						<tr>
							<td><strong><?php echo esc_html( $item['video_title'] ); ?></strong></td>
							<td><?php echo esc_html( $item['gallery_title'] ); ?></td>
							<td><span class="vg-source-badge <?php echo $source_class; ?>"><?php echo esc_html( $display_src ); ?></span></td>
							<td><strong><?php echo intval( $item['plays'] ); ?></strong></td>
							<td style="color: #64748b; font-size: 11px;"><?php echo esc_html( $item['last_played'] ); ?></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		// Live table search filtering
		$('#vg-analytics-search').on('keyup', function() {
			var query = $(this).val().toLowerCase();
			$('#vg-analytics-tbody tr').each(function() {
				var text = $(this).text().toLowerCase();
				if (text.indexOf(query) !== -1) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		});

		// 1. Platform Distribution Chart
		var platformCtx = document.getElementById('vg-platform-chart');
		if (platformCtx && typeof Chart !== 'undefined') {
			new Chart(platformCtx, {
				type: 'doughnut',
				data: {
					labels: ['YouTube', 'Vimeo', 'Image Only'],
					datasets: [{
						data: [
							<?php echo intval($platform_plays['youtube']); ?>,
							<?php echo intval($platform_plays['vimeo']); ?>,
							<?php echo intval($platform_plays['local']); ?>
						],
						backgroundColor: [
							'#ef4444', // YouTube
							'#06b6d4', // Vimeo
							'#10b981'  // Image Only
						],
						borderWidth: 0
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'right',
							labels: {
								boxWidth: 10,
								font: { size: 11, weight: '500' },
								padding: 8
							}
						}
					},
					cutout: '65%'
				}
			});
		}

		// 2. Device Share Chart
		var deviceCtx = document.getElementById('vg-device-chart');
		if (deviceCtx && typeof Chart !== 'undefined') {
			new Chart(deviceCtx, {
				type: 'doughnut',
				data: {
					labels: ['Desktop', 'Mobile', 'Tablet'],
					datasets: [{
						data: [
							<?php echo intval($total_desktop_plays); ?>,
							<?php echo intval($total_mobile_plays); ?>,
							<?php echo intval($total_tablet_plays); ?>
						],
						backgroundColor: ['#4f46e5', '#10b981', '#f59e0b'],
						borderWidth: 0
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'right',
							labels: {
								boxWidth: 10,
								font: { size: 11, weight: '500' },
								padding: 10
							}
						}
					},
					cutout: '65%'
				}
			});
		}

		// 3. Top Videos Chart (Horizontal Bar Chart)
		var topVideosCtx = document.getElementById('vg-top-videos-chart');
		if (topVideosCtx && typeof Chart !== 'undefined') {
			var videoLabels = [];
			var videoData = [];
			<?php
			$top_videos_data = array_slice($analytics, 0, 5);
			foreach ($top_videos_data as $video) {
				?>
				videoLabels.push(<?php echo json_encode(wp_html_excerpt($video['video_title'], 25, '...')); ?>);
				videoData.push(<?php echo intval($video['plays']); ?>);
				<?php
			}
			?>

			new Chart(topVideosCtx, {
				type: 'bar',
				data: {
					labels: videoLabels,
					datasets: [{
						label: 'Plays',
						data: videoData,
						backgroundColor: 'rgba(79, 70, 229, 0.85)',
						hoverBackgroundColor: '#4f46e5',
						borderRadius: 6,
						borderSkipped: false
					}]
				},
				options: {
					indexAxis: 'y',
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false }
					},
					scales: {
						x: {
							grid: { display: false },
							ticks: { precision: 0 }
						},
						y: {
							grid: { display: false }
						}
					}
				}
			});
		}
	});
</script>
