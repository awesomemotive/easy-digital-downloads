<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export Taxed Order Dates', 'easy-digital-downloads' ),
		'id'     => 'taxed-orders-export',
	)
);
$from_to->output();
?>
<label for="edd_reports_filter_regions" class="screen-reader-text"><?php esc_html_e( 'Select Region', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->region_select(
	array(
		'id'          => 'edd_reports_filter_regions',
		'placeholder' => __( 'All Regions', 'easy-digital-downloads' ),
	)
);
