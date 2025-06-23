<?php
/**
 * Handle refunds in the Square Gateway.
 *
 * @package     EDD\Gateways\Square\Admin\Orders\Refunds
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Admin\Orders;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Square\Helpers\Payment;

/**
 * Refunds class.
 *
 * @since 3.4.0
 */
class Refunds {
	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 */
	public function __construct() {
		add_action( 'edd_after_submit_refund_table', array( $this, 'display_refund_checkbox' ) );
		add_action( 'edd_refund_order', array( $this, 'handle_refund_order' ), 10, 3 );
	}

	/**
	 * Display the refund checkbox.
	 *
	 * @since 3.4.0
	 * @param \EDD\Orders\Order $order The order object.
	 * @return void
	 */
	public function display_refund_checkbox( \EDD\Orders\Order $order ) {
		if ( 'square' !== $order->gateway ) {
			return;
		}
		?>
		<div class="edd-form-group edd-square-refund-transaction">
			<div class="edd-form-group__control">
				<input
					type="checkbox"
					id="edd-square-refund"
					name="edd-square-refund"
					class="edd-form-group__input"
					value="1"
					<?php echo esc_attr( 'on_hold' === $order->status ? 'disabled' : '' ); ?>
				>
				<label for="edd-square-refund" class="edd-form-group__label">
					<?php esc_html_e( 'Refund Charge in Square', 'easy-digital-downloads' ); ?>
				</label>
			</div>
			<?php if ( 'on_hold' === $order->status ) : ?>
				<p class="edd-form-group__help description">
					<?php esc_html_e( 'This order is currently on hold. You can create the refund transaction in EDD; Square may have already issued a refund.', 'easy-digital-downloads' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle refund order.
	 *
	 * @since 3.4.0
	 * @param int  $order_id The order ID.
	 * @param int  $refund_id The refund ID.
	 * @param bool $all_refunded Whether the refund is for the full amount.
	 * @return void
	 */
	public function handle_refund_order( $order_id, $refund_id, $all_refunded ) {
		if ( ! current_user_can( 'edit_shop_payments', $order_id ) ) {
			return;
		}

		if ( empty( $_POST['data'] ) ) {
			return;
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order->gateway ) || 'square' !== $order->gateway ) {
			return;
		}

		edd_debug_log( sprintf( 'Square - Maybe processing refund for order #%d.', $order_id ) );

		// Get our data out of the serialized string.
		parse_str( $_POST['data'], $form_data );

		if ( empty( $form_data['edd-square-refund'] ) ) {
			edd_debug_log( 'Square - Exiting refund process, as checkbox was not selected.' );

			edd_add_note(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'user_id'     => is_admin() ? get_current_user_id() : 0,
					'content'     => __( 'Charge not refunded in Square, as checkbox was not selected.', 'easy-digital-downloads' ),
				)
			);

			return;
		}

		edd_debug_log( 'Square - Refund checkbox was selected, proceeding to refund charge.' );

		$refund = edd_get_order( $refund_id );
		if ( empty( $refund->total ) ) {
			edd_debug_log(
				sprintf(
					'Square - Exiting refund for order #%d: refund total is empty.',
					$order_id
				)
			);

			return;
		}

		try {
			Payment::refund_payment( $order, $refund );
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'Exception thrown while refunding order #%d. Message: %s', $order_id, $e->getMessage() ) );
		}
	}
}
