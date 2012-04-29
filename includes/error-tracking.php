<?php

/*
* Prints all stored errors. For use during checkout
* uses edd_get_errors()
*/
function edd_print_errors() {
	$errors = edd_get_errors();
	if($errors) {
		echo '<div class="edd_errors">';
		    // Loop error codes and display errors
		   foreach($errors as $error_id => $error){
		        echo '<p class="edd_error" id="edd_error_' . $error_id . '"><strong>' . __('Error', 'edd') . '</strong>: ' . $error . '</p>';
		    }
		echo '</div>';
		edd_clear_errors();
	}
}
add_action('edd_payment_mode_bottom', 'edd_print_errors');
add_action('edd_before_purchase_form', 'edd_print_errors');
add_action('edd_before_checkout_register_form', 'edd_print_errors');

/*
* Retrieves all error messages stored during the checkout process
* If errors exist, they are returned.
* @param none
* return mixed - array if errors are present, false if none found
*/
function edd_get_errors() {
	if(isset($_SESSION['edd-errors'])) {
		$errors = $_SESSION['edd-errors'];
		return $errors;
	}
	return false;
}

/*
* stores an error in a session var
* @param $error_id string - the ID of the error being set
* @param $error_message - the message to store with the error
* return none
*/
function edd_set_error($error_id, $error_message) {
	$errors = edd_get_errors();
	if(!$errors) {
		$errors = array();
	}
	$errors[$error_id] = $error_message;
	$_SESSION['edd-errors'] = $errors;
}

/*
* clears all stored errors
* return none
*/
function edd_clear_errors() {
	if(isset($_SESSION['edd-errors'])) $_SESSION['edd-errors'] = null;
}