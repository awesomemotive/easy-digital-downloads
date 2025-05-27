<?php
/**
 * Admin tax table dialog.
 *
 * @since 3.3.9
 *
 * @package   EDD\Admin\Views
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version   1.0.0
 */

?>

<form class="edd-tax-rates-dialog">
	<div class="edd-tax-rates-dialog__content">
		<div class="edd-form-group">
			<label for="tax_rate_country"><?php esc_html_e( 'Country', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control">
				<?php
				add_filter(
					'edd_countries',
					function ( $countries ) {
						return array( '*' => __( 'All Countries', 'easy-digital-downloads' ) ) + $countries;
					}
				);
				echo EDD()->html->country_select(
					array(
						'id'                => 'tax_rate_country',
						'show_option_empty' => __( 'Select a country', 'easy-digital-downloads' ),
					)
				);
				?>
			</div>
		</div>

		<div id="tax_rate_region_global" class="edd-form-group edd-hidden">
			<div class="edd-form-group__control edd-toggle">
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'Apply to whole country', 'easy-digital-downloads' ); ?>
				</label>
			</div>
		</div>

		<div id="tax_rate_region_wrapper" class="edd-form-group edd-hidden">
			<label for="tax_rate_region"><?php esc_html_e( 'Region', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control"></div>
		</div>

		<div class="edd-form-group">
			<label for="tax_rate_amount"><?php esc_html_e( 'Rate', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control edd-amount-type-wrapper">
				<input type="number" step="0.0001" min="0.0" max="99" id="tax_rate_amount" />
				<span class="edd-input__symbol edd-input__symbol--suffix"><?php echo esc_html( _x( '%', 'tax rate', 'easy-digital-downloads' ) ); ?></span>
			</div>
		</div>
	</div>

	<div class="edd-tax-rates-dialog__actions">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'Add Rate', 'easy-digital-downloads' ); ?></button>
		<button type="button" class="button button-secondary edd-cancel"><?php esc_html_e( 'Cancel', 'easy-digital-downloads' ); ?></button>
	</div>
</form>
