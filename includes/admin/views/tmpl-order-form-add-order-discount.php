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
	'status' => 'active',
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
				required
			>
				<option value=""><?php esc_html_e( 'Choose a discount', 'easy-digital-downloads' ); ?></option>
				<?php
				if ( false !== $discounts ) :
					foreach ( $discounts as $discount ) :
				?>
					<option
						data-code="<?php echo esc_attr( $discount->code ); ?>"
						value="<?php echo esc_attr( $discount->id ); ?>"
						<# if ( <?php echo esc_js( $discount->id ); ?> === data.typeId ) { #>
							selected
						<# } #>
					>
						<?php echo esc_html( $discount->code ); ?> &ndash; <?php echo esc_html( $discount->name ); ?>
					</option>
				<?php
					endforeach;
				endif;
				?>
			</select>

			<# if ( true === data._isDuplicate ) { #>
			<span class="edd-order-overview-error">
				<?php esc_html_e( 'This Discount already applied to the Order.', 'easy-digital-downloads' ); ?>
			</span>
			<# } #>
		</p>

		<p class="submit">
			<# if ( true === data.state.isFetching ) { #>
				<span class="spinner is-active edd-ml-auto"></span>
			<# } #>

			<input
				type="submit"
				class="button button-primary edd-ml-auto"
				value="<?php esc_html_e( 'Add Discount', 'easy-digital-downloads' ); ?>"
				<# if ( 0 === data.typeId || true === data._isDuplicate || true === data.state.isFetching ) { #>
					disabled
				<# } #>
			/>
		</p>
	</form>
</div>
