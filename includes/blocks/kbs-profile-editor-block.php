<?php
// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to render the block
function kbs_profile_editor_block_render() {
    // Check if the shortcode function exists
    if ( function_exists( 'kbs_profile_editor_shortcode' ) ) {
        // Pass an empty array as the argument
        return kbs_profile_editor_shortcode( array() );
    }

    return '';
}

// Register the block
function kbs_register_profile_editor_block() {
    // Check if function exists
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'kbs/profile-editor-block', array(
            'render_callback' => 'kbs_profile_editor_block_render',
        ) );
    }
}
add_action( 'init', 'kbs_register_profile_editor_block' );
