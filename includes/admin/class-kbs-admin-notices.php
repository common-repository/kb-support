<?php
/**
 * Admin notices
 *
 * @package     KBS
 * @subpackage  Classes/Admin Notices
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Admin Add-ons Notices
 *
 * @since	0.1
 * @return	void
*/
function kbs_admin_addons_notices() {
	add_settings_error(
		'kbs-notices',
		'kbs-addons-feed-error',
		__( 'There seems to be an issue with the server. Please try again in a few minutes.', 'kb-support' ), 'error'
	);

	settings_errors( 'kbs-notices' );
} // kbs_admin_addons_notices

/**
 * Retrieve all dismissed notices.
 *
 * @since	1.3
 * @return	array	Array of dismissed notices
 */
function kbs_dismissed_notices() {

	global $current_user;

	$user_notices = (array) get_user_option( 'kbs_dismissed_notices', $current_user->ID );

	return $user_notices;

} // kbs_dismissed_notices

/**
 * Check if a specific notice has been dismissed.
 *
 * @since	1.3
 * @param	string	$notice	Notice to check
 * @return	bool	Whether or not the notice has been dismissed
 */
function kbs_is_notice_dismissed( $notice ) {

	$dismissed = kbs_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed ) ) {
		return true;
	} else {
		return false;
	}

} // kbs_is_notice_dismissed

/**
 * Dismiss a notice.
 *
 * @since	1.3
 * @param	string		$notice	Notice to dismiss
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function kbs_dismiss_notice( $notice ) {

	global $current_user;

	$dismissed_notices = $new = (array) kbs_dismissed_notices();

	if ( ! array_key_exists( $notice, $dismissed_notices ) ) {
		$new[ $notice ] = 'true';
	}

	$update = update_user_option( $current_user->ID, 'kbs_dismissed_notices', $new );

	return $update;

} // kbs_dismiss_notice

/**
 * Restore a dismissed notice.
 *
 * @since	1.3
 * @param	string		$notice	Notice to restore
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function kbs_restore_notice( $notice ) {

	global $current_user;

	$dismissed_notices = (array) kbs_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed_notices ) ) {
		unset( $dismissed_notices[ $notice ] );
	}

	$update = update_user_option( $current_user->ID, 'kbs_dismissed_notices', $dismissed_notices );

	return $update;

} // kbs_restore_notice

class KBS_Admin_Notices	{

	/**
	 * Get things going.
	 */
	public function __construct()	{
		add_action( 'admin_notices',         array( $this, 'show_notices'                             ) );
        add_action( 'plugins_loaded',        array( $this, 'notify_first_extension_discount_advisory' ) );
        add_action( 'plugins_loaded',        array( $this, 'request_wp_5star_rating'                  ) );
        add_action( 'kbs_dismiss_notices',   array( $this, 'dismiss_notices'                          ) );
        add_action( 'kbs_do_dismiss_notice', array( $this, 'grab_notice_dismiss'                      ) );
	} // __construct

    /**
	 * Show relevant notices
	 *
	 * @since  1.3
	 */
	public function show_notices() {
        $ticket_singular  = kbs_get_ticket_label_singular();
        $ticket_plural    = kbs_get_ticket_label_plural();
        $article_singular = kbs_get_article_label_singular();
        $article_plural   = kbs_get_article_label_plural();

		$notices = array(
			'updated' => array(),
			'error'   => array(),
		);

        // Global messages
        if ( isset( $_GET['kbs-message'], $_GET['field_id'] ) && 'editing_field' == $_GET['kbs-message'] )	{
            ob_start(); ?>
            <div class="notice notice-info">
                <p><strong><?php printf(
                    esc_html__( 'Editing: %s.', 'kb-support' ),
                    esc_html( get_the_title( absint( $_GET['field_id'] ) ) )
                ); ?></strong></p>
            </div>
            <?php echo ob_get_clean();
        }

        if ( isset( $_GET['kbs-message'] ) )    {
            // Security
            switch( $_GET['kbs-message'] ) {
                case 'nonce_fail' :
                    $notices['updated']['kbs-nonce-fail'] = esc_html__( 'Security verification failed.', 'kb-support' );
                    break;
            }

            // Ticket notices
            if ( current_user_can( 'edit_tickets' ) )    {
                switch( $_GET['kbs-message'] ) {
                    case 'ticket_reopened':
                        $notices['updated']['kbs-ticket-reopened'] = sprintf( esc_html__( '%s reopened.', 'kb-support' ), $ticket_singular );
                        break;

                    case 'ticket_not_closed':
                        $notices['error']['kbs-ticket-not-closed'] = sprintf( esc_html__( 'The %s cannot be re-opened. It is not closed.', 'kb-support' ), strtolower( $ticket_singular ) );
                        break;

                    case 'ticket_reply_added':
                        $notices['updated']['kbs-ticket-reply-added'] = esc_html__( 'The reply was successfully added.', 'kb-support' );
                        break;

                    case 'ticket_reply_added_closed':

                        $closed = '';

                        $create_article_link = add_query_arg( array(
                            'kbs-action' => 'create_article',
                            'ticket_id'  => isset( $_GET['kbs_ticket_id'] ) ? absint( $_GET['kbs_ticket_id'] ) : 0
                        ), admin_url() );

                        $create_article_link = apply_filters( 'kbs_create_article_link', $create_article_link, isset( $_GET['kbs_ticket_id'] ) ? absint( $_GET['kbs_ticket_id'] ) : 0 );

                        $closed = sprintf( esc_html__( ' and the %1$s was closed.', 'kb-support' ), strtolower( $ticket_singular ) );
                        $closed .= ' ';
                        $closed .= wp_kses_post( sprintf(__( 'Create <a href="%s">%s</a>', 'kb-support' ), esc_url( $create_article_link ), $article_singular ) );

                        $notices['updated']['kbs-ticket-reply-added'] = sprintf( esc_html__( 'The reply was successfully added%s.', 'kb-support' ), $closed );
                        break;

                    case 'ticket_reply_failed':
                        $notices['error']['kbs-ticket-reply-failed'] = esc_html__( 'The reply could not be added.', 'kb-support' );
                        break;

                    case 'ticket_reply_deleted':
                        $notices['updated']['kbs-ticket-reply-deleted'] = esc_html__( 'The reply was successfully deleted.', 'kb-support' );
                        break;

                    case 'ticket_reply_delete_failed':
                        $notices['error']['kbs-ticket-reply-deleted'] = esc_html__( 'The reply could not be deleted.', 'kb-support' );
                        break;

                    case 'note_deleted':
                        $notices['updated']['kbs-ticket-note-deleted'] = esc_html__( 'The note was deleted.', 'kb-support' );
                        break;

                    case 'note_not_deleted':
                        $notices['error']['kbs-ticket-note-not-deleted'] = esc_html__( 'The note could not be deleted.', 'kb-support' );
                        break;
                }
            }

            // Article notices
            if ( current_user_can( 'edit_articles' ) )   {
                switch( $_GET['kbs-message'] )  {
                    case 'article_created':
                        $notices['updated']['kbs-create-article-success'] = sprintf(
                            esc_html__( 'Draft %1$s created from %2$s. Review, edit and publish the new %1$s below.', 'kb-support' ),
                            strtolower( $article_singular ),
                            strtolower( $ticket_singular )
                        );
                        break;

                    case 'create_article_failed':
                        $notices['error']['kbs-create-article-failed'] = sprintf(
                            esc_html__( 'Could not create new %1$s from %2$s.', 'kb-support' ),
                            strtolower( $article_singular ),
                            strtolower( $ticket_singular )
                        );
                        break;

                    case 'reset_article_views':
                        $notices['updated']['kbs-reset-article-views'] = sprintf( esc_html__( 'View count reset for %s.', 'kb-support' ), $article_singular );
                        break;

                    case 'reset_article_views_failed':
                        $notices['error']['kbs-reset-article-views-failed'] = sprintf( esc_html__( 'Failed to reset %s view count.', 'kb-support' ), $article_singular );
                        break;
                }
            }

            // Submission form notices
            if ( current_user_can( 'edit_submission_forms' ) )    {
                switch( $_GET['kbs-message'] )  {
                    case 'field_added':
                        $notices['updated']['kbs-field-added'] = esc_html__( 'Field was added.', 'kb-support' );
                        break;

                    case 'field_add_fail':
                        $notices['error']['kbs-field-notadded'] = esc_html__( 'Unable to add field.', 'kb-support' );
                        break;

                    case 'field_saved':
                        $notices['updated']['kbs-field-updated'] = esc_html__( 'Field updated.', 'kb-support' );
                        break;

                    case 'field_save_fail':
                        $notices['error']['kbs-field-notsaved'] = esc_html__( 'Unable to save field.', 'kb-support' );
                        break;

                    case 'field_deleted':
                        $notices['updated']['kbs-field-deleted'] = esc_html__( 'Field deleted.', 'kb-support' );
                        break;

                     case 'field_delete_fail':
                        $notices['error']['kbs-field-notdeleted'] = esc_html__( 'Unable to delete field.', 'kb-support' );
                        break;

                    case 'field_deleted':
                        $notices['updated']['kbs-field-deleted'] = esc_html__( 'Field deleted.', 'kb-support' );
                        break;
                }
            }

            // Customer notices
            if ( kbs_can_view_customers() )    {
                switch( $_GET['kbs-message'] )  {
                    case 'customer_created':
                        $notices['updated']['kbs-customer-added'] = esc_html__( 'Customer added successfully.', 'kb-support' );
                        break;

                    case 'customer_list_permission':
                        $notices['error']['kbs-customer-list-permission'] = esc_html__( 'You do not have permission to view the customer list.', 'kb-support' );
                        break;

                    case 'invalid_customer_id':
                        $notices['error']['kbs-invalid-customer-id'] = esc_html__( 'An invalid customer ID was provided.', 'kb-support' );
                        break;

                    case 'email_added':
                        $notices['updated']['kbs-customer-email-added'] = esc_html__( 'Email address added.', 'kb-support' );
                        break;

                    case 'email_removed':
                        $notices['updated']['kbs-customer-email-removed'] = esc_html__( 'Email address removed.', 'kb-support' );
                        break;

                    case 'email_remove_failed':
                        $notices['error']['kbs-customer-email-remove-failed'] = esc_html__( 'Email address could not be removed.', 'kb-support' );
                        break;

                    case 'primary_email_updated':
                        $notices['updated']['kbs-customer-email-primary-updated'] = esc_html__( 'Primary email address updated.', 'kb-support' );
                        break;

                    case 'primary_email_failed':
                        $notices['error']['kbs-customer-email-primary-remove-failed'] = esc_html__( 'Primary email address could not be updated.', 'kb-support' );
                        break;

                    case 'customer_delete_no_confirm':
                        $notices['error']['kbs-customer-delete-no-confirm'] = esc_html__( 'Please confirm you wish to delete this customer.', 'kb-support' );
                        break;

                     case 'customer_deleted':
                        $notices['updated']['kbs-customer-deleted'] = esc_html__( 'Customer deleted.', 'kb-support' );
                        break;

                    case 'customer_delete_failed':
                        $notices['error']['kbs-customer-delete-failed'] = esc_html__( 'Customer could not be deleted.', 'kb-support' );
                        break;

                    case 'disconnect_user':
                        $notices['updated']['kbs-customer-disconnect-user'] = esc_html__( 'Customer disconnected from user ID.', 'kb-support' );
                        break;

                    case 'disconnect_user_fail':
                        $notices['error']['kbs-customer-disconnect-user-failed'] = esc_html__( 'Could not disconnect customer from user ID.', 'kb-support' );
                        break;

                }
            }

            // Import/Export notices
            if ( current_user_can( 'export_ticket_reports' ) )    {
                switch( $_GET['kbs-message'] )  {
                    case 'settings-imported':
                        $notices['updated']['kbs-settings-imported'] = esc_html__( 'Customer added successfully.', 'kb-support' );
                        break;

                     case 'settings-import-missing-file':
                        $notices['error']['kbs-settings-import-file-missing'] = esc_html__( 'Please upload a valid .json file.', 'kb-support' );
                        break;
                }
            }

            // Settings and upgrade notices
            if ( current_user_can( 'manage_ticket_settings' ) )    {
                switch( $_GET['kbs-message'] )  {
                    case 'sequential-numbers-updated':
                        $notices['updated']['kbs-sequential-numbers-updated'] = sprintf( esc_html__( '%s numbers have been successfully upgraded.', 'kb-support' ), $ticket_singular );
                        break;

                    case 'ticket-sources-updated':
                        $notices['updated']['kbs-ticket-sources-updated'] = sprintf( esc_html__( '%s sources have been successfully updated.', 'kb-support' ), $ticket_singular );
                        break;

					case 'api-key-generated' :
						$notices['updated']['kbs-api-key-generated'] = sprintf( esc_html__( 'API keys successfully generated.', 'kb-support' ) );
					break;

					case 'api-key-exists' :
						$notices['error']['kbs-api-key-exists'] = sprintf( esc_html__( 'The specified user already has API keys.', 'kb-support' ) );
					break;

					case 'api-key-regenerated' :
						$notices['updated']['kbs-api-key-regenerated'] = sprintf( esc_html__( 'API keys successfully regenerated.', 'kb-support' ) );
					break;

					case 'api-key-revoked' :
						$notices['updated']['kbs-api-key-revoked'] = sprintf( esc_html__( 'API keys successfully revoked.', 'kb-support' ) );
					break;

					case 'api-key-failed' :
						$notices['error']['kbs-api-key-failed'] = sprintf( esc_html__( 'API key generation failed.', 'kb-support' ) );
					break;
                }
            }
        }

        if ( count( $notices['updated'] ) > 0 ) {
			foreach( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'kbs-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach( $notices['error'] as $notice => $message ) {
				add_settings_error( 'kbs-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'kbs-notices' );

    } // show_notices

    /**
     * Plugin discount Notice
     *
     * @since	1.3
     * @return	void
    */
    function admin_first_extension_discount_advisory_notice() {
        ob_start(); ?>

        <div class="updated notice notice-kbs-dismiss is-dismissible" data-notice="first_extension_discount_advisory">
            <p>
                <?php wp_kses_post( sprintf(
                     __( 'Loving KB Support? Great! Did you know you can receive a <strong>%1$s discount</strong> on the purchase of extensions from our <a target="_blank" href="%2$s">plugin store</a> to further enhance the features and functionality? <a href="%2$s">Shop Now!</a>', 'kb-support' ),
                    '15%',
                    'https://kb-support.com/extensions/' )
                ); ?>
            </p>
        </div>

        <?php echo ob_get_clean();
    } // admin_first_extension_discount_advisory_notice

    /**
     * Advise admins of discount for extensions in the plugin store.
     *
     * After 5 closed tickets we notify the admin that a discount on extensions is available
     *
     * @since	1.3
     * @return	void
     */
    public function notify_first_extension_discount_advisory() {

        if ( defined( 'KBS_SAAS' ) && true === KBS_SAAS )	{
            return ;
        }

        if ( ! current_user_can( 'administrator' ) || ! kbs_is_admin_page() )	{
            return;
        }

        if ( kbs_is_notice_dismissed( 'first_extension_discount_advisory' ) )	{
            return ;
        }

        global $wpdb;

        $closed_tickets = $wpdb->get_var( $wpdb->prepare(
            "
                SELECT COUNT(*)
                FROM $wpdb->posts
                WHERE `post_type` = %s
                AND `post_status` = %s
            ",
            'kbs_ticket',
            'closed'
        ) );

        if ( $closed_tickets >= 5 ) {
            add_action( 'admin_notices', array( $this, 'admin_first_extension_discount_advisory_notice' ) );
        }

    } // notify_first_extension_discount_advisory

    /**
     * Admin WP Rating Request Notice
     *
     * @since	1.3
     * @return	void
    */
    function admin_wp_5star_rating_notice() {
        ob_start(); ?>

        <div class="updated notice notice-kbs-dismiss is-dismissible" data-notice="kbs_request_wp_5star_rating">
            <p>
                <?php echo wp_kses_post( sprintf(
                    __( '<strong>Awesome!</strong> It looks like you have closed over 25 %1$s since you activated KB Support which is really fantastic!', 'kb-support' ),
                    kbs_get_ticket_label_plural( true )
                ) ); ?>
            </p>
            <p>
                <?php echo wp_kses_post( sprintf(
                    __( 'Would you <strong>please</strong> do us a favour and leave a 5 star rating on WordPress.org? It only takes a minute and it <strong>really helps</strong> to motivate our developers and volunteers to continue to work on great new features and functionality. <a href="%1$s" target="_blank">Sure thing, you deserve it!</a>', 'kb-support' ),
                    'https://wordpress.org/support/plugin/kb-support/reviews/'
                ) ); ?>
            </p>
        </div>

        <?php echo ob_get_clean();
    } // admin_wp_5star_rating_notice

    /**
     * Request 5 star rating after 25 closed tickets.
     *
     * After 25 closed tickets we ask the admin for a 5 star rating on WordPress.org
     *
     * @since	1.3
     * @return	void
     */
    public function request_wp_5star_rating() {

        if ( defined( 'KBS_SAAS' ) && true === KBS_SAAS )	{
            return ;
        }

        if ( ! current_user_can( 'administrator' ) || ! kbs_is_admin_page() )	{
            return;
        }

        if ( kbs_is_notice_dismissed( 'kbs_request_wp_5star_rating' ) )	{
            return ;
        }

        global $wpdb;

        $closed_tickets = $wpdb->get_var( $wpdb->prepare(
            "
                SELECT COUNT(*)
                FROM $wpdb->posts
                WHERE `post_type` = %s
                AND `post_status` = %s
            ",
            'kbs_ticket',
            'closed'
        ) );

        if ( $closed_tickets > 25 ) {
            add_action( 'admin_notices', array( $this, 'admin_wp_5star_rating_notice' ) );
        }

    } // request_wp_5star_rating

    /**
     * Check if there is a notice to dismiss.
     *
     * @since	1.3
     * @param	array	$data	Contains the notice ID
     * @return	void
     */
    public function grab_notice_dismiss( $data ) {

        $notice_id = isset( $data['notice_id'] ) ? $data['notice_id'] : false;

        if ( false === $notice_id ) {
            return;
        }

        kbs_dismiss_notice( $notice_id );

    } // grab_notice_dismiss

    /**
     * Dismisses admin notices when Dismiss links are clicked
     *
     * @since	0.1
     * @return	void
    */
    function dismiss_notices() {

        $notice = isset( $_GET['kbs_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['kbs_notice'] ) ) : false;

        if ( ! $notice )	{
            return;
        }

        kbs_dismiss_notice( $notice );

        wp_redirect( remove_query_arg( array( 'kbs_action', 'kbs_notice' ) ) ); exit;

    } // dismiss_notices

} // KBS_Admin_Notices

new KBS_Admin_Notices;
