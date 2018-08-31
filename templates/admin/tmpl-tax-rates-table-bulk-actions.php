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

	<div class="alignleft">
		<select id="edd-admin-tax-rates-table-bulk-actions">
			<option><?php esc_html_e( 'Bulk Actions', 'easy-digital-downloads' ); ?></option>
			<option value="active"><?php esc_html_e( 'Activate', 'easy-digital-downloads' ); ?></option>
			<option value="inactive"><?php esc_html_e( 'Dectivate', 'easy-digital-downloads' ); ?></option>
		</select>

		<button class="button edd-admin-tax-rates-table-filter"><?php esc_html_e( 'Apply', 'easy-digital-downloads' ); ?></button>

		<label for="hide-deactivated" class="edd-admin-tax-rates-table-hide">
			<input type="checkbox" id="hide-deactivated" checked />&nbsp;<?php esc_html_e( 'Hide deactivated rates', 'easy-digital-downloads' ); ?>
		</label>
	</div>

</div>
