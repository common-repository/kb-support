<?php
// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to render the block
function kbs_login_block_render( $attributes ) {
    $redirect = isset( $attributes['redirect'] ) ? $attributes['redirect'] : '';

    // Check if the shortcode function exists
    if ( function_exists( 'kbs_login_form_shortcode' ) ) {
        return kbs_login_form_shortcode( array( 'redirect' => $redirect ) );
    }

    return '';
}

// Register the block
function kbs_register_login_block() {
    // Check if function exists
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'kbs/login-block', array(
            'render_callback' => 'kbs_login_block_render',
            'attributes' => array(
                'redirect' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ) );
    }
}
add_action( 'init', 'kbs_register_login_block' );

