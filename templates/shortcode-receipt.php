<?php
/**
 *
 */

global $edd_receipt_args;

$payment   = get_post( $edd_receipt_args[ 'id' ] );
$meta      = edd_get_payment_meta( $payment->ID );
$cart      = edd_get_payment_meta_cart_details( $payment->ID );
$user      = edd_get_payment_meta_user_info( $payment->ID );
$downloads = edd_get_payment_meta_downloads( $payment->ID );
?>

<table id="edd_purchase_receipt">
	<tbody>
		<?php do_action( 'edd_payment_receipt_before', $payment, $edd_receipt_args ); ?>

		<?php if ( $edd_receipt_args[ 'payment_id' ] ) : ?>
		<tr>
			<td><strong><?php _e( 'Payment', 'edd' ); ?>:</strong></td>
			<td>#<?php echo $payment->ID; ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $edd_receipt_args[ 'date' ] ) : ?>
		<tr>
			<td><strong><?php _e( 'Date', 'edd' ); ?>:</strong></td>
			<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta[ 'date' ] ) ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $edd_receipt_args[ 'price' ] ) : ?>
		<tr>
			<td><strong><?php _e( 'Total Price', 'edd' ); ?>:</strong></td>
			<td><?php echo edd_price( edd_get_payment_amount( $payment->ID ) ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $edd_receipt_args[ 'discount' ] ) : ?>
		<tr>
			<td><strong><?php _e( 'Discount', 'edd' ); ?>:</strong></td>
			<td><?php echo $user[ 'discount' ]; ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $edd_receipt_args[ 'payment_method' ] ) : ?>
		<tr>
			<td><strong><?php _e( 'Payment Method', 'edd' ); ?>:</strong></td>
			<td><?php echo edd_get_payment_gateway( $payment->ID ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $edd_receipt_args[ 'payment_key' ] ) : ?>
		<tr>
			<td><strong><?php _e( 'Payment Key', 'edd' ); ?>:</strong></td>
			<td><?php echo get_post_meta( $payment->ID, '_edd_payment_purchase_key', true ); ?></td>
		</tr>
		<?php endif; ?>

		<?php do_action( 'edd_payment_receipt_after', $payment, $edd_receipt_args ); ?>
	</tbody>
</table>

<?php if ( $edd_receipt_args[ 'products' ] ) : ?>
	<h3><?php echo apply_filters( 'edd_payment_receipt_products_title', __( 'Products', 'edd' ) ); ?></h3>

	<table>
		<thead>
			<th><?php _e( 'Name', 'edd' ); ?></th>
			<th><?php _e( 'Price', 'edd' ); ?></th>
		</thead>
		<tfoot>
			<tr>
				<td><strong><?php _e( 'Total Price', 'edd' ); ?>:</strong></td>
				<td><?php echo edd_price( edd_get_payment_amount( $payment->ID ) ); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $cart as $key => $item ) : ?>
			<tr>
				<td>
					<em><?php echo $item[ 'name' ]; ?></em><br />

					<?php if ( $downloads ) : ?>
						<ul style="margin: 0">
						<?php
							foreach ( $downloads as $download ) :
								$id 			= isset($cart) ? $download['id'] : $download;
								$price_id 		= isset($download['options']['price_id']) ? $download['options']['price_id'] : null;
								$download_files = edd_get_download_files( $id, $price_id );

								if ( edd_no_redownload() )
									continue;

								if ( $download_files ) :
									foreach( $download_files as $filekey => $file ) :
										$download_url = edd_get_download_file_url($meta['key'], $meta['email'], $filekey, $id );
							?>
										<li class="edd_download_file"><a href="<?php echo esc_url( $download_url ); ?>" class="edd_download_file_link"><?php echo esc_html( $file['name'] ); ?></a></li>
							<?php
										do_action( 'edd_purchase_history_files', $filekey, $file, $id, $post->ID, $meta );
									endforeach;
								else :
									echo '<li>' . __( 'No downloadable files found.', 'edd') . '</li>';
								endif;
							?>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</td>
				<td><?php echo edd_price( $item[ 'price' ] ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>