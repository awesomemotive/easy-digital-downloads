<?php
/**
 * Shortcode: Purchase History - [purchase_history]
 *
 * @package EDD
 * @category Template
 *
 * @since 3.0 Allow details link to appear for `partially_refunded` orders.
 */

if ( ! empty( $_GET['edd-verify-success'] ) ) : ?>
	<p class="edd-account-verified edd_success">
		<?php esc_html_e( 'Your account has been successfully verified!', 'easy-digital-downloads' ); ?>
	</p>
	<?php
endif;
/**
 * This template is used to display the purchase history of the current user.
 */
if ( ! is_user_logged_in() ) {
	return;
}
$orders = edd_get_orders(
	array(
		'user_id' => get_current_user_id(),
		'number'  => 20,
		'type'    => 'sale',
	)
);
if ( $orders ) :
	/**
	 * Fires before the purchase history.
	 *
	 * @since 3.0
	 * @param array $orders The array of the current user's orders.
	 */
	do_action( 'edd_before_orders_history', $orders );
	if ( has_action( 'edd_before_purchase_history' ) ) {
		$payments = edd_get_users_purchases( get_current_user_id(), 20, true, 'any' );
		edd_do_action_deprecated( 'edd_before_purchase_history', array( $payments ), '3.0', 'edd_before_orders_history' );
	}
	?>
	<table id="edd_user_history" class="edd-table">
		<thead>
			<tr class="edd_purchase_row">
				<?php do_action( 'edd_purchase_history_header_before' ); ?>
				<th class="edd_purchase_id"><?php esc_html_e( 'ID', 'easy-digital-downloads' ); ?></th>
				<th class="edd_purchase_date"><?php esc_html_e( 'Date', 'easy-digital-downloads' ); ?></th>
				<th class="edd_purchase_amount"><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></th>
				<th class="edd_purchase_details"><?php esc_html_e( 'Details', 'easy-digital-downloads' ); ?></th>
				<?php do_action( 'edd_purchase_history_header_after' ); ?>
			</tr>
		</thead>
		<?php foreach ( $orders as $order ) : ?>
			<?php $order_meta = edd_get_order_meta( $order->id ); ?>
			<tr class="edd_purchase_row">
				<?php
				/**
				 * Fires at the beginning of the order history row.
				 *
				 * @since 3.0
				 * @param \EDD\Orders\Order $order      The current order object.
				 * @param array             $order_meta The current order metadata.
				 */
				do_action( 'edd_order_history_row_start', $order, $order_meta );
				if ( has_action( 'edd_purchase_history_row_start' ) ) {
					$payment = new EDD_Payment( $order->id );
					edd_do_action_deprecated( 'edd_purchase_history_row_start', array( $payment, $payment->meta ), '3.0', 'edd_order_history_row_start' );
				}
				?>
				<td class="edd_purchase_id">#<?php echo esc_html( $order->get_number() ); ?></td>
				<td class="edd_purchase_date"><?php echo esc_html( edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) ); ?></td>
				<td class="edd_purchase_amount">
					<span class="edd_purchase_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $order->total ) ) ); ?></span>
				</td>
				<td class="edd_purchase_details">
					<?php
					$link_text = ! in_array( $order->status, array( 'complete', 'partially_refunded' ), true ) ? __( 'View Details', 'easy-digital-downloads' ) : __( 'View Details and Downloads', 'easy-digital-downloads' );
					?>
					<a href="<?php echo esc_url( add_query_arg( 'payment_key', $order->payment_key, edd_get_success_page_uri() ) ); ?>"><?php echo esc_html( $link_text ); ?></a>
					<?php if ( ! in_array( $order->status, array( 'complete' ), true ) ) : ?>
						| <span class="edd_purchase_status <?php echo esc_attr( $order->status ); ?>"><?php echo esc_html( edd_get_status_label( $order->status ) ); ?></span>
					<?php endif; ?>
					<?php if ( in_array( $order->status, array( 'pending', 'abandoned', 'failed' ), true ) ) : ?>
						<?php
						$url = add_query_arg(
							array(
								'edd_action' => 'recover_payment',
								'payment_id' => $order->id,
							),
							edd_get_checkout_uri()
						);
						?>
						&mdash; <a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Complete Purchase', 'easy-digital-downloads' ); ?></a>
					<?php endif; ?>
				</td>
				<?php
				/**
				 * Fires at the end of the order history row.
				 *
				 * @since 3.0
				 * @param \EDD\Orders\Order $order      The current order object.
				 * @param array             $order_meta The current order metadata.
				 */
				do_action( 'edd_order_history_row_end', $orders, $order_meta );
				if ( has_action( 'edd_purchase_history_row_end' ) ) {
					$payment = ! empty( $payment ) ? $payment : new EDD_Payment( $order->id );
					edd_do_action_deprecated( 'edd_purchase_history_row_start', array( $payment, $payment->meta ), '3.0', 'edd_order_history_row_end' );
				}
				?>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
	$count = edd_count_orders(
		array(
			'user_id' => get_current_user_id(),
			'type'    => 'sale',
		)
	);
	echo edd_pagination(
		array(
			'type'  => 'purchase_history',
			'total' => ceil( $count / 20 ), // 20 items per page
		)
	);
	/**
	 * Fires after the purchase history.
	 *
	 * @since 3.0
	 * @param array $orders The array of the current user's orders.
	 */
	do_action( 'edd_after_orders_history', $orders );
	if ( has_action( 'edd_after_purchase_history' ) ) {
		$payments = ! empty( $payments ) ? $payments : edd_get_users_purchases( get_current_user_id(), 20, true, 'any' );
		edd_do_action_deprecated( 'edd_after_purchase_history', array( $payments ), '3.0', 'edd_after_orders_history' );
	}
	wp_reset_postdata();
	?>
<?php else : ?>
	<p class="edd-no-purchases"><?php esc_html_e( 'You have not made any purchases', 'easy-digital-downloads' ); ?></p>
	<?php
endif;
