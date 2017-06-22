<?php
/**
 * Add Discount Page
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<h2><?php _e( 'Add New Discount', 'easy-digital-downloads' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-discounts' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'easy-digital-downloads' ); ?></a></h2>
<form id="edd-add-discount" action="" method="POST">
	<?php do_action( 'edd_add_discount_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'edd_add_discount_form_before_name' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="edd-name" type="text" value="" style="width: 300px;"/>
					<p class="description"><?php _e( 'The name of this discount', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_code' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-code"><?php _e( 'Code', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="edd-code" name="code" value="" pattern="[a-zA-Z0-9]+" style="width: 300px;"/>
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_type' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-type"><?php _e( 'Type', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<select name="type" id="edd-type">
						<option value="percent"><?php _e( 'Percentage', 'easy-digital-downloads' ); ?></option>
						<option value="flat"><?php _e( 'Flat amount', 'easy-digital-downloads' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-amount"><?php _e( 'Amount', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="edd-amount" name="amount" value="" style="width: 40px;"/>
					<p class="description edd-amount-description flat-discount" style="display:none;"><?php printf( __( 'Enter the discount amount in %s', 'easy-digital-downloads' ), edd_get_currency() ); ?></p>
					<p class="description edd-amount-description percent-discount"><?php _e( 'Enter the discount percentage. 10 = 10%', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_products' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-products"><?php printf( __( '%s Requirements', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
						) ); ?><br/>
					</p>
					<div id="edd-discount-product-conditions" style="display:none;">
						<p>
							<select id="edd-product-condition" name="product_condition">
								<option value="all"><?php printf( __( 'Cart must contain all selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
								<option value="any"><?php printf( __( 'Cart needs one or more of the selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="not_global" value="0" checked="checked"/>
								<?php _e( 'Apply discount to entire purchase.', 'easy-digital-downloads' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="not_global" value="1"/>
								<?php printf( __( 'Apply discount only to selected %s.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount. If left blank, this discount can be used on any product.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_excluded_products' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-excluded-products"><?php printf( __( 'Excluded %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo EDD()->html->product_dropdown( array(
						'name'        => 'excluded-products[]',
						'id'          => 'excluded-products',
						'selected'    => array(),
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_start' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-start"><?php _e( 'Start date', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input name="start" id="edd-start" type="text" value="" style="width: 300px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_expiration' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-expiration"><?php _e( 'Expiration date', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="edd-expiration" type="text" style="width: 300px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_min_cart_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-min-cart-amount" name="min_price" value="" style="width: 40px;"/>
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_max_uses' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-max-uses"><?php _e( 'Max Uses', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-max-uses" name="max" value="" style="width: 40px;"/>
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_add_discount_form_before_use_once' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-use-once"><?php _e( 'Use Once Per Customer', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="edd-use-once" name="use_once" value="1"/>
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'easy-digital-downloads' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'edd_add_discount_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="edd-action" value="add_discount"/>
		<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ); ?>"/>
		<input type="hidden" name="edd-discount-nonce" value="<?php echo wp_create_nonce( 'edd_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Add Discount Code', 'easy-digital-downloads' ); ?>" class="button-primary"/>
	</p>
</form>
