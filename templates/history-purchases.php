<?php
// Retrieve all purchases for the current user
$purchases = edd_get_users_purchases( get_current_user_id(), 20, true );
if ( $purchases ) : ?>
	<table id="edd_user_history">
		<thead>
			<tr class="edd_purchase_row">
				<?php do_action('edd_purchase_history_header_before'); ?>
				<th class="edd_purchase_id"><?php _e('ID', 'edd'); ?></th>
				<th class="edd_purchase_date"><?php _e('Date', 'edd'); ?></th>
				<th class="edd_purchase_amount"><?php _e('Amount', 'edd'); ?></th>
				<th class="edd_purchased_files"><?php _e('Files', 'edd'); ?></th>
				<?php do_action('edd_purchase_history_header_after'); ?>
			</tr>
		</thead>
		<?php foreach ( $purchases as $post ) : setup_postdata( $post ); ?>
			<?php $purchase_data = edd_get_payment_meta( $post->ID ); ?>
			<tr class="edd_purchase_row">
				<?php do_action( 'edd_purchase_history_row_start', $post->ID, $purchase_data ); ?>
				<td class="edd_purchase_id">#<?php echo absint( $post->ID ); ?></td>
				<td class="edd_purchase_date"><?php echo date_i18n( get_option('date_format'), strtotime( get_post_field( 'post_date', $post->ID ) ) ); ?></td>
				<td class="edd_purchase_amount"><?php echo edd_currency_filter( edd_format_amount( edd_get_payment_amount( $post->ID ) ) ); ?></td>
				<td class="edd_purchased_files">
					<?php
						// Show a list of downloadable files
						$downloads = edd_get_payment_meta_downloads( $post->ID );
						if ( $downloads ) {
							foreach ( $downloads as $download ) {
								$id 			= isset($purchase_data['cart_details']) ? $download['id'] : $download;
								$price_id 		= isset($download['options']['price_id']) ? $download['options']['price_id'] : null;
								$download_files = edd_get_download_files( $id, $price_id );
								$name           = get_the_title( $id );

								if ( isset( $download['options']['price_id'] ) ) {
									$name .= ' - ' . edd_get_price_option_name( $id, $download['options']['price_id'], $post->ID );
								}

								echo '<div class="edd_purchased_download_name">' . esc_html( $name ) . '</div>';

								if ( ! edd_no_redownload() ) {
									if ( $download_files ) {
										foreach ( $download_files as $filekey => $file ) {
											$download_url = edd_get_download_file_url( $purchase_data['key'], $purchase_data['email'], $filekey, $id, $price_id );
											echo '<div class="edd_download_file"><a href="' . esc_url( $download_url ) . '" class="edd_download_file_link">' . esc_html( $file['name'] ) . '</a></div>';
											do_action( 'edd_purchase_history_files', $filekey, $file, $id, $post->ID, $purchase_data );
										}
									} else {
										_e( 'No downloadable files found.', 'edd' );
									}
								} // End if ! edd_no_redownload()
							} // End foreach $downloads
						} // End if $downloads
					?>
				</td>
				<?php do_action( 'edd_purchase_history_row_end', $post->ID, $purchase_data ); ?>
			</tr>
		<?php endforeach; ?>
	</table>
	<div id="edd_purchase_history_pagination" class="edd_pagination navigation">
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
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<p class="edd-no-purchases"><?php _e('You have not made any purchases', 'edd'); ?></p>
<?php endif;