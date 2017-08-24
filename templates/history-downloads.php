<?php if( ! empty( $_GET['edd-verify-success'] ) ) : ?>
<p class="edd-account-verified edd_success">
	<?php _e( 'Your account has been successfully verified!', 'easy-digital-downloads' ); ?>
</p>
<?php
endif;
/**
 * This template is used to display the download history of the current user.
 */
$purchases = edd_get_users_purchases( get_current_user_id(), 20, true, 'any' );
if ( $purchases ) :
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
		<?php foreach ( $purchases as $payment ) :
			$downloads      = edd_get_payment_meta_cart_details( $payment->ID, true );
			$purchase_data  = edd_get_payment_meta( $payment->ID );
			$email          = edd_get_payment_user_email( $payment->ID );

			if ( $downloads ) :
				foreach ( $downloads as $download ) :

					// Skip over Bundles. Products included with a bundle will be displayed individually
					if ( edd_is_bundled_product( $download['id'] ) )
						continue; ?>

					<tr class="edd_download_history_row">
						<?php
						$price_id       = edd_get_cart_item_price_id( $download );
						$download_files = edd_get_download_files( $download['id'], $price_id );
						$name           = $download['name'];

						// Retrieve and append the price option name
						if ( ! empty( $price_id ) && 0 !== $price_id ) {
							$name .= ' - ' . edd_get_price_option_name( $download['id'], $price_id, $payment->ID );
						}

						do_action( 'edd_download_history_row_start', $payment->ID, $download['id'] );
						?>
						<td class="edd_download_download_name"><?php echo esc_html( $name ); ?></td>

						<?php if ( ! edd_no_redownload() ) : ?>
							<td class="edd_download_download_files">
								<?php

								if ( 'publish' == $payment->post_status ) :

									if ( $download_files ) :

										foreach ( $download_files as $filekey => $file ) :

											$download_url = edd_get_download_file_url( $purchase_data['key'], $email, $filekey, $download['id'], $price_id );
											?>

											<div class="edd_download_file">
												<a href="<?php echo esc_url( $download_url ); ?>" class="edd_download_file_link">
													<?php echo edd_get_file_name( $file ); ?>
												</a>
											</div>

											<?php do_action( 'edd_download_history_files', $filekey, $file, $id, $payment->ID, $purchase_data );
										endforeach;

									else :
										_e( 'No downloadable files found.', 'easy-digital-downloads' );
									endif; // End if payment complete

								else : ?>
									<span class="edd_download_payment_status">
										<?php printf( __( 'Payment status is %s', 'easy-digital-downloads' ), edd_get_payment_status( $payment, true) ); ?>
									</span>
									<?php
								endif; // End if $download_files
								?>
							</td>
						<?php endif; // End if ! edd_no_redownload()

						do_action( 'edd_download_history_row_end', $payment->ID, $download['id'] );
						?>
					</tr>
					<?php
				endforeach; // End foreach $downloads
			endif; // End if $downloads
		endforeach;
		?>
	</table>
	<div id="edd_download_history_pagination" class="edd_pagination navigation">
		<?php
		$big = 999999;
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => ceil( edd_count_purchases_of_customer() / 20 ) // 20 items per page
		) );
		?>
	</div>
	<?php do_action( 'edd_after_download_history' ); ?>
<?php else : ?>
	<p class="edd-no-downloads"><?php _e( 'You have not purchased any downloads', 'easy-digital-downloads' ); ?></p>
<?php endif; ?>
