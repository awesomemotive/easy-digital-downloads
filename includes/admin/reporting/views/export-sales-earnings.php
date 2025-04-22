<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export Sales and Earnings Dates', 'easy-digital-downloads' ),
		'id'     => 'order-export',
	)
);
$from_to->output();
?>
<label for="edd_orders_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->product_dropdown(
	array(
		'name'        => 'download_id',
		'id'          => 'edd_orders_export_download',
		'chosen'      => true,
		/* translators: %s: Download plural label */
		'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
	)
);
?>
<label for="edd_order_export_customer" class="screen-reader-text"><?php esc_html_e( 'Select Customer', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->customer_dropdown(
	array(
		'name'          => 'customer_id',
		'id'            => 'edd_order_export_customer',
		'chosen'        => true,
		'none_selected' => '',
		'placeholder'   => __( 'All Customers', 'easy-digital-downloads' ),
	)
);
