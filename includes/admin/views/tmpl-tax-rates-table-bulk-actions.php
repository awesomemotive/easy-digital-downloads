<?php
/**
 * Admin tax table bulk actions.
 *
 * @since 3.0
 *
 * @package EDD
 * @category Template
 * @author Easy Digital Downloads
 * @version 1.0.0
 */
?>

<div class="tablenav top">

	<div class="edd-admin-tax-rates__tablenav--left">
		<select id="edd-admin-tax-rates-table-bulk-actions">
			<option><?php esc_html_e( 'Bulk Actions', 'easy-digital-downloads' ); ?></option>
			<option value="active"><?php esc_html_e( 'Activate', 'easy-digital-downloads' ); ?></option>
			<option value="inactive"><?php esc_html_e( 'Deactivate', 'easy-digital-downloads' ); ?></option>
		</select>

		<button class="button edd-admin-tax-rates-table-filter"><?php esc_html_e( 'Apply', 'easy-digital-downloads' ); ?></button>
	</div>

	<div class="edd-admin-tax-rates__tablenav--right">
		<label class="edd-toggle edd-admin-tax-rates-table-hide">
			<span class="label"><?php esc_html_e( 'Show deactivated rates', 'easy-digital-downloads' ); ?></span>
			<input type="checkbox" id="hide-deactivated" />
		</label>
	</div>

</div>
