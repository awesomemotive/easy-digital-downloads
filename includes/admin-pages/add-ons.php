<?php

function edd_add_ons_page() {

	global $edd_options;

	ob_start(); ?>
	<div class="wrap" id="edd-add-ons">
		<h2><?php _e('Add Ons for Easy Digital Downloads', 'edd'); ?></h2>
		<p><?php _e('These add-ons extend the functionality of Easy Digital Downloads.', 'edd'); ?></p>
		<?php
			$feed = wp_remote_get( 'http://easydigitaldownloads.com/?feed=extensions' );
			echo $feed['body'];
		?>
		
	</div>
	<?php
	echo ob_get_clean();
}

function toArray($xml) {
	$array = json_decode(json_encode($xml), TRUE);
	
	foreach ( array_slice($array, 0) as $key => $value ) {
		if ( empty($value) ) $array[$key] = NULL;
		elseif ( is_array($value) ) $array[$key] = toArray($value);
	}

	return $array;
}