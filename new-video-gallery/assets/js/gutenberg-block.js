(function(blocks, blockEditor, element, components) {
    'use strict';

    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var SelectControl = components.SelectControl;
    var PanelBody = components.PanelBody;
    var Disabled = components.Disabled;
    var ServerSideRender = wp.serverSideRender;

    registerBlockType('new-video-gallery/video-gallery-block', {
        apiVersion: 3,
        title: 'Video Gallery',
        description: 'Display an individual video gallery.',
        icon: 'video-alt3',
        category: 'widgets',
        attributes: {
            galleryId: {
                type: 'string',
                default: ''
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps();

            // Load localized galleries
            var gData = window.vgp_gutenberg_data || { galleries: [] };
            
            // Build galleries list
            var galleryOptions = [{ label: '-- Select Gallery --', value: '' }];
            gData.galleries.forEach(function(g) {
                galleryOptions.push({ label: g.title + ' (ID: ' + g.id + ')', value: g.id.toString() });
            });

            // Gallery selector control
            var gallerySelector = el(SelectControl, {
                label: 'Select Gallery',
                value: attributes.galleryId,
                options: galleryOptions,
                onChange: function(value) {
                    setAttributes({ galleryId: value });
                }
            });

            // Build the editor view
            var editorContent;

            if (attributes.galleryId) {
                // Live preview using ServerSideRender
                editorContent = el('div', { style: { pointerEvents: 'none', display: 'flow-root' } },
                    el(Disabled, {},
                        el(ServerSideRender, {
                            block: 'new-video-gallery/video-gallery-block',
                            attributes: attributes
                        })
                    )
                );
            } else {
                // Placeholder when no gallery is selected
                editorContent = el('div', {
                    style: {
                        background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                        color: '#ffffff',
                        padding: '30px 25px',
                        borderRadius: '12px',
                        textAlign: 'center',
                        boxShadow: '0 4px 15px rgba(16, 185, 129, 0.15)',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                    }
                },
                    el('span', { className: 'dashicons dashicons-video-alt3', style: { fontSize: '36px', width: '36px', height: '36px', marginBottom: '12px', color: '#6ee7b7', display: 'block' } }),
                    el('h4', { style: { margin: '0 0 10px 0', fontSize: '16px', fontWeight: '700', color: '#ffffff' } }, 'Video Gallery Block'),
                    el('p', { style: { margin: '0 0 16px 0', opacity: 0.85, fontSize: '13px', color: '#a7f3d0' } }, 
                        'Select a gallery to display.'
                    ),
                    el('div', { style: { maxWidth: '300px', margin: '0 auto', textAlign: 'left' } },
                        el(SelectControl, {
                            value: attributes.galleryId,
                            options: galleryOptions,
                            onChange: function(value) {
                                setAttributes({ galleryId: value });
                            }
                        })
                    )
                );
            }

            return el('div', blockProps,
                // Sidebar Inspector controls
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Gallery Display Settings', initialOpen: true },
                        gallerySelector
                    )
                ),
                editorContent
            );
        },

        save: function() {
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.element,
    window.wp.components
);
