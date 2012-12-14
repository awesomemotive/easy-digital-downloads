<?php
/**
 * Edit Payment Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Edit Payment
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

$payment = get_post( $_GET['purchase_id'] );
$payment_data = get_post_meta( $_GET['purchase_id'], '_edd_payment_meta', true );
?>
<div class="wrap">
	<h2><?php _e( 'Edit Payment', 'edd' ); ?>: <?php echo get_the_title( $_GET['purchase_id'] ) . ' - #' . $_GET['purchase_id']; ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd' ); ?></a></h2>
	<form id="edd-edit-payment" action="" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Buyer\'s Email', 'edd' ); ?></span>
					</th>
					<td>
						<input class="regular-text" type="text" name="edd-buyer-email" id="edd-buyer-email" value="<?php echo $payment_data['email']; ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the buyer\'s email here.', 'edd' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Downloads Purchased', 'edd' ); ?></span>
					</th>
					<td id="purchased-downloads">
						<?php
							$downloads = maybe_unserialize( $payment_data['downloads'] );
							if( $downloads ) :
								foreach( $downloads as $download ):
									$id = isset( $payment_data['cart_details'] ) ? $download['id'] : $download;
									echo '<div class="purchased_download_' . $id . '"><input type="hidden" name="edd-purchased-downloads[]" value="' . $id . '"/><strong>' . get_the_title( $id ) . '</strong> - <a href="#" class="edd-remove-purchased-download" data-action="remove_purchased_download" data-id="' . $id . '">Remove</a></div>';
								endforeach;
							endif;
						?>
						<p id="edit-downloads"><a href="#TB_inline?width=640&inlineId=available-downloads" class="thickbox" title="<?php printf( __( 'Add download to purchase #%s', 'edd' ), $_GET['purchase_id'] ); ?> "><?php _e( 'Add download to purchase', 'edd' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Payment Status', 'edd' ); ?></span>
					</th>
					<td>
						<select name="edd-payment-status" id="edd_payment_status">
							<?php
							$status = $payment->post_status; // current status
							$statuses = edd_get_payment_statuses();
							foreach( $statuses as $status_id => $label ) {
								echo '<option value="' . $status_id	. '" ' . selected( $status, $status_id, false ) . '>' . $label . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr id="edd_payment_notification" style="display:none;">
					<th scope="row" valign="top">
						<span><?php _e( 'Send Purchase Receipt', 'edd' ); ?></span>
					</th>
					<td>
						<input type="checkbox" name="edd-payment-send-email" id="edd_send_email" value="yes"/>
						<span class="description"><?php _e( 'Check this box to send the purchase receipt, including all download links.', 'edd' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>

		<input type="hidden" name="edd-action" value="edit_payment"/>
		<input type="hidden" name="edd-old-status" value="<?php echo $status; ?>"/>
		<input type="hidden" name="payment-id" value="<?php echo $_GET['purchase_id']; ?>"/>
		<?php wp_nonce_field( 'edd_payment_nonce', 'edd-payment-nonce' ); ?>
		<?php echo submit_button( __( 'Update Payment', 'edd' ) ); ?>
	</form>
	<div id="available-downloads" style="display:none;">
		<form id="edd-add-downloads-to-purchase">
			<p>
				<?php
				$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );
				foreach( $downloads as $download ) {
					echo '<input type="checkbox" class="edd-download-to-add" name="edd_downloads_to_add[]" value="' . $download->ID . '"/>&nbsp;' . get_the_title( $download->ID ) . '<br/>';
				}
				?>
			</p>
			<p>
				<a id="edd-add-download" class="button-primary" title="<?php _e( 'Add Selected Downloads', 'edd' ); ?>"><?php _e( 'Add Selected Downloads', 'edd' ); ?></a>
				<a id="edd-close-add-download" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Close', 'edd' ); ?>"><?php _e( 'Close', 'edd' ); ?></a>
			</p>
		</form>
	</div>
</div>