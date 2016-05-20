<?php
/**
 * Admin Add-ons
 *
 * @package     EDD
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0
 * @return void
 */
function edd_add_ons_page() {
	$add_ons_tabs = apply_filters( 'edd_add_ons_tabs', array( 'popular' => 'Popular', 'new' => 'New') );
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $add_ons_tabs ) ? $_GET['tab'] : 'popular';

	ob_start(); ?>
	<div class="wrap" id="edd-add-ons">
		<h1>
			<?php _e( 'Extensions for Easy Digital Downloads', 'easy-digital-downloads' ); ?>
			<span>
				&nbsp;&nbsp;<a href="http://easydigitaldownloads.com/extensions/?utm_source=plugin-addons-page&utm_medium=plugin&utm_campaign=EDD%20Addons%20Page&utm_content=All%20Extensions" class="button-primary" target="_blank"><?php _e( 'Browse All Extensions', 'easy-digital-downloads' ); ?></a>
			</span>
		</h1>
		<p><?php _e( 'These extensions <em><strong>add functionality</strong></em> to your Easy Digital Downloads powered store.', 'easy-digital-downloads' ); ?></p>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( $add_ons_tabs as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
			<span class="edd-tab-span"><img src="<?php echo EDD_PLUGIN_URL; ?>assets/images/edd-peeking.png" /></span>
		</h2>
		<div id="tab_container">
			<?php echo edd_add_ons_get_feed( $active_tab ); ?>
			<div class="clear"></div>
			<div class="edd-add-ons-footer">
				<a href="http://easydigitaldownloads.com/extensions/?utm_source=plugin-addons-page&utm_medium=plugin&utm_campaign=EDD%20Addons%20Page&utm_content=All%20Extensions" class="button-primary" target="_blank"><?php _e( 'Browse All Extensions', 'easy-digital-downloads' ); ?></a>
			</div>
		</div><!-- #tab_container-->
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
function edd_add_ons_get_feed( $tab = 'popular' ) {
	$cache = get_transient( 'easydigitaldownloads_add_ons_feed_' . $tab );

	if ( false === $cache ) {
		$url = 'https://easydigitaldownloads.com/?feed=addons';

		if ( 'popular' !== $tab ) {
			$url = add_query_arg( array( 'display' => $tab ), $url );
		}

		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'easydigitaldownloads_add_ons_feed_' . $tab, $cache, 3600 );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'easy-digital-downloads' ) . '</div>';
		}
	}

	return $cache;
}
