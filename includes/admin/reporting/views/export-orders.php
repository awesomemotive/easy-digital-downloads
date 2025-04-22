<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export Order Dates', 'easy-digital-downloads' ),
		'id'     => 'orders-export',
	)
);
$from_to->output();
?>
<label for="edd_orders_export_status" class="screen-reader-text"><?php esc_html_e( 'Select Status', 'easy-digital-downloads' ); ?></label>
	<?php
		echo EDD()->html->select(
			array(
				'id'               => 'edd_orders_export_status',
				'name'             => 'status',
				'show_option_all'  => __( 'All Statuses', 'easy-digital-downloads' ),
				'show_option_none' => false,
				'selected'         => false,
				'options'          => edd_get_payment_statuses(),
			)
		);
