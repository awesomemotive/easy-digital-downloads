<?php
/**
 * Order Overview: Add Adjustment form
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
?>

<div class="edd-order-overview-modal">
	<form class="edd-order-overview-add-adjustment">
		<fieldset>
			<legend>
				<?php esc_html_e( 'Type', 'easy-digital-downloads' ); ?>
			</legend>

			<p>
				<label for="fee">
					<input
						type="radio"
						id="fee"
						name="type"
						value="fee"
						<# if ( 'fee' === data.type ) { #>
							checked
						<# } #>
					/>
					<?php echo esc_html_e( 'Fee', 'easy-digital-downloads' ); ?>
				</label>
			</p>

			<p>
				<label for="credit">
					<input
						type="radio"
						id="credit"
						name="type"
						value="credit"
						<# if ( 'credit' === data.type ) { #>
							checked
						<# } #>
					/>
					<?php echo esc_html_e( 'Credit', 'easy-digital-downloads' ); ?>
				</label>
			</p>
		</fieldset>

		<# if ( 'fee' === data.type && data.orderItems.length > 0 ) { #>
		<p>
			<label for="object_type">
				<?php esc_html_e( 'Apply to', 'easy-digital-downloads' ); ?>
			</label>

			<select
				id="object_type"
				class="edd-select"
				required
			>
				<option value="order"><?php esc_html_e( 'Entire order', 'easy-digital-downloads' ); ?></option>
				<# _.each( data.orderItems, ( item ) => { #>
					<option
						value="order_item"
						data-order-item-id={{ item.id }}
						<# if ( item.id === data.objectId ) { #>
							selected
						<# } #>
					>
						{{ item.productName }}
					</option>
				<# } ); #>
			</select>
		</p>
		<# } #>

		<p>
			<label for="amount">
				<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>
			</label>
			<span class="edd-amount">
				<?php if ( 'before' === $currency_position ) : ?>
					<?php echo edd_currency_filter( '' ); ?>
				<?php endif; ?>

				<input
					type="text"
					id="amount"
					value="{{ data.amountManual }}"
					required
				/>

				<?php if ( 'after' === $currency_position ) : ?>
					<?php echo edd_currency_filter( '' ); ?>
				<?php endif; ?>
			</span>
		</p>

		<# if ( 'none' !== data.state.hasTax && 'fee' === data.type ) { #>
		<p>
			<label
				class="edd-toggle"
				for="no-tax"
			>
				<input
					type="checkbox"
					id="no-tax"
					<# if ( true === data.isTaxed ) { #>
						checked
					<# } #>
				/>
				<span class="label">
					<?php esc_html_e( 'Apply tax to fee', 'easy-digital-downloads' ); ?>
					<# if ( 'none' !== data.state.hasTax && '' !== data.state.hasTax.country ) { #>
					<br />
					<small>
						<?php
						printf(
							esc_html__( 'Tax Rate: %s', 'easy-digital-downloads' ),
							'{{ data.state.hasTax.country}}<# if ( \'\' !== data.state.hasTax.region ) { #>: {{ data.state.hasTax.region }}<# } #> &ndash; {{ data.state.hasTax.rate }}%'
						); // WPCS: XSS okay.
						?>
					</small>
					<# } #>
				</span>
			</label>
		</p>
		<# } #>

		<#
		if (
			'fee' === data.type &&
			'none' !== data.state.hasTax &&
			'' === data.state.hasTax.country
		) {
		#>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'No tax rate has been set.', 'easy-digital-downloads' ); ?></strong><br />
					<?php esc_html_e( 'Tax rates are defined by the customer\'s billing address.', 'easy-digital-downloads' ); ?>
				</p>
				<p>
					<button class="button button-secondary" id="set-address">
						<?php esc_html_e( 'Set an address', 'easy-digital-downloads' ); ?>
					</button>
				</p>
			</div>
		<# } #>

		<p>
			<label for="description">
				<?php esc_html_e( 'Description', 'easy-digital-downloads' ); ?>
			</label>
			<input
				type="text"
				id="description"
				value="{{ data.description }}"
			/>
		</p>

		<p class="submit">
			<input
				id="submit"
				type="submit"
				class="button button-primary edd-ml-auto"
				value="<?php esc_html_e( 'Add Adjustment', 'easy-digital-downloads' ); ?>"
				<# if ( 0 === data.total ) { #>
					disabled
				<# } #>
			/>
		</p>
	</form>
</div>
