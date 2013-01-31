<?php
/**
 * This template is used to display the purchase summary with [edd_receipt]
 */

global $edd_receipt_args;

$payment = get_post( $edd_receipt_args['id'] );
$meta    = edd_get_payment_meta( $payment->ID );
$cart    = edd_get_payment_meta_cart_details( $payment->ID );
$user    = edd_get_payment_meta_user_info( $payment->ID );
?>

<table id="edd_purchase_receipt">
	<thead>
	<?php do_action( 'edd_payment_receipt_before', $payment, $edd_receipt_args ); ?>

	<?php if ( $edd_receipt_args['payment_id'] ) : ?>
	<tr>
		<th><strong><?php _e( 'Payment', 'edd' ); ?>:</strong></th>
		<th>#<?php echo $payment->ID; ?></th>
	</tr>
		<?php endif; ?>
	</thead>
	<tbody>
	<?php if ( $edd_receipt_args['date'] ) : ?>
	<tr>
		<td><strong><?php _e( 'Date', 'edd' ); ?>:</strong></td>
		<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></td>
	</tr>
		<?php endif; ?>

	<?php if ( $edd_receipt_args['price'] ) : ?>
		<?php if ( edd_use_taxes() ) : ?>
		<tr>
			<td><strong><?php _e( 'Subtotal', 'edd' ); ?></strong></td>
			<td><?php echo edd_payment_subtotal( $payment->ID ); ?></td>
		</tr>
		<tr>
			<td><strong><?php _e( 'Tax', 'edd' ); ?></strong></td>
			<td><?php echo edd_payment_tax( $payment->ID ); ?></td>
		</tr>
			<?php endif; ?>
	<tr>
		<td><strong><?php _e( 'Total Price', 'edd' ); ?>:</strong></td>
		<td><?php echo edd_payment_amount( $payment->ID ); ?></td>
	</tr>
		<?php endif; ?>

	<?php if ( $edd_receipt_args['discount'] && $user['discount'] != 'none' ) : ?>
	<tr>
		<td><strong><?php _e( 'Discount', 'edd' ); ?>:</strong></td>
		<td><?php echo $user['discount']; ?></td>
	</tr>
		<?php endif; ?>

	<?php if ( $edd_receipt_args['payment_method'] ) : ?>
	<tr>
		<td><strong><?php _e( 'Payment Method', 'edd' ); ?>:</strong></td>
		<td><?php echo edd_get_gateway_checkout_label( edd_get_payment_gateway( $payment->ID ) ); ?></td>
	</tr>
		<?php endif; ?>

	<?php if ( $edd_receipt_args['payment_key'] ) : ?>
	<tr>
		<td><strong><?php _e( 'Payment Key', 'edd' ); ?>:</strong></td>
		<td><?php echo get_post_meta( $payment->ID, '_edd_payment_purchase_key', true ); ?></td>
	</tr>
		<?php endif; ?>

	<?php do_action( 'edd_payment_receipt_after', $payment, $edd_receipt_args ); ?>
	</tbody>
</table>

<?php if ( $edd_receipt_args['products'] ) : ?>
<h3><?php echo apply_filters( 'edd_payment_receipt_products_title', __( 'Products', 'edd' ) ); ?></h3>

<table id="edd_purchase_receipt_products">
	<thead>
	<tr>
		<th><?php _e( 'Name', 'edd' ); ?></th>
		<th><?php _e( 'Price', 'edd' ); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td><strong><?php _e( 'Total Price', 'edd' ); ?>:</strong></td>
		<td><?php echo edd_payment_amount( $payment->ID ); ?></td>
	</tr>
	</tfoot>
	<tbody>
		<?php foreach ( $cart as $key => $item ) : ?>
	<tr>
		<td>
			<div class="edd_purchase_receipt_product_name"><?php echo esc_html( $item['name'] ); ?></div>
			<?php if ( $edd_receipt_args['notes'] ) : ?>
			<div class="edd_purchase_receipt_product_notes"><?php echo edd_get_product_notes( $item['id'] ); ?></div>
			<?php endif; ?>
			<ul style="margin: 0">
				<?php
				$price_id       = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
				$download_files = edd_get_download_files( $item['id'], $price_id );

				if ( $download_files ) :

					foreach ( $download_files as $filekey => $file ) :

						$download_url = edd_get_download_file_url( $meta['key'], $meta['email'], $filekey, $item['id'], $price_id );
						?>
						<li class="edd_download_file">
							<a href="<?php echo esc_url( $download_url ); ?>" class="edd_download_file_link"><?php echo esc_html( $file['name'] ); ?></a>
						</li>
						<?php
						do_action( 'edd_receipt_files', $filekey, $file, $item['id'], $payment->ID, $meta );

					endforeach;

				else :
					echo '<li>' . __( 'No downloadable files found.', 'edd' ) . '</li>';
				endif;
				?>
			</ul>
		</td>
		<td><?php echo edd_currency_filter( edd_format_amount( $item['price'] ) ); ?></td>
	</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>