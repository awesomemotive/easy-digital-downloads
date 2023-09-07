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
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Add New Discount', 'easy-digital-downloads' ); ?></h1>

	<hr class="wp-header-end">

	<form id="edd-add-discount" action="" method="post">

		<?php do_action( 'edd_add_discount_form_top' ); ?>

		<table class="form-table">
			<tbody>

				<?php do_action( 'edd_add_discount_form_before_name' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-name"><?php esc_html_e( 'Name', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input name="name" required="required" id="edd-name" type="text" value="" placeholder="<?php esc_html_e( 'Summer Sale', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php esc_html_e( 'The name of this discount. Customers will see this on checkout.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_code' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-code"><?php esc_html_e( 'Code', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<div class="edd-code-wrapper">
							<input type="text" required="required" id="edd-code" name="code" class="code" value="" pattern="[a-zA-Z0-9\-_]+" maxlength="50" placeholder="<?php esc_html_e( '10PERCENT', 'easy-digital-downloads' ); ?>" />
							<?php do_action( 'edd_add_discount_form_after_code_field' ); ?>
						</div>
						<?php do_action( 'edd_add_discount_form_after_code_field_wrapper' ); ?>
						<p class="description"><?php esc_html_e( 'The code customers will enter to apply this discount. Only alphanumeric characters are allowed.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_type' ); ?>

				<?php do_action( 'edd_add_discount_form_before_amount' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-amount"><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<span class="edd-amount-type-wrapper">
							<input type="text" required="required" class="edd-price-field" id="edd-amount" name="amount" value="" placeholder="<?php esc_html_e( '10.00', 'easy-digital-downloads' ); ?>"/>
							<label for="edd-amount-type" class="screen-reader-text"><?php esc_html_e( 'Amount Type', 'easy-digital-downloads' ); ?></label>
							<select name="amount_type" id="edd-amount-type">
								<option value="percent">%</option>
								<option value="flat"><?php echo esc_html( edd_currency_symbol() ); ?></option>
							</select>
						</span>
						<p class="description"><?php esc_html_e( 'The amount as a percentage or flat rate. Cannot be left blank.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_products' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd_products"><?php printf( esc_html__( '%s Requirements', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
					</th>
					<td>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'product_reqs[]',
							'id'          => 'edd_products',
							'selected'    => array(),
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( esc_html__( 'Select %s', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ),
							'variations'  => true,
						) ); // WPCS: XSS ok. ?>
						<div id="edd-discount-product-conditions" style="display:none;">
							<p>
								<select id="edd-product-condition" name="product_condition">
									<option value="all"><?php printf( esc_html__( 'Cart must contain all selected %s', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ); ?></option>
									<option value="any"><?php printf( esc_html__( 'Cart needs one or more of the selected %s', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ); ?></option>
								</select>
							</p>
							<p>
								<label>
									<input type="radio" class="tog" name="scope" value="global" checked="checked"/>
									<?php esc_html_e( 'Apply discount to entire purchase.', 'easy-digital-downloads' ); ?>
								</label><br/>
								<label>
									<input type="radio" class="tog" name="scope" value="not_global"/>
									<?php printf( esc_html__( 'Apply discount only to selected %s.', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ); ?>
								</label>
							</p>
						</div>
						<p class="description"><?php printf( esc_html__( '%s this discount can only be applied to. Leave blank for any.', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_excluded_products' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-excluded-products"><?php printf( esc_html__( 'Excluded %s', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ); ?></label>
					</th>
					<td>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'excluded_products[]',
							'id'          => 'excluded_products',
							'selected'    => array(),
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( esc_html__( 'Select %s', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ),
						) ); // WPCS: XSS ok. ?>
						<p class="description"><?php printf( esc_html__( '%s this discount cannot be applied to. Leave blank for none.', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) ); ?></p>
					</td>
				</tr>

				<?php
				$categories     = array();
				$term_condition = '';
				include 'views/categories.php';
				?>

				<?php do_action( 'edd_add_discount_form_before_start' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-start"><?php esc_html_e( 'Start date', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="edd-discount-datetime">
						<input name="start_date" id="edd-start" type="text" value="" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />

						<label class="screen-reader-text" for="start-date-hour">
							<?php esc_html_e( 'Start Date Hour', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="24" step="1" name="start_date_hour" id="start-date-hour" placeholder="00" />
						:

						<label class="screen-reader-text" for="start-date-minute">
							<?php esc_html_e( 'Start Date Minute', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="59" step="1" name="start_date_minute" id="start-date-minute" placeholder="00" />

						<?php echo esc_html( ' (' . edd_get_timezone_abbr() . ')' ); ?>
						<p class="description"><?php esc_html_e( 'Pick the date and time this discount will start on. Leave blank for no start date.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_expiration' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-expiration"><?php esc_html_e( 'Expiration date', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="edd-discount-datetime">
						<input name="end_date" id="edd-expiration" type="text" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />

						<label class="screen-reader-text" for="end-date-hour">
							<?php esc_html_e( 'Expiration Date Hour', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="24" step="1" name="end_date_hour" id="end-date-hour" placeholder="23" />
						:

						<label class="screen-reader-text" for="end-date-minute">
							<?php esc_html_e( 'Expiration Date Minute', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="59" step="1" name="end_date_minute" id="end-date-minute" placeholder="59" />

						<?php echo esc_html( ' (' . edd_get_timezone_abbr() . ')' ); ?>
						<p class="description"><?php esc_html_e( 'Pick the date and time this discount will expire on. Leave blank to never expire.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_min_cart_amount' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-min-cart-amount"><?php esc_html_e( 'Minimum Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" id="edd-min-cart-amount" name="min_charge_amount" value="" placeholder="<?php esc_html_e( 'No minimum', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php esc_html_e( 'The minimum subtotal of item prices in a cart before this discount may be applied.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_max_uses' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-max-uses"><?php esc_html_e( 'Max Uses', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" id="edd-max-uses" name="max_uses" value="" placeholder="<?php esc_html_e( 'Unlimited', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php esc_html_e( 'The maximum number of times this discount can be used.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_add_discount_form_before_use_once' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-use-once"><?php esc_html_e( 'Use Once Per Customer', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="edd-use-once" name="once_per_customer" value="1"/>
						<span class="description"><?php esc_html_e( 'Prevent customers from using this discount more than once.', 'easy-digital-downloads' ); ?></span>
					</td>
				</tr>

				<?php
				/**
				 * Action after "Use Once Per Customer" checkbox.
				 *
				 * @since 3.0
				 */
				?>
				<?php do_action( 'edd_add_discount_form_after_use_once' ); ?>

			</tbody>
		</table>

		<?php do_action( 'edd_add_discount_form_bottom' ); ?>

		<p class="submit">
			<input type="hidden" name="type" value="discount" />
			<input type="hidden" name="edd-action" value="add_discount"/>
			<input type="hidden" name="edd-redirect" value="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-discounts' ) ) ); ?>"/>
			<input type="hidden" name="edd-discount-nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd_discount_nonce' ) ); ?>"/>
			<input type="submit" value="<?php esc_html_e( 'Add Discount Code', 'easy-digital-downloads' ); ?>" class="button-primary"/>
		</p>
	</form>
</div>
