<?php
/**
 * View Order Details
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
} ?>

<form id="edd-edit-order-form" method="post">

	<?php edd_order_details_publish( $order ); ?>

	<div class="wrap edd-wrap">
		<h1><?php printf( __( 'Edit Order: %s', 'easy-digital-downloads' ), $order->number ); ?></h1>

		<hr class="wp-header-end">

		<?php if ( 'refund' === $order->type ) : ?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						__( 'You are viewing a refund order. To view the original order, %sclick here%s.', 'easy-digital-downloads' ),
						'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $order->parent ) . '">',
						'</a>'
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php do_action( 'edd_view_order_details_before', $order->id ); ?>

		<?php do_action( 'edd_view_order_details_form_top', $order->id ); ?>

		<div id="poststuff">
			<div id="edd-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables">
							<?php

							// Before sidebar
							do_action( 'edd_view_order_details_sidebar_before', $order->id );

							// Attributes
							edd_order_details_attributes( $order );

							// Amounts
							edd_order_details_amounts( $order );

							// Related Refunds
							edd_order_details_refunds( $order );

							// Extras
							edd_order_details_extras( $order );

							// Logs
							edd_order_details_logs( $order );

							// After sidebar
							do_action( 'edd_view_order_details_sidebar_after', $order->id );

							?>
						</div>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables">
							<?php

							// Before body
							do_action( 'edd_view_order_details_main_before', $order->id );

							// Items
							edd_order_details_items( $order );

							// Adjustments
							edd_order_details_adjustments( $order );

							// Details sections
							edd_order_details_sections( $order );

							// Legacy hook from pre version 3 of Easy Digital Downloads.
							do_action( 'edd_view_order_details_billing_after', $order->id );

							// After body
							do_action( 'edd_view_order_details_main_after', $order->id );

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

<div id="edd-download-link" title="<?php _e( 'Download Links', 'easy-digital-downloads' ); ?>"></div>
<div id="edd-refund-order-dialog" title="<?php _e( 'Submit Refund', 'easy-digital-downloads' ); ?>"></div>
