<?php $payment = get_post($_GET['purchase_id']); ?>
<div class="wrap">
	<h2><?php _e('Edit Payment', 'edd'); ?>: <?php echo get_the_title($_GET['purchase_id']) . ' - #' . $_GET['purchase_id']; ?> - <a href="<?php echo admin_url('edit.php?post_type=download&page=edd-payment-history'); ?>" class="button-secondary"><?php _e('Go Back', 'edd'); ?></a></h2>
	<form id="edd-edit-payment" action="" method="post">
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top">
						<span><?php _e('Downloads Purchased', 'edd'); ?></span>
					</th>
					<td id="purchased-downloads">
						<?php
							$payment_data = get_post_meta($_GET['purchase_id'], '_edd_payment_meta', true);
							$downloads = maybe_unserialize($payment_data['downloads']);
							if($downloads) :
								foreach($downloads as $download) :
									$id = isset($payment_data['cart_details']) ? $download['id'] : $download;
									echo '<div class="purchased_download_' . $id . '"><input type="hidden" name="edd-purchased-downloads[]" value="' . $id . '"/><strong>' . get_the_title($id) . '</strong> - <a href="#" class="edd-remove-purchased-download" data-action="remove_purchased_download" data-id="' . $id . '">Remove</a></div>';
								endforeach;
							endif;
						?>
						<p id="edit-downloads"><a href="#TB_inline?width=640&inlineId=available-downloads" class="thickbox" title="<?php printf(__('Add download to purchase #%s', 'edd'), $_GET['purchase_id']); ?> "><?php _e('Add download to purchase', 'edd'); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top">
						<span><?php _e('Payment Status', 'edd'); ?></span>
					</th>
					<td id="purchased-downloads">
						<?php $status = $payment->post_status; ?>
						<select name="edd-payment-status" id="edd_payment_status">
							<option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'edd'); ?></option>
							<option value="publish" <?php selected($status, 'publish'); ?>><?php _e('Complete', 'edd'); ?></option>
						</select>
					</td>
				</tr>
				
			</tbody>
		</table>
		
		<input type="hidden" name="edd-action" value="edit_payment"/>
		<input type="hidden" name="edd-old-status" value="<?php echo $status; ?>"/>
		<input type="hidden" name="payment-id" value="<?php echo $_GET['purchase_id']; ?>"/>
		<?php wp_nonce_field('edd_payment_nonce', 'edd-payment-nonce'); ?>
		<?php echo submit_button(__('Update Payment', 'edd')); ?>
	</form>
	<div id="available-downloads" style="display:none;">
		<form id="edd-add-downloads-to-purchase">
			<p>
				<?php
				$downloads = get_posts(array('post_type' => 'download', 'posts_per_page' => -1));
				foreach($downloads as $download) {
					echo '<input type="checkbox" class="edd-download-to-add" name="edd_downloads_to_add[]" value="' . $download->ID . '"/>&nbsp;' . get_the_title($download->ID) . '<br/>';
				}
				?>
			</p>
			<p>
				<a id="edd-add-download" class="button-primary" title="<?php _e('Add Selected Downloads', 'edd'); ?>"><?php _e('Add Selected Downloads', 'edd'); ?></a>
				<a id="edd-close-add-download" class="button-secondary" onclick="tb_remove();" title="<?php _e('Close', 'edd'); ?>"><?php _e('Close', 'edd'); ?></a>
			</p>
		</form>
	</div>
</div>