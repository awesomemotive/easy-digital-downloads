<?php
/**
 * This template is used to display the purchase summary with [edd_receipt]
 */
global $edd_receipt_args;
$order = edd_get_order( $edd_receipt_args['id'] );

// Display a notice if the order was not found in the database.
if ( ! $order ) : ?>

	<div class="edd_errors edd-alert edd-alert-error">
		<?php esc_html_e( 'The specified receipt ID appears to be invalid', 'easy-digital-downloads' ); ?>
	</div>

	<?php
	return;
endif;

$payment = edd_get_payment( $order->id );
$meta    = edd_get_payment_meta( $order->id );
$cart    = edd_get_payment_meta_cart_details( $order->id, true );
$user    = edd_get_payment_meta_user_info( $order->id );
$status  = edd_get_payment_status( $order, true );
?>
<table id="edd_purchase_receipt" class="edd-table">
	<thead>
		<?php do_action( 'edd_payment_receipt_before', $payment, $edd_receipt_args ); ?>

		<?php if ( filter_var( $edd_receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
		<tr>
			<th><strong><?php echo esc_html_x( 'Order', 'heading', 'easy-digital-downloads' ); ?>:</strong></th>
			<th><?php echo esc_html( $order->get_number() ); ?></th>
		</tr>
		<?php endif; ?>
	</thead>

	<tbody>
		<tr>
			<td class="edd_receipt_payment_status"><strong><?php esc_html_e( 'Order Status', 'easy-digital-downloads' ); ?>:</strong></td>
			<td class="edd_receipt_payment_status <?php echo esc_html( strtolower( $order->status ) ); ?>"><?php echo esc_html( $status ); ?></td>
		</tr>

		<?php if ( filter_var( $edd_receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Payment Key', 'easy-digital-downloads' ); ?>:</strong></td>
				<td><?php echo esc_html( $order->payment_key ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $edd_receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Payment Method', 'easy-digital-downloads' ); ?>:</strong></td>
				<td><?php echo esc_html( edd_get_gateway_checkout_label( $order->gateway ) ); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( filter_var( $edd_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
		<tr>
			<td><strong><?php esc_html_e( 'Date', 'easy-digital-downloads' ); ?>:</strong></td>
			<td><?php echo esc_html( edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( ! empty( $order->get_fees() ) ) : ?>
		<tr>
			<td><strong><?php esc_html_e( 'Fees', 'easy-digital-downloads' ); ?>:</strong></td>
			<td>
				<ul class="edd_receipt_fees">
				<?php foreach ( $order->get_fees() as $fee ) : ?>
					<li>
						<span class="edd_fee_label"><?php echo esc_html( $fee->description ); ?></span>
						<span class="edd_fee_sep">&nbsp;&ndash;&nbsp;</span>
						<span class="edd_fee_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $fee->total ) ) ); ?></span>
					</li>
				<?php endforeach; ?>
				</ul>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( filter_var( $edd_receipt_args['discount'], FILTER_VALIDATE_BOOLEAN ) && isset( $user['discount'] ) && 'none' !== $user['discount'] ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Discount(s)', 'easy-digital-downloads' ); ?>:</strong></td>
				<td><?php echo esc_html( $user['discount'] ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( edd_use_taxes() ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>:</strong></td>
				<td><?php echo esc_html( edd_payment_tax( $order->id ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $edd_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>:</strong></td>
				<td>
					<?php echo esc_html( edd_payment_subtotal( $order->id ) ); ?>
				</td>
			</tr>

			<tr>
				<td><strong><?php esc_html_e( 'Total Price', 'easy-digital-downloads' ); ?>:</strong></td>
				<td><?php echo esc_html( edd_payment_amount( $order->id ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php do_action( 'edd_payment_receipt_after', $payment, $edd_receipt_args ); ?>
	</tbody>
</table>

<?php do_action( 'edd_payment_receipt_after_table', $payment, $edd_receipt_args ); ?>

<?php if ( filter_var( $edd_receipt_args['products'], FILTER_VALIDATE_BOOLEAN ) ) : ?>

	<h3><?php echo esc_html( apply_filters( 'edd_payment_receipt_products_title', __( 'Products', 'easy-digital-downloads' ) ) ); ?></h3>

	<table id="edd_purchase_receipt_products" class="edd-table">
		<thead>
			<th><?php esc_html_e( 'Name', 'easy-digital-downloads' ); ?></th>
			<?php if ( edd_use_skus() ) { ?>
				<th><?php esc_html_e( 'SKU', 'easy-digital-downloads' ); ?></th>
			<?php } ?>
			<?php if ( edd_item_quantities_enabled() ) : ?>
				<th><?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?></th>
			<?php endif; ?>
			<th><?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?></th>
		</thead>

		<tbody>
		<?php if ( $cart ) : ?>
			<?php foreach ( $cart as $key => $item ) : ?>
				<?php
				// Skip this item if we can't view it.
				if ( ! apply_filters( 'edd_user_can_view_receipt_item', true, $item ) ) {
					continue;
				}
				?>

				<?php if ( empty( $item['in_bundle'] ) ) : ?>
				<tr>
					<td>
						<?php
						$price_id       = edd_get_cart_item_price_id( $item );
						$download_files = edd_get_download_files( $item['id'], $price_id );
						?>

						<div class="edd_purchase_receipt_product_name">
							<?php echo esc_html( $item['name'] ); ?>
							<?php if ( edd_has_variable_prices( $item['id'] ) && ! is_null( $price_id ) ) : ?>
							<span class="edd_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo esc_html( edd_get_price_option_name( $item['id'], $price_id, $order->id ) ); ?></span>
							<?php endif; ?>
						</div>

						<?php
						$notes = edd_get_product_notes( $item['id'] );
						if ( $edd_receipt_args['notes'] && ! empty( $notes ) ) :
							?>
							<div class="edd_purchase_receipt_product_notes"><?php echo wp_kses_post( wpautop( $notes ) ); ?></div>
						<?php endif; ?>

						<?php if ( edd_is_payment_complete( $order->id ) && edd_receipt_show_download_files( $item['id'], $edd_receipt_args, $item ) ) : ?>
						<ul class="edd_purchase_receipt_files">
							<?php
							if ( ! empty( $download_files ) && is_array( $download_files ) ) :
								foreach ( $download_files as $filekey => $file ) :
									?>
									<li class="edd_download_file">
										<a href="<?php echo esc_url( edd_get_download_file_url( $order->payment_key, $order->email, $filekey, $item['id'], $price_id ) ); ?>" class="edd_download_file_link"><?php echo esc_html( edd_get_file_name( $file ) ); ?></a>
									</li>
									<?php
									do_action( 'edd_receipt_files', $filekey, $file, $item['id'], $order->id, $meta );
								endforeach;
							elseif ( edd_is_bundled_product( $item['id'] ) ) :
								$price_id         = edd_get_cart_item_price_id( $item );
								$bundled_products = edd_get_bundled_products( $item['id'], $price_id );

								foreach ( $bundled_products as $bundle_item ) :
									?>

									<li class="edd_bundled_product">
										<span class="edd_bundled_product_name"><?php echo esc_html( edd_get_bundle_item_title( $bundle_item ) ); ?></span>
										<ul class="edd_bundled_product_files">
											<?php
											$download_files = edd_get_download_files( edd_get_bundle_item_id( $bundle_item ), edd_get_bundle_item_price_id( $bundle_item ) );

											if ( $download_files && is_array( $download_files ) ) :
												foreach ( $download_files as $filekey => $file ) :
													?>
													<li class="edd_download_file">
														<a href="<?php echo esc_url( edd_get_download_file_url( $order->payment_key, $order->email, $filekey, $bundle_item, $price_id ) ); ?>" class="edd_download_file_link"><?php echo esc_html( edd_get_file_name( $file ) ); ?></a>
													</li>
													<?php
													do_action( 'edd_receipt_bundle_files', $filekey, $file, $item['id'], $bundle_item, $order->id, $meta );
												endforeach;
											else :
												echo '<li>' . __( 'No downloadable files found for this bundled item.', 'easy-digital-downloads' ) . '</li>';
											endif;
											?>
										</ul>
									</li>
									<?php
								endforeach;
							else :
								echo '<li>' . apply_filters( 'edd_receipt_no_files_found_text', __( 'No downloadable files found.', 'easy-digital-downloads' ), $item['id'] ) . '</li>';
							endif;
							?>
						</ul>
						<?php endif; ?>

						<?php
						// Allow extensions to extend the product cell
						do_action( 'edd_purchase_receipt_after_files', $item['id'], $order->id, $meta, $price_id );
						?>
					</td>
					<?php if ( edd_use_skus() ) : ?>
						<td><?php echo esc_html( edd_get_download_sku( $item['id'] ) ); ?></td>
					<?php endif; ?>
					<?php if ( edd_item_quantities_enabled() ) { ?>
						<td><?php echo esc_html( $item['quantity'] ); ?></td>
					<?php } ?>
					<td>
						<?php if ( empty( $item['in_bundle'] ) ) : // Only show price when product is not part of a bundle ?>
							<?php echo esc_html( edd_currency_filter( edd_format_amount( $item['price'] ) ) ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( ( $fees = edd_get_payment_fees( $order->id, 'item' ) ) ) : ?>
			<?php foreach ( $fees as $fee ) : ?>
				<tr>
					<td class="edd_fee_label"><?php echo esc_html( $fee['label'] ); ?></td>
					<?php if ( edd_item_quantities_enabled() ) : ?>
						<td></td>
					<?php endif; ?>
					<td class="edd_fee_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>

	</table>
<?php endif; ?>
