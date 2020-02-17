<?php
/**
 * Order Overview: Add Item form
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$currency_position  = edd_get_option( 'currency_position', 'before' );
?>

<div class="edd-order-overview-modal">
	<form class="edd-order-overview-add-item">
		<p>
			<label for="">
				<?php echo esc_html( edd_get_label_singular() ); ?>
			</label>

			<select
				name="edd-order-add-download-select"
				id="download"
				class="edd-select edd-order-add-download-select variations variations-only edd-select-chosen"
				data-placeholder="<?php echo esc_html_e( 'Search for a download', 'easy-digital-downloads' ); ?>"
				data-search-placeholder="<?php echo esc_html_e( 'Search for a download', 'easy-digital-downloads' ); ?>"
				data-search-type="download">
					<option value=""></option>
					<# if ( data.id ) { #>
						<option value="{{ data.id }}<# if ( 0 !== data.priceId ) { #>_{{ data.priceId }}<# } #>" selected>{{ data.name }}</option>
					<# } #>
			</select>
		</p>

		<# if ( false !== data.config.hasQuantity ) { #>
			<p>
				<label for="">
					<?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?>
				</label>
				<input
					type="number"
					id="quantity"
					class="edd-add-order-quantity"
					value="{{ data.quantity }}"
					step="1"
					min="1"
					<# if ( '' === data.id ) { #>
						disabled
					<# } #>
				/>
			</p>
		<# } #>

		<p>
			<label
				class="edd-toggle"
				for="auto-calculate"
			>
				<input
					type="checkbox"
					id="auto-calculate"
					<# if ( true !== data.config.isAdjustingManually ) { #>
						checked
					<# } #>
					<# if ( '' === data.id ) { #>
						disabled
					<# } #>
				/>
				<span class="label">
					<?php esc_html_e( 'Automatically calculate amounts', 'easy-digital-downloads' ); ?>
					<# if ( false !== data.config.hasTax ) { #>
					<small>
						<# if ( false !== data.config.hasTax.rate && '' !== data.config.hasTax.country ) { #>
						<?php
						printf(
							esc_html__( 'Tax Rate: %s', 'easy-digital-downloads' ),
							'{{ data.config.hasTax.country}}<# if ( \'\' !== data.config.hasTax.region ) { #>: {{ data.config.hasTax.region }}<# } #> &ndash; {{ data.config.hasTax.rate }}%'
						); // WPCS: XSS okay.
						?>
						<# } else { #>
						<?php esc_html_e( 'Select an address to calculate tax amounts', 'easy-digital-downloads' ); ?>
						<# } #>
					</small>
					<# } #>
				</span>
			</label>
		</p>

		<# if ( true === data.config.isAdjustingManually ) { #>
			<p>
				<label for=""><?php esc_html_e( 'Unit Price', 'easy-digital-downloads' ); ?></label>
				<span class="edd-amount">
					<?php if ( 'before' === $currency_position ) : ?>
						<?php echo edd_currency_filter( '' ); ?>
					<?php endif; ?>

					<input
						type="text"
						id="amount"
						value="{{ data.amountFormatted }}"
					/>

					<?php if ( 'after' === $currency_position ) : ?>
						<?php echo edd_currency_filter( '' ); ?>
					<?php endif; ?>
				</span>
			</p>

			<# if ( false !== data.config.hasTax ) { #>
				<p>
					<label for=""><?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?></label>
					<span class="edd-amount">
						<?php if ( 'before' === $currency_position ) : ?>
							<?php echo edd_currency_filter( '' ); ?>
						<?php endif; ?>

						<input
							type="text"
							id="tax"
							value="{{ data.taxFormatted }}"
						/>

						<?php if ( 'after' === $currency_position ) : ?>
							<?php echo edd_currency_filter( '' ); ?>
						<?php endif; ?>
					</span>
				</p>
			<# } #>

			<p>
				<label for=""><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></label>
				<span class="edd-amount">
					<?php if ( 'before' === $currency_position ) : ?>
						<?php echo edd_currency_filter( '' ); ?>
					<?php endif; ?>

					<input
						type="text"
						id="subtotal"
						value="{{ data.subtotalFormatted }}"
					/>

					<?php if ( 'after' === $currency_position ) : ?>
						<?php echo edd_currency_filter( '' ); ?>
					<?php endif; ?>
				</span>
			</p>
		<# } #>

		<p class="submit">
			<input
				type="submit"
				class="button button-primary edd-ml-auto"
				value="<?php echo esc_html( sprintf( __( 'Add %s', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?>"
				<# if ( '' === data.id || ( 0 === data.subtotal && true === data.config.isAdjustingManually ) ) { #>
					disabled
				<# } #>
			/>
		</p>
	</form>
</div>
