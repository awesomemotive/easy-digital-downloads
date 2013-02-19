<?php
$purchases = edd_get_users_purchases( get_current_user_id(), 20, true );
if ( $purchases ) :
	do_action( 'edd_before_download_history' ); ?>
	<table id="edd_user_history">
		<thead>
			<tr class="edd_download_history_row">
				<?php do_action( 'edd_download_history_header_start' ); ?>
				<th class="edd_download_download_name"><?php _e( 'Download Name', 'edd' ); ?></th>
				<?php if ( ! edd_no_redownload() ) { ?>
					<th class="edd_download_download_files"><?php _e( 'Files', 'edd' ); ?></th>
				<?php } ?>
				<?php do_action( 'edd_download_history_header_end' ); ?>
			</tr>
		</thead>
		<?php
		foreach ( $purchases as $post ) : setup_postdata( $post );
			$downloads 		= edd_get_payment_meta_downloads( $post->ID );
			$purchase_data 	= edd_get_payment_meta( $post->ID );

			if ( $downloads ) {
				foreach ( $downloads as $download ) {
					echo '<tr class="edd_download_history_row">';
						$id 			= isset( $purchase_data['cart_details'] )   ? $download['id']                  : $download;
						$price_id 		= isset( $download['options']['price_id'] ) ? $download['options']['price_id'] : null;
						$download_files = edd_get_download_files( $id, $price_id );
						$name           = get_the_title( $id );

						if ( isset( $download['options']['price_id'] ) ) {
							$name .= ' - ' . edd_get_price_option_name( $id, $download['options']['price_id'], $post->ID );
						}

						do_action( 'edd_download_history_row_start', $post->ID, $id );

						echo '<td class="edd_download_download_name">' . esc_html( $name ) . '</td>';

						if ( ! edd_no_redownload() ) {
							echo '<td class="edd_download_download_files">';

							if ( $download_files ) {
								foreach ( $download_files as $filekey => $file ) {
									$download_url = edd_get_download_file_url( $purchase_data['key'], $purchase_data['email'], $filekey, $id, $price_id );

									echo '<div class="edd_download_file"><a href="' . esc_url( $download_url ) . '" class="edd_download_file_link">' . esc_html( $file['name'] ) . '</a></div>';

									do_action( 'edd_download_history_files', $filekey, $file, $id, $post->ID, $purchase_data );
								}
							} else {
								_e( 'No downloadable files found.', 'edd' );
							}

							echo '</td>';
						} // End if ! edd_no_redownload()

						do_action( 'edd_download_history_row_end', $post->ID, $id );
					echo '</tr>';

				} // End foreach $downloads
				wp_reset_postdata();
			} // End if $downloads
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
	<?php
	do_action( 'edd_after_download_history' );
else : ?>
	<p class="edd-no-downloads"><?php _e( 'You have not purchased any downloads', 'edd' ); ?></p>
<?php endif; ?>