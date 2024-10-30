( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;

    registerBlockType( 'kbs/profile-editor-block', {
        title: __( 'KBS Profile Editor', 'kb-support' ),
        icon: 'admin-users', // Use a WordPress dashicon or custom SVG
        category: 'common',

        edit: function() {
            return wp.element.createElement(
                ServerSideRender,
                {
                    block: "kbs/profile-editor-block"
                }
            );
        },

        save: function() {
            // Rendering in PHP, so return null
            return null;
        },
    } );
} )( window.wp );
