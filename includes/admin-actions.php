<?php

function edd_process_actions() {
	if(isset($_POST['edd-action'])) {
		do_action('edd_' . $_POST['edd-action'], $_POST);
	}
	if(isset($_GET['edd-action'])) {
		do_action('edd_' . $_GET['edd-action'], $_GET);
	}
	add_action('admin_head','edd_admin_downloads_icon');
}
add_action('admin_init', 'edd_process_actions');

// adds edd custom post type icon
function edd_admin_downloads_icon() {
	$icon_url = EDD_PLUGIN_URL . 'includes/images/edd-icon.png';	
	?>
	<style type="text/css" media="screen">
		body #adminmenu #menu-posts-download div.wp-menu-image { background:transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px -32px; }
		body #adminmenu #menu-posts-download:hover div.wp-menu-image, 
		body #adminmenu #menu-posts-download.wp-has-current-submenu div.wp-menu-image { background:transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px 0; }	
	</style>
    <?php
}