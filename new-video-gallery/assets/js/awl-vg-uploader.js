jQuery(
	function(jQuery) {
		'use strict';

		var file_frame,
		awl_grid_gallery = {
			ul: '',
			init: function() {
				var self = this;
				self.ul = jQuery( '.sbox' );
				self.ul.sortable({
					items: '.slide',
					handle: '.vg-move-handle',
					placeholder: 'vg-sortable-placeholder',
					forcePlaceholderSize: true,
					tolerance: 'pointer',
					opacity: 0.8,
					revert: false,
					scroll: true,
					scrollSensitivity: 100,
					scrollSpeed: 40,
					start: function(event, ui) {
						ui.placeholder.height(ui.item.outerHeight());
						ui.placeholder.width(ui.item.outerWidth());
					}
				});

				/**
				 * Add Slide Callback Function
				 */
				jQuery( '#add-new-slider, .image-upload-wrap' ).on(
					'click',
					function(event) {
						var vg_add_images_nonce = jQuery("#vg_add_images_nonce").val();
						event.preventDefault();
						if (file_frame) {
							file_frame.open();
							return;
						}
						file_frame = wp.media.frames.file_frame = wp.media(
							{
								multiple: true
							}
						);

						file_frame.on(
							'select',
							function() {
								var images = file_frame.state().get( 'selection' ).toJSON(),
									length = images.length;
								
								if (length > 0) {
									self.showLoading(1, length);
								}

								// Sequentially process selected images to maintain ordering and prevent concurrent AJAX server spikes
								var uploadSequence = Promise.resolve();
								var uploadedCount = 0;
								images.forEach(function(image) {
									uploadSequence = uploadSequence.then(function() {
										uploadedCount++;
										self.updateLoading(uploadedCount, length);
										return new Promise(function(resolve) {
											self.get_thumbnail(image.id, resolve, vg_add_images_nonce);
										});
									});
								});

								uploadSequence.then(function() {
									self.hideLoading();
								}).catch(function() {
									self.hideLoading();
								});
							}
						);
						file_frame.open();
					}
				);

				/**
				 * Delete Slide Callback Function
				 */
				self.ul.on(
					'click',
					'#remove-slide, .remove-single-slide, .pw-trash-icon',
					function() {
						var $slide = jQuery( this ).closest( '.slide' );
						if (confirm( 'Are you sure you want to delete this Banner?' )) {
							$slide.fadeOut(
								400,
								function() {
									$slide.remove();
								}
							);
						}
						return false;
					}
				);

				/**
				 * Delete All Slides Callback Function
				 */
				jQuery( '#remove-all-slides' ).on(
					'click',
					function() {
						if (confirm( 'Are you sure you want to delete all Banners?' )) {
							self.ul.empty();
						}
						return false;
					}
				);

			},
			showLoading: function(current, total) {
				if (jQuery('#vg-loading-indicator').length) {
					this.updateLoading(current, total);
					return;
				}

				var loadingHtml = '<div id="vg-loading-indicator" style="' +
					'display: flex;' +
					'align-items: center;' +
					'justify-content: center;' +
					'gap: 12px;' +
					'padding: 16px 24px;' +
					'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);' +
					'color: #ffffff;' +
					'border-radius: 12px;' +
					'margin: 20px 0;' +
					'box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);' +
					'font-family: -apple-system, BlinkMacSystemFont, \\"Segoe UI\\", Roboto, sans-serif;' +
					'font-weight: 600;' +
					'font-size: 14px;' +
					'transition: all 0.3s ease;' +
					'">' +
					'<div class="vg-uploader-spinner" style="' +
					'width: 20px;' +
					'height: 20px;' +
					'border: 3px solid rgba(255, 255, 255, 0.3);' +
					'border-radius: 50%;' +
					'border-top-color: #ffffff;' +
					'animation: vg-spin 0.8s linear infinite;' +
					'"></div>' +
					'<span class="vg-loading-text">Adding video ' + current + ' of ' + total + ' to gallery...</span>' +
					'<style>' +
					'@keyframes vg-spin {' +
					'  to { transform: rotate(360deg); }' +
					'}' +
					'</style>' +
					'</div>';
				this.ul.before(loadingHtml);
			},
			updateLoading: function(current, total) {
				jQuery('#vg-loading-indicator .vg-loading-text').text('Adding video ' + current + ' of ' + total + ' to gallery...');
			},
			hideLoading: function() {
				jQuery('#vg-loading-indicator').fadeOut(300, function() {
					jQuery(this).remove();
				});
			},
			get_thumbnail: function(id, cb, vg_add_images_nonce) {
				cb = cb || function() {};
				var data = {
					action: 'video_gallery_js',
					slideId: id,
					vg_add_images_nonce: vg_add_images_nonce,
				};
				jQuery.post(
					ajaxurl,
					data,
					function(response) {
						awl_grid_gallery.ul.append( response );
						cb();
					}
				).fail(function() {
					cb();
				});
			}
		};
		awl_grid_gallery.init();
	}
);
