<?php
/**
 * Admin Add-ons
 *
 * @package     EDD
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0
 * @return void
 */
function edd_add_ons_page() {

	// Filter the add-ons tabs.
	$add_ons_tabs = apply_filters( 'edd_add_ons_tabs', array(
		'popular' => __( 'Popular', 'easy-digital-downloads' ),
		'new'     => __( 'New',     'easy-digital-downloads' ),
		'all'     => __( 'All',     'easy-digital-downloads' )
	) );

	// Active tab.
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $add_ons_tabs )
		? sanitize_key( $_GET['tab'] )
		: 'popular';

	// Empty tabs array.
	$tabs = array();

	// Loop through add-ons and make array of tabs.
	foreach ( $add_ons_tabs as $tab_id => $tab_name ) {

		// "All"
		if ( 'all' === $tab_id ) {
			$tab_url = edd_link_helper(
				'https://easydigitaldownloads.com/downloads/',
				array(
					'utm_medium'  => 'addons-page',
					'utm_content' => 'all-extensions',
				)
			);

		// All other tabs besides "All".
		} else {
			$tab_url = add_query_arg( array(
				'settings-updated' => false,
				'tab'              => sanitize_key( $tab_id ),
			) );
		}

		// Active?
		$active = ( $active_tab === $tab_id )
			? 'current'
			: '';

		// Count.
		$count = ( 'all' === $tab_id )
			? '150+'
			: '29';

		// The link.
		$tab  = '<li class="' . esc_attr( $tab_id ) . '">';
		$tab .= ( 'all' === $tab_id )
			? '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $active ) . '" target="_blank">'
			: '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $active ) . '">';

		$tab .= esc_html( $tab_name );
		$tab .= ' <span class="count">(' . esc_html( $count ) . ')</span>';

		// "All" is an external link, so denote it as such.
		if ( 'all' === $tab_id ) {
			$tab .= '<span class="dashicons dashicons-external"></span>';
		}

		$tab .= '</a>';
		$tab .= '</li>';

		// Set the tab.
		$tabs[] = $tab;
	}

	// Start a buffer.
	ob_start(); ?>

	<div class="wrap" id="edd-add-ons">
		<h1>
			<?php _e( 'Apps and Integrations for Easy Digital Downloads', 'easy-digital-downloads' ); ?>
			<span>
				<?php
				$url = edd_link_helper(
					'https://easydigitaldownloads.com/downloads/',
					array(
						'utm_medium'  => 'addons-page',
						'utm_content' => 'browse-all',
					)
				);
				?>
				&nbsp;&nbsp;<a href="<?php echo $url; ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Browse All Integrations', 'easy-digital-downloads' ); ?></a>
			</span>
		</h1>
		<p><?php _e( 'These <em><strong>add functionality</strong></em> to your Easy Digital Downloads powered store.', 'easy-digital-downloads' ); ?></p>

		<ul class="subsubsub"><?php echo implode( ' | ', $tabs ); ?></ul>

		<div class="edd-add-ons-container">
			<?php
			// Display all add ons.
			echo wp_kses_post( edd_add_ons_get_feed( $active_tab ) );
			?>
			<div class="clear"></div>
		</div>

		<div class="edd-add-ons-footer">
			<?php
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/downloads/',
				array(
					'utm_medium'  => 'addons-page',
					'utm_content' => 'browse-all',
				)
			);
			?>
			<a href="<?php echo $url; ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Browse All Integrations', 'easy-digital-downloads' ); ?></a>
		</div>
	</div>

	<?php

	// Output the current buffer.
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

	// Transient.
	$trans_key = 'easydigitaldownloads_add_ons_feed_' . $tab;
	$cache     = get_transient( $trans_key );

	// No add ons, so reach out and get some.
	if ( false === $cache ) {
		$url = 'https://easydigitaldownloads.com/?feed=addons';

		// Popular.
		if ( 'popular' !== $tab ) {
			$url = add_query_arg( array( 'display' => sanitize_key( $tab ) ), $url );
		}

		// Remote request.
		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		// Handle error.
		if ( empty( $feed ) || is_wp_error( $feed ) ) {
			$cache = '<div class="error"><p>' . __( 'These extensions could not be retrieved from the server. Please try again later.', 'easy-digital-downloads' ) . '</div>';

		// Cache the results.
		} elseif ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$cache = wp_remote_retrieve_body( $feed );
			set_transient( $trans_key, $cache, HOUR_IN_SECONDS );
		}
	}

	return $cache;
}
