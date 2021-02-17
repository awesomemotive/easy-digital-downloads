<?php
/**
 * Shortcode: Purchase History - [purchase_history]
 *
 * @package EDD
 * @category Template
 *
 * @since 3.0 Allow details link to appear for `partially_refunded` orders.
 */

if( ! empty( $_GET['edd-verify-success'] ) ) : ?>
<p class="edd-account-verified edd_success">
	<?php _e( 'Your account has been successfully verified!', 'easy-digital-downloads' ); ?>
</p>
<?php
endif;
/**
 * This template is used to display the purchase history of the current user.
 */
if ( is_user_logged_in() ):
	$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 0;
	$orders = edd_get_orders( array(
		'user_id' => get_current_user_id(),
		'number'  => 20,
		'offset'  => get_query_var( 'paged' ) ? 20 * ( intval( get_query_var( 'paged' ) ) - 1 ) : 0
	) );

	if ( $orders ) :
		// do_action( 'edd_before_purchase_history', $payments );
		do_action( 'edd_before_order_history', $orders );
		?>
		<table id="edd_user_history" class="edd-table">
			<thead>
				<tr class="edd_purchase_row">
					<?php do_action('edd_purchase_history_header_before'); ?>
					<th class="edd_purchase_id"><?php _e('ID','easy-digital-downloads' ); ?></th>
					<th class="edd_purchase_date"><?php _e('Date','easy-digital-downloads' ); ?></th>
					<th class="edd_purchase_amount"><?php _e('Amount','easy-digital-downloads' ); ?></th>
					<th class="edd_purchase_details"><?php _e('Details','easy-digital-downloads' ); ?></th>
					<?php do_action('edd_purchase_history_header_after'); ?>
				</tr>
			</thead>
			<?php foreach ( $orders as $order ) : ?>
				<tr class="edd_purchase_row">
					<?php
					do_action( 'edd_order_history_row_start', $order );
					//do_action( 'edd_purchase_history_row_start', $payment->ID, $payment->payment_meta );
					?>
					<td class="edd_purchase_id">#<?php echo $order->order_number ? esc_html( $order->order_number ) : esc_html( $order->id ); ?></td>
					<td class="edd_purchase_date"><?php echo edd_date_i18n( $order->date_created ); ?></td>
					<td class="edd_purchase_amount">
						<span class="edd_purchase_amount"><?php echo edd_currency_filter( edd_format_amount( $order->total ) ); ?></span>
					</td>
					<td class="edd_purchase_details">
						<?php if( ! in_array( $order->status, array( 'complete', 'partially_refunded' ) ) ) : ?>
							<span class="edd_purchase_status <?php echo esc_attr( $order->status ); ?>"><?php echo esc_html( edd_get_status_label( $order->status ) ); ?></span>
							<?php
							// @todo order recovery
							/*
							<?php if ( $order->is_recoverable() ) : ?>
								&mdash; <a href="<?php echo $order->get_recovery_url(); ?>"><?php _e( 'Complete Purchase', 'easy-digital-downloads' ); ?></a>
							<?php endif; ?>
							 */
							?>
						<?php else: ?>
							<a href="<?php echo esc_url( add_query_arg( 'payment_key', urlencode( $order->payment_key ), edd_get_success_page_uri() ) ); ?>"><?php _e( 'View Details and Downloads', 'easy-digital-downloads' ); ?></a>
						<?php endif; ?>
					</td>
					<?php
					//do_action( 'edd_purchase_history_row_end', $order->id, $payment->payment_meta );
					do_action( 'edd_order_history_row_end', $order );
					?>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
			echo edd_pagination(
				array(
					'type'  => 'purchase_history',
					'total' => ceil( edd_count_orders( array( 'user_id' => get_current_user_id() ) ) / 20 ) // 20 items per page
				)
			);
		?>
		<?php
		//do_action( 'edd_after_purchase_history', $payments );
		do_action( 'edd_after_order_history', $orders );
		?>
	<?php else : ?>
		<p class="edd-no-purchases"><?php _e('You have not made any purchases','easy-digital-downloads' ); ?></p>
	<?php endif;
endif;
