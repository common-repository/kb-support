<?php
// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to render the block
function kbs_submit_block_render( $attributes ) {
    $form_id = isset( $attributes['formId'] ) ? $attributes['formId'] : '';

    // Check if the shortcode function exists
    if ( function_exists( 'kbs_submit_form_shortcode' ) ) {
        return kbs_submit_form_shortcode( array( 'form' => $form_id ) );
    }

    return '';
}

// Register the block
function kbs_register_submit_block() {
    // Check if function exists
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'kbs/submit-block', array(
            'render_callback' => 'kbs_submit_block_render',
            'attributes' => array(
                'formId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ) );
    }
}
add_action( 'init', 'kbs_register_submit_block' );
