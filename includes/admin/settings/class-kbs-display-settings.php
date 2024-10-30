<?php
/**
 * Admin notices
 *
 * @package     KBS
 * @subpackage  Classes/Display Settings
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.9
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class KBS_Display_Settings	{

    /**
     * All out settings.
     *
     * @since   1.4.9
     * @var     array
     */
    public $all_settings = array();

	/**
     * Tabs.
     *
     * @since   1.4.9
     * @var     array
     */
	public $all_tabs = array();

	/**
     * Currently active tab.
     *
     * @since   1.4.9
     * @var     string
     */
	public $active_tab;

	/**
     * Tab sections.
     *
     * @since   1.4.9
     * @var     array
     */
	public $all_sections = array();

	/**
     * Currently active section.
     *
     * @since   1.4.9
     * @var     string
     */
	public $section = 'main';

	public $has_main_settings = true;
	public $override          = false;

	/**
     * Active promotions.
     *
     * @since   1.4.9
     * @var     array
     */
    public $promotions = array();

	/**
	 * Get things going.
	 */
	public function __construct()	{
		add_action( 'kbs_menu_after_customers', array( $this, 'add_options_link' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_settings_conditions' ) );
	} // __construct

    /**
	 * Add the settings page to the network menu.
	 *
	 * @since	1.4.9
	 * @return	void
	 */
	public function add_options_link()	{
        if( kbs_tickets_disabled() ){
            add_menu_page(
                //'options-general.php',
                esc_html__( 'KB Support Settings', 'kb-support' ),
                esc_html__( 'KB Support Settings', 'kb-support' ),
                'manage_ticket_settings',
                'kbs-settings',
                array( $this, 'options_page' ),
                'dashicons-book-alt',
                25
            );
        }else{
            add_submenu_page(
                'edit.php?post_type=kbs_ticket',
                esc_html__( 'KB Support Settings', 'kb-support' ),
                esc_html__( 'Settings', 'kb-support' ),
                'manage_ticket_settings',
                'kbs-settings',
                array( $this, 'options_page' )
            );
        }
	} // add_options_link

    /**
	 * Setup options screen.
	 *
	 * @since	1.4.9
	 * @return	void
	 */
	public function setup_options()	{
		$this->all_settings  = kbs_get_registered_settings();
		$this->all_tabs      = kbs_get_settings_tabs();
		$this->all_tabs      = empty( $this->all_tabs ) ? array() : $this->all_tabs;
		$this->active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ( !kbs_tickets_disabled()  ? 'general' : 'tickets' );
		$this->active_tab    = array_key_exists( $this->active_tab, $this->all_tabs ) ? $this->active_tab : ( !kbs_tickets_disabled() ? 'general' : 'tickets' );
		$this->all_sections  = kbs_get_settings_tab_sections( $this->active_tab );
		$this->section       = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'main';
		$this->section       = array_key_exists( $this->section, $this->all_sections ) ? $this->section : 'main';

		// Verify we have a 'main' section to show
		if ( empty( $this->all_settings[ $this->active_tab ]['main'] ) ) {
			$this->has_main_settings = false;
		}

		// Check for old non-sectioned settings
		if ( ! $this->has_main_settings )	{
			foreach( $this->all_settings[ $this->active_tab ] as $sid => $stitle )	{
				if ( is_string( $sid ) && is_array( $this->all_sections ) && array_key_exists( $sid, $this->all_sections ) )	{
					continue;
				} else	{
					$this->has_main_settings = true;
					break;
				}
			}
		}

		if ( false === $this->has_main_settings ) {
			unset( $this->all_sections['main'] );

			if ( 'main' === $this->section ) {
				foreach ( $this->all_sections as $section_key => $section_title ) {
					if ( ! empty( $this->all_settings[ $this->active_tab ][ $section_key ] ) ) {
						$this->section  = $section_key;
						$this->override = true;
						break;
					}
				}
			}
		}

		$this->promotions = kbs_get_current_promotions();
	} // setup_options

    /**
	 * Output the primary navigation (tabs).
	 *
	 * @since	1.4.9
	 * @return	string
	 */
	public function output_primary_nav()	{
		ob_start();?>

        <nav class="nav-tab-wrapper kbs-nav-tab-wrapper kbs-settings-nav" aria-label="<?php esc_attr_e( 'Secondary menu', 'kb-support' ); ?>">
            <?php

            foreach ( $this->all_tabs as $tab_id => $tab_name ) {
                if( kbs_tickets_disabled() ){
                    $tab_url = add_query_arg( array(
                        'page'             => 'kbs-settings',
                        'settings-updated' => false,
                        'tab'              => $tab_id
                    ), admin_url( 'admin.php' ) );
                }else{
                    $tab_url = add_query_arg( array(
                        'post_type'        => 'kbs_ticket',
                        'page'             => 'kbs-settings',
                        'settings-updated' => false,
                        'tab'              => $tab_id
                    ), admin_url( 'edit.php' ) );
                }

                // Remove the section from the tabs so we always end up at the main section
                $tab_url   = remove_query_arg( 'section', $tab_url );
                $tab_class = $this->active_tab == $tab_id ? ' nav-tab-active' : '';

                // Link
                printf(
                    '<a href="%s" class="nav-tab%s">%s</a>',
                    esc_url( $tab_url ),
                    esc_attr( $tab_class ),
                    esc_html( $tab_name )
                );
            }
            ?>
        </nav>

        <?php echo ob_get_clean();
	} // output_primary_nav

    /**
     * Output the secondary navigation
     *
     * @since   1.4.9
     * @return	string
     */
    function output_secondary_nav() {
        // No sections, bail
        if ( empty( $this->all_sections ) || 1 === count( $this->all_sections ) ) {
            return;
        }

        // Default links array
        $links = array();

        // Loop through sections
        foreach ( $this->all_sections as $section_id => $section_name ) {
            // Tab & Section
            if( kbs_tickets_disabled() ){
            $tab_url = add_query_arg( array(
                'page'             => 'kbs-settings',
                'settings-updated' => false,
                'tab'              => $this->active_tab,
                'section'          => $section_id
            ), admin_url( 'admin.php' ) );
        }else{
            $tab_url = add_query_arg( array(
                'post_type'        => 'kbs_ticket',
                'page'             => 'kbs-settings',
                'settings-updated' => false,
                'tab'              => $this->active_tab,
                'section'          => $section_id
            ), admin_url( 'edit.php' ) );
        }
            // Settings not updated
            $tab_url = remove_query_arg( 'settings-updated', $tab_url );

            /**
             * Allow filtering of the section URL.
             *
             * Enables plugin authors to insert links to non-setting pages as sections.
             *
             * @since	1.1.10
             * @param	string		The section URL
             * @param	string		The section ID (array key)
             * @param	string		The current active tab
             * @return	string
             */
            $tab_url = apply_filters( 'kbs_options_page_section_url', $tab_url, $section_id, $this->active_tab );

            // Class for link
            $class = ( $this->section === $section_id ) ? 'current' : '';

            // Add to links array
            $links[ $section_id ] = sprintf(
                '<li class="%1$s"><a class="%1$s" href="%2$s">%3$s</a><li>',
                esc_attr( $class ),
                esc_url( $tab_url ),
                esc_html( $section_name )
            );
        } ?>

        <div class="wp-clearfix">
            <ul class="subsubsub kbs-settings-sub-nav">
                <?php echo wp_kses_post( implode( '&#124;', $links ) ); ?>
            </ul>
        </div>

        <?php
    } // output_secondary_nav

    /**
     * Output the options form.
     *
     * @since   1.4.9
     * @return  string
     */
    public function output_form()   {
        // Setup the action & section suffix
        $suffix          = ! empty( $this->section )    ? $this->active_tab . '_' . $this->section : $this->active_tab . '_main';
        $wrapper_class   = ! empty( $this->promotions ) ? array( ' kbs-has-sidebar' )              : array();
        ?>

        <div class="kbs-settings-wrap<?php echo esc_attr( implode( ' ', $wrapper_class ) ); ?> wp-clearfix">
            <div class="kbs-settings-content">
                <form method="post" action="options.php" class="kbs-settings-form">
                    <?php

                    settings_fields( 'kbs_settings' );

                    if ( 'main' === $this->section ) {
                        do_action( 'kbs_settings_tab_top', $this->active_tab );
                    }

                    do_action( 'kbs_settings_tab_top_' . $suffix );

                    do_settings_sections( 'kbs_settings_' . $suffix );

                    do_action( 'kbs_settings_tab_bottom_' . $suffix  );

                    // For backwards compatibility
                    if ( 'main' === $this->section ) {
                        do_action( 'kbs_settings_tab_bottom', $this->active_tab );
                    }

                    // If the main section was empty and we overrode the view with the
                    // next subsection, prepare the section for saving
                    if ( true === $this->override ) {
                        ?><input type="hidden" name="kbs_section_override" value="<?php echo esc_attr( $this->section ); ?>" /><?php
                    }

                    submit_button(); ?>
                </form>
            </div>
            <?php $this->output_sidebar(); ?>
        </div>
        <?php
    } // output_form

    /**
	 * Output the settings pages.
	 *
	 * @since	1.4.9
	 * @return	string
	 */
	public function options_page()	{
		$this->setup_options();

        ob_start();

        $this->maybe_display_notice(); ?>

        <div class="wrap <?php echo 'wrap-' . esc_attr( $this->active_tab ); ?>">
            <h1><?php esc_html_e( 'Settings', 'kb-support' ); ?></h1><?php
            // Primary nav
            $this->output_primary_nav();

            // Secondary nav
            $this->output_secondary_nav();

            // Form
            $this->output_form();
        ?></div><!-- .wrap --><?php

        // Output the current buffer
        echo ob_get_clean();
	} // options_page

    /**
	 * Output the sidebar.
	 *
	 * @since	1.4.9
	 * @return	string
	 */
	public function output_sidebar()	{
		if ( empty( $this->promotions ) ) {
            return;
        }

        $date_format = 'H:i A F jS';

        foreach( $this->promotions as $code => $data ) : ?>
            <?php
            extract( $data );
            $cta_url = add_query_arg( array(
                'utm_source'   => 'settings',
                'utm_medium'   => 'wp-admin',
                'utm_campaign' => $campaign,
                'utm_content'  => 'sidebar-promo-' . $this->active_tab . '-' . $this->section
            ), $cta_url );
            ?>
            <div class="kbs-settings-sidebar">
                <div class="kbs-settings-sidebar-content">
                    <div class="kbs-sidebar-header-section">
                        <?php if ( ! empty( $image ) )  {
                            printf(
                                '<img class="edd-bfcm-header" src="%s">',
                                esc_url( KBS_PLUGIN_URL . "assets/images/promo/{$image}" )
                            );
                        } else  {
                            echo esc_html( $name );
                        } ?>
                    </div>
                    <div class="kbs-sidebar-description-section">
                        <p class="kbs-sidebar-description">
                            <?php if ( ! empty( $description ) )    {
                                echo wp_kses_post( $description );
                            } else  {
                                printf(
                                    esc_html__( 'Save %s when purchasing the %s <strong>this week</strong>. Including renewals and upgrades!', 'kb-support' ),
                                    wp_kses_post( $discount ),
                                    wp_kses_post( $product )
                                );
                            } ?>
                        </p>
                    </div>
                    <div class="kbs-sidebar-coupon-section">
                        <label for="kbs-coupon-code"><?php esc_html_e( 'Use code at checkout:', 'kb-support' ); ?></label>
                        <input id="kbs-coupon-code" type="text" value="<?php echo esc_attr( $code ); ?>" readonly>
                        <p class="kbs-coupon-note">
                            <?php printf(
                                esc_html__( 'Sale ends %s %s.', 'kb-support' ),
                                esc_html( date_i18n( $date_format, $finish ) ),
                                esc_html( $timezone )
                            ); ?>
                        </p>
                    </div>
                    <div class="kbs-sidebar-footer-section">
                        <a class="button button-primary kbs-cta-button" href="<?php echo esc_url( $cta_url ); ?>" target="_blank"><?php echo wp_kses_post( $cta ); ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach;
	} // output_sidebar

    /**
     * Display notice.
     *
     * @since   1.4.9
     */
    public function maybe_display_notice()    {
        if ( isset( $_GET['updated'] ) ) : ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php esc_html_e( 'Settings saved.', 'kb-support' ); ?></p>
            </div>
        <?php endif;
    } // maybe_display_notice

    public function add_settings_conditions(){
        $assets_dir  = trailingslashit( KBS_PLUGIN_URL . 'assets' );
        $js_dir      = trailingslashit( $assets_dir . 'js' );
        $suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	    wp_enqueue_script( 'kbs-admin-conditions', $js_dir . 'admin-conditions-scripts' . $suffix . '.js', array( 'jquery' ), KBS_VERSION, false );
    }
} // KBS_Display_Settings

new KBS_Display_Settings;
