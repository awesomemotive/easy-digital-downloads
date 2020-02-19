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
						<# if ( data._selected ) { #>
							selected
						<# } #>
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
