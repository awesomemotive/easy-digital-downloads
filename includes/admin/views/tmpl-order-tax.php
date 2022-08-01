<?php
/**
 * Order Overview: Tax
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
?>

<# if ( 'none' !== data.state.hasTax && false !== data.state.hasTax.rate ) { #>

	<tr class="is-expanded">
		<td></td>
		<td colspan="{{ data.config.colspan }}" class="column-primary">
			<# if ( false !== data.state.hasTax.rate && '' !== data.state.hasTax.country ) { #>
				{{ data.state.hasTax.country }}<# if ( '' !== data.state.hasTax.region ) { #>: {{ data.state.hasTax.region }}<# } #> &ndash; {{ data.state.hasTax.rate }}%
				<br />
			<# } else if ( false !== data.state.hasTax.rate ) { #>
				{{ data.state.hasTax.rate }}%
				<br />
			<# } #>
			<small>
				<?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>
			</small>
		</td>
		<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
			{{ data.taxCurrency }}
		</td>
	</tr>

	<# if ( false !== data.state.hasNewTaxRate && true === data.hasTaxableItems ) { #>
	<tr class="is-expanded">
		<td></td>
		<td colspan="{{ data.config.colspan + 1 }}" style="padding: 0;" class="column-primary">
			<div
				id="notice-tax-change"
				class="notice notice-warning is-dismissible"
			>
				<p>
					<?php
					echo esc_html(
						sprintf(
							__( 'The tax rate has been updated to %1$s. Existing automatically calculated amounts have not been updated.', 'easy-digital-downloads' ),
							'{{ data.state.hasTax.rate }}%'
						)
					);
					?>
				</p>
				<p>
					<button
						type="button"
						class="button button-secondary button-small update-amounts"
						style="marign-left: 0;"
					>
						<?php esc_html_e( 'Update Amounts', 'easy-digital-downloads' ); ?>
					</button>
				</p>

				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'easy-digital-downloads' ); ?></span>
				</button>
			</div>
		</td>
	</tr>
	<# } #>

<# } #>

<input type="hidden" value="{{ data.tax }}" name="tax" />
<input type="hidden" value="{{ data.state.hasTax.rate }}" name="tax_rate" />
