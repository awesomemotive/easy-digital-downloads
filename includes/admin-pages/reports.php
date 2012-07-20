<?php
/**
 * Admin Reports Page
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Reports Page
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_reports_page() {
	global $edd_options;	
	$current_page = admin_url('edit.php?post_type=download&page=edd-reports');
	?>
	<div class="wrap">
		<h2><?php _e('Reports', 'edd'); ?></h2>
		<?php
			do_action('edd_reports_page_top'); 
			edd_show_download_sales_graph(); 
			edd_show_download_earnings_graph(); 
			edd_show_monthly_eanings_graph();
			do_action('edd_reports_page_bottom');
		?>
		<p><?php _e('Transactions created while in test mode are not included on this page.', 'edd'); ?></p>
	</div><!--end wrap-->
	<?php
}