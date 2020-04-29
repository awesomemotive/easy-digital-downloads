<?php
/**
 * Shortcode: Download History - [download_history]
 *
 * @package EDD
 * @category Template
 *
 * @since 3.0 Uses new `edd_get_orders()` function and associated helpers.
 *            Checks status on individual order items when determining download link visibility.
 */

if( ! empty( $_GET['edd-verify-success'] ) ) : ?>
<p class="edd-account-verified edd_success">
	<?php _e( 'Your account has been successfully verified!', 'easy-digital-downloads' ); ?>
</p>
<?php
endif;
/**
 * This template is used to display the download history of the current user.
 */
$customer = edd_get_customer_by( 'user_id', get_current_user_id() );

if ( ! empty( $customer ) ) {
	$orders = edd_get_orders( array(
		'customer_id' => $customer->id,
		'number'      => 20,
		'type'        => 'sale',
	) );
} else {
	$orders = array();
}

if ( $orders ) :
	do_action( 'edd_before_download_history' ); ?>
	<table id="edd_user_history" class="edd-table">
		<thead>
			<tr class="edd_download_history_row">
				<?php do_action( 'edd_download_history_header_start' ); ?>
				<th class="edd_download_download_name"><?php _e( 'Download Name', 'easy-digital-downloads' ); ?></th>
				<?php if ( ! edd_no_redownload() ) : ?>
					<th class="edd_download_download_files"><?php _e( 'Files', 'easy-digital-downloads' ); ?></th>
				<?php endif; //End if no redownload?>
				<?php do_action( 'edd_download_history_header_end' ); ?>
			</tr>
		</thead>
		<?php foreach ( $orders as $order ) :
			$downloads      = edd_get_payment_meta_cart_details( $order->id, true );
			$purchase_data  = edd_get_payment_meta( $order->id );
			$email          = edd_get_payment_user_email( $order->id );

			foreach ( $order->get_items() as $key => $item ) :

				// Skip over Bundles. Products included with a bundle will be displayed individually
				if ( edd_is_bundled_product( $item->product_id ) ) {
					continue;
				}
				?>

				<tr class="edd_download_history_row">
					<?php
					$price_id       = $item->price_id;
					$download_files = edd_get_download_files( $item->product_id, $price_id );
					$name           = $item->product_name;

					// Retrieve and append the price option name
					if ( ! empty( $price_id ) && 0 !== $price_id ) {
						$name .= ' - ' . edd_get_price_option_name( $item->product_id, $price_id, $order->id );
					}

					do_action( 'edd_download_history_row_start', $order->id, $item->product_id );
					?>
					<td class="edd_download_download_name"><?php echo esc_html( $name ); ?></td>

					<?php if ( ! edd_no_redownload() ) : ?>
						<td class="edd_download_download_files">
							<?php

							if ( 'complete' == $item->status ) :

								if ( $download_files ) :

									foreach ( $download_files as $filekey => $file ) :

										$download_url = edd_get_download_file_url( $purchase_data['key'], $email, $filekey, $item->product_id, $price_id );
										?>

										<div class="edd_download_file">
											<a href="<?php echo esc_url( $download_url ); ?>" class="edd_download_file_link">
												<?php echo edd_get_file_name( $file ); ?>
											</a>
										</div>

										<?php do_action( 'edd_download_history_files', $filekey, $file, $item->product_id, $order->id, $purchase_data );
									endforeach;

								else :
									_e( 'No downloadable files found.', 'easy-digital-downloads' );
								endif; // End if payment complete

							else : ?>
								<span class="edd_download_payment_status">
									<?php printf( __( 'Payment status is %s', 'easy-digital-downloads' ), edd_get_status_label( $item->status ) ); ?>
								</span>
								<?php
							endif; // End if $download_files
							?>
						</td>
					<?php endif; // End if ! edd_no_redownload()

					do_action( 'edd_download_history_row_end', $order->id, $item->product_id );
					?>
				</tr>
				<?php
			endforeach; // End foreach get_items()
		endforeach;
		?>
	</table>
	<?php
		echo edd_pagination(
			array(
				'type'  => 'download_history',
				'total' => ceil( edd_count_purchases_of_customer() / 20 ) // 20 items per page
			)
		);
	?>
	<?php do_action( 'edd_after_download_history' ); ?>
<?php else : ?>
	<p class="edd-no-downloads"><?php _e( 'You have not purchased any downloads', 'easy-digital-downloads' ); ?></p>
<?php endif; ?>
