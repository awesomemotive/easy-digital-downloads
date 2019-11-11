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
	$add_ons_tabs = apply_filters( 'edd_add_ons_tabs', array( 'popular' => __( 'Popular', 'easy-digital-downloads' ), 'new' => __( 'New', 'easy-digital-downloads' ), 'all' => __( 'View all Integrations', 'easy-digital-downloads' ) ) );
	$active_tab   = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $add_ons_tabs ) ? $_GET['tab'] : 'popular';

	// Set a new campaign for tracking purposes
	$campaign = isset( $_GET['view'] ) && strtolower( $_GET['view'] ) === 'integrations' ? 'EDDIntegrationsPage' : 'EDDAddonsPage';

	ob_start(); ?>
	<div class="wrap" id="edd-add-ons">
		<h1 class="wp-heading-inline"><?php echo edd_get_label_plural(); ?></h1>
		<a href="<?php echo admin_url( 'post-new.php?post_type=download' ); ?>" class="page-title-action">Add New</a>
		<hr class="wp-header-end">
		<?php edd_display_product_tabs(); ?>
		<h2>
			<?php _e( 'Apps and Integrations for Easy Digital Downloads', 'easy-digital-downloads' ); ?>
			<span>
				&nbsp;&nbsp;<a href="https://easydigitaldownloads.com/downloads/?utm_source=plugin-addons-page&utm_medium=plugin&utm_campaign=<?php echo $campaign; ?>&utm_content=All%20Extensions" class="button-primary" target="_blank"><?php _e( 'Browse All Integrations', 'easy-digital-downloads' ); ?></a>
			</span>
		</h2>
		<p><?php _e( 'These <em><strong>add functionality</strong></em> to your Easy Digital Downloads powered store.', 'easy-digital-downloads' ); ?></p>
		<div class="edd-add-ons-view-wrapper">
			<ul class="subsubsub">
				<?php
				$total_tabs = count( $add_ons_tabs );
				$i = 1;
				foreach( $add_ons_tabs as $tab_id => $tab_name ) {

					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab' => $tab_id
					) );

					if ( 'all' === $tab_id ) {
						$tab_url = 'https://easydigitaldownloads.com/downloads/?utm_source=plugin-addons-page&utm_medium=plugin&utm_campaign=' . $campaign . '&utm_content=All%20Extensions';
					}

					$active = $active_tab == $tab_id ? 'current' : '';

					echo '<li class="' . $tab_id . '">';
					echo '<a href="' . esc_url( $tab_url ) . '" class="' . $active . '">';
					echo esc_html( $tab_name );
					echo '</a>';

					if ( 'all' === $tab_id ) {
						$count = '150+';
					} else {
						$count = '29';
					}

					echo ' <span class="count">(' . $count . ')</span>';
					echo '</li>';

					if ( $i !== $total_tabs ) {
						echo ' | ';
					}

					$i++;
				}
				?>
			</ul>
		</div>
		<div id="tab_container">
			<?php

			// Display a promotional element before all add ons if a promotion is active.
			$is_promo_active = edd_is_promo_active();
			if ( true === $is_promo_active ) {

				// Build the main URL for the promotion.
				$args = array(
					'utm_source'   => 'add-ons-feed',
					'utm_medium'   => 'wp-admin',
					'utm_campaign' => 'bfcm2019',
					'utm_content'  => 'first-feed-element-' . $active_tab,
				);
				$url  = add_query_arg( $args, 'https://easydigitaldownloads.com/pricing/' );
				?>
				<div class="edd-add-ons-promo edd-extension">
					<h3 class="edd-extension-title">Black Friday & Cyber Monday sale!</h3>
					<a href="<?php echo esc_url( $url ); ?>" title="Black Friday & Cyber Monday sale">
						<img class="attachment-download-grid-thumb size-download-grid-thumb wp-post-image" src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/promo/edd-25-percent-off.png' ); ?>">
					</a>
					<p><?php echo wp_kses_post( __( 'Save 25% on all Easy Digital Downloads purchases <strong>this week</strong>, including renewals and upgrades! Use code <span class="bfcm-code">BFCM2019</span> at checkout.', 'easy-digital-downloads' ) ); ?></p>
					<a class="button-secondary" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php esc_html_e( 'Don\'t miss out!', 'easy-digital-downloads' ); ?></a>
					<span class="sale-ends"><?php esc_html_e( 'Sale ends 23:59 PM December 6th CST', 'easy-digital-downloads' ); ?></span>
				</div>
				<?php
			}

			// Display all add ons.
			echo wp_kses_post( edd_add_ons_get_feed( $active_tab ) );
			?>
			<div class="clear"></div>
			<div class="edd-add-ons-footer">
				<a href="https://easydigitaldownloads.com/downloads/?utm_source=plugin-addons-page&utm_medium=plugin&utm_campaign=<?php echo esc_attr( $campaign ); ?>&utm_content=All%20Extensions" class="button-primary" target="_blank"><?php esc_html_e( 'Browse All Integrations', 'easy-digital-downloads' ); ?></a>
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

	if ( isset( $_GET['view'] ) && 'integrations' === $_GET['view'] ) {
		// Set a new campaign for tracking purposes
		$cache = str_replace( 'EDDAddonsPage', 'EDDIntegrationsPage', $cache );
	}

	return $cache;
}
