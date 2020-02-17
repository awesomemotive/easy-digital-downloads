<?php
/**
 * Order Overview: Add Discount form
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$discounts = edd_get_discounts( array(
	'number' => 100,
) );
?>

<div class="edd-order-overview-modal">
	<form class="edd-order-overview-add-discount">
		<p>
			<label for="discount">
				<?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?>
			</label>

			<select
				id="discount"
				class="edd-select"
			>
				<option value=""><?php esc_html_e( 'Choose a discount', 'easy-digital-downloads' ); ?></option>
				<?php
				if ( false !== $discounts ) :
					foreach ( $discounts as $discount ) :
				?>
					<option
						data-product-requirements="<?php echo esc_attr( implode( ',', $discount->get_product_reqs() ) ); ?>"
						data-product-exclusions="<?php echo esc_attr( implode( ',', $discount->get_excluded_products() ) ); ?>"
						data-product-condition="<?php echo esc_attr( $discount->product_condition ); ?>"
						data-amount-type="<?php echo esc_attr( $discount->amount_type ); ?>"
						data-amount="<?php echo esc_attr( $discount->amount ); ?>"
						data-name="<?php echo esc_attr( $discount->name ); ?>"
						data-code="<?php echo esc_attr( $discount->code ); ?>"
						data-scope="<?php echo esc_attr( $discount->scope ); ?>"
						data-status="<?php echo esc_attr( $discount->status ); ?>"
						data-id="<?php echo esc_attr( $discount->id ); ?>"
						value="<?php echo esc_attr( $discount->id ); ?>"
					>
						<?php echo esc_html( $discount->name ); ?>: <?php echo esc_html( $discount->code ); ?>
					</option>
				<?php
					endforeach;
				endif;
				?>
			</select>
		</p>

		<p class="submit">
			<input
				type="submit"
				class="button button-primary edd-ml-auto"
				value="<?php esc_html_e( 'Add Discount', 'easy-digital-downloads' ); ?>"
				<# if ( 0 === data.typeId ) { #>
					disabled
				<# } #>
			/>
		</p>
	</form>
</div>
