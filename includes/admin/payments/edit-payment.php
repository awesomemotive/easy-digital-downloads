<?php
/**
 * Edit Payment Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Edit Payment
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$payment = get_post( absint( $_GET['purchase_id'] ) );
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
							$cart_items = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : false;
							if ( $downloads ) :
								foreach ( $downloads as $download ) :
									$id = isset( $payment_data['cart_details'] ) ? $download['id'] : $download;

									if ( isset( $download['options']['price_id'] ) ) {
										$variable_prices = '<input type="hidden" name="edd-purchased-downloads[' . $id . '][options][price_id]" value="'. $download['options']['price_id'] .'" />';
										$variable_prices .= '(' . edd_get_price_option_name( $id, $download['options']['price_id'], $_GET['purchase_id'] ) . ')';
									} else {
										$variable_prices = '';
									}

									echo '<div class="purchased_download_' . $id . '">
											<input type="hidden" name="edd-purchased-downloads[' . $id . ']" value="' . $id . '"/>
											<strong>' . get_the_title( $id ) . ' ' . $variable_prices . '</strong> - <a href="#" class="edd-remove-purchased-download" data-action="remove_purchased_download" data-id="' . $id . '">'. __( 'Remove', 'edd' ) .'</a>
										  </div>';
								endforeach;
							endif;
						?>
						<p id="edit-downloads"><a href="#TB_inline?width=640&amp;inlineId=available-downloads" class="thickbox" title="<?php printf( __( 'Add %s to purchase #%s', 'edd' ), strtolower( edd_get_label_singular() ), $_GET['purchase_id'] ); ?> "><?php _e( 'Add download to purchase', 'edd' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Payment Notes', 'edd' ); ?></span>
					</th>
					<td>
						<?php
							$notes = edd_get_payment_notes( $payment->ID );
							if ( ! empty( $notes ) ) :
								echo '<ul id="payment-notes">';
								foreach ( $notes as $note ):
									if ( ! empty( $note->user_id ) ) {
										$user = get_userdata( $note->user_id );
										$user = $user->display_name;
									} else {
										$user = __( 'EDD Bot', 'edd' );
									}
									echo '<p><strong>' . $user . '</strong>&nbsp;<em>' . $note->comment_date . '</em>&nbsp;&mdash;' . $note->comment_content . '</p>';
								endforeach;
								echo '</ul>';
							else :
								echo '<p>' . __( 'No payment notes', 'edd' ) . '</p>';
							endif;
						?>
						<label for="edd-payment-note"><?php _e( 'Add New Note', 'edd' ); ?></label><br/>
						<textarea name="edd-payment-note" id="edd-payment-note" cols="30" rows="5"></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Payment Status', 'edd' ); ?></span>
					</th>
					<td>
						<select name="edd-payment-status" id="edd_payment_status">
							<?php
							$status = $payment->post_status; // Current status
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
				<select name="downloads[0][id]" class="edd-downloads-list">
				<?php
				$downloads = get_posts( apply_filters( 'edd_add_downloads_to_purchase_query', array( 'post_type' => 'download', 'posts_per_page' => -1 ) ) );
				echo '<option value="0">' . sprintf( __('Select a %s', 'edd'), esc_html( edd_get_label_singular() ) ) . '</option>';
				foreach( $downloads as $download ) {
					?>
					<option value="<?php echo $download->ID; ?>"><?php echo get_the_title( $download->ID ) ?></option>
					<?php
				}
				?>
				</select>
				&nbsp;<img src="<?php echo admin_url('/images/wpspin_light.gif'); ?>" class="hidden edd_add_download_to_purchase_waiting waiting" />
			</p>
			<p>
				<a href="#" class="button-secondary edd-add-another-download"><?php echo sprintf( __( 'Add Another %s', 'edd' ), esc_html( edd_get_label_singular() ) ); ?></a>
			</p>
			<p>
				<a id="edd-add-download" class="button-primary" title="<?php _e( 'Add Selected Downloads', 'edd' ); ?>"><?php _e( 'Add Selected Downloads', 'edd' ); ?></a>
				<a id="edd-close-add-download" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Close', 'edd' ); ?>"><?php _e( 'Close', 'edd' ); ?></a>
			</p>
			<?php wp_nonce_field( 'edd_add_downloads_to_purchase_nonce', 'edd_add_downloads_to_purchase_nonce' ); ?>
		</form>
	</div>
</div>