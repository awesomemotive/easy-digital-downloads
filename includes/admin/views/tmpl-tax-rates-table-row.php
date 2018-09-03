<?php
/**
 * Admin tax table rate row.
 *
 * @since 3.0
 *
 * @package EDD
 * @category Template
 * @author Easy Digital Downloads
 * @version 1.0.0
 */
?>

<td class="edd-tax-rates-table-checkbox">
	<input type="checkbox" <# if ( data.selected ) { #>checked<# } #> />
</td>

<td>
	{{ data.country }}
	<input type="hidden" name="tax_rates[{{ data.id }}][country]" value="{{ data.country }}" />
</td>

<td>
	<# if ( data.global ) { #>
	&mdash;
	<input type="hidden" name="tax_rates[{{ data.id }}][global]" value="{{ data.global }}" />
	<# } else { #>
	{{ data.region }}
	<input type="hidden" name="tax_rates[{{ data.id }}][state]" value="{{ data.region }}" />
	<# } #>
</td>

<td>
	{{ data.formattedAmount }}
	<input type="hidden" name="tax_rates[{{ data.id }}][rate]" value="{{ data.amount }}" />
</td>

<td class="edd-tax-rates-table-actions">
	<# if ( data.unsaved ) { #>
		<button class="button-link remove" data-cid="{{ data.id }}"><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?></button>
	<# } else if ( 'active' === data.status ) { #>
		<button class="button-link deactivate"><?php esc_html_e( 'Deactivate', 'easy-digital-downloads' ); ?></button>
	<# } else { #>
		<button class="button-link activate"><?php esc_html_e( 'Activate', 'easy-digital-downloads' ); ?></button>
	<# } #>

	<input type="hidden" name="tax_rates[{{ data.id }}][status]" value="{{ data.status }}" />
</td>
