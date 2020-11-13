<?php
/**
 * Order Overview: Totals
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
?>

<tr class="is-expanded">
	<td></td>
	<td colspan="{{ data.config.colspan }}" class="column-primary">
		<?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>
	</td>
	<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
		{{ data.subtotalCurrency }}
	</td>
</tr>

<# if ( false !== data.state.hasTax ) { #>

	<tr class="is-expanded">
		<td></td>
		<td colspan="{{ data.config.colspan }}" class="column-primary">
			<?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>
			<# if ( false !== data.state.hasTax ) { #>
			<br />
			<small>
				<# if ( false !== data.state.hasTax.rate && '' !== data.state.hasTax.country ) { #>
					{{ data.state.hasTax.country}}<# if ( '' !== data.state.hasTax.region ) { #>: {{ data.state.hasTax.region }}<# } #> &ndash; {{ data.state.hasTax.rate.toFixed( 2 ) }}%
				<# } #>
			</small>
			<# } #>
		</td>
		<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
			{{ data.taxCurrency }}
		</td>
	</tr>

	<# if ( false !== data.state.hasNewTaxRate && data.state.items.length > 0 ) { #>
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
							'{{ data.state.hasTax.rate.toFixed( 2 ) }}%'
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

<tr class="is-expanded edd-order-overview-summary__totals-total">
	<td></td>
	<td colspan="{{ data.config.colspan }}" class="column-primary">
		<?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?>

		<# if ( data.state.hasManualAdjustment ) { #>
			<br />
			<small><?php esc_html_e( '&dagger; Some amounts have been manually adjusted.', 'easy-digital-downloads' ); ?></small>
		<# } #>
	</td>
	<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
		<span class="total <# if ( data.total < 0 ) { #>is-negative<# } #>">{{ data.totalCurrency }}</span>
	</td>
</tr>

<input type="hidden" value="{{ data.subtotal }}" name="subtotal" />
<input type="hidden" value="{{ data.tax }}" name="tax" />
<input type="hidden" value="{{ data.discount }}" name="discount" />
<input type="hidden" value="{{ data.total }}" name="total" />
