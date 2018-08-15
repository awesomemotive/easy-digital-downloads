<?php
/**
 * Functions to render Orders page.
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Navigation ****************************************************************/

/**
 * Output the primary orders page navigation
 *
 * @since 3.0
 * @param string $active_tab
 */
function edd_orders_page_primary_nav( $active_tab = '' ) {

	$add_new_url = add_query_arg( array( 'view' => 'add-order' ), edd_get_admin_url( array( 'page' => 'edd-payment-history' ) ) );

	ob_start();?>

	<h2 class="nav-tab-wrapper edd-nav-tab-wrapper">
		<?php

		// Get the order pages
		$tabs = edd_get_order_pages();

		// Loop through order pages and create tabs
		foreach ( $tabs as $tab_id => $tab_name ) {

			// Remove
			$tab_url = add_query_arg( array(
				'settings-updated' => false,
				'type'             => $tab_id,
			) );

			// Remove the section from the tabs so we always end up at the main section
			$tab_url = remove_query_arg( 'section', $tab_url );
			$active  = $active_tab === $tab_id
				? ' nav-tab-active'
				: '';

			// Link
			echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">'; // WPCS: XSS ok.
				echo esc_html( $tab_name );
			echo '</a>';
		}
		?>
		<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'easy-digital-downloads' ); ?></a>
	</h2>

	<?php

	echo ob_get_clean(); // WPCS: XSS ok.
}

/**
 * Retrieve the order pages.
 *
 * Used only by the primary tab navigation for orders currently.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_pages() {

	// Default tabs
	$tabs = array(
		'sale'    => __( 'Orders',   'easy-digital-downloads' ),
		'refund'  => __( 'Refunds',  'easy-digital-downloads' ),
		'invoice' => __( 'Invoices', 'easy-digital-downloads' )
	);

	// Filter & return
	return (array) apply_filters( 'edd_get_order_pages', $tabs );
}

/**
 * Render Orders page.
 *
 * @since 1.0
 * @since 3.0 Nomenclature updated for consistency.
 *            Add a link to manually all orders.
*/
function edd_payment_history_page() {
	if ( isset( $_GET['view'] ) && 'view-order-details' === $_GET['view'] ) { // WPCS: CSRF ok.
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
	} elseif ( isset( $_GET['view'] ) && 'add-order' === $_GET['view'] ) { // WPCS: CSRF ok.
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/add-order.php';
	} else {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$orders_table = new EDD_Payment_History_Table();
		$orders_table->prepare_items();

		$active_tab = ! empty( $_GET['type'] ) // WPCS: CSRF ok.
			? sanitize_key( $_GET['type'] )
			: 'sale';

		$admin_url = edd_get_admin_url( array( 'page' => 'edd-payment-history' ) ); ?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Orders', 'easy-digital-downloads' ); ?></h1>
			<hr class="wp-header-end">

			<?php edd_orders_page_primary_nav( $active_tab ); ?>

			<?php do_action( 'edd_payments_page_top' ); ?>

			<form id="edd-payments-filter" method="get" action="<?php echo esc_url( $admin_url ); ?>">
				<input type="hidden" name="post_type" value="download" />
				<input type="hidden" name="page" value="edd-payment-history" />
				<input type="hidden" name="type" value="<?php echo esc_attr( $active_tab ); ?>" />
				<?php
				$orders_table->views();
				$orders_table->advanced_filters();
				$orders_table->display();
				?>
			</form>

			<?php do_action( 'edd_payments_page_bottom' ); ?>
		</div>

		<?php
	}
}

/**
 * Renders the mobile link at the bottom of the payment history page
 *
 * @since 1.8.4
 * @since 3.0 Updated filter to display link next to the reports filters.
*/
function edd_payment_history_mobile_link() {
	?>
	<span class="edd-mobile-link">
		<a href="https://easydigitaldownloads.com/downloads/ios-app/?utm_source=payments&utm_medium=mobile-link&utm_campaign=admin" target="_blank">
			<?php esc_html_e( 'Try the Sales/Earnings iOS App!', 'easy-digital-downloads' ); ?>
		</a>
	</span>
	<?php
}
add_action( 'edd_after_admin_filter_bar_reports', 'edd_payment_history_mobile_link' );

/**
 * Orders admin titles.
 *
 * @since 1.6
 * @since 3.0 Updated to use new nomenclature.
 *
 * @param string $admin_title
 * @param string $title
 *
 * @return string Updated admin title.
 */
function edd_view_order_details_title( $admin_title, $title ) {

	// Bail if we aren't on the Orders page.
	if ( 'download_page_edd-payment-history' !== get_current_screen()->base ) {
		return $admin_title;
	}

	if ( ! isset( $_GET['view'] ) ) { // WPCS: CSRF ok.
		return $admin_title;
	}

	$action = sanitize_text_field( $_GET['view'] );

	switch ( $action ) {
		case 'view-order-details':
		case 'edit-payment':
			$title = __( 'Edit Order', 'easy-digital-downloads' ) . ' &mdash; ' . $admin_title;
			break;
		case 'add-order':
			$title = __( 'Add New Order', 'easy-digital-downloads' ) . ' &mdash; ' . $admin_title;
			break;
		default:
			$title = $admin_title;
			break;
	}

	return $title;
}
add_filter( 'admin_title', 'edd_view_order_details_title', 10, 2 );
