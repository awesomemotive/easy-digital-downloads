<?php

function edd_process_actions() {
	if(isset($_POST['edd-action'])) {
		do_action('edd_' . $_POST['edd-action'], $_POST);
	}
	if(isset($_GET['edd-action'])) {
		do_action('edd_' . $_GET['edd-action'], $_GET);
	}
}
add_action('admin_init', 'edd_process_actions');