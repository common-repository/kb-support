<?php
/**
 * Thickbox
 *
 * @package     KBS
 * @subpackage  Admin
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds an "Link KB Article" button above the TinyMCE Editor on add/edit screens.
 *
 * @since	1.0
 * @global	$pagenow
 * @global	$typenow
 * @return	str		"Link KB Article" Button
 */
function kbs_media_button()	{

	global $pagenow, $typenow;

	if ( ( 'kbs_ticket' == $typenow && ( 'draft' == get_post_status() || 'auto-draft' == get_post_status() ) ) || kbs_articles_disabled() )	{
		return;
	}

	$output = '';

	$post_types = array( 'kbs_ticket' );
	$post_types = apply_filters( 'kbs_link_article_media_button_post_types', $post_types );

	/** Only run in post/page creation and edit screens */
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && in_array( $typenow, $post_types ) ) {

        $title = sprintf( esc_html__( 'Link %s to %s', 'kb-support' ), kbs_get_article_label_singular(), kbs_get_ticket_label_singular() );
		$img = '<span class="wp-media-buttons-icon dashicons dashicons-admin-links" id="kbs-media-button"></span> ';
		$output = '<a href="#TB_inline?width=640&inlineId=choose-article" title="' . $title . '" class="thickbox button kbs-thickbox" style="padding-left: .4em;">' . $img . sprintf( esc_html__( 'Link %s', 'kb-support' ), kbs_get_article_label_singular() ) . '</a>';

	}
	echo wp_kses_post( $output );
}
add_action( 'media_buttons', 'kbs_media_button', 11 );

/**
 * Admin Footer For Thickbox
 *
 * Prints the footer code needed for the Link KB Article
 * TinyMCE button.
 *
 * @since	1.0
 * @global	$pagenow
 * @global	$typenow
 * @return	void
 */
function kbs_admin_footer_for_thickbox() {

	if ( 'draft' == get_post_status() || 'auto-draft' == get_post_status() )	{
		return;
	}

	global $pagenow, $typenow;

    $post_types = array( 'kbs_ticket' );
	$post_types = apply_filters( 'kbs_link_article_media_button_post_types', $post_types );

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && in_array( $typenow, $post_types ) )	{

		$single_article = kbs_get_article_label_singular(); ?>

		<script type="text/javascript">
			function linkArticle() {
				var url = jQuery('#articles').val(),
					text = jQuery('#kbs-text').val() || '';

				// Return early if no article is selected
				if ('' === url || '0' === url) {
					alert('<?php printf( esc_html__( "You must choose a %s.", "kb-support" ), esc_html( $single_article ) ); ?>');
					return;
				}

				// Use article title if no link text specified
				if ('' === text)	{
					text = jQuery('#articles option[value="' + url + '"]').text();
				}

				// Send the shortcode to the editor
				window.send_to_editor('<a href="' + url + '" target="_blank">' + text + '</a>');
			}
		</script>

		<div id="choose-article" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<h3><?php echo sprintf( esc_html__( 'Complete the form below to insert a link to a %s', 'kb-support' ), esc_html( $single_article ) ); ?></h3>

				<p>
                    <label for="kbs-text"><strong><?php esc_html_e( 'Enter Link Text', 'kb-support' ); ?></strong>:</label><br>
                	<input type="text" class="regular-text" size="30" id="kbs-text" value="" /><br>
					<span class="description"><?php printf( esc_html__( 'Leave empty to use %s title', 'kb-support' ), esc_html( $single_article ) ); ?></span>
				</p>

				<p>
				    <label for="articles"><strong><?php printf( esc_html__( 'Select %s', 'kb-support' ), esc_html( $single_article ) ); ?></strong>:</label><br>
					<?php echo KBS()->html->article_dropdown( array( 'name' => 'articles', 'key' => 'url', 'chosen' => true ) ); ?>
				</p>

				<p class="submit">
					<input type="button" id="kbs-insert-link" class="button-primary" value="<?php echo sprintf( esc_attr__( 'Link %s', 'kb-support' ), esc_html( $single_article ) ); ?>" onclick="linkArticle();" />
					<a id="kbs-cancel-link-article" class="button-secondary" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'kb-support' ); ?></a>
				</p>
			</div>
		</div>

	<?php
	}

} // kbs_admin_footer_for_thickbox
add_action( 'admin_footer', 'kbs_admin_footer_for_thickbox' );

/**
 * Admin Footer For Add Customer Thickbox.
 *
 * Prints the footer code needed for the Add Customer link.
 *
 * @since	1.3
 * @global	$pagenow
 * @global	$typenow
 * @return	void
 */
function kbs_admin_footer_for_add_customer_thickbox() {

	global $pagenow, $typenow;

    $post_types = array( 'kbs_ticket' );
	$post_types = apply_filters( 'kbs_add_customer_link_post_types', $post_types );

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && in_array( $typenow, $post_types ) )	{

		?>
		<div id="add-customer" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<p>
                    <label for="kbs_name"><strong><?php esc_html_e( 'Customer Name', 'kb-support' ); ?></strong>:</label><br>
                	<input type="text" class="regular-text" size="30" id="kbs_name" value="" />
				</p>

                <p>
				    <label for="kbs_email"><strong><?php esc_html_e( 'Email Address', 'kb-support' ); ?></strong>:</label><br>
                	<input type="text" class="regular-text" size="30" id="kbs_email" value="" />
				</p>

                <?php if ( kbs_has_companies() ) : ?>
                    <p>
                        <label for="kbs_company"><strong><?php esc_html_e( 'Select Company', 'kb-support' ); ?></strong>:</label><br>
                        <?php echo KBS()->html->company_dropdown( array(
                            'name' => 'kbs_company',
                            'placeholder'      => esc_html__( 'Select a Company', 'kb-support' ),
                            'show_option_none' => esc_html__( 'No Company', 'kb-support' ),
                            'number'           => -1,
                            'data'        => array(
                                'search-type'        => 'company',
                                'search-placeholder' => esc_html__( 'Type to search all companies', 'kb-support' )
                            ) 
                        ) ); ?>
                    </p>
                <?php else : ?>
                    <input type="hidden" name="kbs_company" id="kbs_company" value="" />
                <?php endif; ?>

				<p class="submit">
					<input type="button" id="kbs-new-customer-save" class="button-primary" value="<?php esc_attr_e( 'Create Customer', 'kb-support' ); ?>" />
					<a id="kbs-cancel-add-customer" class="button-secondary" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'kb-support' ); ?></a>
				</p>
			</div>
		</div>

	<?php
	}

} // kbs_admin_footer_for_add_customer_thickbox
add_action( 'admin_footer', 'kbs_admin_footer_for_add_customer_thickbox' );
