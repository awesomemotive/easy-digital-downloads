<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export EU VAT Dates', 'easy-digital-downloads' ),
		'id'     => 'eu-vat-export',
	)
);
$from_to->output();
?>
<label for="eu_vat_country" class="screen-reader-text"><?php esc_html_e( 'Country', 'easy-digital-downloads' ); ?></label>
<?php
$countries       = include EDD_PLUGIN_DIR . 'i18n/countries-eu.php';
$invoice_country = edd_get_option( 'edd_vat_address_invoice', '' );
if ( ! empty( $invoice_country ) ) {
	unset( $countries[ $invoice_country ] );
}
$country_select = new \EDD\HTML\Select(
	array(
		'name'              => 'eu_vat_country',
		'id'                => 'eu_vat_country',
		'options'           => $countries,
		'show_option_empty' => __( 'All Countries', 'easy-digital-downloads' ),
		'show_option_all'   => false,
	)
);
$country_select->output();
?>
<input type="hidden" name="export-key" value="<?php echo esc_attr( uniqid() ); ?>" />
