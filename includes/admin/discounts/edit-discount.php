<?php
/**
 * Edit Discount Page
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Bail if no discount passed
if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}

// Load discount
$discount_id = absint( $_GET['discount'] );

/** @var EDD_Discount */
$discount = edd_get_discount( $discount_id );

// Bail if discount does not exist
if ( empty( $discount ) ) {
	wp_die( __( 'Something went wrong.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}

// Setup discount vars
$product_requirements = $discount->get_product_reqs();
$excluded_products    = $discount->get_excluded_products();
$condition            = $discount->get_product_condition();
$single_use           = $discount->get_once_per_customer();
$type                 = $discount->get_type();
$notes                = edd_get_discount_notes( $discount->id );

// Show/Hide
$flat_display         = ( 'flat'    === $type          ) ? '' : ' style="display:none;"';
$percent_display      = ( 'percent' === $type          ) ? '' : ' style="display:none;"';
$no_notes_display     =   empty( $notes                ) ? '' : ' style="display:none;"';
$condition_display    = ! empty( $product_requirements ) ? '' : ' style="display:none;"';

// Dates & times
$discount_start_date  = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $discount->start_date, 'utc' ) );
$discount_end_date    = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $discount->end_date, 'utc' ) );
$start_date           = $discount_start_date->format( 'Y-m-d' );
$start_hour           = $discount_start_date->format( 'H' );
$start_minute         = $discount_start_date->format( 'i' );
$end_date             = $discount_end_date->format( 'Y-m-d' );
$end_hour             = $discount_end_date->format( 'H' );
$end_minute           = $discount_end_date->format( 'i' );
$hours                = edd_get_hour_values();
$minutes              = edd_get_minute_values();
?>
<div class="wrap">
	<h1><?php _e( 'Edit Discount', 'easy-digital-downloads' ); ?></h1>

	<hr class="wp-header-end">

	<form id="edd-edit-discount" action="" method="post">
		<?php do_action( 'edd_edit_discount_form_top', $discount->id, $discount ); ?>

		<table class="form-table">
			<tbody>

				<?php do_action( 'edd_edit_discount_form_before_name', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-name"><?php _e( 'Name', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input name="name" required="required" id="edd-name" type="text" value="<?php echo esc_attr( stripslashes( $discount->name ) ); ?>" placeholder="<?php esc_html_e( 'Summer Sale', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The name of this discount. Customers will see this on checkout.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_code', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-code"><?php _e( 'Code', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" required="required" id="edd-code" name="code" value="<?php echo esc_attr( $discount->code ); ?>" pattern="[a-zA-Z0-9-_]+" class="code" placeholder="<?php esc_html_e( '10PERCENT', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The code customers will enter to apply this discount. Only alphanumeric characters are allowed.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_type', $discount->id, $discount ); ?>

				<?php do_action( 'edd_edit_discount_form_before_amount', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-amount"><?php _e( 'Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<span class="edd-amount-type-wrapper">
							<input type="text" required="required" class="edd-price-field" id="edd-amount" name="amount" value="<?php echo esc_attr( edd_format_amount( $discount->amount ) ); ?>" placeholder="<?php esc_html_e( '10.00', 'easy-digital-downloads' ); ?>" />
							<label for="edd-amount-type" class="screen-reader-text"><?php esc_html_e( 'Amount Type', 'easy-digital-downloads' ); ?></label>
							<select name="amount_type" id="edd-amount-type">
								<option value="percent" <?php selected( $type, 'percent' ); ?>>%</option>
								<option value="flat"<?php selected( $type, 'flat' ); ?>><?php echo esc_html( edd_currency_symbol() ); ?></option>
							</select>
						</span>
						<p class="description"><?php _e( 'The amount as a percentage or flat rate. Cannot be left blank.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_products', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd_products"><?php printf( __( '%s Requirements', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
					</th>
					<td>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'product_reqs[]',
							'id'          => 'edd_products',
							'selected'    => $product_requirements,
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
							'variations'  => true,
						) ); ?>
						<div id="edd-discount-product-conditions"<?php echo $condition_display; ?>>
							<p>
								<select id="edd-product-condition" name="product_condition">
									<option value="all"<?php selected( 'all', $condition ); ?>><?php printf( __( 'Cart must contain all selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
									<option value="any"<?php selected( 'any', $condition ); ?>><?php printf( __( 'Cart needs one or more of the selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
								</select>
							</p>
							<p>
								<label>
									<input type="radio" class="tog" name="scope" value="global"<?php checked( 'global', $discount->scope ); ?>/>
									<?php _e( 'Apply discount to entire purchase.', 'easy-digital-downloads' ); ?>
								</label><br/>
								<label>
									<input type="radio" class="tog" name="scope" value="not_global"<?php checked( 'not_global', $discount->scope ); ?>/>
									<?php printf( __( 'Apply discount only to selected %s.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>
								</label>
							</p>
						</div>
						<p class="description"><?php printf( __( '%s this discount can only be applied to. Leave blank for any.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_excluded_products', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-excluded-products"><?php printf( __( 'Excluded %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></label>
					</th>
					<td>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'excluded_products[]',
							'id'          => 'excluded_products',
							'selected'    => $excluded_products,
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select %s', 'easy-digital-downloads' ), edd_get_label_plural() )
						) ); ?>
						<p class="description"><?php printf( __( '%s this discount cannot be applied to. Leave blank for none.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
					</td>
				</tr>

				<?php
				$categories     = edd_get_adjustment_meta( $discount->id, 'categories', true );
				$term_condition = edd_get_adjustment_meta( $discount->id, 'term_condition', true );
				$term_condition = $term_condition ?: '';
				include 'views/categories.php';
				?>

				<?php do_action( 'edd_edit_discount_form_before_start', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-start"><?php _e( 'Start date', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="edd-discount-datetime">
						<input name="start_date" id="edd-start" type="text" value="<?php echo esc_attr( false !== $discount->start_date ? $start_date : '' ); ?>" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />

						<label class="screen-reader-text" for="start-date-hour">
							<?php esc_html_e( 'Start Date Hour', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="24" step="1" name="start_date_hour" id="start-date-hour" value="<?php echo esc_attr( false !== $discount->start_date ? $start_hour : '' ); ?>" placeholder="00" />
						:

						<label class="screen-reader-text" for="start-date-minute">
							<?php esc_html_e( 'Start Date Minute', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="59" step="1" name="start_date_minute" id="start-date-minute" value="<?php echo esc_attr( false !== $discount->start_date ? $start_minute : '' ); ?>" placeholder="00" />

						<?php echo esc_html( ' (' . edd_get_timezone_abbr() . ')' ); ?>
						<p class="description"><?php _e( 'Pick the date and time this discount will start on. Leave blank for no start date.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_expiration', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-expiration"><?php _e( 'Expiration date', 'easy-digital-downloads' ); ?></label>
					</th>
					<td class="edd-discount-datetime">
						<input name="end_date" id="edd-expiration" type="text" value="<?php echo esc_attr( false !== $discount->end_date ? $end_date : '' ); ?>"  class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />

						<label class="screen-reader-text" for="end-date-hour">
							<?php esc_html_e( 'Expiration Date Hour', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="24" step="1" name="end_date_hour" id="end-date-hour" value="<?php echo esc_attr( false !== $discount->end_date ? $end_hour : '' ); ?>" placeholder="23" />
						:

						<label class="screen-reader-text" for="end-date-minute">
							<?php esc_html_e( 'Expiration Date Minute', 'easy-digital-downloads' ); ?>
						</label>
						<input type="number" min="0" max="59" step="1" name="end_date_minute" id="end-date-minute" value="<?php echo esc_attr( false !== $discount->end_date ? $end_minute : '' ); ?>" placeholder="59" />

						<?php echo esc_html( ' (' . edd_get_timezone_abbr() . ')' ); ?>
						<p class="description"><?php _e( 'Pick the date and time this discount will expire on. Leave blank to never expire.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_min_cart_amount', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" id="edd-min-cart-amount" name="min_charge_amount" value="<?php echo esc_attr( edd_format_amount( $discount->min_charge_amount ) ); ?>" placeholder="<?php esc_html_e( 'No minimum', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The minimum subtotal of item prices in a cart before this discount may be applied.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_max_uses', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-max-uses"><?php _e( 'Max Uses', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="text" id="edd-max-uses" name="max_uses" value="<?php echo esc_attr( $discount->max_uses ); ?>" placeholder="<?php esc_html_e( 'Unlimited', 'easy-digital-downloads' ); ?>" />
						<p class="description"><?php _e( 'The maximum number of times this discount can be used.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_use_once', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-use-once"><?php _e( 'Use Once Per Customer', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="edd-use-once" name="once_per_customer" value="1"<?php checked( true, $single_use ); ?>/>
						<span class="description"><?php _e( 'Prevent customers from using this discount more than once.', 'easy-digital-downloads' ); ?></span>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_status', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="edd-status"><?php _e( 'Status', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<select name="status" id="edd-status">
							<option value="active" <?php selected( $discount->status, 'active' ); ?>><?php esc_html_e( 'Active', 'easy-digital-downloads' ); ?></option>
							<option value="inactive"<?php selected( $discount->status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'easy-digital-downloads' ); ?></option>
							<option value="archived"<?php selected( $discount->status, 'archived' ); ?>><?php esc_html_e( 'Archived', 'easy-digital-downloads' ); ?></option>
						</select>
						<p class="description"><?php _e( 'The status of this discount code.', 'easy-digital-downloads' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'edd_edit_discount_form_before_notes', $discount->id, $discount ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="notes"><?php _e( 'Discount Notes', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<div class="edd-notes-wrapper">
							<?php echo edd_admin_get_notes_html( $notes ); ?>
							<?php echo edd_admin_get_new_note_form( $discount->id, 'discount' ); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php do_action( 'edd_edit_discount_form_bottom', $discount->id, $discount ); ?>

		<p class="submit">
			<input type="hidden" name="type" value="discount" />
			<input type="hidden" name="edd-action" value="edit_discount" />
			<input type="hidden" name="discount-id" value="<?php echo esc_attr( $discount->id ); ?>" />
			<input type="hidden" name="edd-redirect" value="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-discounts', 'edd-action' => 'edit_discount', 'discount' => absint( $discount->id ) ) ) ); ?>" />
			<input type="hidden" name="edd-discount-nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd_discount_nonce' ) ); ?>" />
			<input type="submit" value="<?php esc_html_e( 'Update Discount Code', 'easy-digital-downloads' ); ?>" class="button-primary" />
		</p>
	</form>
</div>
