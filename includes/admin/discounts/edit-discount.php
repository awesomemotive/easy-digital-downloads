<?php
/**
 * Edit Discount Page
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 400 ) );
}

$discount_id       = absint( $_GET['discount'] );
$discount          = edd_get_discount( $discount_id );
$product_reqs      = edd_get_discount_product_reqs( $discount_id );
$excluded_products = edd_get_discount_excluded_products( $discount_id );
$condition         = edd_get_discount_product_condition( $discount_id );
$single_use        = edd_discount_is_single_use( $discount_id );
$flat_display      = edd_get_discount_type( $discount_id ) == 'flat' ? '' : ' style="display:none;"';
$percent_display   = edd_get_discount_type( $discount_id ) == 'percent' ? '' : ' style="display:none;"';
$condition_display = empty( $product_reqs ) ? ' style="display:none;"' : '';
?>
<h2><?php _e( 'Edit Discount', 'edd' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-discounts' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd' ); ?></a></h2>
<form id="edd-edit-discount" action="" method="post">
	<?php do_action( 'edd_edit_discount_form_top', $discount_id, $discount ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'edd_edit_discount_form_before_name', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'edd' ); ?></label>
				</th>
				<td>
					<input name="name" id="edd-name" type="text" value="<?php echo esc_attr( stripslashes( $discount->post_title ) ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'The name of this discount', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_code', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-code"><?php _e( 'Code', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-code" name="code" value="<?php echo esc_attr( edd_get_discount_code( $discount_id ) ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_type', $discount_id, $discount ); ?>
			<tr>
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
			<?php do_action( 'edd_edit_discount_form_before_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-amount"><?php _e( 'Amount', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-amount" name="amount" value="<?php echo esc_attr( edd_get_discount_amount( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description edd-amount-description"<?php echo $flat_display; ?>><?php printf( __( 'Enter the discount amount in %s', 'edd' ), edd_get_currency() ); ?></p>
					<p class="description edd-amount-description"<?php echo $percent_display; ?>><?php _e( 'Enter the discount percentage. 10 = 10%', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_products', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-products"><?php printf( __( '%s Requirements', 'edd' ), edd_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'selected'    => $product_reqs,
							'multiple'    => true,
                            'chosen'      => true,
                            'placeholder' => sprintf( __( 'Select one or more %s', 'edd' ), edd_get_label_plural() )
						) ); ?><br/>
					</p>
					<div id="edd-discount-product-conditions"<?php echo $condition_display; ?>>
						<p>
							<select id="edd-product-condition" name="product_condition">
								<option value="all"<?php selected( 'all', $condition ); ?>><?php printf( __( 'Cart must contain all selected %s', 'edd' ), edd_get_label_plural() ); ?></option>
								<option value="any"<?php selected( 'any', $condition ); ?>><?php printf( __( 'Cart needs one or more of the selected %s', 'edd' ), edd_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="not_global" value="0"<?php checked( false, edd_is_discount_not_global( $discount_id ) ); ?>/>
								<?php _e( 'Apply discount to entire purchase.', 'edd' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="not_global" value="1"<?php checked( true, edd_is_discount_not_global( $discount_id ) ); ?>/>
								<?php printf( __( 'Apply discount only to selected %s.', 'edd' ), edd_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount.', 'edd' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_excluded_products', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-excluded-products"><?php printf( __( 'Excluded %s', 'edd' ), edd_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo EDD()->html->product_dropdown( array(
						'name'     => 'excluded-products[]',
						'id'       => 'excluded-products',
						'selected' => $excluded_products,
						'multiple' => true,
                        'chosen'   => true,
                        'placeholder' => sprintf( __( 'Select one or more %s', 'edd' ), edd_get_label_plural() )
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'edd' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_start', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-start"><?php _e( 'Start date', 'edd' ); ?></label>
				</th>
				<td>
					<input name="start" id="edd-start" type="text" value="<?php echo esc_attr( edd_get_discount_start_date( $discount_id ) ); ?>" style="width: 300px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_expiration', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-expiration"><?php _e( 'Expiration date', 'edd' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="edd-expiration" type="text" value="<?php echo esc_attr( edd_get_discount_expiration( $discount_id ) ); ?>" style="width: 300px;" class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_max_uses', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-max-uses"><?php _e( 'Max Uses', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-max-uses" name="max" value="<?php echo esc_attr( edd_get_discount_max_uses( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_min_cart_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-min-cart-amount" name="min_price" value="<?php echo esc_attr( edd_get_discount_min_price( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'edd' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'edd_edit_discount_form_before_status', $discount_id, $discount ); ?>
			<tr>
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
			<?php do_action( 'edd_edit_discount_form_before_use_once', $discount_id, $discount ); ?>
			<tr>
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
