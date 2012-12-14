<?php
/**
 * Edit Discount Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Discounts
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !isset( $_GET['discount'] ) || !is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd' ), __( 'Error', 'edd' ) );
}
$discount = edd_get_discount( $_GET['discount']);
?>
<h2><?php _e( 'Edit Discount', 'edd' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-discounts' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd' ); ?></a></h2>
<form id="edd-edit-discount" action="" method="POST">
	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'edd' ); ?></label>
				</th>
				<td>
					<input name="name" id="edd-name" type="text" value="<?php echo esc_attr( $discount['name'] ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'The name of this discount', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-code"><?php _e( 'Code', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-code" name="code" value="<?php echo esc_attr( $discount['code'] ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-type"><?php _e( 'Type', 'edd' ); ?></label>
				</th>
				<td>
					<select name="type" id="edd-type">
						<option value="percent" <?php selected( $discount['type'], 'percent' ); ?>><?php _e( 'Percentage', 'edd' ); ?></option>
						<option value="flat"<?php selected( $discount['type'], 'flat' ); ?>><?php _e( 'Flat amount', 'edd' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-amount"><?php _e( 'Amount', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-amount" name="amount" value="<?php echo esc_attr( $discount['amount'] ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The amount of this discount code.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-start"><?php _e( 'Start date', 'edd' ); ?></label>
				</th>
				<td>
					<input name="start" id="edd-start" type="text" value="<?php echo esc_attr( $discount['start'] ); ?>" style="width: 120px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-expiration"><?php _e( 'Expiration date', 'edd' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="edd-expiration" type="text" value="<?php echo esc_attr( $discount['expiration'] ); ?>" style="width: 120px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-max-uses"><?php _e( 'Max Uses', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-max-uses" name="max" value="<?php echo esc_attr( $discount['max'] ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-min-cart-amount" name="min_price" value="<?php echo esc_attr( $discount['min_price'] ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-type"><?php _e( 'Status', 'edd' ); ?></label>
				</th>
				<td>
					<select name="status" id="edd-status">
						<option value="active" <?php selected( $discount['status'], 'active' ); ?>><?php _e( 'Active', 'edd' ); ?></option>
						<option value="inactive"<?php selected( $discount['status'], 'inactive' ); ?>><?php _e( 'Inactive', 'edd' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The status of this discount code.', 'edd' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="edd-action" value="edit_discount"/>
		<input type="hidden" name="discount-id" value="<?php echo absint( $_GET['discount'] ); ?>"/>
		<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ); ?>"/>
		<input type="hidden" name="edd-discount-nonce" value="<?php echo wp_create_nonce( 'edd_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Update Discount Code', 'edd' ); ?>" class="button-primary"/>
	</p>
</form>