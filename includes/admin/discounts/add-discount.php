<?php
/**
 * Add Discount Page
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Hours & Minutes
$start_hour   = '00';
$start_minute = '00';
$end_hour     = '23';
$end_minute   = '59';
$hours        = edd_get_hour_values();
$minutes      = edd_get_minute_values();

// Output
?><div class="wrap">
	<h1><?php _e( 'Add New Discount', 'easy-digital-downloads' ); ?></h1>

	<hr class="wp-header-end">

	<form id="edd-add-discount" action="" method="post">

		<?php do_action( 'edd_add_discount_form_top' ); ?>

		<table class="form-table">
			<tbody>

				<?php do_action( 'edd_add_discount_form_before_name' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-name"><?php _e( 'Name', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input name="name" required="required" id="edd-name" type="text" value="" placeholder="<?php esc_html_e( 'Summer Sale', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The name of this discount. Customers will see this on checkout.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_code' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-code"><?php _e( 'Code', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" required="required" id="edd-code" name="code" class="code" value="" pattern="[a-zA-Z0-9-_]+" placeholder="<?php esc_html_e( '10PERCENT', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The code customers will enter to apply this discount. Only alphanumeric characters are allowed.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_type' ); ?>

				<?php do_action( 'edd_add_discount_form_before_amount' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-amount"><?php _e( 'Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<span class="edd-amount-type-wrapper">
							<span class="edd-amount-type-select-wrapper">
								<select name="amount_type" id="edd-amount-type">
									<option value="percent"><?php _e( '%', 'easy-digital-downloads' ); ?></option>
									<option value="flat"><?php _e( '$', 'easy-digital-downloads' ); ?></option>
								</select>
							</span>
							<input type="text" required="required" class="edd-price-field" id="edd-amount" name="amount" value="" placeholder="<?php esc_html_e( '10.00', 'easy-digital-downloads' ); ?>"/>
						</span>
						<p class="description"><?php _e( 'The amount as a percentage or flat rate. Cannot be left blank.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_products' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-products"><?php printf( __( '%s Requirements', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
					</th>
					<td>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'selected'    => array(),
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
						) ); ?>
						<div id="edd-discount-product-conditions" style="display:none;">
							<p>
								<select id="edd-product-condition" name="product_condition">
									<option value="all"><?php printf( __( 'Cart must contain all selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
									<option value="any"><?php printf( __( 'Cart needs one or more of the selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
								</select>
							</p>
							<p>
								<label>
									<input type="radio" class="tog" name="scope" value="global" checked="checked"/>
									<?php _e( 'Apply discount to entire purchase.', 'easy-digital-downloads' ); ?>
								</label><br/>
								<label>
									<input type="radio" class="tog" name="scope" value="not_global"/>
									<?php printf( __( 'Apply discount only to selected %s.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>
								</label>
							</p>
						</div>
						<p class="description"><?php printf( __( '%s this discount can only be applied to. Leave blank for any.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_excluded_products' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-excluded-products"><?php printf( __( 'Excluded %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></label>
					</th>
					<td>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'excluded_products[]',
							'id'          => 'excluded_products',
							'selected'    => array(),
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
						) ); ?>
						<p class="description"><?php printf( __( '%s this discount cannot be applied to. Leave blank for none.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_start' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-start"><?php _e( 'Start date', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input name="start_date" id="edd-start" type="text" value="" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />
						<?php
						echo EDD()->html->select( array(
							'name'             => 'start_date_hour',
							'options'          => $hours,
							'selected'         => $start_hour,
							'chosen'           => true,
							'class'            => 'edd-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
						:
						<?php
						echo EDD()->html->select( array(
							'name'             => 'start_date_minute',
							'options'          => $minutes,
							'selected'         => $start_minute,
							'chosen'           => true,
							'class'            => 'edd-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
						<p class="description"><?php _e( 'Pick the date this discount will start on. Leave blank for no start date.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_expiration' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-expiration"><?php _e( 'Expiration date', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input name="end_date" id="edd-expiration" type="text" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />
						<?php
						echo EDD()->html->select( array(
							'name'             => 'end_date_hour',
							'options'          => $hours,
							'selected'         => $end_hour,
							'chosen'           => true,
							'class'            => 'edd-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
						:
						<?php
						echo EDD()->html->select( array(
							'name'             => 'end_date_minute',
							'options'          => $minutes,
							'selected'         => $end_minute,
							'chosen'           => true,
							'class'            => 'edd-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
						<p class="description"><?php _e( 'Pick the date this discount will expire on. Leave blank to never expire.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_min_cart_amount' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" id="edd-min-cart-amount" name="min_cart_price" value="" placeholder="<?php esc_html_e( 'No minimum', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The minimum subtotal of item prices in a cart before this discount may be applied.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_max_uses' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-max-uses"><?php _e( 'Max Uses', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" id="edd-max-uses" name="max_uses" value="" placeholder="<?php esc_html_e( 'Unlimited', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The maximum number of times this discount can be used.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_use_once' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-use-once"><?php _e( 'Use Once Per Customer', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="edd-use-once" name="once_per_customer" value="1"/>
						<span class="description"><?php _e( 'Prevent customers from using this discount more than once.', 'easy-digital-downloads' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>

		<?php do_action( 'edd_add_discount_form_bottom' ); ?>

		<p class="submit">
			<input type="hidden" name="type" value="discount" />
			<input type="hidden" name="edd-action" value="add_discount"/>
			<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ); ?>"/>
			<input type="hidden" name="edd-discount-nonce" value="<?php echo wp_create_nonce( 'edd_discount_nonce' ); ?>"/>
			<input type="submit" value="<?php _e( 'Add Discount Code', 'easy-digital-downloads' ); ?>" class="button-primary"/>
		</p>
	</form>
</div>
