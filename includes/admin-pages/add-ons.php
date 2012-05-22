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
 * Add-ons Page Init
 *
 * Hooks check feed to the page load action.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_add_ons_init() {
	global $edd_add_ons_page;
	add_action( 'load-'.$edd_add_ons_page, 'edd_add_ons_check_feed' );
}
add_action( 'admin_menu', 'edd_add_ons_init');


/**
 * Add-ons Check Feed
 *
 * Gets the feed or sets an error.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_add_ons_check_feed() {
	global $edd_addons_feed_content;
	$screen = get_current_screen();
	if (isset( $screen->base ) && $screen->base === 'download_page_edd-addons') {
		$cache = edd_add_ons_get_feed();
		if ( false === $cache ) {
			add_action( 'admin_notices', 'edd_admin_addons_notices' );
		} else {
			$edd_addons_feed_content = $cache;
		}
	}	 
}


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
	global $edd_addons_feed_content;

	ob_start(); ?>
	<div class="wrap" id="edd-add-ons">
		<h2><?php _e('Add Ons for Easy Digital Downloads', 'edd'); ?></h2>
		<p><?php _e('These add-ons extend the functionality of Easy Digital Downloads.', 'edd'); ?></p>
		<?php echo $edd_addons_feed_content; ?>
		<p class="edd-browse-all">
			<a href="http://easydigitaldownloads.com/extensions/?ref=1" class="button-primary" title="<?php _e('Browse All Extensions', 'edd'); ?>" target="_blank"><?php _e('Browse All Extensions', 'edd'); ?></a>
		</p>
	</div>
	<?php
	echo ob_get_clean();
}


/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_add_ons_get_feed() {
	if (false === $cache = get_transient('easydigitaldownloads_add_ons_feed')) {
		$feed = wp_remote_get('http://easydigitaldownloads.com/?feed=extensions');
		if (isset($feed['body']) && strlen($feed['body'])>0) {
			$cache = $feed['body'];
			set_transient('easydigitaldownloads_add_ons_feed', $cache);
		}
	}
	return $cache;
}