<?php
/**
 * Admin Add-ons
 *
 * @package     EDD
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons Page Init
 *
 * Hooks check feed to the page load action.
 *
 * @since 1.0
 * @global $edd_add_ons_page EDD Add-ons Pages
 * @return void
 */
function edd_add_ons_init() {
	global $edd_add_ons_page;
	add_action( 'load-' . $edd_add_ons_page, 'edd_add_ons_check_feed' );
}
add_action( 'admin_menu', 'edd_add_ons_init');

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0
 * @return void
 */
function edd_add_ons_page() {
	ob_start(); ?>
	<div class="wrap" id="edd-add-ons">
		<h2>
			<?php _e( 'Add Ons for Easy Digital Downloads', 'edd' ); ?>
			&nbsp;&mdash;&nbsp;<a href="http://easydigitaldownloads.com/extensions/?ref=1" class="button-primary" title="<?php _e( 'Browse All Extensions', 'edd' ); ?>" target="_blank"><?php _e( 'Browse All Extensions', 'edd' ); ?></a>
		</h2>
		<p><?php _e( 'These add-ons extend the functionality of Easy Digital Downloads.', 'edd' ); ?></p>
		<?php echo edd_add_ons_get_feed(); ?>
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @since 1.0
 * @return void
 */
function edd_add_ons_get_feed() {
	if ( false === ( $cache = get_transient( 'easydigitaldownloads_add_ons_feed' ) ) ) {
		$feed = wp_remote_get( 'https://easydigitaldownloads.com/?feed=extensions', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'easydigitaldownloads_add_ons_feed', $cache, 3600 );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'edd' ) . '</div>';
		}
	}
	return $cache;
}