<?php
/**
 * Admin Payment History
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Payment History
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/


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

	if( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'edit-payment' ) {
		include_once( EDD_PLUGIN_DIR . '/includes/admin/payments/edit-payment.php' );
	} else {
		include_once( EDD_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php' );
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
	</div>
<?php

	}
}
