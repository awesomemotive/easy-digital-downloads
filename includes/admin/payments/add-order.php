<?php
/**
 * Add Order Page.
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Bail if incorrect view.
if ( ! isset( $_GET['view'] ) || 'add-order' !== $_GET['view'] ) {
	wp_die( esc_html__( 'Something went wrong.', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}

// Determine float accuracy for the steps and rounding.
$decimals = edd_currency_decimal_filter();
if ( empty( $decimals ) ) {
	$step = 1;
} else {
	$i    = 1;
	$step = '0.';
	while ( $i < $decimals ) {
		$step .= '0';
		$i++;
	}
	$step .= '1';
	$step = (float) $step;
}

// Output
?><div class="wrap">
	<h1><?php esc_html_e( 'Add New Order', 'easy-digital-downloads' ); ?></h1>

	<hr class="wp-header-end">

	<form id="edd-add-order" method="post" action="">

		<?php do_action( 'edd_add_order_form_top' ); ?>

		<table class="form-table" id="edd-customer-details">
			<tbody>
				<tr class="form-field edd-add-order-download-wrap">
					<th scope="row" valign="top"><label><?php echo esc_html_e( 'Order Items', 'easy-digital-downloads' ); ?></label></th>
					<td>
						<div id="edd-order-items">
							<table class="wp-list-table widefat fixed striped orderitems">
								<thead>
									<tr>
										<th scope="col" class="column-primary"><?php echo esc_html_e( 'Product', 'easy-digital-downloads' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Price Option', 'easy-digital-downloads' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></th>
										<?php if ( edd_item_quantities_enabled() ) : ?>
											<th scope="col"><?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?></th>
										<?php endif; ?>
										<?php if ( edd_use_taxes() ) : ?>
										<th scope="col"><?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?></th>
										<?php endif; ?>
									</tr>
								</thead>
								<tbody>
									<tr class="edd_repeatable_row" data-key="1">
										<td>
											<?php
											echo EDD()->html->product_dropdown( array(
												'name'     => 'downloads[1][id]',
												'id'       => 'downloads',
												'class'    => 'add-order-download',
												'multiple' => false,
												'chosen'   => true,
											) );
											?>
										</td>
										<td class="download-price-option-wrap"><?php esc_html_e( '&mdash;', 'easy-digital-downloads' ); ?> <span class="spinner"></span></td>
										<td><input type="number" step="<?php echo esc_attr( $step ); ?>" class="edd-amount" name="downloads[1][amount]" value="" min="0" placeholder="<?php esc_attr_e( 'Amount', 'easy-digital-downloads' ); ?>"/></td>
										<?php if ( edd_item_quantities_enabled() ) : ?>
											<td>&nbsp;&times;&nbsp; <input type="number" step="1" class="edd-quantity" name="downloads[1][quantity]" value="1" min="1" placeholder="<?php esc_attr_e( 'Quantity', 'easy-digital-downloads' ); ?>"/></td>
										<?php endif; ?>
										<?php if ( edd_use_taxes() ) : ?>
											<td><input type="number" step="<?php echo esc_attr( $step ); ?>" class="edd-tax" name="downloads[1][tax]" value="" min="0" placeholder="<?php esc_attr_e( 'Tax', 'easy-digital-downloads' ); ?>"/></td>
										<?php endif; ?>
									</tr>
								</tbody>
							</table>
							<p><a href="#" class="button button-secondary edd-add-order-item"><?php esc_html_e( 'Add Item', 'easy-digital-downloads' ); ?></a> </p>
							<p><strong><?php esc_html_e( 'Total:', 'easy-digital-downloads' ); ?></strong> <?php echo esc_html( edd_currency_symbol() ); ?><span id="edd-total">0.00</span></p>
						</div>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top"><label><?php esc_html_e( 'Customer', 'easy-digital-downloads' ); ?></label></th>
					<td class="customer">
						<div class="customer-info">
							<?php echo EDD()->html->customer_dropdown( array( 'name' => 'customer' ) ); ?>
						</div>
						<p class="customer-info">
							<a href="#new" class="button button-secondary edd-payment-new-customer order-customer-info" title="<?php esc_html_e( 'New Customer', 'easy-digital-downloads' ); ?>"><?php esc_html_e( 'New Customer', 'easy-digital-downloads' ); ?></a>
						</p>
						<p class="description new-customer" style="display: none">
							<a href="#cancel" class="button button-secondary edd-payment-new-customer-cancel" title="<?php esc_html_e( 'Select Existing Customer', 'easy-digital-downloads' ); ?>"><?php esc_html_e( 'Select Existing Customer', 'easy-digital-downloads' ); ?></a>
						</p>
					</td>
				</tr>

				<?php if ( edd_use_taxes() ) : ?>
				<tr class="form-field">
					<th scope="row" valign="top"><?php esc_html_e( 'Customer Address', 'easy-digital-downloads' ); ?></th>
					<td class="customer-address">
						<p>
							<label for="edd-address"><?php esc_html_e( 'Address Line 1' ); ?></label>
							<input type="text" id="edd-address" name="address" />
						</p>

						<p>
							<label for="edd-address2"><?php esc_html_e( 'Address Line 2' ); ?></label>
							<input type="text" id="edd-address2" name="address2" />
						</p>

						<p>
							<label for="edd-city"><?php esc_html_e( 'City' ); ?></label>
							<input type="text" id="edd-city" name="city" />
						</p>

						<p>
							<label for="edd-postal-code"><?php esc_html_e( 'Zip / Postal Code' ); ?></label>
							<input type="text" id="edd-postal-code" name="postal_code" />
						</p>

						<p>
							<label for="edd-country"><?php esc_html_e( 'Country' ); ?></label>
							<?php
							$countries = edd_get_country_list();

							// Remove empty values.
							$countries = array_filter( $countries );

							echo EDD()->html->select( array(
								'name'             => 'country',
								'id'               => 'edd-country',
								'class'            => 'edd-country',
								'options'          => $countries,
								'chosen'           => true,
								'selected'         => false,
								'show_option_none' => false,
								'show_option_all'  => false,
							) );
							?>
						</p>

						<p>
							<label for="edd-region"><?php esc_html_e( 'Region' ); ?></label>
							<?php
							$regions = edd_get_shop_states( edd_get_shop_country() );

							// Remove empty values.
							$regions = array_filter( $regions );

							echo EDD()->html->select( array(
								'name'             => 'region',
								'id'               => 'edd-region',
								'class'            => 'edd-region',
								'options'          => $regions,
								'chosen'           => true,
								'selected'         => false,
								'show_option_none' => false,
								'show_option_all'  => false,
							) );
							?>
						</p>
					</td>
				</tr>
				<?php endif; ?>

				<tr class="form-field new-customer" style="display: none">
					<th scope="row" valign="top"><label for="edd-customer-email"><?php esc_html_e( 'Customer Email', 'easy-digital-downloads' ); ?></label></th>
					<td class="customer-email">
						<input type="text" id="edd-customer-email" name="email" />
						<p class="description"><?php esc_html_e( 'Enter the email address of the customer.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<tr class="form-field new-customer" style="display: none">
					<th scope="row" valign="top"><label for="edd-customer-first-name"><?php esc_html_e( 'Customer First Name', 'easy-digital-downloads' ); ?></label></th>
					<td class="customer-first-name">
						<input type="text" id="edd-customer-first-name" name="first" />
						<p class="description"><?php esc_html_e( 'Enter the first name of the customer (optional).', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<tr class="form-field new-customer" style="display: none">
					<th scope="row" valign="top">
						<label for="edd-customer-last-name"><?php esc_html_e( 'Customer Last Name', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="customer-last-name">
						<input type="text" id="edd-customer-last-name" name="last" />
						<p class="description"><?php esc_html_e( 'Enter the last name of the customer (optional).', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<tr class="form-field amount">
					<th scope="row" valign="top">
						<label for="edd-amount"><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="amount">
						<input type="text" class="edd-price-field" id="edd-amount" name="amount" />
						<?php if ( edd_item_quantities_enabled() ) : ?>
							<p class="description"><?php esc_html_e( 'Enter the total purchase amount, or leave blank to auto calculate price based on the selected items and quantities above. Use 0.00 for 0.', 'easy-digital-downloads' ); ?></p>
						<?php else : ?>
							<p class="description"><?php esc_html_e( 'Enter the total purchase amount, or leave blank to auto calculate price based on the selected items above. Use 0.00 for 0.', 'easy-digital-downloads' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>

				<tr class="form-field status">
					<th scope="row" valign="top">
						<label for="edd-status"><?php esc_html_e( 'Status', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="status">
						<?php
						echo EDD()->html->select( array(
							'name'             => 'status',
							'options'          => edd_get_payment_statuses(),
							'selected'         => 'publish',
							'chosen'           => true,
							'show_option_all'  => false,
							'show_option_none' => false,
						) );
						?>
						<p class="description"><?php esc_html_e( 'Select the status of this order.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="edd-gateway"><?php _e( 'Payment Method', 'edd-manual-purchases' ); ?></label>
					</th>
					<td class="edd-gateway">
						<?php
						$known_gateways = edd_get_payment_gateways();

						$gateways = array();

						foreach ( $known_gateways as $id => $data ) {
							$gateways[ $id ] = esc_html( $data['admin_label'] );
						}

						echo EDD()->html->select( array(
							'name'             => 'gateway',
							'id'               => 'edd-gateway',
							'options'          => $gateways,
							'selected'         => 'manual',
							'chosen'           => true,
							'show_option_all'  => false,
							'show_option_none' => false,
						) );
						?>
						<p class="description"><?php esc_html_e( 'Select the payment gateway used.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<tr class="form-field transaction-id">
					<th scope="row" valign="top">
						<label for="edd-transaction-id"><?php esc_html_e( 'Transaction ID', 'edd-manual-purchases' ); ?></label>
					</th>
					<td class="transaction-id">
						<input type="text" maxlength="32" id="edd-transaction-id" name="transaction_id" />
						<p class="description"><?php esc_html_e( 'Enter the transaction ID, if any.', 'edd-manual-purchases' ); ?></p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="edd-date"><?php esc_html_e( 'Date Created', 'edd-manual-purchases' ); ?></label>
					</th>
					<td class="edd-mp-downloads">
						<?php
						echo EDD()->html->date_field( array(
							'id'          => 'date',
							'name'        => 'date',
							'value'       => '',
							'placeholder' => esc_html_x( 'Date Created', 'date filter', 'easy-digital-downloads' ),
						) );
						?>
						<p class="description"><?php esc_html_e( 'Enter the purchase date, or leave blank for the current date.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<?php esc_html_e( 'Send Purchase Receipt', 'easy-digital-downloads' ); ?>
					</th>
					<td class="edd-receipt">
						<label for="edd-receipt">
							<input type="checkbox" id="edd-receipt" name="receipt" checked="checked" value="1"/>
							<?php esc_html_e( 'Send the purchase receipt to the buyer?', 'easy-digital-downloads' ); ?>
						</label>
					</td>
				</tr>

			</tbody>
		</table>

		<?php wp_nonce_field( 'edd_add_order_nonce', 'edd_add_order_nonce' ); ?>

		<input type="hidden" name="edd-action" value="add_manual_order" />

		<?php do_action( 'edd_add_order_form_bottom' ); ?>

		<?php submit_button( __( 'Add Order', 'easy-digital-downloads' ) ); ?>
	</form>
</div>
