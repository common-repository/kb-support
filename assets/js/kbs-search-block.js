( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;

    registerBlockType( 'kbs/search-block', {
        title: __( 'KBS Search', 'kb-support' ),
        icon: 'search', // Use a WordPress dashicon or custom SVG
        category: 'common',

        edit: function() {
            return wp.element.createElement(
                ServerSideRender,
                {
                    block: "kbs/search-block"
                }
            );
        },

        save: function() {
            // Rendering in PHP, so return null
            return null;
        },
    } );
} )( window.wp );
