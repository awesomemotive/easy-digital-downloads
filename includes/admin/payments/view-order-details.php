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

// Load list tables if not already loaded
if ( ! class_exists( 'EDD_Order_Item_Table' ) ) {
	require_once 'class-order-items-table.php';
}

if ( ! class_exists( 'EDD_Order_Adjustemnt_Table' ) ) {
	require_once 'class-order-adjustments-table.php';
}

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

// For backwards compatibility.
$payment = edd_get_payment( $order_id );

// Check that the order exists in the database.
if ( empty( $order ) ) {
	wp_die( __( 'The specified ID does not belong to an order. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

$unlimited      = $order->has_unlimited_downloads();
$transaction_id = $order->get_transaction_id();
$address        = $order->get_customer_address();
$user_info      = $order->get_user_info();
$fees           = $order->get_fees();
$discounts      = $order->get_discounts();
$order_items    = $order->get_items();
$order_date     = strtotime( $order->date_created );
$customer       = edd_get_customer( $order->customer_id );
$notes          = edd_get_payment_notes( $order->id );
$cart_fees      = edd_get_order_adjustments( array(
	'object_id'   => $order->id,
	'object_type' => 'order',
	'type_id'     => '',
	'type'        => 'fee'
) ); ?>

<div class="wrap edd-wrap">
    <h2><?php printf( __( 'Edit Order - %s', 'easy-digital-downloads' ), $order->number ); ?></h2>

	<div class="notice notice-info">
		<p>Testers: This page is newly refreshed and non-functioning for this beta.</p>
	</div>

	<?php do_action( 'edd_view_order_details_before', $order->id ); ?>

    <form id="edd-edit-order-form" method="post">
		<?php do_action( 'edd_view_order_details_form_top', $order->id ); ?>

        <div id="poststuff">
            <div id="edd-dashboard-widgets-wrap">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="postbox-container-1" class="postbox-container">
                        <div id="side-sortables">
							<?php do_action( 'edd_view_order_details_sidebar_before', $order->id ); ?>

                            <div id="edd-order-update" class="postbox edd-order-data">
                                <h3 class="hndle">
                                    <span><?php _e( 'Update Order', 'easy-digital-downloads' ); ?></span>
									<a href="" id="edd-order-recalc-total" class="edd-metabox-title-action"><?php _e( 'Recalculate', 'easy-digital-downloads' ); ?></a>
                                </h3>

                                <div class="inside">
                                    <div class="edd-order-update-box edd-admin-box">
										<?php do_action( 'edd_view_order_details_totals_before', $order->id ); ?>

                                        <div class="edd-order-payment edd-admin-box-inside">
											<span class="label"><?php _e( 'Total', 'easy-digital-downloads' ); ?>:</span>
											<?php echo edd_currency_symbol( $order->currency ); ?>&nbsp;<input name="edd-payment-total" type="text" class="med-text" value="<?php echo esc_attr( edd_format_amount( $order->total ) ); ?>"/>
                                        </div>

                                        <div class="edd-admin-box-inside">
											<span class="label"><?php _e( 'Status:', 'easy-digital-downloads' ); ?></span>
											<select name="edd-payment-status" class="edd-select-chosen">
												<?php foreach ( edd_get_payment_statuses() as $key => $status ) : ?>
													<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $order->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
												<?php endforeach; ?>
											</select>

											<?php
											$status_help = '<ul>';
											$status_help .= '<li>' . __( '<strong>Pending</strong>: order is still processing or was abandoned by customer. Successful orders will be marked as Complete automatically once processing is finalized.', 'easy-digital-downloads' ) . '</li>';
											$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'easy-digital-downloads' ) . '</li>';
											$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'easy-digital-downloads' ) . '</li>';
											$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'easy-digital-downloads' ) . '</li>';
											$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'easy-digital-downloads' ) . '</li>';
											$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'easy-digital-downloads' ) . '</li>';
											$status_help .= '</ul>';
											?>
											<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help"
												  title="<?php echo $status_help; ?>"></span>
										</div>

										<?php if ( edd_is_order_recoverable( $order->id ) && $payment->get_recovery_url() ) : ?>
											<div class="edd-admin-box-inside">
												<span class="label"><?php _e( 'Recover', 'easy-digital-downloads' ); ?>:</span>
												<input type="text" readonly="readonly"
													   value="<?php echo $payment->get_recovery_url(); ?>"/>
												<span alt="f223"
													  class="edd-help-tip dashicons dashicons-editor-help"
													  title="<?php _e( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'easy-digital-downloads' ); ?>"></span>
											</div>
										<?php endif; ?>

                                        <div class="edd-admin-box-inside">
											<span class="label"><?php _e( 'Date:', 'easy-digital-downloads' ); ?></span>
											<input type="text" name="edd-payment-date"
												   value="<?php echo esc_attr( date( 'Y-m-d', $order_date ) ); ?>"
												   class="medium-text edd_datepicker"
												   placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>"/>
                                        </div>

                                        <div class="edd-admin-box-inside">
											<span class="label"><?php _e( 'Time:', 'easy-digital-downloads' ); ?></span>
											<?php
											echo EDD()->html->select( array(
												'name'             => 'edd-payment-time-hour',
												'options'          => edd_get_hour_values(),
												'selected'         => date( 'G', $order_date ),
												'chosen'           => true,
												'class'            => 'edd-time',
												'show_option_none' => false,
												'show_option_all'  => false
											) );
											?>
											:
											<?php
											echo EDD()->html->select( array(
												'name'             => 'edd-payment-time-min',
												'options'          => edd_get_minute_values(),
												'selected'         => date( 'i', $order_date ),
												'chosen'           => true,
												'class'            => 'edd-time',
												'show_option_none' => false,
												'show_option_all'  => false
											) );
											?>
                                        </div>

										<?php do_action( 'edd_view_order_details_update_inner', $order->id ); ?>

										<?php if ( edd_use_taxes() ) : ?>
                                            <div class="edd-order-taxes edd-admin-box-inside">
												<span class="label"><?php _e( 'Tax', 'easy-digital-downloads' ); ?>:</span>
												<input name="edd-payment-tax" class="med-text" type="text" value="<?php echo esc_attr( edd_format_amount( $order->tax ) ); ?>"/>
												<span class="edd-tax-rate"><?php echo $order->get_tax_rate() * 100; ?>%</span>
                                            </div>
										<?php endif; ?>

										<?php do_action( 'edd_view_order_details_totals_after', $order->id ); ?>

                                    </div><!-- /.edd-admin-box -->
                                </div><!-- /.inside -->

                                <div class="edd-order-update-box edd-admin-box">
									<?php do_action( 'edd_view_order_details_update_before', $order->id ); ?>

                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a href="<?php echo wp_nonce_url( add_query_arg( array(
												'edd-action'  => 'delete_payment',
												'purchase_id' => $order->id,
											), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ), 'edd_payment_nonce' ) ?>"
                                               class="edd-delete-payment edd-delete"><?php _e( 'Delete Order', 'easy-digital-downloads' ); ?></a>
                                        </div>

                                        <input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Order', 'easy-digital-downloads' ); ?>"/>
                                        <div class="clear"></div>
                                    </div>

									<?php do_action( 'edd_view_order_details_update_after', $order->id ); ?>
                                </div><!-- /.edd-order-update-box -->

                            </div><!-- /#edd-order-data -->

                            <div id="edd-order-details" class="postbox edd-order-data">
                                <h3 class="hndle"><span><?php _e( 'Order Extras', 'easy-digital-downloads' ); ?></span>
                                </h3>

                                <div class="inside">
                                    <div class="edd-admin-box">
										<?php do_action( 'edd_view_order_details_payment_meta_before', $order->id ); ?>

										<?php if ( $order->gateway ) : ?>
                                            <div class="edd-order-gateway edd-admin-box-inside">
												<span class="label"><?php _e( 'Gateway', 'easy-digital-downloads' ); ?>:</span>
												<?php echo edd_get_gateway_admin_label( $order->gateway ); ?>
                                            </div>
										<?php endif; ?>

                                        <div class="edd-order-payment-key edd-admin-box-inside">
											<span class="label"><?php _e( 'Key', 'easy-digital-downloads' ); ?>:</span>
											<input type="text" disabled value="<?php echo $order->payment_key; ?>" />
                                        </div>

                                        <div class="edd-order-ip edd-admin-box-inside">
											<span class="label"><?php _e( 'IP', 'easy-digital-downloads' ); ?>:</span>
											<span><?php echo edd_payment_get_ip_address_url( $order->id ); ?></span>
                                        </div>

										<?php if ( $transaction_id ) : ?>
                                            <div class="edd-order-tx-id edd-admin-box-inside">
												<span class="label"><?php _e( 'Transaction ID', 'easy-digital-downloads' ); ?>:</span>
												<span><?php echo apply_filters( 'edd_payment_details_transaction_id-' . $order->gateway, $transaction_id, $order->id ); ?></span>
                                            </div>
										<?php endif; ?>

                                        <div class="edd-unlimited-downloads edd-admin-box-inside">
											<span class="label"><?php _e( 'Downloads', 'easy-digital-downloads' ); ?>:</span>
											<input type="checkbox" name="edd-unlimited-downloads" id="edd_unlimited_downloads" value="1"<?php checked( true, $unlimited, true ); ?>/>
											<label class="description" for="edd_unlimited_downloads"><?php _e( 'Unlimited', 'easy-digital-downloads' ); ?></label>
											<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Unlimited Downloads</strong>: checking this box will override all other file download limits for this purchase, granting the customer unliimited downloads of all files included on the purchase.', 'easy-digital-downloads' ); ?>"></span>
                                        </div>

										<?php do_action( 'edd_view_order_details_payment_meta_after', $order->id ); ?>
                                    </div><!-- /.column-container -->
                                </div><!-- /.inside -->
                            </div><!-- /#edd-order-data -->

                            <div id="edd-order-logs" class="postbox edd-order-logs">
                                <h3 class="hndle"><span><?php _e( 'Logs', 'easy-digital-downloads' ); ?></span></h3>

                                <div class="inside">
                                    <div class="edd-admin-box">
                                        <div class="edd-admin-box-inside">
											<ul>
												<li><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=logs&payment=' . $order->id ); ?>"><?php _e( 'File Download Log for Order', 'easy-digital-downloads' ); ?></a></li>
												<li><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=logs&customer=' . $order->customer_id ); ?>"><?php _e( 'Customer Download Log', 'easy-digital-downloads' ); ?></a></li>
												<li><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . esc_attr( edd_get_payment_user_email( $order->id ) ) ); ?>"><?php _e( 'Customer Orders', 'easy-digital-downloads' ); ?></a></li>
											</ul>
                                        </div>

										<?php do_action( 'edd_view_order_details_logs_inner', $order->id ); ?>
                                    </div><!-- /.column-container -->
                                </div><!-- /.inside -->
                            </div><!-- /#edd-order-logs -->

							<?php do_action( 'edd_view_order_details_sidebar_after', $order->id ); ?>
                        </div><!-- /#side-sortables -->
                    </div><!-- /#postbox-container-1 -->

                    <div id="postbox-container-2" class="postbox-container">
                        <div id="normal-sortables">
							<?php do_action( 'edd_view_order_details_main_before', $order->id ); ?>

							<div id="edd-purchased-files" class="postbox edd-edit-purchase-element">
								<h3 class="hndle">
									<span><?php _e( 'Order Items', 'easy-digital-downloads' ); ?></span>
									<a href="#" class="edd-metabox-title-action"><?php _e( 'Add Item', 'easy-digital-downloads' ); ?></a>
								</h3>
                                <div class="edd-add-download-to-purchase" style="display: none;">
                                    <ul>
                                        <li class="download">
                                            <span class="edd-payment-details-label-mobile"><?php printf( _x( '%s To Add', 'payment details select item to add - mobile', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></span>

											<?php echo EDD()->html->product_dropdown( array(
												'name'   => 'edd-order-download-select',
												'id'     => 'edd-order-download-select',
												'chosen' => true,
											) ); ?>
                                        </li>

                                        <li class="item_price">
											<span class="edd-payment-details-label-mobile">
												<?php
												_ex( 'Price', 'payment details add item price - mobile', 'easy-digital-downloads' );
												if ( edd_item_quantities_enabled() ) :
													_ex( ' & Quantity', 'payment details add item quantity - mobile', 'easy-digital-downloads' );
												endif;
												?>
											</span>
											<?php
											echo edd_currency_symbol( $order->currency ) . '&nbsp;';
											echo EDD()->html->text( array(
												'name'  => 'edd-order-download-price',
												'id'    => 'edd-order-download-price',
												'class' => 'medium-text edd-price-field edd-order-download-price edd-add-download-field',
											) );

											if ( edd_item_quantities_enabled() ) : ?>
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
												echo edd_currency_symbol( $order->currency ) . '&nbsp;';
												echo EDD()->html->text( array(
													'name'  => 'edd-order-download-tax',
													'id'    => 'edd-order-download-tax',
													'class' => 'small-text edd-order-download-tax edd-add-download-field',
												) );
												?>
                                            </li>
										<?php endif; ?>

                                        <li class="edd-add-download-to-purchase-actions actions">
                                            <span class="edd-payment-details-label-mobile"><?php _e( 'Actions', 'easy-digital-downloads' ); ?></span>
                                            <a href="" id="edd-order-add-download" class="button button-secondary"><?php _e( 'Add', 'easy-digital-downloads' ); ?></a>
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

                                </div>
								<div class="edd-order-children-wrapper">
								<?php
									$order_items = new EDD_Order_Item_Table();
									$order_items->prepare_items();
									$order_items->display();
								?>
								</div>
							</div>

							<div id="edd-purchased-files" class="postbox edd-edit-purchase-element">
								<h3 class="hndle">
									<span><?php _e( 'Order Adjustments', 'easy-digital-downloads' ); ?></span>
									<a href="#" class="edd-metabox-title-action"><?php _e( 'Add Adjustment', 'easy-digital-downloads' ); ?></a>
								</h3>
								<div class="edd-order-children-wrapper">
								<?php
									$order_adjustments = new EDD_Order_Adjustment_Table();
									$order_adjustments->prepare_items();
									$order_adjustments->display();
								?>
								</div>
							</div>

							<?php do_action( 'edd_view_order_details_files_after', $order->id ); ?>

							<?php do_action( 'edd_view_order_details_billing_before', $order->id ); ?>

                            <div id="edd-customer-details" class="postbox">
                                <h3 class="hndle">
									<span><?php _e( 'Customer Details', 'easy-digital-downloads' ); ?></span>

									<?php if ( 'publish' === $order->status ) : ?>

										<?php if ( ! empty( $customer->emails ) && count( (array) $customer->emails ) > 1 ) : ?>

											<span class="edd-order-resend-receipt-addresses" style="display:none;">
												<select class="edd-order-resend-receipt-email">
													<option value=""><?php _e( ' -- select email --', 'easy-digital-downloads' ); ?></option>
													<?php foreach ( $customer->emails as $email ) : ?>
														<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo $email; ?></option>
													<?php endforeach; ?>
												</select>
											</span>

										<?php endif; ?>

										<a href="<?php echo esc_url( add_query_arg( array(
											'edd-action'  => 'email_links',
											'purchase_id' => $order->id,
										) ) ); ?>" id="<?php if ( ! empty( $customer->emails ) && count( (array) $customer->emails ) > 1 ) {
											echo 'edd-select-receipt-email';
										} else {
											echo 'edd-resend-receipt';
										} ?>"
										   class="edd-metabox-title-action edd-help-tip" title="<?php _e( '<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If download URLs are included in the receipt, new file download URLs will also be included with the receipt.', 'easy-digital-downloads' ); ?>"><?php _e( 'Resend Receipt', 'easy-digital-downloads' ); ?></a>

										<?php do_action( 'edd_view_order_details_resend_receipt_after', $order->id ); ?>
									<?php endif; ?>
								</h3>

                                <div class="inside edd-clearfix">
                                    <div class="column-container customer-info">
                                        <div class="column">
											<?php if ( ! empty( $customer ) ) : ?>
                                                <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $order->customer_id ); ?>"><?php echo esc_html( $customer->name ); ?>
                                                    - <?php echo $customer->email; ?></a>
												<input type="hidden" name="edd-current-customer"
													   value="<?php echo esc_attr( $order->customer_id ); ?>"/>
											<?php else : ?>
												&mdash;
											<?php endif; ?>
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
												'selected'    => $order->customer_id,
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
                                            <strong><?php _e( 'Name', 'easy-digital-downloads' ); ?>:</strong>
                                            <input type="text" name="edd-new-customer-name" value="" class="medium-text"/>
                                        </div>

                                        <div class="column">
                                            <strong><?php _e( 'Email', 'easy-digital-downloads' ); ?>:</strong>
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
									do_action( 'edd_payment_personal_details_list', edd_get_order_meta( $order->id ), $user_info );
									do_action( 'edd_payment_view_details', $order->id );
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

									<?php do_action( 'edd_payment_billing_details', $order->id ); ?>
                                </div><!-- /.inside -->
                            </div><!-- /#edd-billing-details -->

							<?php do_action( 'edd_view_order_details_billing_after', $order->id ); ?>

                            <div id="edd-notes" class="postbox">
                                <h3 class="hndle"><span><?php _e( 'Order Notes', 'easy-digital-downloads' ); ?></span>
                                </h3>
                                <div class="inside">
									<?php echo edd_admin_get_notes_html( $notes ); ?>
									<?php echo edd_admin_get_new_note_form( $order->id, 'order' ); ?>
                                </div><!-- /.inside -->
                            </div><!-- /#edd-notes -->

							<?php do_action( 'edd_view_order_details_main_after', $order->id ); ?>
                        </div><!-- /#normal-sortables -->
                    </div><!-- #postbox-container-2 -->
                </div><!-- /#post-body -->
            </div><!-- #edd-dashboard-widgets-wrap -->
        </div><!-- /#post-stuff -->

		<?php do_action( 'edd_view_order_details_form_bottom', $order->id ); ?>

		<?php wp_nonce_field( 'edd_update_payment_details_nonce' ); ?>
        <input type="hidden" name="edd_payment_id" value="<?php echo esc_attr( $order->id ); ?>"/>
        <input type="hidden" name="edd_action" value="update_payment_details"/>
    </form>
	<?php do_action( 'edd_view_order_details_after', $order->id ); ?>
</div><!-- /.wrap -->

<div id="edd-download-link"></div>
