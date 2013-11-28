<?php
/**
 * Admin Footer
 *
 * @package     EDD
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add rating links to the admin dashboard
 *
 * @since	    1.8.5
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function edd_admin_rate_us( $footer_text ) {
	$rate_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">EDD</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">wordpress.org</a>', 'edd' ),
		'http://wordpress.org/plugins/easy-digital-downloads',
		'http://wordpress.org/support/view/plugin-reviews/easy-digital-downloads#postform'
	);

	return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . '</span>';
}
add_filter( 'admin_footer_text', 'edd_admin_rate_us' );
