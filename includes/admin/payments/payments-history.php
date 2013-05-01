<?php
/**
 * Admin Payment History
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Payment History Page
 *
 * Renders the payment history page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function edd_payment_history_page() {
	global $edd_options;

	if ( isset( $_GET['edd-action'] ) && 'edit-payment' == $_GET['edd-action'] ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/edit-payment.php';
	} elseif ( isset( $_GET['edd-action'] ) && 'view-order-details' == $_GET['edd-action'] ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
	} else {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$payments_table = new EDD_Payment_History_Table();
		$payments_table->prepare_items();
	?>
	<div class="wrap">
		<h2><?php _e( 'Payment History', 'edd' ); ?></h2>
		<?php do_action( 'edd_payments_page_top' ); ?>
		<form id="edd-payments-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history' ); ?>">
			<?php $payments_table->search_box( __( 'Search', 'edd' ), 'edd-payments' ); ?>

			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-payment-history" />

			<?php $payments_table->views() ?>

			<?php $payments_table->display() ?>
		</form>
		<?php do_action( 'edd_payments_page_bottom' ); ?>

		<p class="edd-mobile-link">
			<a href="https://easydigitaldownloads.com/extension/ios-sales-earnings-tracker/" target="_blank">
				<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/iphone.png'; ?>"/>
				<?php _e( 'Get the EDD Sales / Earnings tracker for iOS', 'edd' ); ?>
			</a>
		</p>

	</div>
<?php
	}
}

/**
 * Payment History admin titles
 *
 * @since 1.6
 * @return string
 */
function edd_view_order_details_title( $admin_title, $title ) {
	if ( 'download_page_edd-payment-history' != get_current_screen()->base )
		return $admin_title;

	if( ! isset( $_GET['edd-action'] ) )
		return $admin_title;

	switch( $_GET['edd-action'] ) :

		case 'view-order-details' :
			$title = __( 'View Order Details', 'edd' ) . ' - ' . $admin_title;
			break;
		case 'edit-payment' :
			$title = __( 'Edit Payment', 'edd' ) . ' - ' . $admin_title;
			break;
		default:
			$title = $admin_title;
			break;
	endswitch;

	return $title;
}
add_filter( 'admin_title', 'edd_view_order_details_title', 10, 2 );