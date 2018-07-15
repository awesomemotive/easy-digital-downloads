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

/**
 * Render Orders page.
 *
 * @since 1.0
 * @since 3.0 Nomenclature updated for consistency.
 *            Add a link to manually all orders.
*/
function edd_payment_history_page() {

	if ( isset( $_GET['view'] ) && 'view-order-details' === $_GET['view'] ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
	} else {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$payments_table = new EDD_Payment_History_Table();
		$payments_table->prepare_items();

		$admin_url = add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-payment-history'
		), admin_url( 'edit.php' ) ); ?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Orders', 'easy-digital-downloads' ); ?></h1>
			<hr class="wp-header-end">

			<?php do_action( 'edd_payments_page_top' ); ?>

			<form id="edd-payments-filter" method="get" action="<?php echo esc_url( $admin_url ); ?>">
				<input type="hidden" name="post_type" value="download" />
				<input type="hidden" name="page" value="edd-payment-history" />
				<?php
				$payments_table->views();
				$payments_table->advanced_filters();
				$payments_table->display();
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
			<?php _e( 'Try the Sales/Earnings iOS App!', 'easy-digital-downloads' ); ?>
		</a>
	</span>
	<?php
}
add_action( 'edd_after_admin_filter_bar_reports', 'edd_payment_history_mobile_link' );

/**
 * Orders admin titles.
 *
 * @since 1.6
 *
 * @param $admin_title
 * @param $title
 *
 * @return string
 */
function edd_view_order_details_title( $admin_title, $title ) {
	if ( 'download_page_edd-payment-history' !== get_current_screen()->base ) {
		return $admin_title;
	}

	if ( ! isset( $_GET['view'] ) ) {
		return $admin_title;
	}

	$action = sanitize_text_field( $_GET['view'] );

	switch ( $action ) {
		case 'view-order-details':
		case 'edit-payment':
			$title = __( 'Edit Order', 'easy-digital-downloads' ) . ' - ' . $admin_title;
			break;
		default:
			$title = $admin_title;
			break;
	}

	return $title;
}
add_filter( 'admin_title', 'edd_view_order_details_title', 10, 2 );