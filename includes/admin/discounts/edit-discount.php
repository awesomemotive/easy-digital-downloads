<?php
/**
 * Edit Discount Page
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd' ), __( 'Error', 'edd' ) );
}

$discount_id  = absint( $_GET['discount'] );
$discount     = edd_get_discount( $discount_id );
$product_reqs = edd_get_discount_product_reqs( $discount_id );
$condition    = edd_get_discount_product_condition( $discount_id );
$single_use   = edd_discount_is_single_use( $discount_id );
?>
<h2><?php _e( 'Edit Discount', 'edd' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-discounts' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd' ); ?></a></h2>
<form id="edd-edit-discount" action="" method="post">
	<?php do_action( 'edd_edit_discount_form_top', $discount_id, $discount ); ?>
	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'edd' ); ?></label>
				</th>
				<td>
					<input name="name" id="edd-name" type="text" value="<?php echo esc_attr( $discount->post_title ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'The name of this discount', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-code"><?php _e( 'Code', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-code" name="code" value="<?php echo esc_attr( edd_get_discount_code( $discount_id ) ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-type"><?php _e( 'Type', 'edd' ); ?></label>
				</th>
				<td>
					<select name="type" id="edd-type">
						<option value="percent" <?php selected( edd_get_discount_type( $discount_id ), 'percent' ); ?>><?php _e( 'Percentage', 'edd' ); ?></option>
						<option value="flat"<?php selected( edd_get_discount_type( $discount_id ), 'flat' ); ?>><?php _e( 'Flat amount', 'edd' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-amount"><?php _e( 'Amount', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-amount" name="amount" value="<?php echo esc_attr( edd_get_discount_amount( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The amount of this discount code.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-products"><?php printf( __( '%s Requirements', 'edd' ), edd_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<select id="edd-product-condition" name="product_condition">
							<option value="all"<?php selected( 'all', $condition ); ?>><?php printf( __( 'All Selected %s', 'edd' ), edd_get_label_plural() ); ?></option>
							<option value="any"<?php selected( 'any', $condition ); ?>><?php printf( __( 'Any Selected %s', 'edd' ), edd_get_label_singular() ); ?></option>
						</select>
						<label for="edd-product-condition"><?php _e( 'Condition', 'edd' ); ?></label>
					</p>
					<select multiple id="edd-products" name="products[]" class="edd-select-chosen" data-placeholder="<?php printf( __( 'Choose one or more %s', 'edd' ), edd_get_label_plural() ); ?>">
						<?php
						$downloads = get_posts( array( 'post_type' => 'download', 'nopaging' => true ) );
						if( $downloads ) :
							foreach( $downloads as $download ) :
								echo '<option value="' . esc_attr( $download->ID ) . '"' . selected( true, in_array( $download->ID, $product_reqs ), false ) . '>' . esc_html( get_the_title( $download->ID ) ) . '</option>';
							endforeach;
						endif;
						?>
					</select><br/>
					<p class="description"><?php printf( __( '%s required to be purchased for this discount.', 'edd' ), edd_get_label_plural() ); ?></p>
					<p>
						<label for="edd-non-global-discount">
							<input type="checkbox" id="edd-non-global-discount" name="not_global" value="1"<?php checked( true, edd_is_discount_not_global( $discount_id ) ); ?>/>
							<?php printf( __( 'Apply discount only to selected %s?', 'edd' ), edd_get_label_plural() ); ?>
						</label>
					</p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-start"><?php _e( 'Start date', 'edd' ); ?></label>
				</th>
				<td>
					<input name="start" id="edd-start" type="text" value="<?php echo esc_attr( edd_get_discount_start_date( $discount_id ) ); ?>" style="width: 120px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-expiration"><?php _e( 'Expiration date', 'edd' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="edd-expiration" type="text" value="<?php echo esc_attr( edd_get_discount_expiration( $discount_id ) ); ?>" style="width: 120px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-max-uses"><?php _e( 'Max Uses', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-max-uses" name="max" value="<?php echo esc_attr( edd_get_discount_max_uses( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-min-cart-amount" name="min_price" value="<?php echo esc_attr( edd_get_discount_min_price( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-status"><?php _e( 'Status', 'edd' ); ?></label>
				</th>
				<td>
					<select name="status" id="edd-status">
						<option value="active" <?php selected( $discount->post_status, 'active' ); ?>><?php _e( 'Active', 'edd' ); ?></option>
						<option value="inactive"<?php selected( $discount->post_status, 'inactive' ); ?>><?php _e( 'Inactive', 'edd' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The status of this discount code.', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-use-once"><?php _e( 'Use Once Per Customer', 'edd' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="edd-use-once" name="use_once" value="1"<?php checked( true, $single_use ); ?>/>
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'edd' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'edd_edit_discount_form_bottom', $discount_id, $discount ); ?>
	<p class="submit">
		<input type="hidden" name="edd-action" value="edit_discount"/>
		<input type="hidden" name="discount-id" value="<?php echo absint( $_GET['discount'] ); ?>"/>
		<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ); ?>"/>
		<input type="hidden" name="edd-discount-nonce" value="<?php echo wp_create_nonce( 'edd_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Update Discount Code', 'edd' ); ?>" class="button-primary"/>
	</p>
</form>
