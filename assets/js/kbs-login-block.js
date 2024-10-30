( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;
    var TextControl = wp.components.TextControl;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;

    registerBlockType( 'kbs/login-block', {
        title: __( 'KBS Login', 'kb-support' ),
        icon: 'admin-users',
        category: 'common',
        attributes: {
            redirect: {
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
                                label: __( 'Redirect URL', 'kb-support' ),
                                value: attributes.redirect,
                                onChange: function( value ) {
                                    setAttributes( { redirect: value } );
                                }
                            }
                        )
                    )
                ),
                wp.element.createElement(
                    ServerSideRender,
                    {
                        block: "kbs/login-block",
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
