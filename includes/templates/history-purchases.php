<?php

$purchases = edd_get_users_purchases();

if($purchases) : ?>
	<table id="edd_user_history">
		<thead>
			<tr>
				<?php do_action('edd_purchase_history_header_before'); ?>
				<th class="edd_purchase_id_header"><?php _e('Purchase ID', 'edd'); ?></th>
				<th class="edd_purchase_date_header"><?php _e('Date', 'edd'); ?></th>
				<th class="edd_purchase_amount_header"><?php _e('Amount', 'edd'); ?></th>
				<th class="edd_purchased_files_header"><?php _e('Files', 'edd'); ?></th>
				<?php do_action('edd_purchase_history_header_after'); ?>
			</tr>
		</thead>
		<?php foreach($purchases as $purchase) { ?>
			<?php $purchase_data = edd_get_payment_meta( $purchase->ID ); ?>
			<?php do_action('edd_purchase_history_body_start', $purchase, $purchase_data); ?>
			<tr class="edd_purchase_row">
				<td>#<?php echo $purchase->ID; ?></td>
				<td><?php echo date(get_option('date_format'), strtotime($purchase->post_date)); ?></td>
				<td><?php echo edd_currency_filter($purchase_data['amount']); ?></td>
				<td>
					<?php
						// show a list of downloadable files
						$downloads = edd_get_downloads_of_purchase($purchase->ID);
						if($downloads) {
							foreach($downloads as $download) {
								$id = isset($purchase_data['cart_details']) ? $download['id'] : $download;
								$price_id = isset($download['options']['price_id']) ? $download['options']['price_id'] : null;
								$download_files = edd_get_download_files( $id, $price_id );
								echo '<div class="edd_purchased_download_name">' . get_the_title($id) . '</div>';
								if( ! edd_no_redownload() ) {
									if($download_files) {
										foreach($download_files as $filekey => $file) {
											$download_url = edd_get_download_file_url($purchase_data['key'], $purchase_data['email'], $filekey, $id);
											echo '<div class="edd_download_file"><a href="' . $download_url . '" class="edd_download_file_link">' . $file['name'] . '</a></div>';
										} 
									} else {
										_e('No downloadable files found.', 'edd');
									}
								}
							}
						}
					?>
				</td>
			</tr>
			<?php do_action('edd_purchase_history_body_end', $purchase, $purchase_data); ?>
		<?php } ?>
	</table>
<?php else : ?>
	<p class="edd-no-purchases"><?php _e('You have not made any purchases', 'edd'); ?></p>
<?php endif;