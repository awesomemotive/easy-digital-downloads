<?php
/**
 * View Order Details
 *
 * @package     EDD\Admin\Payments
 * @copyright   Copyright (c) 2018, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * View Order Details Page
 *
 * @since 1.6
 * @since 3.0 Updated to use the new EDD\Orders\Order object.
 */

if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Order ID not supplied. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

$order_id = absint( $_GET['id'] );
$order    = edd_get_order( $order_id );

// Check that the order exists in the database.
if ( empty( $order ) ) {
	wp_die( __( 'The specified ID does not belong to an order. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

if ( 'refund' === $order->type ) {
	$refund_link = edd_get_admin_url(
		array(
			'page' => 'edd-payment-history',
			'view' => 'view-refund-details',
			'id'   => urlencode( $order->id ),
		)
	);
	/* translators: %s: refund link */
	wp_die( sprintf( __( 'The specified ID is for a refund, not an order. Please <a href="%s">access the refund directly</a>.', 'easy-digital-downloads' ), esc_url( $refund_link ) ), __( 'Error', 'easy-digital-downloads' ) );
}

wp_enqueue_script( 'edd-admin-orders' );
// Enqueued for backwards compatibility. Empty file.
wp_enqueue_script( 'edd-admin-payments' );
?>

<form id="edd-edit-order-form" method="post">

	<?php edd_order_details_publish( $order ); ?>

	<div class="wrap edd-wrap edd-clearfix">
		<?php /* translators: %s: order number */ ?>
		<h1><?php printf( esc_html__( 'Order: %s', 'easy-digital-downloads' ), esc_html( $order->number ) ); ?></h1>

		<hr class="wp-header-end">

		<div class="notice notice-error inline" id="edd-add-order-customer-error" style="display: none;">
			<p><strong><?php esc_html_e( 'Error', 'easy-digital-downloads' ); ?>:</strong> <?php esc_html_e( 'Please select an existing customer or create a new customer.', 'easy-digital-downloads' ); ?></p>
		</div>

		<?php do_action( 'edd_view_order_details_before', $order->id ); ?>

		<?php do_action( 'edd_view_order_details_form_top', $order->id ); ?>

		<div id="poststuff">
			<div id="edd-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables">
							<?php

							// Before body
							do_action( 'edd_view_order_details_main_before', $order->id );

							// Overview
							edd_order_details_overview( $order );

							// Details sections
							edd_order_details_sections( $order );

							// Legacy hook from pre version 3 of Easy Digital Downloads.
							do_action( 'edd_view_order_details_billing_after', $order->id );

							// After body
							do_action( 'edd_view_order_details_main_after', $order->id );

							?>
						</div>
					</div>

					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables">
							<?php

							// Before sidebar
							do_action( 'edd_view_order_details_sidebar_before', $order->id );

							// Attributes
							edd_order_details_attributes( $order );

							// Extras
							edd_order_details_extras( $order );

							// After sidebar
							do_action( 'edd_view_order_details_sidebar_after', $order->id );

							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php

		do_action( 'edd_view_order_details_form_bottom', $order->id );

		wp_nonce_field( 'edd_update_payment_details_nonce' ); ?>
		<input type="hidden" name="edd_payment_id" value="<?php echo esc_attr( $order->id ); ?>"/>

		<input type="hidden" name="edd_action" value="update_payment_details"/>

		<?php do_action( 'edd_view_order_details_after', $order->id ); ?>

	</div><!-- /.wrap -->

</form>

<dialog
	id="edd-refund-order-dialog"
	class="edd-modal edd-modal--refund"
	aria-labelledby="edd-refund-order-dialog__title"
>
	<div class="edd-modal__header">
		<h2 id="edd-refund-order-dialog__title"><?php esc_html_e( 'Submit Refund', 'easy-digital-downloads' ); ?></h2>
		<button type="button" class="edd-modal__close" aria-label="<?php esc_attr_e( 'Close', 'easy-digital-downloads' ); ?>">
			<span class="dashicons dashicons-no-alt"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'easy-digital-downloads' ); ?></span>
		</button>
	</div>
	<div class="edd-modal__content" id="edd-refund-dialog-content"></div>
</dialog>
