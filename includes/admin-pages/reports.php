<?php

function edd_reports_page() {
	global $edd_options;	
	$current_page = admin_url('edit.php?post_type=download&page=edd-reports');
	?>
	<div class="wrap">
		<h2><?php _e('Reports', 'edd'); ?></h2>
		<?php 
			edd_show_download_sales_graph(); 
			edd_show_download_eanings_graph(); 
			edd_show_monthly_eanings_graph();
		?>
		<p><?php _e('Transactions created while in test mode are not included on this page.', 'edd'); ?></p>
	</div><!--end wrap-->
	<?php
}