<?php
// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to render the block
function kbs_tickets_block_render() {
    // Check if the shortcode function exists
    if ( function_exists( 'kbs_tickets_shortcode' ) ) {
        // Pass an empty array as the argument
        return kbs_tickets_shortcode( array() );
    }

    return '';
}

// Register the block
function kbs_register_tickets_block() {
    // Check if function exists
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'kbs/tickets-block', array(
            'render_callback' => 'kbs_tickets_block_render',
        ) );
    }
}
add_action( 'init', 'kbs_register_tickets_block' );
