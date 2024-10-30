<?php
// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to render the block
function kbs_search_block_render() {
    // Check if the shortcode function exists
    if ( function_exists( 'kbs_article_search_form_shortcode' ) ) {
        // Call the correct shortcode function
        return kbs_article_search_form_shortcode();
    }

    return '';
}

// Register the block
function kbs_register_search_block() {
    // Check if function exists
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'kbs/search-block', array(
            'render_callback' => 'kbs_search_block_render',
        ) );
    }
}
add_action( 'init', 'kbs_register_search_block' );
