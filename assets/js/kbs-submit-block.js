( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;
    var TextControl = wp.components.TextControl;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;

    registerBlockType( 'kbs/submit-block', {
        title: __( 'KBS Submit', 'kb-support' ),
        icon: 'forms', // Use a WordPress dashicon or custom SVG
        category: 'common',
        attributes: {
            formId: {
                type: 'string',
                default: ''
            }
        },

        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return [
                wp.element.createElement(
                    InspectorControls,
                    null,
                    wp.element.createElement(
                        PanelBody,
                        { title: __( 'Settings', 'kb-support' ) },
                        wp.element.createElement(
                            TextControl,
                            {
                                label: __( 'Form ID', 'kb-support' ),
                                value: attributes.formId,
                                onChange: function( value ) {
                                    setAttributes( { formId: value } );
                                }
                            }
                        )
                    )
                ),
                wp.element.createElement(
                    ServerSideRender,
                    {
                        block: "kbs/submit-block",
                        attributes: attributes
                    }
                )
            ];
        },

        save: function() {
            return null;
        },
    } );
} )( window.wp );
