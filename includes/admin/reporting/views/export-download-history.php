<label for="edd_file_download_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->product_dropdown(
	array(
		'name'        => 'download_id',
		'id'          => 'edd_file_download_export_download',
		'chosen'      => true,
		/* translators: %s: Download plural label */
		'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
	)
);
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export File Download Log Dates', 'easy-digital-downloads' ),
		'id'     => 'file-download-export',
	)
);
$from_to->output();

echo EDD()->html->customer_dropdown(
	array(
		'name'          => 'customer_id',
		'id'            => 'edd_file_download_logs_export_customer',
		'chosen'        => true,
		'none_selected' => '',
		'placeholder'   => __( 'All Customers', 'easy-digital-downloads' ),
	)
);
