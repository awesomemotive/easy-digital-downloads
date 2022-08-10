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

<th class="edd-tax-rates-table-checkbox check-column">
	<input type="checkbox" <# if ( data.selected ) { #>checked<# } #> />
</th>

<td class="edd-tax-rates-table-country" data-colname="<?php esc_attr_e( 'Country', 'easy-digital-downloads' ); ?>">
	<# if ( data.country ) { #>
		{{ data.country }}
	<# } else { #>
		*
	<# } #>
	<input type="hidden" name="tax_rates[{{ data.id }}][country]" value="{{ data.country }}" />
</td>

<td data-colname="<?php esc_attr_e( 'Region', 'easy-digital-downloads' ); ?>">
	<# if ( data.global ) { #>
	&mdash;
	<input type="hidden" name="tax_rates[{{ data.id }}][global]" value="{{ data.global }}" />
	<# } else if ( data.region ) { #>
	{{ data.region }}
	<input type="hidden" name="tax_rates[{{ data.id }}][state]" value="{{ data.region }}" />
	<# } else { #>
	&mdash;
	<input type="hidden" name="tax_rates[{{ data.id }}][state]" value="" />
	<# } #>
</td>

<td data-colname="<?php esc_attr_e( 'Rate', 'easy-digital-downloads' ); ?>">
	{{ data.formattedAmount }}
	<input type="hidden" name="tax_rates[{{ data.id }}][rate]" value="{{ data.amount }}" />
</td>

<td class="edd-tax-rates-table-actions" data-colname="<?php esc_attr_e( 'Actions', 'easy-digital-downloads' ); ?>">
	<# if ( data.unsaved ) { #>
		<button class="button-link remove" data-cid="{{ data.id }}"><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?></button>
	<# } else if ( 'active' === data.status ) { #>
		<button class="button-link deactivate"><?php esc_html_e( 'Deactivate', 'easy-digital-downloads' ); ?></button>
	<# } else { #>
		<button class="button-link activate"><?php esc_html_e( 'Activate', 'easy-digital-downloads' ); ?></button>
	<# } #>

	<input type="hidden" name="tax_rates[{{ data.id }}][status]" value="{{ data.status }}" />
</td>
