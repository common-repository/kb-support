<?php
/**
 * Post Taxonomy Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Registers the Categories taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_category_taxonomy()	{

	$ticket_category_labels = array(
		'name'              => esc_html_x( 'Categories', 'taxonomy general name', 'kb-support' ),
		'singular_name'     => esc_html_x( 'Category', 'taxonomy singular name', 'kb-support' ),
		'search_items'      => sprintf( esc_html__( 'Search %s Categories', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'all_items'         => sprintf( esc_html__( 'All %s Categories', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item'       => sprintf( esc_html__( 'Parent %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item_colon' => sprintf( esc_html__( 'Parent %s Category:', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'edit_item'         => sprintf( esc_html__( 'Edit %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'update_item'       => sprintf( esc_html__( 'Update %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'add_new_item'      => sprintf( esc_html__( 'Add New %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'new_item_name'     => sprintf( esc_html__( 'New %s Category Name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'menu_name'         => sprintf( esc_html__( 'Categories', 'kb-support' ), kbs_get_ticket_label_singular() )
	);

	$ticket_category_args = apply_filters( 'kbs_ticket_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'kbs_ticket_category_labels', $ticket_category_labels),
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'query_var'    => 'ticket_category',
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count',
			'show_in_rest'          => true,
            'rest_base'             => 'ticket_categories',
            'rest_controller_class' => 'WP_REST_Terms_Controller'
		)
	);

	register_taxonomy( 'ticket_category', array( 'kbs_ticket' ), $ticket_category_args );
	register_taxonomy_for_object_type( 'ticket_category', 'kbs_ticket' );

} // kbs_setup_kbs_ticket_category_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_category_taxonomy', 2 );

/**
 * Registers the Tags taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_tag_taxonomy()	{

	$ticket_tag_labels = array(
		'name'                  => _x( 'Tags', 'taxonomy general name', 'kb-support' ),
		'singular_name'         => _x( 'Tag', 'taxonomy singular name', 'kb-support' ),
		'search_items'          => sprintf( esc_html__( 'Search %s Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'all_items'             => sprintf( esc_html__( 'All %s Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item'           => sprintf( esc_html__( 'Parent %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item_colon'     => sprintf( esc_html__( 'Parent %s Tag:', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'edit_item'             => sprintf( esc_html__( 'Edit %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'update_item'           => sprintf( esc_html__( 'Update %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'add_new_item'          => sprintf( esc_html__( 'Add New %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'new_item_name'         => sprintf( esc_html__( 'New %s Tag Name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'menu_name'             => esc_html__( 'Tags', 'kb-support' ),
		'choose_from_most_used' => sprintf( esc_html__( 'Choose from most used %s tags', 'kb-support' ), kbs_get_ticket_label_singular( true ) )
	);

	$ticket_tag_args = apply_filters( 'kbs_ticket_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_ticket_tag_labels', $ticket_tag_labels ),
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'query_var'    => 'ticket_tag',
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count',
			'show_in_rest' => true
		)
	);

	register_taxonomy( 'ticket_tag', array( 'kbs_ticket' ), $ticket_tag_args );
	register_taxonomy_for_object_type( 'ticket_tag', 'kbs_ticket' );


} // kbs_setup_kbs_ticket_tag_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_tag_taxonomy', 2 );

/**
 * Registers the Source taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_source_taxonomy()	{
    $ticket_singular = kbs_get_ticket_label_singular();

	$source_labels = array(
		'name'              => sprintf( esc_html_x( '%s Source', 'taxonomy general name', 'kb-support' ), $ticket_singular ),
		'singular_name'     => sprintf( esc_html_x( '%s Source', 'taxonomy singular name', 'kb-support' ), $ticket_singular ),
		'search_items'      => sprintf( esc_html__( 'Search %s Sources', 'kb-support' ), $ticket_singular ),
		'all_items'         => sprintf( esc_html__( 'All %s Sources', 'kb-support' ), $ticket_singular ),
		'parent_item'       => sprintf( esc_html__( 'Parent %s Source', 'kb-support' ), $ticket_singular ),
		'parent_item_colon' => sprintf( esc_html__( 'Parent %s Source:', 'kb-support' ), $ticket_singular ),
		'edit_item'         => sprintf( esc_html__( 'Edit %s Source', 'kb-support' ), $ticket_singular ),
		'update_item'       => sprintf( esc_html__( 'Update %s Source', 'kb-support' ), $ticket_singular ),
		'add_new_item'      => sprintf( esc_html__( 'Add %s Source', 'kb-support' ), $ticket_singular ),
		'new_item_name'     => sprintf( esc_html__( 'New %s Source Name', 'kb-support' ), $ticket_singular ),
		'menu_name'         => sprintf( esc_html__( '%s Sources', 'kb-support' ), $ticket_singular ),
		'not_found'         => sprintf( esc_html__( 'No %s sources found', 'kb-support' ), strtolower( $ticket_singular ) ),
        'back_to_items'     => sprintf( esc_html__( 'Back to %s sources', 'kb-support' ), strtolower( $ticket_singular ) )
	);

	$source_args = apply_filters( 'kbs_ticket_source_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_ticket_source_labels', $source_labels ),
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count',
			'show_in_rest'          => true,
            'rest_base'             => 'ticket_sources',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
		)
	);

	register_taxonomy( 'ticket_source', array( 'kbs_ticket', 'kbs_ticket_reply' ), $source_args );
	register_taxonomy_for_object_type( 'ticket_source', 'kbs_ticket' );
    register_taxonomy_for_object_type( 'ticket_source', 'kbs_ticket_reply' );
} // kbs_setup_kbs_ticket_source_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_source_taxonomy', 2 );

/**
 * Registers the Department taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_department_taxonomy()	{
	if ( ! kbs_departments_enabled() )	{
		return;
	}

	$department_labels = array(
		'name'              => _x( 'Departments', 'taxonomy general name', 'kb-support' ),
		'singular_name'     => _x( 'Department', 'taxonomy singular name', 'kb-support' ),
		'search_items'      => esc_html__( 'Search Departments', 'kb-support' ),
		'all_items'         => esc_html__( 'All Departments', 'kb-support' ),
		'parent_item'       => esc_html__( 'Parent Department', 'kb-support' ),
		'parent_item_colon' => esc_html__( 'Parent Department:', 'kb-support' ),
		'edit_item'         => esc_html__( 'Edit Department', 'kb-support' ),
		'update_item'       => esc_html__( 'Update Department', 'kb-support' ),
		'add_new_item'      => esc_html__( 'Add Department', 'kb-support' ),
		'new_item_name'     => esc_html__( 'New Department Name', 'kb-support' ),
		'menu_name'         => esc_html__( 'Departments', 'kb-support' ),
		'not_found'         => esc_html__( 'No departments found', 'kb-support' ),
        'back_to_items'     => esc_html__( 'Back to departments', 'kb-support' )
	);

	$department_args = apply_filters( 'kbs_ticket_department_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_ticket_department_labels', $department_labels ),
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count',
			'show_in_rest'          => true,
            'rest_base'             => 'ticket_departments',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
		)
	);

	register_taxonomy( 'department', array( 'kbs_ticket' ), $department_args );
	register_taxonomy_for_object_type( 'department', 'kbs_ticket' );
} // kbs_setup_kbs_ticket_department_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_department_taxonomy', 2 );

/**
 * Get the singular and plural labels for a taxonomy.
 *
 * @since	1.0
 * @param	str		$taxonomy	The Taxonomy to get labels for
 * @return	arr		Associative array of labels (name = plural)
 */
function kbs_get_taxonomy_labels( $taxonomy = 'ticket_category' ) {

	$allowed_taxonomies = apply_filters(
		'kbs_allowed_taxonomies',
		array( 'ticket_category', 'ticket_tag', 'ticket_source', 'department', 'article_category', 'article_tag' )
	);

	if ( ! in_array( $taxonomy, $allowed_taxonomies ) ) {
		return false;
	}

	$labels   = array();
	$taxonomy = get_taxonomy( $taxonomy );

	if ( false !== $taxonomy ) {
		$singular  = $taxonomy->labels->singular_name;
		$name      = $taxonomy->labels->name;
		$menu_name = $taxonomy->labels->menu_name;

		$labels = array(
			'name'          => $name,
			'singular_name' => $singular,
			'menu_name'     => $menu_name
		);
	}

	return apply_filters( 'kbs_get_taxonomy_labels', $labels, $taxonomy );

} // kbs_get_taxonomy_labels
