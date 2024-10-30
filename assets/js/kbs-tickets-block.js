( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;

    registerBlockType( 'kbs/tickets-block', {
        title: __( 'KBS Tickets', 'kb-support' ),
        icon: 'tickets-alt', // Use a WordPress dashicon or custom SVG
        category: 'common',

        edit: function() {
            return wp.element.createElement(
                ServerSideRender,
                {
                    block: "kbs/tickets-block"
                }
            );
        },

        save: function() {
            // Rendering in PHP, so return null
            return null;
        },
    } );
} )( window.wp );
