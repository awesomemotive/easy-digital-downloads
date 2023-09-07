<?php
if ( ! $item->is_deliverable() || ! edd_receipt_show_download_files( $item->product_id, $edd_receipt_args, $item ) ) {
	return;
}
$download_files = edd_get_download_files( $item->product_id, $item->price_id );
?>
<ul class="edd_purchase_receipt_files">
	<?php
	if ( ! empty( $download_files ) && is_array( $download_files ) ) :
		foreach ( $download_files as $filekey => $file ) :
			?>
			<li class="edd_download_file">
				<a href="<?php echo esc_url( edd_get_download_file_url( $order, $order->email, $filekey, $item->product_id, $item->price_id ) ); ?>" class="edd_download_file_link"><?php echo esc_html( edd_get_file_name( $file ) ); ?></a>
			</li>
			<?php
			/**
			 * Fires at the end of the order receipt files list.
			 *
			 * @since 3.0
			 * @param int   $filekey          Index of array of files returned by edd_get_download_files() that this download link is for.
			 * @param array $file             The array of file information.
			 * @param int   $item->product_id The product ID.
			 * @param int   $order->id        The order ID.
			 */
			do_action( 'edd_order_receipt_files', $filekey, $file, $item->product_id, $order->id );
		endforeach;
	elseif ( edd_is_bundled_product( $item->product_id ) ) :
		$bundled_products = edd_get_bundled_products( $item->product_id, $item->price_id );

		foreach ( $bundled_products as $bundle_item ) :
			?>

			<li class="edd_bundled_product">
				<span class="edd_bundled_product_name"><?php echo esc_html( edd_get_bundle_item_title( $bundle_item ) ); ?></span>
				<ul class="edd_bundled_product_files">
					<?php
					$bundle_item_id       = edd_get_bundle_item_id( $bundle_item );
					$bundle_item_price_id = edd_get_bundle_item_price_id( $bundle_item );
					$download_files       = edd_get_download_files( $bundle_item_id, $bundle_item_price_id );

					if ( $download_files && is_array( $download_files ) ) :
						foreach ( $download_files as $filekey => $file ) :
							?>
							<li class="edd_download_file">
								<a href="<?php echo esc_url( edd_get_download_file_url( $order, $order->email, $filekey, $bundle_item, $bundle_item_price_id ) ); ?>" class="edd_download_file_link"><?php echo esc_html( edd_get_file_name( $file ) ); ?></a>
							</li>
							<?php
							/**
							 * Fires at the end of the order receipt bundled files list.
							 *
							 * @since 3.0
							 * @param int   $filekey          Index of array of files returned by edd_get_download_files() that this download link is for.
							 * @param array $file             The array of file information.
							 * @param int   $item->product_id The product ID.
							 * @param array $bundle_item      The array of information about the bundled item.
							 * @param int   $order->id        The order ID.
							 */
							do_action( 'edd_order_receipt_bundle_files', $filekey, $file, $item->product_id, $bundle_item, $order->id );
						endforeach;
					else :
						echo '<li>' . esc_html__( 'No downloadable files found for this bundled item.', 'easy-digital-downloads' ) . '</li>';
					endif;
					?>
				</ul>
			</li>
			<?php
		endforeach;

	else :
		echo '<li>' . esc_html( apply_filters( 'edd_receipt_no_files_found_text', __( 'No downloadable files found.', 'easy-digital-downloads' ), $item->product_id ) ) . '</li>';
	endif;
	?>
</ul>
