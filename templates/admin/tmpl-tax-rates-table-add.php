<?php
/**
 * Admin tax table add "form".
 *
 * @since 3.0
 *
 * @package EDD
 * @category Template
 * @author Easy Digital Downloads
 * @version 1.0.0
 */
?>

<tr class="edd-tax-rate-table-add">

	<th>&nbsp;</th>

	<th>
		<label for="tax_rate_country" class="screen-reader-text"><?php esc_html_e( 'Country', 'easy-digital-downloads' ); ?></label>
		<?php 
		echo EDD()->html->select( array(
			'options'          => edd_get_country_list(),
			'id'               => 'tax_rate_country',
			'show_option_all'  => false,
			'show_option_none' => false,
			'chosen'           => true,
			'placeholder'      => __( 'Choose a country', 'easy-digital-downloads' ),
		) );
		?>
	</th>

	<th>
		<label for="tax_rate_region" class="screen-reader-text"><?php esc_html_e( 'Region', 'easy-digital-downloads' ); ?></label>

		<label style="margin: 5px 0; display: block;">
			<input type="checkbox" checked style="margin-left: 0;" /> Apply to whole country
		</label>

		<div id="tax_rate_region_wrapper"></div>
	</th>

	<th>
		<label for="tax_rate_amount" class="screen-reader-text"><?php esc_html_e( 'Rate', 'easy-digital-downloads' ); ?></label>
		<input type="number" step="0.0001" min="0.0" max="99" id="tax_rate_amount" />
	</p>

	<th class="edd-tax-rates-table-actions">
		<button id="tax_rate_submit" class="button button-secondary"><?php esc_html_e( 'Add Rate', 'easy-digital-downloads' ); ?></button>
	</th>

</tr>
