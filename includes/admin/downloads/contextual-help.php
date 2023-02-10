<?php
/**
 * Contextual Help
 *
 * @package     EDD
 * @subpackage  Admin/Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.3
 */

use EDD\Admin\Pass_Manager;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Adds the Contextual Help for the main Downloads page
 *
 * @since 1.2.3
 * @return void
 */
function edd_downloads_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'download' ) {
		return;
	}

	$pass_manager = new Pass_Manager();
	if ( $pass_manager->isFree() ) {
		$docs_url = edd_link_helper(
			'https://easydigitaldownloads.com/docs/',
			array(
				'utm_medium'  => 'downloads-contextual-help',
				'utm_content' => 'documentation',
			)
		);

		$upgrade_url = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'downloads-contextual-help',
				'utm_content' => 'lite-upgrade',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'easy-digital-downloads' ) . '</strong></p>' .
			'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Easy Digital Downloads website.', 'easy-digital-downloads' ), $docs_url ) . '</p>' .
			'<p>' . sprintf(
				__( 'Need more from your Easy Digital Downloads store? <a href="%s">Upgrade Now</a>!', 'easy-digital-downloads' ),
				$upgrade_url
			) . '</p>'
		);
	}

	$screen->add_help_tab( array(
		'id'	    => 'edd-download-configuration',
		'title'	    => sprintf( __( '%s Settings', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'content'	=>
			'<p>' . __( '<strong>File Download Limit</strong> - Define how many times customers are allowed to download their purchased files. Leave at 0 for unlimited. Resending the purchase receipt will permit the customer one additional download if their limit has already been reached.', 'easy-digital-downloads' ) . '</p>' .

			'<p>' . __( '<strong>Accounting Options</strong> - If enabled, define an individual SKU or product number for this download.', 'easy-digital-downloads' ) . '</p>' .

			'<p>' . __( '<strong>Button Options</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-download-prices',
		'title'	    => sprintf( __( '%s Prices', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'content'	=>
			'<p>' . __( '<strong>Enable variable pricing</strong> - By enabling variable pricing, multiple download options and prices can be configured.', 'easy-digital-downloads' ) . '</p>' .

			'<p>' . __( '<strong>Enable multi-option purchases</strong> - By enabling multi-option purchases customers can add multiple variable price items to their cart at once.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-download-files',
		'title'	    => sprintf( __( '%s Files', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'content'	=>
			'<p>' . __( '<strong>Product Type Options</strong> - Choose a default product type or a bundle. Bundled products automatically include access to other download&#39;s files when purchased.', 'easy-digital-downloads' ) . '</p>' .

			'<p>' . __( '<strong>File Downloads</strong> - Define download file names and their respective file URL. Multiple files can be assigned to a single price, or variable prices.', 'easy-digital-downloads' ) . '</p>'
	) );


	$screen->add_help_tab( array(
		'id'	    => 'edd-product-notes',
		'title'	    => sprintf( __( '%s Instructions', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'content'	=> '<p>' . sprintf( __( 'Special instructions for this %s. These will be added to the sales receipt, and may be used by some extensions or themes.', 'easy-digital-downloads' ), strtolower( edd_get_label_singular() ) ) . '</p>'
	) );

	$colors = array(
		'gray', 'pink', 'blue', 'green', 'teal', 'black', 'dark gray', 'orange', 'purple', 'slate'
	);

	$screen->add_help_tab( array(
		'id'      => 'edd-purchase-shortcode',
		'title'   => __( 'Purchase Shortcode', 'easy-digital-downloads' ),
		'content' =>
			'<p>' . __( '<strong>Purchase Shortcode</strong> - If the automatic output of the purchase button has been disabled via the Download Configuration box, a shortcode can be used to output the button or link.', 'easy-digital-downloads' ) . '</p>' .
			'<p><code>[purchase_link id="#" price="1" text="Add to Cart" color="blue"]</code></p>' .
			'<ul>
				<li><strong>id</strong> - ' . __( 'The ID of a specific download to purchase.', 'easy-digital-downloads' ) . '</li>
				<li><strong>price</strong> - ' . __( 'Whether to show the price on the purchase button. 1 to show the price, 0 to disable it.', 'easy-digital-downloads' ) . '</li>
				<li><strong>text</strong> - ' . __( 'The text to be displayed on the button or link.', 'easy-digital-downloads' ) . '</li>
				<li><strong>style</strong> - ' . __( '<em>button</em> | <em>text</em> - The style of the purchase link.', 'easy-digital-downloads' ) . '</li>
				<li><strong>color</strong> - <em>' . implode( '</em> | <em>', $colors ) . '</em></li>
				<li><strong>class</strong> - ' . __( 'One or more custom CSS classes you want applied to the button.', 'easy-digital-downloads' ) . '</li>
			</ul>' .
			'<p>' . sprintf(
				__( 'For more information, see <a href="%s">using Shortcodes</a> on the WordPress.org Codex or <a href="%s">Easy Digital Downloads Documentation</a>', 'easy-digital-downloads' ),
				'https://codex.wordpress.org/Shortcode',
				'https://easydigitaldownloads.com/docs/purchase_link-shortcode/'
			) . '</p>'
	) );

	/**
	 * Fires off in the EDD Downloads Contextual Help Screen
	 *
	 * @since 1.2.3
	 * @param object $screen The current admin screen
	 */
	do_action( 'edd_downloads_contextual_help', $screen );
}
add_action( 'load-post.php', 'edd_downloads_contextual_help' );
add_action( 'load-post-new.php', 'edd_downloads_contextual_help' );
