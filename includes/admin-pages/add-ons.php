<?php
/**
 * Admin Add-ons
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Add-ons
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

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


/**
 * To Array
 *
 * Converts XML into an array.
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function toArray($xml) {
	$array = json_decode(json_encode($xml), TRUE);
	
	foreach ( array_slice($array, 0) as $key => $value ) {
		if ( empty($value) ) $array[$key] = NULL;
		elseif ( is_array($value) ) $array[$key] = toArray($value);
	}

	return $array;
}