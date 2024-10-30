<?php
/**
 * Login / Register Functions
 *
 * @package     KBS
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Login Form
 *
 * @since	1.0
 * @global	$post
 * @param	str		$redirect	Redirect page URL
 * @return	str		Login form
 */
function kbs_login_form( $redirect = '' ) {
	global $kbs_login_redirect;

	if ( empty( $redirect ) ) {
		if ( ! empty( $_GET['kbs_redirect'] ) )	{
			$redirect = sanitize_url( wp_unslash( $_GET['kbs_redirect'] ) );
		} else	{
			$redirect = kbs_get_current_page_url();
		}
	}

	$kbs_login_redirect = $redirect;

	ob_start();

	kbs_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'kbs_login_form', ob_get_clean() );
} // kbs_login_form

/**
 * Registration Form
 *
 * @since	1.0
 * @global	$post
 * @param	str		$redirect	Redirect page URL
 * @return	str		Login form
 */
function kbs_register_form( $redirect = '' ) {
    global $kbs_register_redirect;

    if ( empty( $redirect ) ) {
        if ( ! empty( $_GET['kbs_redirect'] ) ) {
            $redirect = sanitize_url( wp_unslash( $_GET['kbs_redirect'] ) );
        } else {
            $redirect = kbs_get_current_page_url();
        }
    }

    $kbs_register_redirect = $redirect;

    ob_start();

    if( ! is_user_logged_in() ) {
        kbs_get_template_part( 'shortcode', 'register' );
    } else {
        // Handle logged-in users
        echo '<p class="kbs-logged-in">' . esc_html__( 'You are already logged in', 'kb-support' ) . '</p>';
    }

    return apply_filters( 'kbs_register_form', ob_get_clean() );
} // kbs_registration_form

/**
 * Process Login Form
 *
 * @since	1.0
 * @return void
 */
function kbs_process_login_form()	{
	if ( ! isset( $_POST['kbs_action'] ) || 'user_login' != $_POST['kbs_action'] || ! isset( $_POST['kbs_user_login'] )  || '' == $_POST['kbs_user_login'] || ! isset( $_POST['kbs_user_pass'] ) )	{
		return;
	}

	if ( isset( $_POST['kbs_login_nonce'] ) && wp_verify_nonce( $_POST['kbs_login_nonce'], 'kbs-login-nonce' ) ) {
        $user_data = get_user_by( 'login', sanitize_text_field( wp_unslash( $_POST['kbs_user_login'] ) ) );

        if ( ! $user_data && isset( $_POST['kbs_user_login'] ) ) {
            $user_data = get_user_by( 'email', sanitize_email( wp_unslash( $_POST['kbs_user_login'] ) ) );
        }

        if ( $user_data ) {
            $user_ID = $user_data->ID;
            $user_email = $user_data->user_email;
            if ( wp_check_password( sanitize_text_field( wp_unslash( $_POST['kbs_user_pass'] ) ), $user_data->user_pass, $user_data->ID ) ) {
                kbs_log_user_in( $user_data->ID, sanitize_text_field( wp_unslash( $_POST['kbs_user_login'] ) ), sanitize_text_field( wp_unslash( $_POST['kbs_user_pass'] ) ) );
            } else {
                $error = 'password_incorrect';
            }
        } else {
            $error = 'username_incorrect';
        }

		if ( ! empty( $error ) )	{
			$url = remove_query_arg( array( 'kbs_notice', 'kbs_redirect' ) );
			wp_redirect( add_query_arg( array(
				'kbs_notice'   => $error,
				'kbs_redirect' => isset( $_POST['kbs_redirect'] ) ? sanitize_url( wp_unslash( $_POST['kbs_redirect'] ) ) : ''
			), $url ) );
			die();
		}

		$redirect = apply_filters( 'kbs_login_redirect', isset( $_POST['kbs_redirect'] ) ? sanitize_url( wp_unslash( $_POST['kbs_redirect'] ) ) : '', $user_ID );

		$redirect = kbs_safe_redirect_allow_internal( $redirect );

		// If the redirect is not internal, default to the homepage
		if ( ! $redirect ) {
			$redirect = home_url();
		}
		
		wp_redirect( $redirect );
		die();
	}
} // kbs_process_login_form
add_action( 'init', 'kbs_process_login_form' );

if ( ! function_exists( 'kbs_safe_redirect_allow_internal' ) ) {
	function kbs_safe_redirect_allow_internal( $redirect_url ) {
		// Parse the redirect URL
		$redirect_host = parse_url( $redirect_url, PHP_URL_HOST );

		// Get the site's host
		$site_host = parse_url( home_url(), PHP_URL_HOST );

		// Check if the redirect host matches the site host
		if ( $redirect_host && strtolower( $redirect_host ) === strtolower( $site_host ) ) {
			return $redirect_url;
		}

		// If hosts don't match, return false
		return false;
	}
}

/**
 * Log User In
 *
 * @since	1.0
 * @param	int		$user_id	User ID
 * @param	str		$user_login Username
 * @param	str		$user_pass	Password
 * @return	void
 */
function kbs_log_user_in( $user_id, $user_login, $user_pass ) {
	if ( $user_id < 1 )	{
		return;
	}

	wp_set_auth_cookie( $user_id );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'kbs_log_user_in', $user_id, $user_login, $user_pass );
} // kbs_log_user_in


/**
 * Process Register Form
 *
 * @since	1.0
 * @return	void
*/
function kbs_process_register_form() {
	if ( ! isset( $_POST['kbs_action'] ) || 'user_register' != $_POST['kbs_action'] )	{
		return;
	}

	if ( is_user_logged_in() ) {
		return;
	}

	if ( empty( $_POST['kbs_register_submit'] ) ) {
		return;
	}

	do_action( 'kbs_pre_process_register_form' );

    $required_name_fields = kbs_get_option( 'require_name_fields' );

    if ( ( 'first' == $required_name_fields || 'both' == $required_name_fields ) && empty( $_POST['kbs_user_first_name'] ) ) {
        $error = 'empty_first_name';
    } elseif ( 'both' == $required_name_fields && empty( $_POST['kbs_user_last_name'] ) ) {
        $error = 'empty_last_name';
    } elseif ( empty( $_POST['kbs_user_email'] ) || ! is_email( wp_unslash( $_POST['kbs_user_email'] ) ) ) {
		$error = 'email_invalid';
    } elseif ( email_exists( sanitize_email( wp_unslash( $_POST['kbs_user_email'] ) ) ) ) {
		$error = 'email_unavailable';
	} elseif ( empty( $_POST['kbs_user_pass'] ) ) {
		$error = 'empty_password';
	} elseif ( ( ! empty( $_POST['kbs_user_pass'] ) && empty( $_POST['kbs_user_pass2'] ) ) || ( $_POST['kbs_user_pass'] !== $_POST['kbs_user_pass2'] ) ) {
		$error = 'password_mismatch';
	} elseif ( kbs_use_recaptcha_on_registration_form() && ! kbs_validate_recaptcha( isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '' ) )    {
        $error = 'recaptcha_failed';
    }

	do_action( 'kbs_process_register_form' );

	if ( ! empty( $error ) )	{
		$url = remove_query_arg( array( 'kbs_notice', 'kbs_redirect' ) );
		wp_redirect( add_query_arg( array(
			'kbs_notice'   => $error,
			'kbs_redirect' => isset( $_POST['kbs_redirect'] ) ? sanitize_url( wp_unslash( $_POST['kbs_redirect'] ) ) : ''
		), $url ) );
		die();
	}

    $user_data = array(
        'user_first'      => isset( $_POST['kbs_user_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['kbs_user_first_name'] ) ) : '',
        'user_last'       => isset( $_POST['kbs_user_last_name'] )  ? sanitize_text_field( wp_unslash( $_POST['kbs_user_last_name'] ) ) : '',
		'user_pass'       => sanitize_text_field( $_POST['kbs_user_pass'] ),
		'user_email'      => sanitize_email( wp_unslash( $_POST['kbs_user_email'] ) ),
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => kbs_get_option( 'default_role' )
    );

    $user_data['user_login'] = kbs_create_user_name( $user_data );

	$redirect = apply_filters( 'kbs_register_redirect', isset( $_POST['kbs_redirect'] ) ? sanitize_url( wp_unslash( $_POST['kbs_redirect'] ) ) : '' );

	kbs_register_and_login_new_user( $user_data );

	wp_safe_redirect( $redirect );
	die();
} // kbs_process_register_form
add_action( 'init', 'kbs_process_register_form' );

/**
 * Whether or not to show a recaptcha field on the registration form.
 *
 * @since   1.5.1
 * @return  bool    True if the field should be displayed
 */
function kbs_use_recaptcha_on_registration_form()   {
    $show_recaptcha = kbs_get_option( 'show_recaptcha', false );

    return $show_recaptcha;
} // kbs_use_recaptcha_on_registration_form

/**
 * Displays the recaptcha field on the registration form.
 *
 * @since   1.5.1
 * @return  void
 */
function kbs_login_register_recaptcha_display()   {
    if ( ! kbs_use_recaptcha_on_registration_form() )    {
        return;
    }

    $field = new stdClass();
    $field->post_name = 'kbs_recaptcha';

    kbs_display_form_recaptcha_field( $field, array() );
} // kbs_login_register_recaptcha_display
add_action( 'kbs_register_form_fields_before_submit', 'kbs_login_register_recaptcha_display', PHP_INT_MAX );

/**
 * Register And Login New User.
 *
 * @param	arr		$user_data	Data from registration form.
 * @since	1.0
 * @return	int
 */
function kbs_register_and_login_new_user( $user_data = array() ) {
	$return = remove_query_arg( 'kbs_notice' );

	if ( empty( $user_data ) )	{
		wp_safe_redirect( add_query_arg( array(
			'kbs_notice' => 'missing_registration_data'
		), $return ) );
	}

	$user_args = apply_filters( 'kbs_insert_user_args', array(
		'user_login'      => isset( $user_data['user_login'] ) ? $user_data['user_login'] : '',
		'user_pass'       => isset( $user_data['user_pass'] )  ? $user_data['user_pass']  : '',
		'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
		'first_name'      => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
		'last_name'       => isset( $user_data['user_last'] )  ? $user_data['user_last']  : '',
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => isset( $user_data['role'] ) ? $user_data['role'] : kbs_get_option( 'default_role' )
	), $user_data );

	// Insert new user
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user
	if ( is_wp_error( $user_id ) )	{
		wp_safe_redirect( add_query_arg( array(
			'kbs_notice' => 'could_not_register'
		), $return ) );
	}

	// Allow themes and plugins to filter the user data
	$user_data = apply_filters( 'kbs_insert_user_data', $user_data, $user_args );

	// Allow themes and plugins to hook
	do_action( 'kbs_insert_user', $user_id, $user_data );

	// Login new user
	kbs_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Return user id
	return $user_id;
} // kbs_register_and_login_new_user

/**
 * Whether or not a user needs to be logged in before submitting a ticket.
 *
 * @since	1.0
 * @return	true|false
 */
function kbs_user_must_be_logged_in()	{
	return kbs_get_option( 'logged_in_only' );
} // kbs_user_must_be_logged_in
