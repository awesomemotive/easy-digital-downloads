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

			<div class="edit-post-header__settings">
				<?php if ( edd_is_add_order_page() ) : ?>
					<div class="edd-send-purchase-receipt">
						<div class="edd-form-group">
							<div class="edd-form-group__control">
								<input type="checkbox" name="edd_order_send_receipt" id="edd-order-send-receipt" class="edd-form-group__input" value="1" checked />

								<label for="edd-order-send-receipt">
								<?php esc_html_e( 'Send Purchase Receipt', 'easy-digital-downloads' ); ?>
								</label>
								<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Checking this box will email the purchase receipt to the selected customer.', 'easy-digital-downloads' ); ?>"></span>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div id="publishing-action">
					<span class="spinner"></span>
					<input
						type="submit"
						id="edd-order-submit"
						class="button button-primary right"
						value="<?php echo esc_html( $action_name ); ?>"
						<?php if ( ! edd_is_add_order_page() ) : ?>
							autofocus
						<?php endif; ?>
					/>
				</div>
			</div>

			<div class="edit-post-header__toolbar">
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
				<div class="edd-form-group">
					<label for="customer_id" class="edd-form-group__label"><?php esc_html_e( 'Assign to an existing customer', 'easy-digital-downloads' ); ?></label>
					<div class="edd-form-group__control">
						<?php
						echo EDD()->html->customer_dropdown(
							array(
								'class'         => 'edd-payment-change-customer-input edd-form-group__input',
								'selected'      => $customer_id,
								'id'            => 'customer-id',
								'name'          => 'customer-id',
								'none_selected' => esc_html__( 'Search for a customer', 'easy-digital-downloads' ),
								'placeholder'   => esc_html__( 'Search for a customer', 'easy-digital-downloads' ),
							)
						); // WPCS: XSS ok.
						?>
					</div>
				</div>

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
						<a href="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-customers' ) ) ); ?>"><?php esc_html_e( 'View customer record', 'easy-digital-downloads' ); ?></a>
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

			<div class="edd-form-group">
				<label class="edd-form-group__label" for="edd_new_customer_first_name">
					<?php esc_html_e( 'First Name', 'easy-digital-downloads' ); ?>
				</label>

				<div class="edd-form-group__control">
					<input type="text" id="edd_new_customer_first_name" name="edd-new-customer-first-name" value="" class="edd-form-group__input regular-text" />
				</div>
			</div>

			<div class="edd-form-group">
				<label class="edd-form-group__label" for="edd_new_customer_last_name">
					<?php esc_html_e( 'Last Name', 'easy-digital-downloads' ); ?>
				</label>

				<div class="edd-form-group__control">
					<input type="text" id="edd_new_customer_last_name" name="edd-new-customer-last-name" value="" class="edd-form-group__input regular-text" />
				</div>
			</div>

			<div class="edd-form-group">
				<label class="edd-form-group__label" for="edd_new_customer_email">
					<?php esc_html_e( 'Email', 'easy-digital-downloads' ); ?>
				</label>

				<div class="edd-form-group__control">
					<input type="email" id="edd_new_customer_email" name="edd-new-customer-email" value="" class="edd-form-group__input regular-text" />
				</div>
			</div>
		</div>
	</div>

	<?php

	// The edd_payment_personal_details_list hook is left here for backwards compatibility
	if ( ! edd_is_add_order_page() && $payment instanceof EDD_Payment ) {
		do_action( 'edd_payment_personal_details_list', $payment->get_meta(), $user_info );
	}
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

	if ( $customer->email !== $order->email ) {
		$all_emails['order'] = $order->email;
	}

	foreach ( $customer->emails as $key => $email ) {
		if ( $customer->email === $email ) {
			continue;
		}

		$all_emails[ $key ] = $email;
	}

	$help = sprintf(
		/* translators: email type */
		__('Send a new copy of the purchase receipt to the %s email address. If download URLs were included in the original receipt, new ones will be included.', 'easy-digital-downloads' ),
		count( $all_emails ) > 1 ? __( 'selected', 'easy-digital-downloads' ) : __( 'customer', 'easy-digital-downloads' )
	);

	$is_multiselect = count( $all_emails ) > 1;
	$label_text     = $is_multiselect ? __( 'Send email receipt to', 'easy-digital-downloads' ) : __( 'Email Address', 'easy-digital-downloads' );
?>

	<div>
		<div class="edd-form-group">
			<label class="edd-form-group__label <?php echo esc_attr( ! $is_multiselect ? 'screen-reader-text' : '' ); ?>"
				for="edd-order-receipt-email"
			>
				<?php echo esc_html( $label_text ); ?>
			</label>

			<div class="edd-form-group__control">
				<?php if ( $is_multiselect ) : ?>
					<select class="edd-form-group__input edd-order-resend-receipt-email" name="edd-select-receipt-email" id="edd-order-receipt-email">
						<?php foreach ( $all_emails as $key => $email ) : ?>
						<option
							value="<?php echo rawurlencode( sanitize_email( $email ) ); ?>"
							<?php selected( 'primary', $key ); ?>
						>
							<?php
							echo sprintf(
								'%1$s (%2$s)',
								esc_attr( $email ),
								esc_html(
									$key === 'primary' ? __( 'Customer Primary', 'easy-digital-downloads' ) :
										(
											$key === 'order' ? __( 'Order Email', 'easy-digital-downloads' ) :
											__( 'Customer Email', 'easy-digital-downloads' )
										)
								)
							);
							?>
						</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input readonly type="email" id="edd-order-receipt-email" class="edd-form-group__input regular-text" value="<?php echo esc_attr( $order->email ); ?>" />
				<?php endif; ?>
			</div>

			<p class="edd-form-group__help description">
				<?php echo esc_html( $help ); ?>
			</p>
		</div>

		<p>
			<a href="<?php echo esc_url( add_query_arg( array(
				'edd-action'  => 'email_links',
				'purchase_id' => absint( $order->id ),
			) ) ); ?>" id="<?php echo esc_attr( 'edd-resend-receipt' ); ?>" class="button button-secondary"><?php esc_html_e( 'Resend Receipt', 'easy-digital-downloads' ); ?></a>
		</p>

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

			<div class="customer-address-select-wrap edd-form-group" style="display: none; padding: 16px 0; border-bottom: 1px solid #ccd0d4;">
				<label for="edd_customer_existing_addresses" class="edd-form-group__label"><?php esc_html_e( 'Existing Address:', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control"></div>
			</div>

			<div class="edd-form-group">
				<label for="edd_order_address_address" class="edd-form-group__label"><?php esc_html_e( 'Line 1:', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control">
					<input type="text" name="edd_order_address[address]" id="edd_order_address_address" class="edd-form-group__input regular-text" value="<?php echo esc_attr( $address->address ); ?>" />
				</div>
			</div>

			<div class="edd-form-group">
				<label for="edd_order_address_address2" class="edd-form-group__label"><?php esc_html_e( 'Line 2:', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control">
					<input type="text" name="edd_order_address[address2]" class="edd-form-group__input regular-text" id="edd_order_address_address2" value="<?php echo esc_attr( $address->address2 ); ?>" />
				</div>
			</div>

			<div class="edd-form-group">
				<label for="edd_order_address_city" class="edd-form-group__label"><?php echo esc_html_x( 'City:', 'Address City', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control">
					<input type="text" name="edd_order_address[city]" class="edd-form-group__input regular-text" id="edd_order_address_city" value="<?php echo esc_attr( $address->city ); ?>" />
				</div>
			</div>

			<div class="edd-form-group">
				<label for="edd_order_address_postal_code" class="edd-form-group__label"><?php echo esc_html_x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control">
					<input type="text" name="edd_order_address[postal_code]" class="edd-form-group__input regular-text" id="edd_order_address_postal_code" value="<?php echo esc_attr( $address->postal_code ); ?>" class="med-text" />
				</div>
			</div>

			<div class="edd-form-group">
				<label for="edd_order_address_country" class="edd-form-group__label"><?php echo esc_html_x( 'Country:', 'Address country', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control" id="edd-order-address-country-wrap">
					<?php
					echo EDD()->html->country_select(
						array(
							'name'            => 'edd_order_address[country]',
							'id'              => 'edd-order-address-country',
							'class'           => 'edd-order-address-country edd-form-group__input',
							'show_option_all' => false,
							'data'            => array(
								'nonce'              => wp_create_nonce( 'edd-country-field-nonce' ),
								'search-type'        => 'no_ajax',
								'search-placeholder' => esc_html__( 'Search Countries', 'easy-digital-downloads' ),
							),
						),
						$address->country
					); // WPCS: XSS ok.
					?>
				</div>
			</div>

			<div class="edd-form-group">
				<label for="edd_order_address_region" class="edd-form-group__label"><?php echo esc_html_x( 'Region:', 'Region of address', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control" id="edd-order-address-state-wrap">
					<?php
					$states = edd_get_shop_states( $address->country );
					if ( ! empty( $states ) ) {
						echo EDD()->html->region_select(
							array(
								'name'             => 'edd_order_address[region]',
								'id'               => 'edd_order_address_region',
								'class'            => 'edd-order-address-region edd-form-group__input',
								'data'             => array(
									'search-type'        => 'no_ajax',
									'search-placeholder' => esc_html__( 'Search Regions', 'easy-digital-downloads' ),
								),
							),
							$address->country,
							$address->region
						); // WPCS: XSS ok.
					} else {
						?>
						<input type="text" id="edd_order_address_region" name="edd_order_address[region]" class="edd-form-group__input" value="<?php echo esc_attr( $address->region ); ?>" />
						<?php
					}
					?>
				</div>
			</div>

			<input type="hidden" name="edd_order_address[address_id]" value="<?php echo esc_attr( $address->id ); ?>" />
			<?php wp_nonce_field( 'edd_get_tax_rate_nonce', 'edd_get_tax_rate_nonce' ); ?>
		</div>

	</div><!-- /#edd-order-address -->

	<?php
	do_action( 'edd_payment_billing_details', $order->id );
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
		$download_log_url    = edd_get_admin_url(
			array(
				'page'    => 'edd-tools',
				'tab'     => 'logs',
				'payment' => absint( $order->id ),
			)
		);
		$customer_log_url    = edd_get_admin_url(
			array(
				'page'     => 'edd-tools',
				'tab'      => 'logs',
				'customer' => absint( $order->customer_id ),
			)
		);
		$customer_orders_url = edd_get_admin_url(
			array(
				'page'     => 'edd-payment-history',
				'customer' => absint( $order->customer_id ),
			)
		);
		?>

		<p><a href="<?php echo esc_url( $download_log_url ); ?>"><?php esc_html_e( 'File Download Log for Order', 'easy-digital-downloads' ); ?></a></p>
		<p><a href="<?php echo esc_url( $customer_log_url ); ?>"><?php esc_html_e( 'Customer Download Log', 'easy-digital-downloads' ); ?></a></p>
		<p><a href="<?php echo esc_url( $customer_orders_url ); ?>"><?php esc_html_e( 'Customer Orders', 'easy-digital-downloads' ); ?></a></p>

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
function edd_order_details_overview( $order ) {
	$_items       = array();
	$_adjustments = array();
	$_refunds     = array();

	if ( true !== edd_is_add_order_page() ) {
		$items = edd_get_order_items( array(
			'order_id' => $order->id,
			'number'   => 999,
		) );

		foreach ( $items as $item ) {
			$item_adjustments = array();

			$adjustments = edd_get_order_adjustments( array(
				'object_id'   => $item->id,
				'number'      => 999,
				'object_type' => 'order_item',
				'type'        => array(
					'discount',
					'credit',
					'fee',
				),
			) );

			foreach ( $adjustments as $adjustment ) {
				// @todo edd_get_order_adjustment_to_json()?
				$adjustment_args = array(
					'id'           => esc_html( $adjustment->id ),
					'objectId'     => esc_html( $adjustment->object_id ),
					'objectType'   => esc_html( $adjustment->object_type ),
					'typeId'       => esc_html( $adjustment->type_id ),
					'type'         => esc_html( $adjustment->type ),
					'description'  => esc_html( $adjustment->description ),
					'subtotal'     => esc_html( $adjustment->subtotal ),
					'tax'          => esc_html( $adjustment->tax ),
					'total'        => esc_html( $adjustment->total ),
					'dateCreated'  => esc_html( $adjustment->date_created ),
					'dateModified' => esc_html( $adjustment->date_modified ),
					'uuid'         => esc_html( $adjustment->uuid ),
				);

				$item_adjustments[] = $adjustment_args;
				$_adjustments[]     = $adjustment_args;
			}

			// @todo edd_get_order_item_to_json()?
			$_items[] = array(
				'id'           => esc_html( $item->id ),
				'orderId'      => esc_html( $item->order_id ),
				'productId'    => esc_html( $item->product_id ),
				'productName'  => esc_html( $item->get_order_item_name() ),
				'priceId'      => esc_html( $item->price_id ),
				'cartIndex'    => esc_html( $item->cart_index ),
				'type'         => esc_html( $item->type ),
				'status'       => esc_html( $item->status ),
				'statusLabel'  => esc_html( edd_get_status_label( $item->status ) ),
				'quantity'     => esc_html( $item->quantity ),
				'amount'       => esc_html( $item->amount ),
				'subtotal'     => esc_html( $item->subtotal ),
				'discount'     => esc_html( $item->discount ),
				'tax'          => esc_html( $item->tax ),
				'total'        => esc_html( $item->total ),
				'dateCreated'  => esc_html( $item->date_created ),
				'dateModified' => esc_html( $item->date_modified ),
				'uuid'         => esc_html( $item->uuid ),
				'deliverable'  => $item->is_deliverable(),
				'adjustments'  => $item_adjustments,
			);
		}

		$adjustments = edd_get_order_adjustments( array(
			'object_id'   => $order->id,
			'number'      => 999,
			'object_type' => 'order',
			'type'        => array(
				'discount',
				'credit',
				'fee',
			),
		) );

		foreach ( $adjustments as $adjustment ) {
			// @todo edd_get_order_adjustment_to_json()?
			$_adjustments[] = array(
				'id'           => esc_html( $adjustment->id ),
				'objectId'     => esc_html( $adjustment->object_id ),
				'objectType'   => esc_html( $adjustment->object_type ),
				'typeId'       => esc_html( $adjustment->type_id ),
				'type'         => esc_html( $adjustment->type ),
				'description'  => esc_html( $adjustment->description ),
				'subtotal'     => esc_html( $adjustment->subtotal ),
				'tax'          => esc_html( $adjustment->tax ),
				'total'        => esc_html( $adjustment->total ),
				'dateCreated'  => esc_html( $adjustment->date_created ),
				'dateModified' => esc_html( $adjustment->date_modified ),
				'uuid'         => esc_html( $adjustment->uuid ),
			);
		}

		$refunds = edd_get_order_refunds( $order->id );

		foreach ( $refunds as $refund ) {
			$_refunds[] = array(
				'id'              => esc_html( $refund->id ),
				'number'          => esc_html( $refund->order_number ),
				'total'           => esc_html( $refund->total ),
				'dateCreated'     => esc_html( $refund->date_created ),
				'dateCreatedi18n' => esc_html( edd_date_i18n( $refund->date_created ) ),
				'uuid'            => esc_html( $refund->uuid ),
			);
		}
	}

	$has_tax  = 'none';
	$tax_rate = $order->id ? $order->get_tax_rate() : false;

	$location = array(
		'rate'      => $tax_rate,
		'country'   => '',
		'region'    => '',
		'inclusive' => edd_prices_include_tax(),
	);

	if ( edd_is_add_order_page() && edd_use_taxes() ) {
		$default_rate = edd_get_tax_rate_by_location(
			array(
				'country' => '',
				'region'  => '',
			)
		);
		if ( $default_rate ) {
			$location['rate'] = floatval( $default_rate->amount );
		}
		$has_tax = $location;
	} elseif ( $tax_rate ) {
		$has_tax         = $location;
		$has_tax['rate'] = $tax_rate;

		if ( $order->tax_rate_id ) {
			$tax_rate_object = $order->get_tax_rate_object();

			if ( $tax_rate_object ) {
				$has_tax['country'] = $tax_rate_object->name;
				$has_tax['region']  = $tax_rate_object->description;
			}
		}
	}

	$has_quantity = true;
	if ( edd_is_add_order_page() && ! edd_item_quantities_enabled() ) {
		$has_quantity = false;
	}

	wp_localize_script(
		'edd-admin-orders',
		'eddAdminOrderOverview',
		array(
			'items'        => $_items,
			'adjustments'  => $_adjustments,
			'refunds'      => $_refunds,
			'isAdding'     => true === edd_is_add_order_page(),
			'hasQuantity'  => $has_quantity,
			'hasTax'       => $has_tax,
			'hasDiscounts' => true === edd_has_active_discounts(),
			'order'        => array(
				'status'         => $order->status,
				'currency'       => $order->currency,
				'currencySymbol' => html_entity_decode( edd_currency_symbol( $order->currency ) ),
				'subtotal'       => $order->subtotal,
				'discount'       => $order->discount,
				'tax'            => $order->tax,
				'total'          => $order->total,
			),
			'nonces'       => array(
				'edd_admin_order_get_item_amounts' => wp_create_nonce( 'edd_admin_order_get_item_amounts' ),
			),
			'i18n'         => array(
				'closeText' => esc_html__( 'Close', 'easy-digital-downloads' ),
			),
		)
	);

	$templates = array(
		'actions',
		'subtotal',
		'tax',
		'total',
		'item',
		'adjustment',
		'adjustment-discount',
		'refund',
		'no-items',
		'copy-download-link',
		'form-add-order-item',
		'form-add-order-discount',
		'form-add-order-adjustment',
	);

	foreach ( $templates as $tmpl ) {
		echo '<script type="text/html" id="tmpl-edd-admin-order-' . esc_attr( $tmpl ) . '">';
		require_once EDD_PLUGIN_DIR . 'includes/admin/views/tmpl-order-' . $tmpl . '.php';
		echo '</script>';
	}
?>

<div id="edd-order-overview" class="postbox edd-edit-purchase-element edd-order-overview">
	<table id="edd-order-overview-summary" class="widefat wp-list-table edd-order-overview-summary">
		<thead>
			<tr>
				<th class="column-name column-primary"><?php echo esc_html( edd_get_label_singular() ); ?></th>
				<th class="column-amount"><?php esc_html_e( 'Unit Price', 'easy-digital-downloads' ); ?></th>
				<?php if ( $has_quantity ) : ?>
					<th class="column-quantity"><?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?></th>
				<?php endif; ?>
				<th class="column-subtotal column-right"><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></th>
			</tr>
		</thead>
	</table>

	<div id="edd-order-overview-actions" class="edd-order-overview-actions inside"></div>
</div>

<?php

	/**
	 * @since unknown
	 */
	do_action( 'edd_view_order_details_files_after', $order->id );
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
		<h2 class="hndle">
			<span><?php esc_html_e( 'Order Details', 'easy-digital-downloads' ); ?></span>
		</h2>
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
	}

	// Filter the transaction ID (here specifically for back-compat)
	if ( ! empty( $transaction_id ) ) {
		$transaction_id = apply_filters( 'edd_payment_details_transaction_id-' . $order->gateway, $transaction_id, $order->id );
	} ?>

	<div id="edd-order-extras" class="postbox edd-order-data">
		<h2 class="hndle">
			<span><?php esc_html_e( 'Order Extras', 'easy-digital-downloads' ); ?></span>
		</h2>

		<div class="inside">
			<div class="edd-admin-box">
				<?php do_action( 'edd_view_order_details_payment_meta_before', $order->id ); ?>


				<?php if ( ! edd_is_add_order_page() ) : ?>
					<div class="edd-order-gateway edd-admin-box-inside edd-admin-box-inside--row">
						<span class="label"><?php esc_html_e( 'Gateway', 'easy-digital-downloads' ); ?></span>
						<span class="value"><?php echo edd_get_gateway_admin_label( $order->gateway ); ?></span>
					</div>
				<?php else : ?>
					<div class="edd-order-gateway edd-admin-box-inside">
						<div class="edd-form-group">
							<label for="edd_gateway_select" class="edd-form-group__label"><?php esc_html_e( 'Gateway', 'easy-digital-downloads' ); ?></label>
							<div class="edd-form-group__control">
								<?php
								echo EDD()->html->select(
									array(
										'name'             => 'gateway',
										'class'            => 'edd-form-group__input',
										'id'               => 'edd_gateway_select',
										'options'          => $gateways,
										'selected'         => 'manual',
										'show_option_none' => false,
										'show_option_all'  => false,
									)
								); // WPCS: XSS ok.
								?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div class="edd-admin-box-inside">
					<div class="edd-form-group">
						<label for="edd_payment_key" class="edd-form-group__label"><?php esc_html_e( 'Key', 'easy-digital-downloads' ); ?></label>
						<div class="edd-form-group__control">
							<input type="text" name="payment_key" id="edd_payment_key" class="edd-form-group__input regular-text" <?php echo esc_attr( $readonly ); ?> value="<?php echo esc_attr( $order->payment_key ); ?>" />
						</div>
					</div>
				</div>

				<?php if ( edd_is_add_order_page() ) : ?>
					<div class="edd-order-ip edd-admin-box-inside">
						<div class="edd-form-group">
							<label for="edd_ip" class="edd-form-group__label"><?php esc_html_e( 'IP', 'easy-digital-downloads' ); ?></label>
							<div class="edd-form-group__control">
								<input type="text" name="ip" id="edd_ip" class="edd-form-group__input" value="<?php echo esc_attr( edd_get_ip() ); ?>" />
							</div>
						</div>
					</div>
				<?php else : ?>
					<div class="edd-order-gateway edd-admin-box-inside edd-admin-box-inside--row">
						<span class="label"><?php esc_html_e( 'IP', 'easy-digital-downloads' ); ?></span>
						<span class="value"><?php echo edd_payment_get_ip_address_url( $order->id ); // WPCS: XSS ok. ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $transaction_id ) : ?>
					<div class="edd-order-tx-id edd-admin-box-inside edd-admin-box-inside--row">
						<span class="label"><?php esc_html_e( 'Transaction ID', 'easy-digital-downloads' ); ?></span>
						<span><?php echo $transaction_id; ?></span>
					</div>
				<?php endif; ?>

				<?php
				if ( ! edd_is_add_order_page() && 'on_hold' === $order->status ) {
					$dispute_id = edd_get_order_dispute_id( $order->id );
					if ( $dispute_id ) {
						$dispute_id = apply_filters( "edd_payment_details_dispute_id_{$order->gateway}", $dispute_id, $order );
						?>
						<div class="edd-order-dispute-id edd-admin-box-inside edd-admin-box-inside--row">
							<span class="label"><?php esc_html_e( 'Dispute ID', 'easy-digital-downloads' ); ?></span>
							<span><?php echo wp_kses_post( $dispute_id ); ?></span>
						</div>
						<?php
					}
				}
				?>

				<?php if ( edd_is_add_order_page() ) : ?>
					<div class="edd-order-tx-id edd-admin-box-inside edd-admin-box-inside--row">
						<div class="edd-form-group">
							<label for="edd_transaction_id" class="edd-form-group__label"><?php esc_html_e( 'Transaction ID', 'easy-digital-downloads' ); ?></label>
							<div class="edd-form-group__control">
								<input type="text" name="transaction_id" class="edd-form-group__input" id="edd_transaction_id" value="" />
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div class="edd-unlimited-downloads edd-admin-box-inside">
					<div class="edd-form-group">
						<div class="edd-form-group__control">
							<input type="checkbox" name="edd-unlimited-downloads" id="edd_unlimited_downloads" class="edd-form-group__input" value="1"<?php checked( true, $unlimited, true ); ?>/>

							<label for="edd_unlimited_downloads">
							<?php esc_html_e( 'Unlimited Downloads', 'easy-digital-downloads' ); ?></label>
							<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Checking this box will override all other file download limits for this purchase, granting the customer unlimited downloads of all files included on the purchase.', 'easy-digital-downloads' ); ?>"></span>
						</div>
					</div>
				</div>

				<?php if ( ! edd_is_add_order_page() ) : ?>
				<div class="edd-order-tx-id edd-admin-box-inside edd-admin-box-inside--row">
					<span class="label"><?php esc_html_e( 'Deferred Actions', 'easy-digital-downloads' ); ?></span>
					<?php
					$status = '';
					$label  = __( 'Not Run', 'easy-digital-downloads' );

					if ( ! empty( $order->date_actions_run ) ) {
						$status  = 'success';
						$label   = __( 'Completed', 'easy-digital-downloads' );
					} elseif ( wp_next_scheduled( 'edd_after_payment_scheduled_actions', array( intval( $order->id ), false ) ) ) {
						$status = 'processing';
						$label  = __( 'Scheduled', 'easy-digital-downloads' );
					}

					$status_badge = new EDD\Utils\StatusBadge(
						array(
							'status' => $status,
							'label'  => $label,
						)
					);

					echo $status_badge->get();
					if ( empty( $status ) ) {
						?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Deferred Actions were added in Easy Digital Downloads 2.8. Orders placed on prior versions will not have a deferred actions status. If this order was placed on a version of Easy Digital Downloads supporting Deferred Actions, please verify that WP Cron is able to be run.', 'easy-digital-downloads' ); ?>"></span><?php
					}
					?>
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

	$recovery_url = edd_is_add_order_page()
		? ''
		: edd_get_payment( $order->id )->get_recovery_url();

	$order_date = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $order->date_created, 'utc', true ) );

	?>

	<div id="edd-order-update" class="postbox edd-order-data">
		<h2 class="hndle">
			<span><?php esc_html_e( 'Order Attributes', 'easy-digital-downloads' ); ?></span>
		</h2>

		<div class="inside">
			<div class="edd-order-update-box edd-admin-box">
				<div class="edd-admin-box-inside">
					<div class="edd-form-group">
						<label for="edd_payment_status" class="edd-form-group__label">
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
						<div class="edd-form-group__control">
							<select name="edd-payment-status" id="edd_payment_status" class="edd-form-group__input">
							<?php foreach ( edd_get_payment_statuses() as $key => $status ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $order->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
							<?php endforeach; ?>
							</select>
						</div>
					</div>

					<?php
					if ( ! edd_is_add_order_page() ) :
						$hold_reason = edd_get_order_hold_reason( $order->id );
						if ( $hold_reason ) {
							$label = 'on_hold' === $order->status ?
								__( 'On Hold Due To:', 'easy-digital-downloads' ) :
								__( 'Original Hold Reason:', 'easy-digital-downloads' );
							if ( is_array( $hold_reason ) ) {
								$hold_reason = array_map( 'edd_get_order_hold_reason_label', $hold_reason );
								$hold_reason = implode( ', ', $hold_reason );
							} else {
								$hold_reason = edd_get_order_hold_reason_label( $hold_reason );
							}
							?>
							<div style="margin-top: 8px;">
								<strong><?php echo esc_html( $label ); ?></strong>
								<?php echo esc_html( $hold_reason ); ?>
							</div>
							<?php
						}

						$trash_url = wp_nonce_url(
							edd_get_admin_url( array(
								'page'        => 'edd-payment-history',
								'order_type'  => 'sale',
								'edd-action'  => 'trash_order',
								'purchase_id' => absint( $order->id ),
							) ),
							'edd_payment_nonce'
						);
					?>
					<div style="margin-top: 8px;">
						<a href="<?php echo esc_url( $trash_url ); ?>" class="edd-delete-payment edd-delete">
							<?php esc_html_e( 'Move to Trash', 'easy-digital-downloads' ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( ! edd_is_add_order_page() && edd_is_order_recoverable( $order->id ) && ! empty( $recovery_url ) ) : ?>
					<div class="edd-admin-box-inside">
						<div class="edd-form-group">
							<label class="edd-form-group__label" for="edd_recovery_url">
								<?php esc_html_e( 'Recover', 'easy-digital-downloads' ); ?>
								<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php esc_html_e( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'easy-digital-downloads' ); ?>"></span>
							</label>
							<div class="edd-form-group__control">
								<input type="text" class="edd-form-group__input" id="edd_recovery_url" readonly="readonly" value="<?php echo esc_url( $recovery_url ); ?>"/>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div class="edd-admin-box-inside">
					<div class="edd-form-group">
						<label for="edd-payment-date" class="edd-form-group__label"><?php esc_html_e( 'Date', 'easy-digital-downloads' ); ?>
						</label>
						<div class="edd-form-group__control">
							<input type="text" id="edd-payment-date" class="edd-form-group__input edd_datepicker" name="edd-payment-date" value="<?php echo esc_attr( $order_date->format( 'Y-m-d' ) ); ?>"placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>"/>
						</div>
					</div>
				</div>

				<div class="edd-admin-box-inside">
					<fieldset class="edd-form-group">
						<legend class="edd-form-group__label">
							<?php echo esc_html( __( 'Time', 'easy-digital-downloads' ) . ' (' . edd_get_timezone_abbr() . ')' ); ?>
						</legend>

						<div class="edd-form-group__control">
							<label for="edd-payment-time-hour" class="screen-reader-text">
								<?php esc_html_e( 'Hour', 'easy-digital-downloads' ); ?>
							</label>
							<input type="number" class="edd-form-group__input small-text" min="0" max="24" step="1" name="edd-payment-time-hour" id="edd-payment-time-hour" value="<?php echo esc_attr( $order_date->format( 'H' ) ); ?>" />
							:

							<label for="edd-payment-time-min" class="screen-reader-text">
								<?php esc_html_e( 'Minute', 'easy-digital-downloads' ); ?>
							</label>
							<input type="number" class="edd-form-group__input small-text" min="0" max="59" step="1" name="edd-payment-time-min" id="edd-payment-time-min" value="<?php echo esc_attr( $order_date->format( 'i' ) ); ?>" />
						</div>
					</fieldset>
				</div>

				<?php do_action( 'edd_view_order_details_update_inner', $order->id ); ?>

			</div><!-- /.edd-admin-box -->
		</div><!-- /.inside -->

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

/**
 * Returns markup for an Order status badge.
 *
 * @since 3.0
 *
 * @param string $order_status Order status slug.
 * @return string
 */
function edd_get_order_status_badge( $order_status ) {
	$icon   = '';
	$status = $order_status;
	switch ( $order_status ) {
		case 'refunded':
			$icon = 'undo';
			break;
		case 'failed':
			$icon   = 'no-alt';
			$status = 'error';
			break;
		case 'complete':
		case 'partially_refunded':
			$icon   = 'yes';
			$status = 'success';
			break;
		case 'pending':
			$status = 'warning';
			break;
		case 'on_hold':
			$icon   = 'warning';
			$status = 'error';
			break;
	}

	/**
	 * Filters the arguments for the order status badge.
	 *
	 * @since 3.1.4
	 * @param array  $status_badge_args Array of arguments for the status badge.
	 * @param string $order_status      Order status slug.
	 */
	$status_badge_args = apply_filters(
		'edd_get_order_status_badge_args',
		array(
			'status' => $status,
			'label'  => edd_get_payment_status_label( $order_status ),
			'icon'   => $icon,
			'class'  => "edd-admin-order-status-badge--{$order_status}",
		),
		$order_status
	);
	$status_badge      = new EDD\Utils\StatusBadge( $status_badge_args );

	/**
	 * Filters the markup for the order status badge icon.
	 *
	 * @since 3.0
	 *
	 * @param string $icon Icon HTML markup.
	 */
	$icon = apply_filters( 'edd_get_order_status_badge_icon', $status_badge->get_icon(), $order_status );

	return $status_badge->get( $icon );
}
