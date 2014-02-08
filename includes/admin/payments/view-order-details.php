<?php
/**
 * View Order Details
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * View Order Details Page
 *
 * @since 1.6
 * @return void
*/
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Payment ID not supplied. Please try again', 'edd' ), __( 'Error', 'edd' ) );
}

// Setup the variables
$payment_id   = absint( $_GET['id'] );
$item         = get_post( $payment_id );
$payment_meta = edd_get_payment_meta( $payment_id );
$cart_items   = edd_get_payment_meta_cart_details( $payment_id );
$user_id      = edd_get_payment_user_id( $payment_id );
$payment_date = strtotime( $item->post_date );
$user_info    = edd_get_payment_meta_user_info( $payment_id );
$address      = ! empty( $user_info['address'] ) ? $user_info['address'] : array( 'line1' => '', 'line2' => '', 'city' => '', 'country' => '', 'state' => '', 'zip' => '' );
?>
<div class="wrap">
	<h2><?php printf( __( 'Payment #%d', 'edd' ), $payment_id ); ?></h2>
	<?php do_action( 'edd_view_order_details_before' ); ?>
	<form id="edd-edit-order-form" method="post">
		<?php do_action( 'edd_view_order_details_form_top' ); ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<?php do_action( 'edd_view_order_details_sidebar_before' ); ?>
						
						<div id="edd-order-totals" class="postbox">
							<h3 class="hndle">
								<span><?php _e( 'Payment Totals', 'edd' ); ?></span>
							</h3>
							<div class="inside">
								<div class="edd-order-totals-box edd-admin-box">
									<?php do_action( 'edd_view_order_details_totals_before', $payment_id ); ?>
									<div class="edd-order-discount edd-admin-box-inside">
										<p>
											<span class="label"><?php _e( 'Discount Code', 'edd' ); ?>:</span>&nbsp;
											<span class="right"><?php if ( isset( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) { echo '<code>' . $user_info['discount'] . '</code>'; } else { _e( 'None', 'edd' ); } ?></span>
										</p>
									</div>
									<?php if ( edd_use_taxes() ) : ?>
									<div class="edd-order-taxes edd-admin-box-inside">
										<p>
											<span class="label"><?php _e( 'Tax', 'edd' ); ?>:</span>&nbsp;
											<input name="edd-payment-tax" type="number" step="0.01" class="small-text right " value="<?php echo esc_attr( edd_get_payment_tax( $payment_id ) ); ?>"/>
										</p>
									</div>
									<?php endif; ?>
									<?php
									$fees = edd_get_payment_fees( $payment_id );
									if ( ! empty( $fees ) ) : ?>
									<div class="edd-order-fees edd-admin-box-inside">
										<p class="strong"><?php _e( 'Fees', 'edd' ); ?>:</p>
										<ul class="edd-payment-fees">
											<?php foreach( $fees as $fee ) : ?>
											<li><span class="fee-label"><?php echo $fee['label'] . ':</span> ' . '<span class="right fee-amount" data-fee="' . esc_attr( $fee['amount'] ) . '">' . edd_currency_filter( $fee['amount'] ); ?></span></li>
											<?php endforeach; ?>
										</ul>
									</div>
									<?php endif; ?>
									<div class="edd-order-payment edd-admin-box-inside">
										<p>
											<span class="label"><?php _e( 'Total Price', 'edd' ); ?>:</span>&nbsp;
											<input name="edd-payment-total" type="number" step="0.01" class="small-text right" value="<?php echo esc_attr( edd_get_payment_amount( $payment_id ) ); ?>"/>
										</p>
									</div>
									<div class="edd-order-payment-recalc-totals edd-admin-box-inside" style="display:none">
										<p>
											<span class="label"><?php _e( 'Recalculate Totals', 'edd' ); ?>:</span>&nbsp;
											<a href="" id="edd-order-recalc-total" class="button button-secondary right"><?php _e( 'Recalculate', 'edd' ); ?></a>
										</p>
									</div>
									<?php do_action( 'edd_view_order_details_totals_after', $payment_id ); ?>
								</div><!-- /.edd-order-totals-box -->
							</div><!-- /.inside -->
						</div><!-- /#edd-order-totals -->

						<div id="edd-order-update" class="postbox edd-order-data">
							
							<h3 class="hndle">
								<span><?php _e( 'Update Payment', 'edd' ); ?></span>
							</h3>
							<div class="inside">
								<div class="edd-admin-box">

									<div class="edd-admin-box-inside">

										<?php
										$gateway = edd_get_payment_gateway( $payment_id );
										if ( $gateway ) { ?>
										<p>
											<strong><?php _e( 'Gateway:', 'edd' ); ?></strong>&nbsp;
											<span><?php echo edd_get_gateway_admin_label( $gateway ); ?></span>
										</p>
										<?php } ?>

										<p>
											<strong><?php _e( 'Key:', 'edd' ); ?></strong>&nbsp;
											<span><?php echo edd_get_payment_key( $payment_id ); ?></span>
										</p>

										<p>
											<strong><?php _e( 'IP:', 'edd' ); ?></strong>&nbsp;
											<span><?php esc_attr_e( edd_get_payment_user_ip( $payment_id )); ?></span>
										</p>

									</div>

									<div class="edd-admin-box-inside">
										<p>
											<span class="label"><?php _e( 'Status:', 'edd' ); ?></span>&nbsp;
											<select name="edd-payment-status" class="medium-text">
												<?php foreach( edd_get_payment_statuses() as $key => $status ) : ?>
													<option value="<?php esc_attr_e( $key ); ?>"<?php selected( edd_get_payment_status( $item, true ), $status ); ?>><?php esc_html_e( $status ); ?></option>
												<?php endforeach; ?>
											</select>
										</p>
									</div>

									<div class="edd-admin-box-inside">
										<p>
											<span class="label"><?php _e( 'Date:', 'edd' ); ?></span>&nbsp;
											<input type="text" name="edd-payment-date" value="<?php esc_attr_e( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text edd_datepicker"/>
										</p>
									</div>

									<div class="edd-admin-box-inside">
										<p>
											<span class="label"><?php _e( 'Time:', 'edd' ); ?></span>&nbsp;
											<input type="number" step="1" max="24" name="edd-payment-time-hour" value="<?php esc_attr_e( date_i18n( 'H', $payment_date ) ); ?>" class="small-text edd-payment-time-hour"/>&nbsp;:&nbsp;
											<input type="number" step="1" max="59" name="edd-payment-time-min" value="<?php esc_attr_e( date( 'i', $payment_date ) ); ?>" class="small-text edd-payment-time-min"/>
										</p>
									</div>

									<?php do_action( 'edd_view_order_details_update_inner', $payment_id ); ?>

								</div><!-- /.column-container -->

							</div><!-- /.inside -->

							<div class="edd-order-update-box edd-admin-box">
								<?php do_action( 'edd_view_order_details_update_before', $payment_id ); ?>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Payment', 'edd' ); ?>"/>
										<?php if( edd_is_payment_complete( $payment_id ) ) : ?>
											<a href="<?php echo add_query_arg( array( 'edd-action' => 'email_links', 'purchase_id' => $payment_id ) ); ?>" id="edd-resend-receipt" class="button-secondary right"><?php _e( 'Resend Receipt', 'edd' ); ?></a>
										<?php endif; ?>
									</div>
									<div class="clear"></div>
								</div>
								<?php do_action( 'edd_view_order_details_update_after', $payment_id ); ?>
							</div><!-- /.edd-order-update-box -->

						</div><!-- /#edd-order-data -->

						<?php do_action( 'edd_view_order_details_sidebar_after', $payment_id ); ?>
					</div><!-- /#side-sortables -->
				</div><!-- /#postbox-container-1 -->

				<div id="postbox-container-2" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">

						<?php do_action( 'edd_view_order_details_main_before', $payment_id ); ?>

						<div id="edd-customer-details" class="postbox">
							<h3 class="hndle">
								<span><?php _e( 'Customer Details', 'edd' ); ?></span>
							</h3>
							<div class="inside edd-clearfix">

								<div class="column-container">
									<div class="column">
										<strong><?php _e( 'Name:', 'edd' ); ?></strong>&nbsp;
										<input type="text" name="edd-payment-user-name" value="<?php esc_attr_e( $user_info['first_name'] . ' ' . $user_info['last_name'] ); ?>" class="medium-text"/>
										<p class="description"><?php _e( 'Customer name, as shown on purchase receipts', 'edd' ); ?></p>
									</div>
									<div class="column">
										<strong><?php _e( 'Email:', 'edd' ); ?></strong>&nbsp;
										<input type="email" name="edd-payment-user-email" value="<?php esc_attr_e( edd_get_payment_user_email( $payment_id ) ); ?>" class="medium-text"/>
										<p class="description"><?php _e( 'Customer email address that purchase receipts are sent to', 'edd' ); ?></p>
									</div>
									<div class="column">
										<strong><?php _e( 'User ID:', 'edd' ); ?></strong>&nbsp;
										<input type="number" step="1" min="-1" name="edd-payment-user-id" value="<?php esc_attr_e( $user_id ); ?>" class="small-text"/>&nbsp;&nbsp;&nbsp;
										<p class="description"><?php _e( 'User ID of the customer', 'edd' ); ?></p>
									</div>
								</div>

								<?php do_action( 'edd_payment_personal_details_list', $payment_meta, $user_info ); ?>

								<?php do_action( 'edd_payment_view_details', $payment_id ); ?>

							</div><!-- /.inside -->
						</div><!-- /#edd-customer-details -->

						<?php do_action( 'edd_view_order_details_billing_before', $payment_id ); ?>

						<div id="edd-billing-details" class="postbox">
							<h3 class="hndle">
								<span><?php _e( 'Billing Address', 'edd' ); ?></span>
							</h3>
							<div class="inside edd-clearfix">

								<div id="edd-order-address">

									<div class="order-data-address">
										<div class="data column-container">
											<div class="column">
												<p>
													<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'edd' ); ?></strong><br/>
													<input type="text" name="edd-payment-address[0][line1]" value="<?php esc_attr_e( $address['line1'] ); ?>" class="medium-text" />
												</p>
												<p>
													<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'edd' ); ?></strong><br/>
													<input type="text" name="edd-payment-address[0][line2]" value="<?php esc_attr_e( $address['line2'] ); ?>" class="medium-text" />
												</p>
													
											</div>
											<div class="column">
												<p>
													<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'edd' ); ?></strong><br/>
													<input type="text" name="edd-payment-address[0][city]" value="<?php esc_attr_e( $address['city'] ); ?>" class="medium-text"/>
													
												</p>
												<p>
													<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'edd' ); ?></strong><br/>
													<input type="text" name="edd-payment-address[0][zip]" value="<?php esc_attr_e( $address['zip'] ); ?>" class="medium-text"/>
													
												</p>
											</div>
											<div class="column">
												<p id="edd-order-address-country-wrap">
													<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'edd' ); ?></strong><br/>
													<?php
													echo EDD()->html->select( array(
														'options'          => edd_get_country_list(),
														'name'             => 'edd-payment-address[0][country]',
														'selected'         => $address['country'],
														'show_option_all'  => false,
														'show_option_none' => false
													) );
													?>
												</p>
												<p id="edd-order-address-state-wrap">
													<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'edd' ); ?></strong><br/>
													<?php
													$states = edd_get_shop_states( $address['country'] );
													if( ! empty( $states ) ) {
														echo EDD()->html->select( array(
															'options'          => $states,
															'name'             => 'edd-payment-address[0][state]',
															'selected'         => $address['state'],
															'show_option_all'  => false,
															'show_option_none' => false
														) );
													} else { ?>
														<input type="text" name="edd-payment-address[0][state]" value="<?php esc_attr_e( $address['state'] ); ?>" class="medium-text"/>
														<?php
													} ?>
												</p>
											</div>
										</div>
									</div>
								</div><!-- /#edd-order-address -->

								<?php do_action( 'edd_payment_billing_details', $payment_id ); ?>

							</div><!-- /.inside -->
						</div><!-- /#edd-billing-details -->

						<?php do_action( 'edd_view_order_details_billing_after', $payment_id ); ?>

						<?php $column_count = edd_item_quantities_enabled() ? 'columns-4' : 'columns-3'; ?>
						<div id="edd-purchased-files" class="postbox <?php echo $column_count; ?>">
							<h3 class="hndle">
								<span><?php printf( __( 'Purchased %s', 'edd' ), edd_get_label_plural() ); ?></span>
							</h3>
							
							<?php
							if ( $cart_items ) :
								$i = 0;
								foreach ( $cart_items as $key => $cart_item ) : ?>
								<div class="row">
									<ul>
										<?php
										// Item ID is checked if isset due to the near-1.0 cart data
										$item_id  = isset( $cart_item['id']    ) ? $cart_item['id']    : $cart_item;
										$price    = isset( $cart_item['price'] ) ? $cart_item['price'] : false;
										$price_id = isset( $cart_item['item_number']['options']['price_id'] ) ? $cart_item['item_number']['options']['price_id'] : null;
										$quantity = isset( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ? $cart_item['quantity'] : 1;

										if( ! $price ) {
											// This function is only used on payments with near 1.0 cart data structure
											$price = edd_get_download_final_price( $item_id, $user_info, null );
										}
										?>

										<li class="download">
											<span>
												<?php echo get_the_title( $item_id );

												if ( isset( $cart_items[ $key ]['item_number'] ) && isset( $cart_items[ $key ]['item_number']['options'] ) ) {
													$price_options = $cart_items[ $key ]['item_number']['options'];

													if ( isset( $price_id ) ) {
														echo ' - ' . edd_get_price_option_name( $item_id, $price_id, $payment_id );
													}
												}
												?>
											</span>
											<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][id]" class="edd-payment-details-download-id" value="<?php echo esc_attr( $item_id ); ?>"/>
											<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][price_id]" class="edd-payment-details-download-price-id" value="<?php echo esc_attr( $price_id ); ?>"/>
											<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][amount]" class="edd-payment-details-download-amount" value="<?php echo esc_attr( $price ); ?>"/>
											<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][quantity]" class="edd-payment-details-download-quantity" value="<?php echo esc_attr( $quantity ); ?>"/>
											
										</li>

										<?php if( edd_item_quantities_enabled() ) : ?>
										<li class="quantity">
											<?php echo __( 'Quantity:', 'edd' ) . '&nbsp;<span>' . $quantity . '</span>'; ?>
										</li>
										<?php endif; ?>

										<li class="price">
											<?php echo edd_currency_filter( edd_format_amount( $price ) ); ?>
										</li>

										<li class="actions">
											<a href="" class="edd-order-remove-download" data-key="<?php echo esc_attr( $key ); ?>"><?php _e( 'Remove', 'edd' ); ?></a>
										</li>
									</ul>
								</div>
								<?php
								$i++;
								endforeach;
							endif;
							?>

							<div class="inside">

								<ul>
									<li class="download">
										<?php echo EDD()->html->product_dropdown( array(
											'name'   => 'edd-order-download-select',
											'id'     => 'edd-order-download-select',
											'chosen' => true
										) ); ?>
									</li>

									<?php if( edd_item_quantities_enabled() ) : ?>
									<li class="quantity">
										<span><?php _e( 'Quantity', 'edd' ); ?>:&nbsp;</span>
										<input type="number" id="edd-order-download-quantity" class="small-text" min="1" step="1" value="1" />
									</li>
									<?php endif; ?>

									<li class="price">
										<span><?php _e( 'Amount', 'edd' ); ?>:&nbsp;</span>
										<?php
										echo EDD()->html->text( array( 'name' => 'edd-order-download-amount',
											'label' => __( 'Enter amount', 'edd' ),
											'class' => 'small-text edd-order-download-price' 
										) );
										?>
									</li>

									<li class="actions">
										<a href="" id="edd-order-add-download" class="button button-secondary"><?php printf( __( 'Add %s to Payment', 'edd' ), edd_get_label_singular() ); ?></a>
									</li>

								</ul>
							
								<input type="hidden" name="edd-payment-downloads-changed" id="edd-payment-downloads-changed" value=""/>

							</div><!-- /.inside -->
						</div><!-- /#edd-purchased-files -->

						<?php do_action( 'edd_view_order_details_files_after', $payment_id ); ?>

						<div id="edd-payment-notes" class="postbox">
							<h3 class="hndle"><span><?php _e( 'Payment Notes', 'edd' ); ?></span></h3>
							<div class="inside">
								<div id="edd-payment-notes-inner">
									<?php
									$notes = edd_get_payment_notes( $payment_id );
									if ( ! empty( $notes ) ) :
										$no_notes_display = ' style="display:none;"';
										foreach ( $notes as $note ) :
											
											echo edd_get_payment_note_html( $note, $payment_id );

										endforeach;
									else :
										$no_notes_display = '';
									endif;
									echo '<p class="edd-no-payment-notes"' . $no_notes_display . '>'. __( 'No payment notes', 'edd' ) . '</p>';
									?>
								</div>
								<textarea name="edd-payment-note" id="edd-payment-note" class="large-text"></textarea>
								
								<p>
									<button id="edd-add-payment-note" class="button button-secondary right" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'edd' ); ?></button>
								</p>
								
								<div class="clear"></div>
							</div><!-- /.inside -->
						</div><!-- /#edd-payment-notes -->

						<?php do_action( 'edd_view_order_details_main_after', $payment_id ); ?>
					</div><!-- /#normal-sortables -->
				</div><!-- #postbox-container-2 -->
			</div><!-- /#post-body -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'edd_view_order_details_form_bottom', $payment_id ); ?>
		<?php wp_nonce_field( 'edd_update_payment_details_nonce' ); ?>
		<input type="hidden" name="edd_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
		<input type="hidden" name="edd_action" value="update_payment_details"/>
	</form>
	<?php do_action( 'edd_view_order_details_after', $payment_id ); ?>
</div><!-- /.wrap -->
