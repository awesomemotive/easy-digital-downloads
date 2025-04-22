<?php
$from_to = new \EDD\HTML\FromTo(
	array(
		'legend' => __( 'Export API Request Log Dates', 'easy-digital-downloads' ),
		'id'     => 'api-requests-export',
	)
);
$from_to->output();
