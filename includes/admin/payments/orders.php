<?php
/**
 * Order Details Sections
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Sections ******************************************************************/

/**
 * Contains code to setup tabs & views using EDD_Sections()
 *
 * @since 3.0
 */
function edd_order_sections( $item = false ) {

	// Instantiate the Sections class and sections array
	$sections = new EDD\Admin\Order_Sections();

	// Setup sections variables
	$sections->use_js          = true;
	$sections->current_section = 'customer';
	$sections->item            = $item;
	$sections->base_url        = '';

	// Get all registered tabs & views
	$o_sections = edd_get_order_details_sections( $item );

	// Set the customer sections
	$sections->set_sections( $o_sections );

	// Display the sections
	$sections->display();
}

/**
 * Return the order details sections
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_get_order_details_sections( $order ) {
	return (array) apply_filters( 'edd_get_order_details_sections', array(
		array(
			'id'       => 'customer',
			'label'    => __( 'Customer', 'easy-digital-downloads' ),
			'icon'     => 'businessman',
			'callback' => 'edd_order_details_customer'
		),
		array(
			'id'       => 'email',
			'label'    => __( 'Email', 'easy-digital-downloads' ),
			'icon'     => 'email',
			'callback' => 'edd_order_details_email'
		),
		array(
			'id'       => 'address',
			'label'    => __( 'Address', 'easy-digital-downloads' ),
			'icon'     => 'admin-home',
			'callback' => 'edd_order_details_addresses'
		),
		array(
			'id'       => 'notes',
			'label'    => __( 'Notes', 'easy-digital-downloads' ),
			'icon'     => 'admin-comments',
			'callback' => 'edd_order_details_notes'
		)
	), $order );
}

/**
 * Output the order details customer section
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_customer( $order ) {
	$customer  = edd_get_customer( $order->customer_id );
	$payment   = edd_get_payment( $order->id );
	$user_info = $payment->user_info;

	$customer_id = ! empty( $customer )
		? $customer->id
		: 0; ?>

	<div>
		<div class="column-container order-customer-info">
			<?php if ( ! empty( $customer ) ) : ?>
				<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $order->customer_id ); ?>"><?php echo esc_html( $customer->name ); ?></a>
				<input type="hidden" name="edd-current-customer"
					   value="<?php echo esc_attr( $order->customer_id ); ?>"/>
			<?php else : ?>
				&mdash;
			<?php endif; ?>

			<div style="clear: both;">
				<hr>
				<a href="#change"
				   class="edd-payment-change-customer"><?php _e( 'Reassign', 'easy-digital-downloads' ); ?></a>
				&nbsp;|&nbsp;
				<a href="#new"
				   class="edd-payment-new-customer"><?php _e( 'New', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>

		<div class="column-container change-customer" style="display: none">
			<?php
			echo EDD()->html->customer_dropdown( array(
				'class'       => 'edd-payment-change-customer-input',
				'selected'    => $customer_id,
				'name'        => 'customer-id',
				'placeholder' => __( 'Type to search all Customers', 'easy-digital-downloads' ),
			) );
			?>

			<input type="hidden" id="edd-change-customer" name="edd-change-customer" value="0" />
			<a href="#cancel" class="edd-payment-change-customer-cancel edd-delete"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
		</div>

		<div class="column-container new-customer" style="display: none">
			<strong><?php _e( 'Name', 'easy-digital-downloads' ); ?>:</strong>
			<input type="text" name="edd-new-customer-name" value="" class="medium-text"/>

			<strong><?php _e( 'Email', 'easy-digital-downloads' ); ?>:</strong>
			<input type="email" name="edd-new-customer-email" value="" class="medium-text"/>

			<input type="hidden" id="edd-new-customer" name="edd-new-customer" value="0" />
			<a href="#cancel" class="edd-payment-new-customer-cancel edd-delete"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
		</div>
	</div>

	<?php

	// The edd_payment_personal_details_list hook is left here for backwards compatibility
	do_action( 'edd_payment_personal_details_list', $user_info );
	do_action( 'edd_payment_view_details',          $order->id );
}

/**
 * Output the order details email section
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_email( $order ) {
	$customer = edd_get_customer( $order->customer_id ); ?>

	<div><?php
		if ( ! empty( $customer->emails ) && count( (array) $customer->emails ) > 1 ) : ?>

			<span class="edd-order-resend-receipt-addresses" style="display:none;">
				<select class="edd-order-resend-receipt-email">
					<option value=""><?php _e( ' -- select email --', 'easy-digital-downloads' ); ?></option>
					<?php foreach ( $customer->emails as $email ) : ?>
						<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo $email; ?></option>
					<?php endforeach; ?>
				</select>
			</span>

		<?php else : ?>

			<input readonly="true" value="<?php echo esc_attr( $order->email ); ?>" />

		<?php endif; ?>

		<a href="<?php echo esc_url( add_query_arg( array(
			'edd-action'  => 'email_links',
			'purchase_id' => $order->id,
		) ) ); ?>" id="<?php if ( ! empty( $customer->emails ) && count( (array) $customer->emails ) > 1 ) {
			echo 'edd-select-receipt-email';
		} else {
			echo 'edd-resend-receipt';
		} ?>" class="button-secondary"><?php _e( 'Resend Receipt', 'easy-digital-downloads' ); ?></a>

		<p class="description"><?php _e( 'Send a new copy of the purchase receipt to the email address used for this order. If download URLs were included in the original receipt, new ones will be included.', 'easy-digital-downloads' ); ?></p>

		<?php do_action( 'edd_view_order_details_resend_receipt_after', $order->id ); ?>

	</div>
	<?php
}

/**
 * Output the order details addresses section
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_addresses( $order ) {
	$address = $order->get_address(); ?>

	<div id="edd-order-address">
		<?php do_action( 'edd_view_order_details_billing_before', $order->id ); ?>

		<div class="order-data-address">
			<div>
				<p>
					<strong class="order-data-address-line"><?php _e( 'Billing Address Line 1:', 'easy-digital-downloads' ); ?></strong><br/>
					<input type="text" name="edd-payment-address[0][address]" value="<?php echo esc_attr( $address->address ); ?>" class="large-text" />
				</p>
				<p>
					<strong class="order-data-address-line"><?php _e( 'Billing Address Line 2:', 'easy-digital-downloads' ); ?></strong><br/>
					<input type="text" name="edd-payment-address[0][address2]" value="<?php echo esc_attr( $address->address2 ); ?>" class="large-text" />
				</p>
			</div>

			<div>
				<p>
					<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'easy-digital-downloads' ); ?></strong><br/>
					<input type="text" name="edd-payment-address[0][city]" value="<?php echo esc_attr( $address->city ); ?>" class="large-text" />
				</p>

				<p>
					<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'easy-digital-downloads' ); ?></strong><br/>
					<input type="text" name="edd-payment-address[0][postal_code]" value="<?php echo esc_attr( $address->postal_code ); ?>" class="large-text" />
				</p>
			</div>

			<div>
				<p id="edd-order-address-country-wrap">
					<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'easy-digital-downloads' ); ?></strong><br/>
					<?php
					echo EDD()->html->select( array(
						'options'          => edd_get_country_list(),
						'name'             => 'edd-payment-address[0][country]',
						'id'               => 'edd-payment-address-country',
						'selected'         => $address->country,
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
					$states = edd_get_shop_states( $address->country );
					if ( ! empty( $states ) ) {
						echo EDD()->html->select( array(
							'options'          => $states,
							'name'             => 'edd-payment-address[0][region]',
							'id'               => 'edd-payment-address-state',
							'selected'         => $address->region,
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
						<input type="text" name="edd-payment-address[0][region]" value="<?php echo esc_attr( $address->region ); ?>" class="large-text" />
						<?php
					} ?>
				</p>

				<input type="hidden" name="edd-payment-address[0][address_id]" value="<?php echo esc_attr( $address->id ); ?>" />
			</div>
		</div>

		<?php do_action( 'edd_view_order_details_billing_after', $order->id ); ?>
	</div><!-- /#edd-order-address -->

	<?php do_action( 'edd_payment_billing_details', $order->id );
}

/**
 * Output the order details notes section
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_notes( $order ) {
	$notes = edd_get_payment_notes( $order->id ); ?>

	<div>
		<?php echo edd_admin_get_notes_html( $notes ); ?>
		<?php echo edd_admin_get_new_note_form( $order->id, 'order' ); ?>
	</div>

	<?php
}

/** Main **********************************************************************/

/**
 * Output the order details items box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_items( $order ) {

	// Load list table if not already loaded
	if ( ! class_exists( 'EDD_Order_Item_Table' ) ) {
		require_once 'class-order-items-table.php';
	}

	// Query for items
	$order_items = new EDD_Order_Item_Table();
	$order_items->prepare_items(); ?>

	<div id="edd-order-items" class="postbox edd-edit-purchase-element">
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
		<div class="edd-order-children-wrapper <?php echo 'child-count-' . count( $order_items->items ); ?>">
			<?php $order_items->display(); ?>
		</div>
	</div>

	<?php do_action( 'edd_view_order_details_files_after', $order->id ); ?>

	<?php
}

/**
 * Output the order details adjustments box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_adjustments() {

	// Load list table if not already loaded
	if ( ! class_exists( 'EDD_Order_Adjustment_Table' ) ) {
		require_once 'class-order-adjustments-table.php';
	}

	// Query for adjustments
	$order_adjustments = new EDD_Order_Adjustment_Table();
	$order_adjustments->prepare_items(); ?>

	<div id="edd-order-adjustments" class="postbox edd-edit-purchase-element">
		<h3 class="hndle">
			<span><?php _e( 'Order Adjustments', 'easy-digital-downloads' ); ?></span>
			<a href="#" class="edd-metabox-title-action"><?php _e( 'Add Adjustment', 'easy-digital-downloads' ); ?></a>
		</h3>
		<div class="edd-order-children-wrapper <?php echo 'child-count-' . count( $order_adjustments->items ); ?>"">
			<?php $order_adjustments->display(); ?>
		</div>
	</div>

<?php
}

/**
 * Output the order details sections box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_sections( $order ) {
?>

	<div id="edd-customer-details" class="postbox">
		<h3 class="hndle">
			<span><?php _e( 'Order Details', 'easy-digital-downloads' ); ?></span>
		</h3>
		<?php edd_order_sections( $order ); ?>
	</div>

<?php
}

/** Sidebar *******************************************************************/

/**
 * Output the order details extras box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_extras( $order ) {
	$transaction_id  = $order->get_transaction_id();
	$unlimited       = $order->has_unlimited_downloads();

	// Filter the transaction ID (here specifically for back-compat)
	if ( ! empty( $transaction_id ) ) {
		$transaction_id = apply_filters( 'edd_payment_details_transaction_id-' . $order->gateway, $transaction_id, $order->id );
	} ?>

	<div id="edd-order-extras" class="postbox edd-order-data">
		<h3 class="hndle">
			<span><?php _e( 'Order Extras', 'easy-digital-downloads' ); ?></span>
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
					<input type="text" readonly value="<?php echo esc_attr( $order->payment_key ); ?>" />
				</div>

				<div class="edd-order-ip edd-admin-box-inside">
					<span class="label"><?php _e( 'IP', 'easy-digital-downloads' ); ?>:</span>
					<span><?php echo edd_payment_get_ip_address_url( $order->id ); ?></span>
				</div>

				<?php if ( $transaction_id ) : ?>
					<div class="edd-order-tx-id edd-admin-box-inside">
						<span class="label"><?php _e( 'Transaction ID', 'easy-digital-downloads' ); ?>:</span>
						<span><?php echo esc_html( $transaction_id ); ?></span>
					</div>
				<?php endif; ?>

				<div class="edd-unlimited-downloads edd-admin-box-inside">
					<span class="label"><?php _e( 'Downloads', 'easy-digital-downloads' ); ?>:</span>
					<input type="checkbox" name="edd-unlimited-downloads" id="edd_unlimited_downloads" value="1"<?php checked( true, $unlimited, true ); ?>/>
					<label class="description" for="edd_unlimited_downloads"><?php _e( 'Unlimited', 'easy-digital-downloads' ); ?></label>
					<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Unlimited Downloads</strong>: checking this box will override all other file download limits for this purchase, granting the customer unliimited downloads of all files included on the purchase.', 'easy-digital-downloads' ); ?>"></span>
				</div>

				<?php do_action( 'edd_view_order_details_payment_meta_after', $order->id ); ?>
			</div>
		</div>
	</div>

<?php
}

/**
 * Output the order details logs box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_logs( $order ) {
	?>

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

	<?php
}

/**
 * Output the order details attributes box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_attributes( $order ) {
	$recovery_url = edd_get_payment( $order->id )->get_recovery_url();
	$order_date   = strtotime( $order->date_created ); ?>

	<div id="edd-order-update" class="postbox edd-order-data">
		<h3 class="hndle">
			<span><?php _e( 'Order Attributes', 'easy-digital-downloads' ); ?></span>
		</h3>

		<div class="inside">
			<div class="edd-order-update-box edd-admin-box">
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

				<?php if ( edd_is_order_recoverable( $order->id ) && ! empty( $recovery_url ) ) : ?>
					<div class="edd-admin-box-inside">
						<span class="label"><?php _e( 'Recover', 'easy-digital-downloads' ); ?>:</span>
						<input type="text" readonly="readonly"
							   value="<?php echo esc_url( $recovery_url ); ?>"/>
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

		</div>
	</div>

<?php
}

/**
 * Output the order details amounts box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_amounts( $order ) {
?>

	<div id="edd-order-amounts" class="postbox edd-order-data">
		<h3 class="hndle">
			<span><?php _e( 'Order Amounts', 'easy-digital-downloads' ); ?></span>
			<a href="" id="edd-order-recalc-total" class="edd-metabox-title-action"><?php _e( 'Recalculate', 'easy-digital-downloads' ); ?></a>
		</h3>

		<div class="inside">
			<div class="edd-order-update-box edd-admin-box">
				<?php do_action( 'edd_view_order_details_totals_before', $order->id ); ?>

				<div class="edd-order-subtotal edd-admin-box-inside">
					<span class="label"><?php _e( 'Subtotal', 'easy-digital-downloads' ); ?>:</span><?php
					echo esc_html( edd_currency_symbol( $order->currency ) );
					?><span class="value"><?php echo esc_attr( edd_format_amount( $order->subtotal ) ); ?></span>
				</div>

				<?php if ( edd_use_taxes() ) : ?>
					<div class="edd-order-taxes edd-admin-box-inside">
						<span class="label"><?php _e( 'Tax', 'easy-digital-downloads' ); ?>:</span><?php
						echo esc_html( edd_currency_symbol( $order->currency ) );
						?><span class="value"><?php echo esc_attr( edd_format_amount( $order->tax ) ); ?></span>
					</div>
				<?php endif; ?>

				<div class="edd-order-discounts edd-admin-box-inside">
					<span class="label"><?php _e( 'Discount', 'easy-digital-downloads' ); ?>:</span><?php
					echo esc_html( edd_currency_symbol( $order->currency ) );
					?><span class="value"><?php echo esc_attr( edd_format_amount( $order->discount ) ); ?></span>
				</div>

				<div class="edd-order-total edd-admin-box-inside">
					<span class="label"><?php _e( 'Total', 'easy-digital-downloads' ); ?>:</span><?php
					echo esc_html( edd_currency_symbol( $order->currency ) );
					?><span class="value"><?php echo esc_attr( edd_format_amount( $order->total ) ); ?></span>
				</div>

				<?php do_action( 'edd_view_order_details_totals_after', $order->id ); ?>
			</div>
		</div>
	</div>

<?php
}
