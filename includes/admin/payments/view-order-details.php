<?php
/**
 * View Order Details
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function pw_add_handling_fee() {
	// Add a $10 handling fee
	EDD()->fees->add_fee( '10', 'Handling Fee', 'handling_fee' );
}
add_action( 'init', 'pw_add_handling_fee' );

/**
 * View Order Details Page
 *
 * @access public
 * @since 1.6
 * @return void
*/
function edd_view_order_details_screen() {
	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		wp_die( __( 'Payment ID not supplied. Please try again', 'edd' ), __( 'Error', 'edd' ) );
	}

	// Setup the variables
	$item = get_post( $_GET['id'] );
	$payment_meta = edd_get_payment_meta( $_GET['id'] );
	$cart_items   = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : false;
	if ( empty( $cart_items ) || ! $cart_items ) {
		$cart_items = maybe_unserialize( $payment_meta['downloads'] );
	}
	$user_info = edd_get_payment_meta_user_info( $_GET['id'] );
	$payment_date = strtotime( $item->post_date );
	?>
	<div class="wrap">
		<h2><?php _e( 'View Order Details', 'edd' ); ?> <a class="add-new-h2" href="<?php echo admin_url( 'edit.php?post_type=download&amp;page=edd-payment-history&amp;edd-action=edit-payment&amp;purchase_id=' . $_GET['id'] ); ?>"><?php _e( 'Edit Order', 'edd' ); ?></a></h2>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<div id="edd_order_totals" class="postbox">
							<h3 class="hndle"><span>Order Totals</span></h3>
							<div class="inside">
								<div class="edd-order-totals-box edd-admin-box">
									<div class="edd-order-discounts edd-admin-box-inside">
										<p><span class="label"><?php _e( 'Discount Code', 'edd' ); ?></span> <span class="right"><?php if ( isset( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) { echo '<code>' . $user_info['discount'] . '</code>'; } else { _e( 'None', 'edd' ); } ?></span></p>
									</div>
									<?php
									$fees = edd_get_payment_fees( $_GET['id'] );
									if( ! empty( $fees ) ) : ?>
									<div class="edd-order-fees edd-admin-box-inside">
										<p class="strong"><?php _e( 'Fees', 'edd' ); ?></p>
										<ul class="edd-payment-fees">
											<?php foreach( $fees as $fee ) : ?>
											<li><span class="fee-label"><?php echo $fee['label'] . ':</span> ' . '<span class="right">' . edd_currency_filter( $fee['amount'] ); ?></span></li>
											<?php endforeach; ?>
										</ul>
									</div>
									<?php endif; ?>
									<div class="edd-order-payment edd-admin-box-inside">
										<p><span class="label"><?php _e( 'Total Price', 'edd' ); ?></span> <span class="right"><?php echo edd_currency_filter( edd_format_amount( edd_get_payment_amount( $_GET['id'] ) ) ); ?></span></p>
									</div>
								</div>
							</div>
						</div>

						<div id="edd_payment_notes" class="postbox">
							<h3 class="hndle"><span>Payment Notes</span></h3>
							<div class="inside">
								<?php
								$notes = edd_get_payment_notes( $_GET['id'] );
								if ( ! empty( $notes ) ) :
									foreach ( $notes as $note ) :
										if ( ! empty( $note->user_id ) ) {
											$user = get_userdata( $note->user_id );
											$user = $user->display_name;
										} else {
											$user = __( 'EDD Bot', 'edd' );
										}
										?>
										<div class="edd-payment-note">
											<p><?php echo $note->comment_content; ?></p>
											<p><strong><?php echo $user; ?></strong> <em><?php echo $note->comment_date; ?></em></p>
										</div>
										<?php
									endforeach;
								else :
									echo '<p>'. __( 'No payment notes', 'edd' ) . '</p>';
								endif;
								?>
							</div>
						</div>
					</div>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<div id="edd-order-data" class="postbox">
							<h3 class="hndle"><?php _e( 'Order Details', 'edd' ); ?></h3>
							<div class="inside">
								<p class="order-id"><?php _e( 'Payment', 'edd' ); ?> <?php echo '#' . $_GET['id']; ?></p>
								<div class="column-container">
									<div class="order-data-column">
										<h4><?php _e( 'General Details' ); ?></h4>
										<p class="data"><span><?php _e( 'Status:', 'edd' ); ?></span> <?php echo edd_get_payment_status( get_post( $_GET['id'] ), true ) ?></p>
										<p class="data"><span><?php _e( 'Date:', 'edd' ); ?></span> <?php echo date_i18n( get_option( 'date_format' ), $payment_date ) ?></p>
										<p class="data"><span><?php _e( 'Time:', 'edd' ); ?></span> <?php echo date_i18n( get_option( 'time_format' ), $payment_date ); ?></p>
									</div>

									<div class="order-data-column">
										<h4><?php _e( 'Buyer\'s Personal Details', 'edd' ); ?></h4>
										<p class="data"><span><?php _e( 'Name:', 'edd' ); ?></span> <?php echo $user_info['first_name'] . ' ' . $user_info['last_name']; ?></p>
										<p class="data"><span><?php _e( 'Email:', 'edd' ); ?></span> <a href="mailto:<?php echo $payment_meta['email']; ?>"><?php echo $payment_meta['email']; ?></a></p>
									</div>

									<div class="order-data-column">
										<h4><?php _e( 'Payment Details', 'edd' ); ?></h4>
										<?php 
										$gateway = edd_get_payment_gateway( $_GET['id'] );
										if ( $gateway ) {
										?>
										<p class="data"><span><?php _e( 'Gateway:', 'edd' ); ?></span> <?php echo edd_get_gateway_admin_label( $gateway ); ?></p>
										<?php } ?>
										<p class="data"><span><?php _e( 'Key:', 'edd' ); ?></span> <?php echo $payment_meta['key']; ?></p>
									</div>
								</div>
							</div>
						</div>

						<div id="edd-purchased-files" class="postbox">
							<h3 class="hndle"><?php _e( 'Purchased Files', 'edd' ); ?></h3>
							<div class="inside">
								<table class="wp-list-table widefat fixed" cellspacing="0">
									<thead>
										<tr>
											<th scope="col" id="name" class="manage-column column-name"><?php _e( 'Download Name', 'edd' ); ?></th>
											<th scope="col" id="price" class="manage-column column-price"><?php _e( 'Price', 'edd' ); ?></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th scope="col" id="name" class="manage-column column-name"><?php _e( 'Download Name', 'edd' ); ?></th>
											<th scope="col" id="price" class="manage-column column-price"><?php _e( 'Price', 'edd' ); ?></th>
										</tr>
									</tfoot>
									<tbody id="the-list">
										<?php
										if ( $cart_items ) :
											$i = 0;
											foreach ( $cart_items as $key => $cart_item ) :
												$id = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;
												$price_override = isset( $payment_meta['cart_details'] ) ? $cart_item['price'] : null;
												$price = edd_get_download_final_price( $id, $user_info, $price_override );
												?>
												<tr class="<?php if ( $i % 2 == 0 ) { echo 'alternate'; } ?>">
													<td class="name column-name">
														<?php
														echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '">' . get_the_title( $id ) . '</a>';

														if ( isset( $cart_items[ $key ]['item_number'] ) ) {
															$price_options = $cart_items[ $key ]['item_number']['options'];

															if ( isset( $price_options['price_id'] ) ) {
																echo ' - ' . edd_get_price_option_name( $id, $price_options['price_id'], $_GET['id'] );
															}
														}
														?>
													</td>
													<td class="price column-price">
														<?php echo edd_currency_filter( edd_format_amount( $price ) ); ?>
													</td>
												</tr>
												<?php
												$i++;
											endforeach;
										endif;
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php	
}