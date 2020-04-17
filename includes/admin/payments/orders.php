<?php
/**
 * Order Details/Add New Order Sections
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Publishing ******************************************************************/

/**
 * Outputs publishing actions.
 *
 * UI is modelled off block-editor header region.
 *
 * @since 3.0
 *
 * @param EDD\Orders\Order $order Current order.
 */
function edd_order_details_publish( $order ) {
	$action_name = edd_is_add_order_page()
		? __( 'Create Order', 'easy-digital-downloads' )
		: __( 'Save Order', 'easy-digital-downloads' )
?>

	<div class="edit-post-editor-regions__header">
		<div class="edit-post-header">

			<div>
				<?php if ( ! edd_is_add_order_page() ) : ?>
				<div id="delete-action">
					<a href="<?php echo wp_nonce_url( add_query_arg( array(
						'edd-action'  => 'delete_payment',
						'purchase_id' => $order->id,
					), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ), 'edd_payment_nonce' ) ?>"
							class="edd-delete-payment edd-delete"><?php esc_html_e( 'Delete Order', 'easy-digital-downloads' ); ?></a>
				</div>
				<?php endif; ?>
			</div>

			<div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input type="submit" id="edd-order-submit" class="button button-primary right" value="<?php echo esc_html( $action_name ); ?>"/>
				</div>
			</div>

		</div>

	</div>

<?php
}

/** Sections ******************************************************************/

/**
 * Contains code to setup tabs & views using EDD\Admin\Order_Sections().
 *
 * @since 3.0
 *
 * @param mixed $item
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
 * Return the order details sections.
 *
 * @since 3.0
 *
 * @param object $order
 * @return array Sections.
 */
function edd_get_order_details_sections( $order ) {
	$sections = array(
		array(
			'id'       => 'customer',
			'label'    => __( 'Customer', 'easy-digital-downloads' ),
			'icon'     => 'businessman',
			'callback' => 'edd_order_details_customer',
		),
		array(
			'id'       => 'email',
			'label'    => __( 'Email', 'easy-digital-downloads' ),
			'icon'     => 'email',
			'callback' => 'edd_order_details_email',
		),
		array(
			'id'       => 'address',
			'label'    => __( 'Address', 'easy-digital-downloads' ),
			'icon'     => 'admin-home',
			'callback' => 'edd_order_details_addresses',
		),
		array(
			'id'       => 'notes',
			'label'    => __( 'Notes', 'easy-digital-downloads' ),
			'icon'     => 'admin-comments',
			'callback' => 'edd_order_details_notes',
		),
		array(
			'id'       => 'logs',
			'label'    => __( 'Logs', 'easy-digital-downloads' ),
			'icon'     => 'admin-tools',
			'callback' => 'edd_order_details_logs',
		),
	);

	// Override sections if adding a new order.
	if ( edd_is_add_order_page() ) {
		$sections = array(
			array(
				'id'       => 'customer',
				'label'    => __( 'Customer', 'easy-digital-downloads' ),
				'icon'     => 'businessman',
				'callback' => 'edd_order_details_customer',
			),
			array(
				'id'       => 'address',
				'label'    => __( 'Address', 'easy-digital-downloads' ),
				'icon'     => 'admin-home',
				'callback' => 'edd_order_details_addresses',
			),
		);
	}

	/**
	 * Filter the sections.
	 *
	 * @since 3.0
	 *
	 * @param array  $sections Sections.
	 * @param object $order    Order object.
	 */
	return (array) apply_filters( 'edd_get_order_details_sections', $sections, $order );
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
	$user_info = $payment
		? $payment->user_info
		: array();

	$change_text = edd_is_add_order_page()
		? esc_html__( 'Assign', 'easy-digital-downloads' )
		: esc_html__( 'Switch Customer', 'easy-digital-downloads' );

	$customer_id = ! empty( $customer )
		? $customer->id
		: 0; ?>

	<div>
		<div class="column-container order-customer-info">
			<div class="column-container change-customer">
				<label for="customer_id" class="edd-order-details-label-mobile"><?php esc_html_e( 'Assign to an existing customer', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->customer_dropdown( array(
					'class'         => 'edd-payment-change-customer-input',
					'selected'      => $customer_id,
					'id'            => 'customer-id',
					'name'          => 'customer-id',
					'none_selected' => esc_html__( 'Search for a customer', 'easy-digital-downloads' ),
					'placeholder'   => esc_html__( 'Search for a customer', 'easy-digital-downloads' ),
				) ); // WPCS: XSS ok.
				?>
				<input type="hidden" name="current-customer-id" value="<?php echo esc_attr( $customer_id ); ?>" />
				<?php wp_nonce_field( 'edd_customer_details_nonce', 'edd_customer_details_nonce' ); ?>
			</div>

			<div class="customer-details-wrap" style="display: <?php echo esc_attr( ! empty( $customer ) ? 'flex' : 'none' ); ?>">
				<div class="avatar-wrap" id="customer-avatar">
					<span class="spinner is-active"></span>
				</div>
				<div class="customer-details" style="display: none;">
					<strong class="customer-name"></strong>
					<em class="customer-since">
						<?php
						echo wp_kses(
							sprintf(
								__( 'Customer since %s', 'easy-digital-downloads' ), '<span>&hellip;</span>' ),
							array(
								'span' => true,
							)
						);
						?>
					</em>

					<span class="customer-record">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-customers' ) ); ?>"><?php esc_html_e( 'View customer record', 'easy-digital-downloads' ); ?></a>
					</span>
				</div>
			</div>

			<p class="description">
				or <button class="edd-payment-new-customer button-link"><?php esc_html_e( 'create a new customer', 'easy-digital-downloads' ); ?></button>
			</p>
		</div>

		<div class="column-container new-customer" style="display: none">
			<p style="margin-top: 0;">
				<input type="hidden" id="edd-new-customer" name="edd-new-customer" value="0" />
				<button class="edd-payment-new-customer-cancel button-link"><?php esc_html_e( '&larr; Use an existing customer', 'easy-digital-downloads' ); ?></button>
			</p>

			<p>
				<label for="edd-new-customer-first-name"><?php esc_html_e( 'First Name', 'easy-digital-downloads' ); ?>:</label>
				<input type="text" name="edd-new-customer-first-name" id="edd-new-customer-first-name" value="" class="medium-text"/>
			</p>

			<p>
				<label for="edd-new-customer-last-name"><?php esc_html_e( 'Last Name', 'easy-digital-downloads' ); ?>:</label>
				<input type="text" name="edd-new-customer-last-name" id="edd-new-customer-last-name" value="" class="medium-text"/>
			</p>

			<p>
				<label for="edd-new-customer-email"><?php esc_html_e( 'Email', 'easy-digital-downloads' ); ?>:</label>
				<input type="email" name="edd-new-customer-email" id="edd-new-customer-email" value="" class="medium-text"/>
			</p>
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
	$customer   = edd_get_customer( $order->customer_id );
	$all_emails = array( 'primary' => $customer->email );

	foreach ( $customer->emails as $key => $email ) {
		if ( $customer->email === $email ) {
			continue;
		}

		$all_emails[ $key ] = $email;
	}
?>

	<div><?php
		if ( ! empty( $customer->emails ) && count( (array) $customer->emails ) > 1 ) : ?>
			<fieldset class="edd-order-resend-email-chooser">
				<legend>
					<?php _e( 'Send email receipt to', 'easy-digital-downloads' ); ?>
				</legend>

				<?php foreach ( $all_emails as $key => $email ) : ?>
				<p>
					<label for="<?php echo rawurlencode( sanitize_email( $email ) ); ?>">
						<input autocomplete="off" id="<?php echo rawurlencode( sanitize_email( $email ) ); ?>" class="edd-order-resend-receipt-email" name="edd-order-resend-receipt-address" type="radio" value="<?php echo rawurlencode( sanitize_email( $email ) ); ?>" <?php checked( true, ( 'primary' === $key ) ); ?> />
						<?php echo esc_attr( $email ); ?>
					</label>
				</p>
				<?php endforeach; ?>
			</fieldset>

		<?php else : ?>

			<input readonly type="email" value="<?php echo esc_attr( $order->email ); ?>" />

		<?php endif; ?>

		<p class="description"><?php esc_html_e( 'Send a new copy of the purchase receipt to the email address used for this order. If download URLs were included in the original receipt, new ones will be included.', 'easy-digital-downloads' ); ?></p>

		<a href="<?php echo esc_url( add_query_arg( array(
			'edd-action'  => 'email_links',
			'purchase_id' => $order->id,
		) ) ); ?>" id="<?php if ( ! empty( $customer->emails ) && count( (array) $customer->emails ) > 1 ) {
			echo esc_attr( 'edd-select-receipt-email' );
		} else {
			echo esc_attr( 'edd-resend-receipt' );
		} ?>" class="button-secondary"><?php esc_html_e( 'Resend Receipt', 'easy-digital-downloads' ); ?></a>

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
	$address = edd_is_add_order_page()
		? (object) array(
			'id'          => 0,
			'order_id'    => 0,
			'first_name'  => '',
			'last_name'   => '',
			'address'     => '',
			'address2'    => '',
			'city'        => '',
			'region'      => '',
			'postal_code' => '',
			'country'     => '',
		)
		: $order->get_address(); ?>

	<div id="edd-order-address">
		<?php do_action( 'edd_view_order_details_billing_before', $order->id ); ?>

		<div class="order-data-address">
			<h3><?php esc_html_e( 'Billing Address', 'easy-digital-downloads' ); ?></h3>

			<div class="input-wrap customer-address-select-wrap">
			</div>

			<div class="input-wrap">
				<label for="edd_order_address[address]"><?php esc_html_e( 'Line 1:', 'easy-digital-downloads' ); ?></label>
				<input type="text" name="edd_order_address[address]" id="edd_order_address[address]" value="<?php echo esc_attr( $address->address ); ?>" />
			</div>

			<div class="input-wrap">
				<label for="edd_order_address[address2]"><?php esc_html_e( 'Line 2:', 'easy-digital-downloads' ); ?></label>
				<input type="text" name="edd_order_address[address2]" id="edd_order_address[address2]" value="<?php echo esc_attr( $address->address2 ); ?>" />
			</div>

			<div class="input-wrap">
				<label for="edd_order_address[city]"><?php echo esc_html_x( 'City:', 'Address City', 'easy-digital-downloads' ); ?></label>
				<input type="text" name="edd_order_address[city]" id="edd_order_address[city]" value="<?php echo esc_attr( $address->city ); ?>" />
			</div>

			<div class="input-wrap">
				<label for="edd_order_address[postal_code]"><?php echo esc_html_x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'easy-digital-downloads' ); ?></label>
				<input type="text" name="edd_order_address[postal_code]" id="edd_order_address[postal_code]" value="<?php echo esc_attr( $address->postal_code ); ?>" class="med-text" />
			</div>

			<div class="input-wrap">
				<label for="edd_order_address_country"><?php echo esc_html_x( 'Country:', 'Address country', 'easy-digital-downloads' ); ?></label>
				<div id="edd-order-address-country-wrap">
					<?php
					echo EDD()->html->select( array(
						'options'          => edd_get_country_list(),
						'name'             => 'edd_order_address[country]',
						'id'               => 'edd-order-address-country',
						'class'            => 'edd-order-address-country',
						'selected'         => esc_attr( $address->country ),
						'show_option_all'  => false,
						'show_option_none' => false,
						'chosen'           => true,
						'placeholder'      => esc_html__( 'Select a country', 'easy-digital-downloads' ),
						'data'             => array(
							'nonce'              => wp_create_nonce( 'edd-country-field-nonce' ),
							'search-type'        => 'no_ajax',
							'search-placeholder' => esc_html__( 'Search Countries', 'easy-digital-downloads' ),
						),
					) ); // WPCS: XSS ok.
					?>
				</div>
			</div>

			<div class="input-wrap">
				<label for="edd_order_address_region"><?php echo esc_html_x( 'Region:', 'Region of address', 'easy-digital-downloads' ); ?></label>
				<div id="edd-order-address-state-wrap">
					<?php
					$states = edd_get_shop_states( $address->country );
					if ( ! empty( $states ) ) {
						echo EDD()->html->select( array(
							'options'          => $states,
							'name'             => 'edd_order_address[region]',
							'id'               => 'edd-order-address-region',
							'class'            => 'edd-order-address-region',
							'selected'         => esc_attr( $address->region ),
							'show_option_all'  => false,
							'show_option_none' => false,
							'chosen'           => true,
							'placeholder'      => esc_html__( 'Select a region', 'easy-digital-downloads' ),
							'data'             => array(
								'search-type'        => 'no_ajax',
								'search-placeholder' => esc_html__( 'Search Regions', 'easy-digital-downloads' ),
							),
						) ); // WPCS: XSS ok.
					} else { ?>
						<input type="text" name="edd_order_address[region]" value="<?php echo esc_attr( $address->region ); ?>" />
						<?php
					} ?>
				</div>
			</div>

			<input type="hidden" name="edd_order_address[address_id]" value="<?php echo esc_attr( $address->id ); ?>" />
		</div>

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
		<?php echo edd_admin_get_notes_html( $notes ); // WPCS: XSS ok. ?>
		<?php echo edd_admin_get_new_note_form( $order->id, 'order' ); // WPCS: XSS ok. ?>
	</div>

	<?php
}

/**
 * Outputs the Order Details logs section.
 *
 * @since 3.0
 *
 * @param \EDD\Orders\Order $order
 */
function edd_order_details_logs( $order ) {
?>

	<div>
		<?php
		/**
		 * Allows output before the list of logs.
		 *
		 * @since 3.0.0
		 *
		 * @param int $order_id ID of the current order.
		 */
		do_action( 'edd_view_order_details_logs_before', $order->id );
		?>

		<p><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=logs&payment=' . $order->id ); ?>"><?php esc_html_e( 'File Download Log for Order', 'easy-digital-downloads' ); ?></a></p>
		<p><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=logs&customer=' . $order->customer_id ); ?>"><?php esc_html_e( 'Customer Download Log', 'easy-digital-downloads' ); ?></a></p>
		<p><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . esc_attr( edd_get_payment_user_email( $order->id ) ) ); ?>"><?php esc_html_e( 'Customer Orders', 'easy-digital-downloads' ); ?></a></p>

		<?php
		/**
		 * Allows further output after the list of logs.
		 *
		 * @since 3.0.0
		 *
		 * @param int $order_id ID of the current order.
		 */
		do_action( 'edd_view_order_details_logs_after', $order->id );
		?>
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
	if ( ! class_exists( '\\EDD\\Admin\\Order_Items_Table' ) ) {
		require_once 'class-order-items-table.php';
	}

	// Query for items
	$order_items = new EDD\Admin\Order_Items_Table();
	$order_items->prepare_items(); ?>

	<div id="edd-order-items" class="postbox edd-edit-purchase-element">
		<h3 class="hndle">
			<span><?php _e( 'Order Items', 'easy-digital-downloads' ); ?></span>

			<?php if ( edd_is_add_order_page() && current_user_can( 'edit_shop_payments' ) ) : ?>
				<label class="edd-toggle">
					<span class="label"><?php esc_html_e( 'Manually adjust amounts', 'easy-digital-downloads' ); ?></span>
					<input type="checkbox" id="edd-override-amounts" />
				</label>
			<?php endif; ?>
		</h3>

		<div class="edd-order-children-wrapper <?php echo 'child-count-' . count( $order_items->items ); ?>">
			<?php $order_items->display(); ?>
		</div>

		<?php if ( edd_is_add_order_page() ) : ?>
			<div class="edd-add-download-to-purchase">
				<ul>
					<li class="download">
						<label for="edd_order_add_download_select_chosen" class="edd-order-details-label-mobile"><?php printf( esc_html_x( '%s To Add', 'order details select item to add - mobile', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>

						<?php echo EDD()->html->product_dropdown( array(
							'name'                 => 'edd-order-add-download-select',
							'id'                   => 'edd-order-add-download-select',
							'class'                => 'edd-order-add-download-select',
							'chosen'               => true,
							'variations'           => true,
							'show_variations_only' => true,
							'number'               => 15,
						) ); // WPCS: XSS ok. ?>

						<?php if ( edd_item_quantities_enabled() ) : ?>
							&times;
							<input type="number" class="edd-add-order-quantity" value="1" step="1" min="1" name="quantity" />
						<?php endif; ?>

						<button type="button" class="button button-secondary edd-add-order-item-button"><?php esc_html_e( 'Add', 'easy-digital-downloads' ); ?></button>

						<span class="spinner"></span>
					</li>
				</ul>

				<input type="hidden" name="edd-payment-downloads-changed" id="edd-payment-downloads-changed" value="" />
				<input type="hidden" name="edd-payment-removed" id="edd-payment-removed" value="{}" />
				<input type="hidden" name="edd-order-download-is-overrideable" value="0" />

				<?php if ( ! edd_item_quantities_enabled() ) : ?>
					<input type="hidden" id="edd-order-download-quantity" name="edd-order-download-quantity" value="1" />
				<?php endif; ?>

				<?php if ( ! edd_use_taxes() ) : ?>
					<input type="hidden" id="edd-order-download-tax" name="edd-order-download-tax" value="0" />
				<?php endif; ?>
			</div>
		<?php endif; ?>
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
function edd_order_details_adjustments( $order ) {

	// Load list table if not already loaded
	if ( ! class_exists( '\\EDD\\Admin\\Order_Adjustments_Table' ) ) {
		require_once 'class-order-adjustments-table.php';
	}

	// Query for adjustments
	$order_adjustments = new EDD\Admin\Order_Adjustments_Table();
	$order_adjustments->prepare_items(); ?>

	<div id="edd-order-adjustments" class="postbox edd-edit-purchase-element">
		<h3 class="hndle">
			<span><?php esc_html_e( 'Order Adjustments', 'easy-digital-downloads' ); ?></span>
		</h3>

		<div class="edd-order-children-wrapper <?php echo 'child-count-' . count( $order_adjustments->items ); ?>">
			<?php $order_adjustments->display(); ?>
		</div>

		<?php if ( edd_is_add_order_page() ) : ?>
			<div class="edd-add-adjustment-to-purchase">
				<ul>
					<li class="adjustment">
						<label for="edd_order_add_adjustment_select" class="edd-order-details-label-mobile"><?php echo esc_html_x( 'Adjustment To Add', 'order details select adjustment to add - mobile', 'easy-digital-downloads' ); ?></label>

						<?php echo EDD()->html->select( array(
							'name'             => 'edd-order-add-adjustment-select',
							'id'               => 'edd-order-add-adjustment-select',
							'class'            => 'edd-order-add-adjustment-select',
							'options'          => array(
								''         => '', // Empty  option needed to display placeholder.
								'discount' => __( 'Discount', 'easy-digital-downloads' ),
								'credit'   => __( 'Credit', 'easy-digital-downloads' ),
							),
							'placeholder'      => __( 'Choose an Adjustment Type', 'easy-digital-downloads' ),
							'show_option_all'  => false,
							'show_option_none' => false,
							'chosen'           => true,
						) ); // WPCS: XSS ok. ?>
					</li>
				</ul>

				<ul>
					<li class="discount" style="display: none;">
						<?php
						$d = edd_get_discounts( array(
							'fields' => array( 'code', 'name' ),
							'number' => 100,
						) );

						$discounts = array();

						foreach ( $d as $discount_data ) {
							$discounts[ $discount_data->code ] = esc_html( $discount_data->name );
						}

						echo EDD()->html->discount_dropdown( array(
							'name'             => 'edd-order-add-discount-select',
							'class'            => 'edd-order-add-discount-select',
							'id'               => 'edd-order-add-discount-select',
							'chosen'           => true,
							'selected'         => false,
							'show_option_all'  => false,
							'show_option_none' => false,
						) );  // WPCS: XSS ok.
						?>
					</li>
				</ul>

				<ul>
					<li class="credit" style="display: none;">
						<input type="text" class="edd-add-order-credit-description" value="" placeholder="<?php echo esc_attr( 'Description', 'easy-digital-downloads' ); ?>" />
						<input type="text" class="edd-add-order-credit-amount" value="" min="1" placeholder="<?php echo esc_attr( 'Amount', 'easy-digital-downloads' ); ?>" />
					</li>
				</ul>

				<ul>
					<li class="add">
						<button type="button" class="button button-secondary edd-add-order-adjustment-button"><?php esc_html_e( 'Add', 'easy-digital-downloads' ); ?></button>

						<span class="spinner"></span>
					</li>
				</ul>
			</div>
		<?php endif; ?>
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
			<span><?php esc_html_e( 'Order Details', 'easy-digital-downloads' ); ?></span>
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
function edd_order_details_extras( $order = false ) {
	$transaction_id = ! empty( $order->id )
		? $order->get_transaction_id()
		: '';

	$unlimited = ! empty( $order->id )
		? $order->has_unlimited_downloads()
		: false;

	$readonly = ! empty( $order->id )
		? 'readonly'
		: '';

	// Setup gateway list.
	if ( empty( $order->id ) ) {
		$known_gateways = edd_get_payment_gateways();

		$gateways = array();

		foreach ( $known_gateways as $id => $data ) {
			$gateways[ $id ] = esc_html( $data['admin_label'] );
		}

		$gateway_select = EDD()->html->select( array(
			'name'             => 'gateway',
			'options'          => $gateways,
			'selected'         => edd_get_default_gateway(),
			'chosen'           => true,
			'show_option_none' => false,
			'show_option_all'  => false,
		) );
	}

	// Filter the transaction ID (here specifically for back-compat)
	if ( ! empty( $transaction_id ) ) {
		$transaction_id = apply_filters( 'edd_payment_details_transaction_id-' . $order->gateway, $transaction_id, $order->id );
	} ?>

	<div id="edd-order-extras" class="postbox edd-order-data">
		<h3 class="hndle">
			<span><?php esc_html_e( 'Order Extras', 'easy-digital-downloads' ); ?></span>
		</h3>

		<div class="inside">
			<div class="edd-admin-box">
				<?php do_action( 'edd_view_order_details_payment_meta_before', $order->id ); ?>

				<?php if ( ! edd_is_add_order_page() && ! empty( $order->id ) ) : ?>
					<?php if ( ! empty( $order->gateway ) ) : ?>
					<div class="edd-order-gateway edd-admin-box-inside edd-admin-box-inside--row">
						<span class="label"><?php esc_html_e( 'Gateway', 'easy-digital-downloads' ); ?></span>
						<span class="value"><?php echo edd_get_gateway_admin_label( $order->gateway ); ?></span>
					</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="edd-order-gateway edd-admin-box-inside">
						<span class="label"><?php esc_html_e( 'Gateway', 'easy-digital-downloads' ); ?></span>
						<?php echo $gateway_select; ?>
					</div>
				<?php endif; ?>

				<div class="edd-order-payment-key edd-admin-box-inside">
					<label for="edd_payment_key" class="label"><?php esc_html_e( 'Key', 'easy-digital-downloads' ); ?></label>
					<input type="text" name="edd_payment_key" id="edd_payment_key" <?php echo esc_attr( $readonly ); ?> value="<?php echo esc_attr( $order->payment_key ); ?>" />
				</div>

				<div class="edd-order-ip edd-admin-box-inside">
					<label for="edd_ip" class="label"><?php esc_html_e( 'IP', 'easy-digital-downloads' ); ?></label>
					<?php if ( edd_is_add_order_page() ) : ?>
						<input type="text" name="edd_ip" id="edd_ip" value="<?php echo esc_attr( edd_get_ip() ); ?>" />
					<?php else : ?>
						<span><?php echo edd_payment_get_ip_address_url( $order->id ); // WPCS: XSS ok. ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $transaction_id ) : ?>
					<div class="edd-order-tx-id edd-admin-box-inside">
						<span class="label"><?php esc_html_e( 'Transaction ID', 'easy-digital-downloads' ); ?></span>
						<span><?php echo $transaction_id; ?></span>
					</div>
				<?php endif; ?>

				<?php if ( edd_is_add_order_page() ) : ?>
					<div class="edd-order-tx-id edd-admin-box-inside">
						<label for="edd_transaction_id" class="label"><?php esc_html_e( 'Transaction ID', 'easy-digital-downloads' ); ?></label>
						<input type="text" name="edd_transaction_id" id="edd_transaction_id" value="" />
					</div>
				<?php endif; ?>

				<div class="edd-unlimited-downloads edd-admin-box-inside">
					<label for="edd_unlimited_downloads" class="label label--has-tip label--has-checkbox">
						<input type="checkbox" name="edd-unlimited-downloads" id="edd_unlimited_downloads" value="1"<?php checked( true, $unlimited, true ); ?>/>
						<?php esc_html_e( 'Unlimited Downloads', 'easy-digital-downloads' ); ?>
						<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Unlimited Downloads</strong>: checking this box will override all other file download limits for this purchase, granting the customer unliimited downloads of all files included on the purchase.', 'easy-digital-downloads' ); ?>"></span>
					</label>
				</div>

				<?php if ( edd_is_add_order_page() ) : ?>
					<div class="edd-send-purchase-receipt edd-admin-box-inside">
						<label class="description label label--has-tip label--has-checkbox" for="edd-order-send-receipt">
							<input type="checkbox" name="edd_order_send_receipt" id="edd-order-send-receipt" value="1" />
							<?php esc_html_e( 'Send Receipt', 'easy-digital-downloads' ); ?>
							<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Send Receipt</strong>: checking this box will send the purchase receipt to the selected customer.', 'easy-digital-downloads' ); ?>"></span>
						</label>
					</div>
				<?php endif; ?>

				<?php do_action( 'edd_view_order_details_payment_meta_after', $order->id ); ?>
			</div>
		</div>
	</div>

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

	$rtl_class = is_rtl()
		? ' chosen-rtl'
		: '';

	$recovery_url = edd_is_add_order_page()
		? ''
		: edd_get_payment( $order->id )->get_recovery_url();

	$order_date = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $order->date_created, 'utc', true ) );

	?>

	<div id="edd-order-update" class="postbox edd-order-data">
		<h3 class="hndle">
			<span><?php esc_html_e( 'Order Attributes', 'easy-digital-downloads' ); ?></span>
		</h3>

		<div class="inside">
			<div class="edd-order-update-box edd-admin-box">
				<div class="edd-admin-box-inside">
					<label for="edd-payment-status" class="label label--has-tip">
						<?php
						esc_html_e( 'Status', 'easy-digital-downloads' );

						$status_help  = '<ul>';
						$status_help .= '<li>' . __( '<strong>Pending</strong>: order is still processing or was abandoned by customer. Successful orders will be marked as Complete automatically once processing is finalized.', 'easy-digital-downloads' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'easy-digital-downloads' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'easy-digital-downloads' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'easy-digital-downloads' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'easy-digital-downloads' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'easy-digital-downloads' ) . '</li>';
						$status_help .= '</ul>';
						?>
						<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo $status_help; // WPCS: XSS ok. ?>"></span>
					</label>
					<select name="edd-payment-status" id="edd-payment-status" class="edd-select-chosen <?php echo esc_attr( $rtl_class ); ?>">
						<?php foreach ( edd_get_payment_statuses() as $key => $status ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $order->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<?php if ( ! edd_is_add_order_page() && edd_is_order_recoverable( $order->id ) && ! empty( $recovery_url ) ) : ?>
					<div class="edd-admin-box-inside">
						<span class="label label--has-tip">
							<?php esc_html_e( 'Recover', 'easy-digital-downloads' ); ?>
							<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php esc_html_e( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'easy-digital-downloads' ); ?>"></span>
						</span>

						<input type="text" readonly="readonly" value="<?php echo esc_url( $recovery_url ); ?>"/>
					</div>
				<?php endif; ?>

				<div class="edd-admin-box-inside">
					<label for="edd-payment-date" class="label">
						<?php esc_html_e( 'Date', 'easy-digital-downloads' ); ?>
					</label>
					<input type="text" id="edd-payment-date" name="edd-payment-date" value="<?php echo esc_attr( $order_date->format( 'Y-m-d' ) ); ?>" class="medium-text edd_datepicker" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>"/>

				</div>

				<div class="edd-admin-box-inside">
					<label for="edd_payment_time_hour" class="label">
						<?php echo esc_html( __( 'Time', 'easy-digital-downloads' ) . ' (' . edd_get_timezone_abbr() . ')' ); ?>
					</label>

					<label for="edd_payment_time_hour_chosen" class="screen-reader-text">Hour</label>
					<?php
					echo EDD()->html->select( array(
						'name'             => 'edd-payment-time-hour',
						'id'               => 'edd-payment-time-hour',
						'options'          => edd_get_hour_values(),
						'selected'         => $order_date->format( 'H' ),
						'chosen'           => true,
						'class'            => 'edd-time',
						'show_option_none' => false,
						'show_option_all'  => false,
					) ); // WPCS: XSS ok.
					?>
					:
					<label for="edd_payment_time_min" class="screen-reader-text">Minute</label>
					<?php
					echo EDD()->html->select( array(
						'name'             => 'edd-payment-time-min',
						'id'               => 'edd-payment-time-min',
						'options'          => edd_get_minute_values(),
						'selected'         => $order_date->format( 'i' ),
						'chosen'           => true,
						'class'            => 'edd-time',
						'show_option_none' => false,
						'show_option_all'  => false,
					) ); // WPCS: XSS ok.
					?>
				</div>

				<?php do_action( 'edd_view_order_details_update_inner', $order->id ); ?>

			</div><!-- /.edd-admin-box -->
		</div><!-- /.inside -->

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
		<h3 class="hndle"><span><?php esc_html_e( 'Order Amounts', 'easy-digital-downloads' ); ?></span></h3>

		<div class="inside">
			<div class="edd-order-update-box edd-admin-box">
				<?php do_action( 'edd_view_order_details_totals_before', $order->id ); ?>

				<div class="edd-order-subtotal edd-admin-box-inside edd-admin-box-inside--row">
					<strong class="label">
						<?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>
					</strong>
					<span class="value">
						<?php echo esc_attr( edd_currency_filter( edd_format_amount( $order->subtotal ) ) ); ?>
					</span>
				</div>

				<div class="edd-order-discounts edd-admin-box-inside edd-admin-box-inside--row">
					<strong class="label">
						<?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?>
					</strong>
					<span class="value">
						<?php echo esc_attr( edd_currency_filter( edd_format_amount( $order->discount ) ) ); ?>
					</span>
				</div>

				<div class="edd-order-adjustments edd-admin-box-inside edd-admin-box-inside--row">
					<strong class="label">
						<?php esc_html_e( 'Adjustments', 'easy-digital-downloads' ); ?>
					</strong>
					<span class="value">
						<?php echo esc_attr( edd_currency_filter( edd_format_amount( $order->discount ) ) ); ?>
					</span>
				</div>

				<?php if ( edd_use_taxes() ) : ?>
					<div class="edd-order-taxes edd-admin-box-inside edd-admin-box-inside--row">
						<strong class="label">
							<?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>
						</strong>
						<span class="value">
							<?php echo esc_attr( edd_currency_filter( edd_format_amount( $order->tax ) ) ); ?>
						</span>
					</div>
				<?php endif; ?>

				<div class="edd-order-total edd-admin-box-inside edd-admin-box-inside--row">
					<strong class="label">
						<?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?>
					</strong>
					<span class="value">
						<?php echo esc_attr( edd_currency_filter( edd_format_amount( $order->total ) ) ); ?>
					</span>
				</div>

				<?php do_action( 'edd_view_order_details_totals_after', $order->id ); ?>
			</div>
		</div>
	</div>

<?php
}

/**
 * Output the order details refunds box
 *
 * @since 3.0
 *
 * @param object $order
 */
function edd_order_details_refunds( $order ) {
	$refunds_db = new \EDD\Database\Queries\Order();

	$refunds = $refunds_db->query( array( 'type' => 'refund', 'parent' => $order->id ) );
	if ( empty( $refunds ) ) {
		return;
	}
	?>

	<div id="edd-order-refunds" class="postbox edd-order-data">
		<h3 class="hndle"><span><?php esc_html_e( 'Related Refunds', 'easy-digital-downloads' ); ?></span></h3>

		<div class="inside">
			<?php do_action( 'edd_view_order_details_refunds_before', $order->id ); ?>
			<ul id="edd-order-refunds-list">
			<?php foreach( $refunds as $refund ) : ?>
				<?php $order_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $refund->id ); ?>
				<li>
					<span class="howto"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $refund->completed_date ) ); ?></span>
					<a href="<?php echo esc_url( $order_url ); ?>">
						<?php echo '#' . $refund->number ?>
					</a>&nbsp;&ndash;&nbsp;
					<span><?php echo edd_currency_filter( edd_format_amount( $refund->total ) ); ?>&nbsp;&ndash;&nbsp;</span>
					<span><?php echo edd_get_status_label( $refund->status ); ?></span>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php do_action( 'edd_view_order_details_refunds_after', $order->id ); ?>
		</div>
	</div>

	<?php
}

/**
 * Check if we are on the `Add New Order` page, or editing an existing one.
 *
 * @since 3.0
 *
 * @return boolean True if on the `Add Order` page, false otherwise.
 */
function edd_is_add_order_page() {
	return isset( $_GET['view'] ) && 'add-order' === sanitize_key( $_GET['view'] ); // WPCS: CSRF ok.
}
