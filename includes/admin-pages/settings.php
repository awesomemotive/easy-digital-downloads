<?php

function edd_options_page() {

	global $edd_options;

	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';  

	ob_start(); ?>
	<div class="wrap">
		
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg('tab', 'general'); ?>" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'edd'); ?></a>
			<a href="<?php echo add_query_arg('tab', 'gateways'); ?>" class="nav-tab <?php echo $active_tab == 'gateways' ? 'nav-tab-active' : ''; ?>"><?php _e('Payment Gateways', 'edd'); ?></a>
			<a href="<?php echo add_query_arg('tab', 'emails'); ?>" class="nav-tab <?php echo $active_tab == 'emails' ? 'nav-tab-active' : ''; ?>"><?php _e('Emails', 'edd'); ?></a>
			<a href="<?php echo add_query_arg('tab', 'styles'); ?>" class="nav-tab <?php echo $active_tab == 'styles' ? 'nav-tab-active' : ''; ?>"><?php _e('Styles', 'edd'); ?></a>
			<a href="<?php echo add_query_arg('tab', 'misc'); ?>" class="nav-tab <?php echo $active_tab == 'misc' ? 'nav-tab-active' : ''; ?>"><?php _e('Misc', 'edd'); ?></a>
		</h2>
			
		<div id="tab_container">
			
			<?php settings_errors(); ?>  
			
			<form method="post" action="options.php">
	
				<?php
				
				if($active_tab == 'general') {
					settings_fields('edd_settings_general'); 
					do_settings_sections('edd_settings_general');
				} elseif($active_tab == 'gateways') {
					settings_fields('edd_settings_gateways');
					do_settings_sections('edd_settings_gateways');
				} elseif($active_tab == 'emails') {
					settings_fields('edd_settings_emails');
					do_settings_sections('edd_settings_emails');
				} elseif($active_tab == 'styles') {
					settings_fields('edd_settings_styles');
					do_settings_sections('edd_settings_styles');
				} else {
					settings_fields('edd_settings_misc');
					do_settings_sections('edd_settings_misc');
				}
				
				submit_button(); 
				
				?>
				 
			</form>
		</div><!--end #tab_container-->
		
	</div>
	<?php
	echo ob_get_clean();
}