<?php
/**
 * View Order Details
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * View Order Details Page
 *
 * @since 1.6
 * @since 3.0 Updated to use the new EDD\Orders\Order object.
 */
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Order ID not supplied. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

$order_id = absint( $_GET['id'] );
$order    = edd_get_order( $order_id );

// Check that the order exists in the database.
if ( ! $order ) {
	wp_die( __( 'The specified ID does not belong to an order. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

$transaction_id = esc_attr( $order->get_transaction_id() );
$order_items    = $order->get_items();
$cart_items     = $payment->cart_details;
$user_id        = $order->get_user_id();
$order_date     = strtotime( $order->get_date_created() );
$unlimited      = $order->has_unlimited_downloads();
$address        = $order->get_customer_address();
$gateway        = $order->get_gateway();
$currency_code  = $order->get_currency();
$customer       = edd_get_customer( $order->get_customer_id() );
$user_info      = edd_get_payment_meta_user_info( $order_id );
$notes          = edd_get_payment_notes( $order_id );
?>
<div class="wrap edd-wrap">
	<h2><?php printf( __( 'Order %s', 'easy-digital-downloads' ), $order->get_number() ); ?></h2>

	<?php do_action( 'edd_view_order_details_before', $order_id ); ?>

	<form id="edd-edit-order-form" method="post">
		<?php do_action( 'edd_view_order_details_form_top', $order_id ); ?>

		<div id="poststuff">
			<div id="edd-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php do_action( 'edd_view_order_details_sidebar_before', $order_id ); ?>

							<div id="edd-order-update" class="postbox edd-order-data">
								<h3 class="hndle">
									<span><?php _e( 'Update Payment', 'easy-digital-downloads' ); ?></span>
								</h3>

								<div class="inside">
									<div class="edd-admin-box">
										<?php do_action( 'edd_view_order_details_totals_before', $order_id ); ?>

										<div class="edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Status:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<select name="edd-payment-status" class="medium-text">
													<?php foreach ( edd_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $order->get_status(), $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>

												<?php
												$status_help = '<ul>';
												$status_help .= '<li>' . __( '<strong>Pending</strong>: payment is still processing or was abandoned by customer. Successful payments will be marked as Complete automatically once processing is finalized.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '</ul>';
												?>
												<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help"
													  title="<?php echo $status_help; ?>"></span>
											</p>

											<?php if ( edd_is_order_recoverable( $order_id ) ) : ?>
												<p>
													<span class="label"><?php _e( 'Recovery URL', 'easy-digital-downloads' ); ?>
														:</span>
													<?php $recover_help = __( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'easy-digital-downloads' ); ?>
													<span alt="f223"
														  class="edd-help-tip dashicons dashicons-editor-help"
														  title="<?php echo $recover_help; ?>"></span>

													<input type="text" class="large-text" readonly="readonly"
														   value="<?php echo $payment->get_recovery_url(); ?>"/>
												</p>
											<?php endif; ?>
										</div>

										<div class="edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Date:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<input type="text" name="edd-payment-date"
													   value="<?php echo esc_attr( date( 'Y-m-d', $order_date ) ); ?>"
													   class="medium-text edd_datepicker"
													   placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>"/>
											</p>
										</div>

										<div class="edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Time:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<input type="text" maxlength="2" name="edd-payment-time-hour"
													   value="<?php echo esc_attr( date_i18n( 'H', $order_date ) ); ?>"
													   class="small-text edd-payment-time-hour"/>&nbsp;:&nbsp;
												<input type="text" maxlength="2" name="edd-payment-time-min"
													   value="<?php echo esc_attr( date( 'i', $order_date ) ); ?>"
													   class="small-text edd-payment-time-min"/>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_update_inner', $order_id ); ?>

										<div class="edd-order-discount edd-admin-box-inside">
                                            <p class="strong"><?php _e( 'Discounts', 'easy-digital-downloads' ); ?>:</p>
                                            <ul class="edd-order-discounts">
                                                <?php
                                                foreach ( $order->get_discounts() as $discount ) {
                                                    /** @var EDD\Orders\Order_Adjustment $discount */
                                                    $discount_obj = edd_get_discount_by( 'code', $discount->get_description() );

                                                    echo '<a href="' . $discount_obj->edit_url() . '">' . $discount_obj->code . '</a>' . ': ' . edd_currency_filter( edd_format_amount( $discount->get_amount() ), $currency_code );
                                                }
                                                ?>
                                            </ul>
										</div>

										<?php
										$fees = $order->get_fees();
										if ( ! empty( $fees ) ) : ?>
											<div class="edd-order-fees edd-admin-box-inside">
												<p class="strong"><?php _e( 'Fees', 'easy-digital-downloads' ); ?>:</p>
												<ul class="edd-payment-fees">
													<?php foreach ( $fees as $fee ) : /** @var EDD\Orders\Order_Adjustment $fee */ ?>
														<li data-fee-id="<?php echo edd_get_order_adjustment_meta( $fee->get_id(), 'fee_id', true ) ?>">
															<span class="fee-label"><?php echo $fee->get_description() . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr( $fee->get_amount() ) . '">' . edd_currency_filter( edd_format_amount( $fee->get_amount() ), $currency_code ); ?></span>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>

										<?php if ( edd_use_taxes() ) : ?>
											<div class="edd-order-taxes edd-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Tax', 'easy-digital-downloads' ); ?>:</span>&nbsp;
													<input name="edd-payment-tax" class="med-text" type="text" value="<?php echo esc_attr( edd_format_amount( $order->get_tax() ) ); ?>"/>
													<span class="edd-tax-rate"><?php echo $order->get_tax_rate() * 100; ?>%</span>
												</p>
											</div>
										<?php endif; ?>

										<div class="edd-order-payment edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Total Price', 'easy-digital-downloads' ); ?>:</span>&nbsp;
												<?php echo edd_currency_symbol( $order->get_currency() ); ?>&nbsp;<input name="edd-payment-total" type="text" class="med-text" value="<?php echo esc_attr( edd_format_amount( $order->get_total() ) ); ?>"/>
											</p>
										</div>

										<div class="edd-order-payment-recalc-totals edd-admin-box-inside" style="display:none">
											<p>
												<span class="label"><?php _e( 'Recalculate Totals', 'easy-digital-downloads' ); ?>:</span>&nbsp;
												<a href="" id="edd-order-recalc-total" class="button button-secondary right"><?php _e( 'Recalculate', 'easy-digital-downloads' ); ?></a>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_totals_after', $order_id ); ?>

									</div><!-- /.edd-admin-box -->

								</div><!-- /.inside -->

								<div class="edd-order-update-box edd-admin-box">
									<?php do_action( 'edd_view_order_details_update_before', $order_id ); ?>

									<div id="major-publishing-actions">
										<div id="delete-action">
											<a href="<?php echo wp_nonce_url( add_query_arg( array(
												'edd-action'  => 'delete_payment',
												'purchase_id' => $order_id,
											), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ), 'edd_payment_nonce' ) ?>"
											   class="edd-delete-payment edd-delete"><?php _e( 'Delete Payment', 'easy-digital-downloads' ); ?></a>
										</div>

										<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Payment', 'easy-digital-downloads' ); ?>"/>
										<div class="clear"></div>
									</div>

									<?php do_action( 'edd_view_order_details_update_after', $order_id ); ?>
								</div><!-- /.edd-order-update-box -->

							</div><!-- /#edd-order-data -->

							<?php if ( 'publish' === $order->get_status() ) : ?>
								<div id="edd-order-resend-receipt" class="postbox edd-order-data">
									<div class="inside">
										<div class="edd-order-resend-receipt-box edd-admin-box">
											<?php do_action( 'edd_view_order_details_resend_receipt_before', $order_id ); ?>

											<a href="<?php echo esc_url( add_query_arg( array(
												'edd-action'  => 'email_links',
												'purchase_id' => $order_id,
											) ) ); ?>" id="<?php if ( count( (array) $customer->emails ) > 1 ) {
												echo 'edd-select-receipt-email';
											} else {
												echo 'edd-resend-receipt';
											} ?>"
											   class="button-secondary alignleft"><?php _e( 'Resend Receipt', 'easy-digital-downloads' ); ?></a>
											<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If download URLs are included in the receipt, new file download URLs will also be included with the receipt.', 'easy-digital-downloads' ); ?>"></span>

											<?php if ( count( (array) $customer->emails ) > 1 ) : ?>
												<div class="clear"></div>

												<div class="edd-order-resend-receipt-addresses" style="display:none;">
													<select class="edd-order-resend-receipt-email">
														<option value=""><?php _e( ' -- select email --', 'easy-digital-downloads' ); ?></option>
														<?php foreach ( $customer->emails as $email ) : ?>
															<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo $email; ?></option>
														<?php endforeach; ?>
													</select>
												</div>
											<?php endif; ?>

											<div class="clear"></div>

											<?php do_action( 'edd_view_order_details_resend_receipt_after', $order_id ); ?>
										</div><!-- /.edd-order-resend-receipt-box -->
									</div>
								</div>
							<?php endif; ?>

							<div id="edd-order-details" class="postbox edd-order-data">
								<h3 class="hndle"><span><?php _e( 'Order Meta', 'easy-digital-downloads' ); ?></span>
								</h3>

								<div class="inside">
									<div class="edd-admin-box">
										<?php do_action( 'edd_view_order_details_payment_meta_before', $order_id ); ?>

										<?php if ( $gateway ) : ?>
											<div class="edd-order-gateway edd-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Gateway:', 'easy-digital-downloads' ); ?></span>&nbsp;
													<?php echo edd_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="edd-order-payment-key edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Key:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<span><?php echo $order->get_payment_key(); ?></span>
											</p>
										</div>

										<div class="edd-order-ip edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'IP:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<span><?php echo edd_payment_get_ip_address_url( $order_id ); ?></span>
											</p>
										</div>

										<?php if ( $transaction_id ) : ?>
											<div class="edd-order-tx-id edd-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Transaction ID:', 'easy-digital-downloads' ); ?></span>&nbsp;
													<span><?php echo apply_filters( 'edd_payment_details_transaction_id-' . $gateway, $transaction_id, $order_id ); ?></span>
												</p>
											</div>
										<?php endif; ?>

										<div class="edd-unlimited-downloads edd-admin-box-inside">
											<p>
												<span class="label"><i data-code="f316" class="dashicons dashicons-download"></i></span>&nbsp;
												<input type="checkbox" name="edd-unlimited-downloads" id="edd_unlimited_downloads" value="1"<?php checked( true, $unlimited, true ); ?>/>
												<label class="description" for="edd_unlimited_downloads"><?php _e( 'Unlimited file downloads', 'easy-digital-downloads' ); ?></label>
												<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Unlimited file downloads</strong>: checking this box will override all other file download limits for this purchase, granting the customer unliimited downloads of all files included on the purchase.', 'easy-digital-downloads' ); ?>"></span>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_payment_meta_after', $order_id ); ?>
									</div><!-- /.column-container -->
								</div><!-- /.inside -->
							</div><!-- /#edd-order-data -->

							<div id="edd-order-logs" class="postbox edd-order-logs">
								<h3 class="hndle"><span><?php _e( 'Logs', 'easy-digital-downloads' ); ?></span></h3>

								<div class="inside">
									<div class="edd-admin-box">
										<div class="edd-admin-box-inside">
											<p><a href="<?php echo admin_url( '/edit.php?post_type=download&page=edd-tools&tab=logs&payment=' . $order_id ); ?>"><?php _e( 'View file download log for purchase', 'easy-digital-downloads' ); ?></a></p>
											<p><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=logs&user=' . $user_id ); ?>"><?php _e( 'View customer download log', 'easy-digital-downloads' ); ?></a></p>
											<p><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . esc_attr( edd_get_payment_user_email( $order_id ) ) ); ?>"><?php _e( 'View all purchases of customer', 'easy-digital-downloads' ); ?></a></p>
										</div>

										<?php do_action( 'edd_view_order_details_logs_inner', $order_id ); ?>
									</div><!-- /.column-container -->
								</div><!-- /.inside -->
							</div><!-- /#edd-order-logs -->

							<?php do_action( 'edd_view_order_details_sidebar_after', $order_id ); ?>
						</div><!-- /#side-sortables -->
					</div><!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_action( 'edd_view_order_details_main_before', $order_id ); ?>

							<?php $column_count = edd_use_taxes() ? 'columns-5' : 'columns-4'; ?>

							<?php if ( is_array( $order_items ) ) :
								$is_qty_enabled = edd_item_quantities_enabled() ? ' item_quantity' : '';
								?>
								<div id="edd-purchased-files"
									 class="postbox edd-edit-purchase-element <?php echo $column_count; ?>">
									<h3 class="hndle edd-payment-details-label-mobile">
										<span><?php printf( __( 'Purchased %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></span>
									</h3>

									<div class="edd-purchased-files-header row header">
										<ul class="edd-purchased-files-list-wrapper">
											<li class="download"><?php printf( _x( '%s Purchased', 'payment details purchased item title - full screen', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></li>

											<li class="item_price<?php echo $is_qty_enabled; ?>">
												<?php
												_ex( 'Price', 'payment details purchased item price - full screen', 'easy-digital-downloads' );
												if ( edd_item_quantities_enabled() ) {
													_ex( ' & Quantity', 'payment details purchased item quantity - full screen', 'easy-digital-downloads' );
												}
												?>
											</li>

											<?php if ( edd_use_taxes() ) : ?>
												<li class="item_tax"><?php _ex( 'Tax', 'payment details purchased item tax - full screen', 'easy-digital-downloads' ); ?></li>
											<?php endif; ?>

											<li class="price"><?php printf( _x( '%s Total', 'payment details purchased item total - full screen', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></li>
										</ul>
									</div>
									<?php
									$i = 0;
									foreach ( $order_items as $key => $order_item ) :
										/** @var EDD\Orders\Order_Item $order_item */ ?>
										<div class="row">
											<ul class="edd-purchased-files-list-wrapper">
												<?php
												// Item ID is checked if isset due to the near-1.0 cart data
												$item_id    = ! empty( $order_item->get_product_id() ) ? $order_item->get_product_id() : $order_item;
												$price      = ! empty( $order_item->get_amount() ) ? $order_item->get_amount() : false;
												$item_price = ! empty( $order_item->get_subtotal() ) ? $order_item->get_subtotal() : $price;
												$subtotal   = ! empty( $order_item->get_subtotal() ) ? $order_item->get_subtotal() : $price;
												$item_tax   = ! empty( $order_item->get_tax() ) ? $order_item->get_tax() : 0;
												$price_id   = ! empty( $order_item->get_price_id() ) ? $order_item->get_price_id() : null;
												$quantity   = ! empty( $order_item->get_quantity() ) && $order_item->get_quantity() > 0 ? $order_item->get_quantity() : 1;
												$download   = new EDD_Download( $item_id );

												if ( false === $price ) {
													// This function is only used on payments with near 1.0 cart data structure
													$price = edd_get_download_final_price( $item_id, $user_info, null );
												}
												?>

												<li class="download">
													<span class="edd-purchased-download-title">
														<?php if ( ! empty( $download->ID ) ) : ?>
															<a href="<?php echo admin_url( 'post.php?post=' . $item_id . '&action=edit' ); ?>">
																<?php echo $download->get_name();
																$price_id = $order_item->get_price_id();
                                                                if ( $download->has_variable_prices() && ! empty( $price_id ) ) {
                                                                    echo ' - ' . edd_get_price_option_name( $item_id, $price_id, $order_id );
                                                                }
																?>
															</a>
														<?php else: ?>
															<span class="deleted">
																<?php if ( ! empty( $cart_item['name'] ) ) : ?>
																	<?php echo $cart_item['name']; ?>&nbsp;-&nbsp;<em>(<?php _e( 'Deleted', 'easy-digital-downloads' ); ?>)</em>
																<?php else: ?>
																	<em><?php printf( __( '%s deleted', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></em>
																<?php endif; ?>
															</span>
														<?php endif; ?>
													</span>
													<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][id]" class="edd-payment-details-download-id" value="<?php echo esc_attr( $item_id ); ?>"/>
													<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][price_id]" class="edd-payment-details-download-price-id" value="<?php echo esc_attr( $price_id ); ?>"/>

													<?php if ( ! edd_item_quantities_enabled() ) : ?>
														<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][quantity]" class="edd-payment-details-download-quantity" value="<?php echo esc_attr( $quantity ); ?>"/>
													<?php endif; ?>

													<?php if ( ! edd_use_taxes() ): ?>
														<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][item_tax]" class="edd-payment-details-download-item-tax" value="<?php echo $item_tax; ?>"/>
													<?php endif; ?>

													<?php if ( ! empty( $cart_items[ $key ]['fees'] ) ) : ?>
														<?php $fees = array_keys( $cart_items[ $key ]['fees'] ); ?>
														<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][fees]" class="edd-payment-details-download-fees" value="<?php echo esc_attr( json_encode( $fees ) ); ?>"/>
													<?php endif; ?>
												</li>

												<li class="item_price<?php echo $is_qty_enabled; ?>">
													<span class="edd-payment-details-label-mobile">
														<?php
														_ex( 'Price', 'payment details purchased item price - mobile', 'easy-digital-downloads' );
														if ( edd_item_quantities_enabled() ) {
															_ex( ' & Quantity', 'payment details purchased item quantity - mobile', 'easy-digital-downloads' );
														}
														?>
													</span>
													<?php echo edd_currency_symbol( $currency_code ); ?>
													<input type="text" class="medium-text edd-price-field edd-payment-details-download-item-price edd-payment-item-input" name="edd-payment-details-downloads[<?php echo $key; ?>][item_price]" value="<?php echo edd_format_amount( $item_price ); ?>"/>
													<?php if ( edd_item_quantities_enabled() ) : ?>
														&nbsp;&times;&nbsp;
														<input type="number" name="edd-payment-details-downloads[<?php echo $key; ?>][quantity]" class="small-text edd-payment-details-download-quantity edd-payment-item-input" min="1" step="1" value="<?php echo $quantity; ?>"/>
													<?php endif; ?>
												</li>

												<?php if ( edd_use_taxes() ) : ?>
													<li class="item_tax">
													<span class="edd-payment-details-label-mobile">
														<?php _ex( 'Tax', 'payment details purchased item tax - mobile', 'easy-digital-downloads' ); ?>
													</span>
														<?php echo edd_currency_symbol( $currency_code ); ?>
														<input type="text" class="small-text edd-price-field edd-payment-details-download-item-tax edd-payment-item-input" name="edd-payment-details-downloads[<?php echo $key; ?>][item_tax]" value="<?php echo edd_format_amount( $item_tax ); ?>"/>
													</li>
												<?php endif; ?>

												<li class="price">
													<span class="edd-payment-details-label-mobile">
														<?php printf( _x( '%s Total', 'payment details purchased item total - mobile', 'easy-digital-downloads' ), edd_get_label_singular() ); ?>
													</span>
													<span class="edd-price-currency"><?php echo edd_currency_symbol( $currency_code ); ?></span><span class="price-text edd-payment-details-download-amount"><?php echo edd_format_amount( $price ); ?></span>
													<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][amount]" class="edd-payment-details-download-amount" value="<?php echo esc_attr( $price ); ?>"/>
												</li>
											</ul>

											<div class="edd-purchased-download-actions actions">
												<input type="hidden" class="edd-payment-details-download-has-log" name="edd-payment-details-downloads[<?php echo $key; ?>][has_log]" value="1" />
												<?php if ( edd_get_download_files( $item_id, $price_id ) && edd_is_payment_complete( $order_id ) ) : ?>
													<span class="edd-copy-download-link-wrapper"><a href="" class="edd-copy-download-link" data-download-id="<?php echo esc_attr( $item_id ); ?>" data-price-id="<?php echo esc_attr( $price_id ); ?>"><?php _e( 'Copy Download Link(s)', 'easy-digital-downloads' ); ?></a> |</span>
												<?php endif; ?>
												<a href="" class="edd-order-remove-download edd-delete" data-key="<?php echo esc_attr( $key ); ?>"><?php _e( 'Remove', 'easy-digital-downloads' ); ?></a>
											</div>
										</div>
										<?php
										$i ++;
									endforeach; ?>
								</div>
							<?php else : $key = 0; ?>
								<div class="row">
									<p><?php printf( __( 'No %s included with this purchase', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
								</div>
							<?php endif; ?>

							<div class="postbox edd-edit-purchase-element <?php echo $column_count; ?>">

								<div class="edd-add-download-to-purchase-header row header">
									<ul class="edd-purchased-files-list-wrapper">
										<li class="download"><?php printf( __( 'Add New %s', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></li>

										<li class="item_price<?php echo $is_qty_enabled; ?>">
											<?php _e( 'Price', 'easy-digital-downloads' ); ?>
											<?php if ( edd_item_quantities_enabled() ) : ?>
												<?php _e( ' & Quantity', 'easy-digital-downloads' ); ?>
											<?php endif; ?>
										</li>

										<?php if ( edd_use_taxes() ) : ?>
											<li class="item_tax"><?php _e( 'Tax', 'easy-digital-downloads' ); ?></li>
										<?php endif; ?>

										<li class="price"><?php _e( 'Actions', 'easy-digital-downloads' ); ?></li>
									</ul>
								</div>
								<div class="edd-add-download-to-purchase inside">
									<ul>
										<li class="download">
											<span class="edd-payment-details-label-mobile"><?php printf( _x( 'Select New %s To Add', 'payment details select item to add - mobile', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></span>

											<?php echo EDD()->html->product_dropdown( array(
												'name'   => 'edd-order-download-select',
												'id'     => 'edd-order-download-select',
												'chosen' => true,
											) ); ?>
										</li>

										<li class="item_price<?php echo $is_qty_enabled; ?>">
											<span class="edd-payment-details-label-mobile">
												<?php
												_ex( 'Price', 'payment details add item price - mobile', 'easy-digital-downloads' );
												if ( edd_item_quantities_enabled() ) :
													_ex( ' & Quantity', 'payment details add item quantity - mobile', 'easy-digital-downloads' );
												endif;
												?>
											</span>
											<?php
											echo edd_currency_symbol( $currency_code ) . '&nbsp;';
											echo EDD()->html->text(
												array(
													'name'  => 'edd-order-download-price',
													'id'    => 'edd-order-download-price',
													'class' => 'medium-text edd-price-field edd-order-download-price edd-add-download-field',
												)
											);
											?>

											<?php if ( edd_item_quantities_enabled() ) : ?>
												&nbsp;&times;&nbsp;
												<input type="number" id="edd-order-download-quantity"
													   name="edd-order-download-quantity"
													   class="small-text edd-add-download-field" min="1" step="1"
													   value="1"/>
											<?php endif; ?>
										</li>

										<?php if ( edd_use_taxes() ) : ?>
											<li class="item_tax">
												<span class="edd-payment-details-label-mobile">
													<?php _ex( 'Tax', 'payment details add item tax - mobile', 'easy-digital-downloads' ); ?>
												</span>
												<?php
												echo edd_currency_symbol( $currency_code ) . '&nbsp;';
												echo EDD()->html->text(
													array(
														'name'  => 'edd-order-download-tax',
														'id'    => 'edd-order-download-tax',
														'class' => 'small-text edd-order-download-tax edd-add-download-field',
													)
												);
												?>
											</li>
										<?php endif; ?>

										<li class="edd-add-download-to-purchase-actions actions">
											<span class="edd-payment-details-label-mobile"><?php _e( 'Actions', 'easy-digital-downloads' ); ?></span>
											<a href="" id="edd-order-add-download" class="button button-secondary"><?php printf( __( 'Add New %s', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></a>
										</li>
									</ul>

									<input type="hidden" name="edd-payment-downloads-changed" id="edd-payment-downloads-changed" value="" />
									<input type="hidden" name="edd-payment-removed" id="edd-payment-removed" value="{}" />

									<?php if ( ! edd_item_quantities_enabled() ) : ?>
										<input type="hidden" id="edd-order-download-quantity" name="edd-order-download-quantity" value="1" />
									<?php endif; ?>

									<?php if ( ! edd_use_taxes() ) : ?>
										<input type="hidden" id="edd-order-download-tax" name="edd-order-download-tax" value="0" />
									<?php endif; ?>

								</div><!-- /.inside -->

							</div>

							<?php do_action( 'edd_view_order_details_files_after', $order_id ); ?>

							<?php do_action( 'edd_view_order_details_billing_before', $order_id ); ?>

							<div id="edd-customer-details" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Customer Details', 'easy-digital-downloads' ); ?></span></h3>

								<div class="inside edd-clearfix">
									<div class="column-container customer-info">
										<div class="column">
											<?php if ( ! empty( $customer->id ) ) : ?>
												<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $customer->id ); ?>"><?php echo $customer->name; ?>
													- <?php echo $customer->email; ?></a>
											<?php endif; ?>
											<input type="hidden" name="edd-current-customer"
												   value="<?php echo $customer->id; ?>"/>
										</div>
										<div class="column">
											<a href="#change"
											   class="edd-payment-change-customer"><?php _e( 'Assign to another customer', 'easy-digital-downloads' ); ?></a>
											&nbsp;|&nbsp;
											<a href="#new"
											   class="edd-payment-new-customer"><?php _e( 'New Customer', 'easy-digital-downloads' ); ?></a>
										</div>
									</div>

									<div class="column-container change-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Select a customer', 'easy-digital-downloads' ); ?>
												:</strong>
											<?php
											echo EDD()->html->customer_dropdown( array(
												'class'       => 'edd-payment-change-customer-input',
												'selected'    => $customer->id,
												'name'        => 'customer-id',
												'placeholder' => __( 'Type to search all Customers', 'easy-digital-downloads' ),
											) );
											?>
										</div>
										<div class="column"></div>
										<div class="column">
											<strong><?php _e( 'Actions', 'easy-digital-downloads' ); ?>:</strong>
											<br/>
											<input type="hidden" id="edd-change-customer" name="edd-change-customer" value="0" />
											<a href="#cancel" class="edd-payment-change-customer-cancel edd-delete"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
										</div>
										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to change the customer', 'easy-digital-downloads' ); ?></em></small>
										</div>
									</div>

									<div class="column-container new-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Name', 'easy-digital-downloads' ); ?>:</strong>&nbsp;
											<input type="text" name="edd-new-customer-name" value="" class="medium-text"/>
										</div>

										<div class="column">
											<strong><?php _e( 'Email', 'easy-digital-downloads' ); ?>:</strong>&nbsp;
											<input type="email" name="edd-new-customer-email" value="" class="medium-text"/>
										</div>

										<div class="column">
											<strong><?php _e( 'Actions', 'easy-digital-downloads' ); ?>:</strong>
											<br/>
											<input type="hidden" id="edd-new-customer" name="edd-new-customer" value="0" />
											<a href="#cancel" class="edd-payment-new-customer-cancel edd-delete"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
										</div>

										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to create new customer', 'easy-digital-downloads' ); ?></em></small>
										</div>
									</div>

									<?php
									// The edd_payment_personal_details_list hook is left here for backwards compatibility
									do_action( 'edd_payment_personal_details_list', edd_get_order_meta( $order_id ), $user_info );
									do_action( 'edd_payment_view_details', $order_id );
									?>
								</div><!-- /.inside -->
							</div><!-- /#edd-customer-details -->

							<div id="edd-billing-details" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Billing Address', 'easy-digital-downloads' ); ?></span></h3>

								<div class="inside edd-clearfix">
									<div id="edd-order-address">
										<div class="order-data-address">
											<div class="data column-container">
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="large-text" />
													</p>
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="large-text" />
													</p>
												</div>

												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="large-text" />
													</p>

													<p>
														<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="large-text" />
													</p>
												</div>

												<div class="column">
													<p id="edd-order-address-country-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'easy-digital-downloads' ); ?></strong><br/>
														<?php
														echo EDD()->html->select( array(
															'options'          => edd_get_country_list(),
															'name'             => 'edd-payment-address[0][country]',
															'id'               => 'edd-payment-address-country',
															'selected'         => $address['country'],
															'show_option_all'  => false,
															'show_option_none' => false,
															'chosen'           => true,
															'placeholder'      => __( 'Select a country', 'easy-digital-downloads' ),
															'data'             => array(
																'search-type'        => 'no_ajax',
																'search-placeholder' => __( 'Search Countries', 'easy-digital-downloads' ),
															),
														) );
														?>
													</p>

													<p id="edd-order-address-state-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'easy-digital-downloads' ); ?></strong><br/>
														<?php
														$states = edd_get_shop_states( $address['country'] );
														if ( ! empty( $states ) ) {
															echo EDD()->html->select( array(
																'options'          => $states,
																'name'             => 'edd-payment-address[0][state]',
																'id'               => 'edd-payment-address-state',
																'selected'         => $address['state'],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => __( 'Select a state', 'easy-digital-downloads' ),
																'data'             => array(
																	'search-type'        => 'no_ajax',
																	'search-placeholder' => __( 'Search States/Provinces', 'easy-digital-downloads' ),
																),
															) );
														} else { ?>
															<input type="text" name="edd-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="large-text" />
															<?php
														} ?>
													</p>
												</div>
											</div>
										</div>
									</div><!-- /#edd-order-address -->

									<?php do_action( 'edd_payment_billing_details', $order_id ); ?>
								</div><!-- /.inside -->
							</div><!-- /#edd-billing-details -->

							<?php do_action( 'edd_view_order_details_billing_after', $order_id ); ?>

							<div id="edd-notes" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Payment Notes', 'easy-digital-downloads' ); ?></span>
								</h3>
								<div class="inside">
									<?php echo edd_admin_get_notes_html( $notes ); ?>
									<?php echo edd_admin_get_new_note_form( $order_id, 'payment' ); ?>
								</div><!-- /.inside -->
							</div><!-- /#edd-notes -->

							<?php do_action( 'edd_view_order_details_main_after', $order_id ); ?>
						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->
				</div><!-- /#post-body -->
			</div><!-- #edd-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->

		<?php do_action( 'edd_view_order_details_form_bottom', $order_id ); ?>

		<?php wp_nonce_field( 'edd_update_payment_details_nonce' ); ?>
		<input type="hidden" name="edd_payment_id" value="<?php echo esc_attr( $order_id ); ?>"/>
		<input type="hidden" name="edd_action" value="update_payment_details"/>
	</form>
	<?php do_action( 'edd_view_order_details_after', $order_id ); ?>
</div><!-- /.wrap -->

<div id="edd-download-link"></div>