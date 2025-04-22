<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export Taxed Customer Dates', 'easy-digital-downloads' ),
		'id'     => 'taxed-customers-export',
	)
);
$from_to->output();
