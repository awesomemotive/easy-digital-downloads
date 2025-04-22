<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export Sales Dates', 'easy-digital-downloads' ),
		'id'     => 'sales-export',
	)
);
$from_to->output();
?>
<label for="edd_sales_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->product_dropdown(
	array(
		'name'   => 'download_id',
		'id'     => 'edd_sales_export_download',
		'chosen' => true,
	)
);
