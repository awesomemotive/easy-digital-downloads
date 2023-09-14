<?php
/**
 * Adjustments
 *
 * These are functions used for displaying discounts, credits, fees, and more.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Shows the adjustments page, containing of all registered & visible adjustment
 * types (Discounts|Credits|Fees)
 *
 * @since 3.0
 */
function edd_adjustments_page() {

	// Get all tabs
	$all_tabs = edd_get_adjustments_tabs();

	// Current tab
	$active_tab = isset( $_GET['tab'] )
		? sanitize_key( $_GET['tab'] )
		: 'discount';

	// Add new URL
	$add_new_url = edd_get_admin_url( array(
		'page'       => 'edd-discounts',
		'edd-action' => 'add_' . sanitize_key( $active_tab ),
	) );

	// Start the output buffer
	ob_start(); ?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Discounts', 'easy-digital-downloads' ); ?></h1>
		<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'easy-digital-downloads' ); ?></a>

		<hr class="wp-header-end">
		<?php if ( 1 < count( $all_tabs ) ) : ?>

		<nav class="nav-tab-wrapper edd-nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-digital-downloads' ); ?>">
			<?php

			// Loop through all tabs
			foreach ( $all_tabs as $tab_id => $tab_name ) :

				// Add the tab ID
				$tab_url = edd_get_admin_url( array(
					'page' => 'edd-discounts',
					'tab'  => sanitize_key( $tab_id ),
				) );

				// Remove messages
				$tab_url = remove_query_arg( array(
					'edd-message',
				), $tab_url );

				// Setup the selected class
				$active = ( $active_tab === $tab_id )
					? ' nav-tab-active'
					: ''; ?>

				<a href="<?php echo esc_url( $tab_url ); ?>" class="nav-tab<?php echo esc_attr( $active ); ?>"><?php echo esc_html( $tab_name ); ?></a>

			<?php endforeach; ?>

			</nav>
		<br>
		<?php endif; ?>

		<?php do_action( 'edd_adjustments_page_' . esc_attr( $active_tab ) ); ?>
	</div><!-- .wrap -->

	<?php

	// Output the current buffer
	echo ob_get_clean();
}

/**
 * Retrieve adjustments tabs.
 *
 * @since 3.0
 *
 * @return array Tabs for the 'Adjustments' page.
 */
function edd_get_adjustments_tabs() {

	// Tabs
	$tabs = array(
		'discount' => __( 'Discounts', 'easy-digital-downloads' ),
//		'credit'   => __( 'Credits',   'easy-digital-downloads' ),
//		'fee'      => __( 'Fees',      'easy-digital-downloads' )
	);

	// Filter & return
	return apply_filters( 'edd_adjustments_tabs', $tabs );
}
