<?php
/**
 * Checkout Template
 *
 * @package     EDD
 * @subpackage  Checkout
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Checkout Form
 *
 * @since 1.0
 * @return string
 */
function edd_checkout_form() {
	$payment_mode = edd_get_chosen_gateway();
	$form_action  = esc_url( edd_get_checkout_uri( 'payment-mode=' . $payment_mode ) );

	ob_start();
		echo '<div id="edd_checkout_wrap">';
		if ( edd_get_cart_contents() || edd_cart_has_fees() ) :

			edd_checkout_cart();
?>
			<div id="edd_checkout_form_wrap" class="edd_clearfix">
				<?php do_action( 'edd_before_purchase_form' ); ?>
				<form id="edd_purchase_form" class="edd_form" action="<?php echo $form_action; ?>" method="POST">
					<?php
					/**
					 * Hooks in at the top of the checkout form
					 *
					 * @since 1.0
					 */
					do_action( 'edd_checkout_form_top' );

					if ( edd_is_ajax_disabled() && ! empty( $_REQUEST['payment-mode'] ) ) {
						do_action( 'edd_purchase_form' );
					} elseif ( edd_show_gateways() ) {
						do_action( 'edd_payment_mode_select'  );
					} else {
						do_action( 'edd_purchase_form' );
					}

					/**
					 * Hooks in at the bottom of the checkout form
					 *
					 * @since 1.0
					 */
					do_action( 'edd_checkout_form_bottom' )
					?>
				</form>
				<?php do_action( 'edd_after_purchase_form' ); ?>
			</div><!--end #edd_checkout_form_wrap-->
		<?php
		else:
			/**
			 * Fires off when there is nothing in the cart
			 *
			 * @since 1.0
			 */
			do_action( 'edd_cart_empty' );
		endif;
		echo '</div><!--end #edd_checkout_wrap-->';
	return ob_get_clean();
}

/**
 * Renders the Purchase Form, hooks are provided to add to the purchase form.
 * The default Purchase Form rendered displays a list of the enabled payment
 * gateways, a user registration form (if enable) and a credit card info form
 * if credit cards are enabled
 *
 * @since 1.4
 * @return string
 */
function edd_show_purchase_form() {
	$payment_mode = edd_get_chosen_gateway();

	/**
	 * Hooks in at the top of the purchase form
	 *
	 * @since 1.4
	 */
	do_action( 'edd_purchase_form_top' );

	if ( edd_can_checkout() ) {

		do_action( 'edd_purchase_form_before_register_login' );

		$show_register_form = edd_get_option( 'show_register_form', 'none' ) ;
		if( ( $show_register_form === 'registration' || ( $show_register_form === 'both' && ! isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) : ?>
			<div id="edd_checkout_login_register">
				<?php do_action( 'edd_purchase_form_register_fields' ); ?>
			</div>
		<?php elseif( ( $show_register_form === 'login' || ( $show_register_form === 'both' && isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) : ?>
			<div id="edd_checkout_login_register">
				<?php do_action( 'edd_purchase_form_login_fields' ); ?>
			</div>
		<?php endif; ?>

		<?php if( ( ! isset( $_GET['login'] ) && is_user_logged_in() ) || ! isset( $show_register_form ) || 'none' === $show_register_form || 'login' === $show_register_form ) {
			do_action( 'edd_purchase_form_after_user_info' );
		}

		/**
		 * Hooks in before Credit Card Form
		 *
		 * @since 1.4
		 */
		do_action( 'edd_purchase_form_before_cc_form' );

		if( edd_get_cart_total() > 0 ) {

			// Load the credit card form and allow gateways to load their own if they wish
			if ( has_action( 'edd_' . $payment_mode . '_cc_form' ) ) {
				do_action( 'edd_' . $payment_mode . '_cc_form' );
			} else {
				do_action( 'edd_cc_form' );
			}

		}

		/**
		 * Hooks in after Credit Card Form
		 *
		 * @since 1.4
		 */
		do_action( 'edd_purchase_form_after_cc_form' );

	} else {
		// Can't checkout
		do_action( 'edd_purchase_form_no_access' );
	}

	/**
	 * Hooks in at the bottom of the purchase form
	 *
	 * @since 1.4
	 */
	do_action( 'edd_purchase_form_bottom' );
}
add_action( 'edd_purchase_form', 'edd_show_purchase_form' );

/**
 * Shows the User Info fields in the Personal Info box, more fields can be added
 * via the hooks provided.
 *
 * @since 1.3.3
 * @return void
 */
function edd_user_info_fields() {

	$customer = EDD()->session->get( 'customer' );
	$customer = wp_parse_args( $customer, array( 'first_name' => '', 'last_name' => '', 'email' => '' ) );

	if( is_user_logged_in() ) {
		$user_data = get_userdata( get_current_user_id() );
		foreach( $customer as $key => $field ) {

			if ( 'email' == $key && empty( $field ) ) {
				$customer[ $key ] = $user_data->user_email;
			} elseif ( empty( $field ) ) {
				$customer[ $key ] = $user_data->$key;
			}

		}
	}

	$customer = array_map( 'sanitize_text_field', $customer );
	?>
	<fieldset id="edd_checkout_user_info">
		<legend><?php echo apply_filters( 'edd_checkout_personal_info_text', esc_html__( 'Personal Info', 'easy-digital-downloads' ) ); ?></legend>
		<?php do_action( 'edd_purchase_form_before_email' ); ?>
		<p id="edd-email-wrap">
			<label class="edd-label" for="edd-email">
				<?php esc_html_e( 'Email Address', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'edd_email' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description" id="edd-email-description"><?php esc_html_e( 'We will send the purchase receipt to this address.', 'easy-digital-downloads' ); ?></span>
			<input class="edd-input required" type="email" name="edd_email" placeholder="<?php esc_html_e( 'Email address', 'easy-digital-downloads' ); ?>" id="edd-email" value="<?php echo esc_attr( $customer['email'] ); ?>" aria-describedby="edd-email-description"<?php if( edd_field_is_required( 'edd_email' ) ) {  echo ' required '; } ?>/>
		</p>
		<?php do_action( 'edd_purchase_form_after_email' ); ?>
		<p id="edd-first-name-wrap">
			<label class="edd-label" for="edd-first">
				<?php esc_html_e( 'First Name', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'edd_first' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description" id="edd-first-description"><?php esc_html_e( 'We will use this to personalize your account experience.', 'easy-digital-downloads' ); ?></span>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php esc_html_e( 'First Name', 'easy-digital-downloads' ); ?>" id="edd-first" value="<?php echo esc_attr( $customer['first_name'] ); ?>"<?php if( edd_field_is_required( 'edd_first' ) ) {  echo ' required '; } ?> aria-describedby="edd-first-description" />
		</p>
		<p id="edd-last-name-wrap">
			<label class="edd-label" for="edd-last">
				<?php esc_html_e( 'Last Name', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'edd_last' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description" id="edd-last-description"><?php esc_html_e( 'We will use this as well to personalize your account experience.', 'easy-digital-downloads' ); ?></span>
			<input class="edd-input<?php if( edd_field_is_required( 'edd_last' ) ) { echo ' required'; } ?>" type="text" name="edd_last" id="edd-last" placeholder="<?php esc_html_e( 'Last Name', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['last_name'] ); ?>"<?php if( edd_field_is_required( 'edd_last' ) ) {  echo ' required '; } ?> aria-describedby="edd-last-description"/>
		</p>
		<?php do_action( 'edd_purchase_form_user_info' ); ?>
		<?php do_action( 'edd_purchase_form_user_info_fields' ); ?>
	</fieldset>
	<?php
}
add_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
add_action( 'edd_register_fields_before', 'edd_user_info_fields' );

/**
 * Renders the credit card info form.
 *
 * @since 1.0
 * @return void
 */
function edd_get_cc_form() {
	ob_start(); ?>

	<?php do_action( 'edd_before_cc_fields' ); ?>

	<fieldset id="edd_cc_fields" class="edd-do-validate">
		<legend><?php _e( 'Credit Card Info', 'easy-digital-downloads' ); ?></legend>
		<?php if( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock">
					<svg class="edd-icon edd-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
						<path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"/>
					</svg>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'easy-digital-downloads' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="edd-card-number-wrap">
			<label for="card_number" class="edd-label">
				<?php _e( 'Card Number', 'easy-digital-downloads' ); ?>
				<span class="edd-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="edd-description"><?php _e( 'The (typically) 16 digits on the front of your credit card.', 'easy-digital-downloads' ); ?></span>
			<input type="tel" pattern="^[0-9!@#$%^&* ]*$" autocomplete="off" name="card_number" id="card_number" class="card-number edd-input required" placeholder="<?php _e( 'Card number', 'easy-digital-downloads' ); ?>" />
		</p>
		<p id="edd-card-cvc-wrap">
			<label for="card_cvc" class="edd-label">
				<?php _e( 'CVC', 'easy-digital-downloads' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'easy-digital-downloads' ); ?></span>
			<input type="tel" pattern="[0-9]{3,4}" size="4" maxlength="4" autocomplete="off" name="card_cvc" id="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e( 'Security code', 'easy-digital-downloads' ); ?>" />
		</p>
		<p id="edd-card-name-wrap">
			<label for="card_name" class="edd-label">
				<?php _e( 'Name on the Card', 'easy-digital-downloads' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The name printed on the front of your credit card.', 'easy-digital-downloads' ); ?></span>
			<input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name edd-input required" placeholder="<?php _e( 'Card name', 'easy-digital-downloads' ); ?>" />
		</p>
		<?php do_action( 'edd_before_cc_expiration' ); ?>
		<p class="card-expiration">
			<label for="card_exp_month" class="edd-label">
				<?php _e( 'Expiration (MM/YY)', 'easy-digital-downloads' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The date your credit card expires, typically on the front of the card.', 'easy-digital-downloads' ); ?></span>
			<select id="card_exp_month" name="card_exp_month" class="card-expiry-month edd-select edd-select-small required">
				<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option value="' . $i . '">' . sprintf ('%02d', $i ) . '</option>'; } ?>
			</select>
			<span class="exp-divider"> / </span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
				<?php for( $i = date('Y'); $i <= date('Y') + 30; $i++ ) { echo '<option value="' . $i . '">' . substr( $i, 2 ) . '</option>'; } ?>
			</select>
		</p>
		<?php do_action( 'edd_after_cc_expiration' ); ?>

	</fieldset>
	<?php
	do_action( 'edd_after_cc_fields' );

	echo ob_get_clean();
}
add_action( 'edd_cc_form', 'edd_get_cc_form' );

/**
 * Outputs the default credit card address fields
 *
 * @since 1.0
 * @return void
 */
function edd_default_cc_address_fields() {

	$logged_in = is_user_logged_in();
	$customer  = EDD()->session->get( 'customer' );
	$customer  = wp_parse_args( $customer, array( 'address' => array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => ''
	) ) );

	$customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

	if( $logged_in ) {

		$user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );

		foreach( $customer['address'] as $key => $field ) {

			if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
				$customer['address'][ $key ] = $user_address[ $key ];
			} else {
				$customer['address'][ $key ] = '';
			}

		}

	}

	/**
	 * Billing Address Details.
	 *
	 * Allows filtering the customer address details that will be pre-populated on the checkout form.
	 *
	 * @since 2.8
	 *
	 * @param array $address The customer address.
	 * @param array $customer The customer data from the session
	 */
	$customer['address'] = apply_filters( 'edd_checkout_billing_details_address', $customer['address'], $customer );

	ob_start(); ?>
	<fieldset id="edd_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'easy-digital-downloads' ); ?></legend>
		<?php do_action( 'edd_cc_billing_top' ); ?>
		<p id="edd-card-address-wrap">
			<label for="card_address" class="edd-label">
				<?php _e( 'Billing Address', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_address' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The primary billing address for your credit card.', 'easy-digital-downloads' ); ?></span>
			<input type="text" id="card_address" name="card_address" class="card-address edd-input<?php if( edd_field_is_required( 'card_address' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Address line 1', 'easy-digital-downloads' ); ?>" value="<?php echo $customer['address']['line1']; ?>"<?php if( edd_field_is_required( 'card_address' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-address-2-wrap">
			<label for="card_address_2" class="edd-label">
				<?php _e( 'Billing Address Line 2 (optional)', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_address_2' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The suite, apt no, PO box, etc, associated with your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 edd-input<?php if( edd_field_is_required( 'card_address_2' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Address line 2', 'easy-digital-downloads' ); ?>" value="<?php echo $customer['address']['line2']; ?>"<?php if( edd_field_is_required( 'card_address_2' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-city-wrap">
			<label for="card_city" class="edd-label">
				<?php _e( 'Billing City', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_city' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The city for your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" id="card_city" name="card_city" class="card-city edd-input<?php if( edd_field_is_required( 'card_city' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'City', 'easy-digital-downloads' ); ?>" value="<?php echo $customer['address']['city']; ?>"<?php if( edd_field_is_required( 'card_city' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e( 'Billing Zip / Postal Code', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_zip' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The zip or postal code for your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" size="4" id="card_zip" name="card_zip" class="card-zip edd-input<?php if( edd_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Zip / Postal Code', 'easy-digital-downloads' ); ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if( edd_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e( 'Billing Country', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'billing_country' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The country for your billing address.', 'easy-digital-downloads' ); ?></span>
			<select name="billing_country" id="billing_country" data-nonce="<?php echo wp_create_nonce( 'edd-country-field-nonce' ); ?>" class="billing_country edd-select<?php if( edd_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if( edd_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?>>
				<?php

				$selected_country = edd_get_shop_country();

				if( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = edd_get_country_list();
				foreach( $countries as $country_code => $country ) {
				  echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>
		<p id="edd-card-state-wrap">
			<label for="card_state" class="edd-label">
				<?php _e( 'Billing State / Province', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_state' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The state or province for your billing address.', 'easy-digital-downloads' ); ?></span>
			<?php
			$selected_state = edd_get_shop_state();
			$states         = edd_get_shop_states( $selected_country );

			if( ! empty( $customer['address']['state'] ) ) {
				$selected_state = $customer['address']['state'];
			}

			if( ! empty( $states ) ) : ?>
			<select name="card_state" id="card_state" class="card_state edd-select<?php if( edd_field_is_required( 'card_state' ) ) { echo ' required'; } ?>">
				<?php
					foreach( $states as $state_code => $state ) {
						echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
					}
				?>
			</select>
			<?php else : ?>
			<?php $customer_state = ! empty( $customer['address']['state'] ) ? $customer['address']['state'] : ''; ?>
			<input type="text" size="6" name="card_state" id="card_state" class="card_state edd-input" value="<?php echo esc_attr( $customer_state ); ?>" placeholder="<?php _e( 'State / Province', 'easy-digital-downloads' ); ?>"/>
			<?php endif; ?>
		</p>
		<?php do_action( 'edd_cc_billing_bottom' ); ?>
		<?php wp_nonce_field( 'edd-checkout-address-fields', 'edd-checkout-address-fields-nonce', false, true ); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );


/**
 * Renders the billing address fields for cart taxation
 *
 * @since 1.6
 * @return void
 */
function edd_checkout_tax_fields() {
	if( edd_cart_needs_tax_address_fields() && edd_get_cart_total() )
		edd_default_cc_address_fields();
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_tax_fields', 999 );


/**
 * Renders the user registration fields. If the user is logged in, a login
 * form is displayed other a registration form is provided for the user to
 * create an account.
 *
 * @since 1.0
 * @return string
 */
function edd_get_register_fields() {
	$show_register_form = edd_get_option( 'show_register_form', 'none' );

	ob_start(); ?>
	<fieldset id="edd_register_fields">

		<?php if( $show_register_form == 'both' ) { ?>
			<p id="edd-login-account-wrap">
				<?php _e( 'Already have an account?', 'easy-digital-downloads' ); ?>
				 <a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="edd_checkout_register_login" data-action="checkout_login" data-nonce="<?php echo wp_create_nonce( 'edd_checkout_login' ); ?>">
					 <?php _e( 'Login', 'easy-digital-downloads' ); ?>
				 </a>
			</p>
		<?php } ?>

		<?php do_action('edd_register_fields_before'); ?>

		<fieldset id="edd_register_account_fields">
			<legend><?php _e( 'Create an account', 'easy-digital-downloads' ); if( !edd_no_guest_checkout() ) { echo ' ' . __( '(optional)', 'easy-digital-downloads' ); } ?></legend>
			<?php do_action('edd_register_account_fields_before'); ?>
			<p id="edd-user-login-wrap">
				<label for="edd_user_login">
					<?php _e( 'Username', 'easy-digital-downloads' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="edd-description"><?php _e( 'The username you will use to log into your account.', 'easy-digital-downloads' ); ?></span>
				<input name="edd_user_login" id="edd_user_login" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" placeholder="<?php _e( 'Username', 'easy-digital-downloads' ); ?>"/>
			</p>
			<p id="edd-user-pass-wrap">
				<label for="edd_user_pass">
					<?php _e( 'Password', 'easy-digital-downloads' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="edd-description"><?php _e( 'The password used to access your account.', 'easy-digital-downloads' ); ?></span>
				<input name="edd_user_pass" id="edd_user_pass" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e( 'Password', 'easy-digital-downloads' ); ?>" type="password"/>
			</p>
			<p id="edd-user-pass-confirm-wrap" class="edd_register_password">
				<label for="edd_user_pass_confirm">
					<?php _e( 'Password Again', 'easy-digital-downloads' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="edd-description"><?php _e( 'Confirm your password.', 'easy-digital-downloads' ); ?></span>
				<input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e( 'Confirm password', 'easy-digital-downloads' ); ?>" type="password"/>
			</p>
			<?php do_action( 'edd_register_account_fields_after' ); ?>
		</fieldset>

		<?php do_action('edd_register_fields_after'); ?>

		<input type="hidden" name="edd-purchase-var" value="needs-to-register"/>

		<?php do_action( 'edd_purchase_form_user_info' ); ?>
		<?php do_action( 'edd_purchase_form_user_register_fields' ); ?>

	</fieldset>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_purchase_form_register_fields', 'edd_get_register_fields' );

/**
 * Gets the login fields for the login form on the checkout. This function hooks
 * on the edd_purchase_form_login_fields to display the login form if a user already
 * had an account.
 *
 * @since 1.0
 * @return string
 */
function edd_get_login_fields() {
	$color = edd_get_option( 'checkout_color', 'gray' );
	$color = ( $color == 'inherit' ) ? '' : $color;
	$style = edd_get_option( 'button_style', 'button' );

	$show_register_form = edd_get_option( 'show_register_form', 'none' );

	ob_start(); ?>
		<fieldset id="edd_login_fields">
			<?php if( $show_register_form == 'both' ) { ?>
				<p id="edd-new-account-wrap">
					<?php _e( 'Need to create an account?', 'easy-digital-downloads' ); ?>
					<a href="<?php echo esc_url( remove_query_arg('login') ); ?>" class="edd_checkout_register_login" data-action="checkout_register" data-nonce="<?php echo wp_create_nonce( 'edd_checkout_register' ); ?>">
						<?php _e( 'Register', 'easy-digital-downloads' ); if(!edd_no_guest_checkout()) { echo ' ' . __( 'or checkout as a guest.', 'easy-digital-downloads' ); } ?>
					</a>
				</p>
			<?php } ?>
			<?php do_action('edd_checkout_login_fields_before'); ?>
			<p id="edd-user-login-wrap">
				<label class="edd-label" for="edd_user_login">
					<?php _e( 'Username or Email', 'easy-digital-downloads' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="<?php _e( 'Your username or email address', 'easy-digital-downloads' ); ?>"/>
			</p>
			<p id="edd-user-pass-wrap" class="edd_login_password">
				<label class="edd-label" for="edd_user_pass">
					<?php _e( 'Password', 'easy-digital-downloads' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="<?php _e( 'Your password', 'easy-digital-downloads' ); ?>"/>
				<?php if( edd_no_guest_checkout() ) : ?>
					<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
				<?php endif; ?>
			</p>
			<p id="edd-user-login-submit">
				<input type="submit" class="edd-submit button <?php echo $color; ?>" name="edd_login_submit" value="<?php _e( 'Login', 'easy-digital-downloads' ); ?>"/>
				<?php wp_nonce_field( 'edd-login-form', 'edd_login_nonce', false, true ); ?>
			</p>
			<?php do_action('edd_checkout_login_fields_after'); ?>
		</fieldset><!--end #edd_login_fields-->
	<?php
	echo ob_get_clean();
}
add_action( 'edd_purchase_form_login_fields', 'edd_get_login_fields' );

/**
 * Renders the payment mode form by getting all the enabled payment gateways and
 * outputting them as radio buttons for the user to choose the payment gateway. If
 * a default payment gateway has been chosen from the EDD Settings, it will be
 * automatically selected.
 *
 * @since 1.2.2
 * @return void
 */
function edd_payment_mode_select() {
	$gateways = edd_get_enabled_payment_gateways( true );
	$page_URL = edd_get_current_page_url();
	$chosen_gateway = edd_get_chosen_gateway();
	?>
	<div id="edd_payment_mode_select_wrap">
		<?php do_action('edd_payment_mode_top'); ?>
		<?php if( edd_is_ajax_disabled() ) { ?>
		<form id="edd_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
		<?php } ?>
			<fieldset id="edd_payment_mode_select">
				<legend><?php _e( 'Select Payment Method', 'easy-digital-downloads' ); ?></legend>
				<?php do_action( 'edd_payment_mode_before_gateways_wrap' ); ?>
				<div id="edd-payment-mode-wrap">
					<?php

					do_action( 'edd_payment_mode_before_gateways' );

					foreach ( $gateways as $gateway_id => $gateway ) :

						$label         = apply_filters( 'edd_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] );
						$checked       = checked( $gateway_id, $chosen_gateway, false );
						$checked_class = $checked ? ' edd-gateway-option-selected' : '';
						$nonce         = ' data-' . esc_attr( $gateway_id ) . '-nonce="' . wp_create_nonce( 'edd-gateway-selected-' . esc_attr( $gateway_id ) ) .'"';

						echo '<label for="edd-gateway-' . esc_attr( $gateway_id ) . '" class="edd-gateway-option' . $checked_class . '" id="edd-gateway-option-' . esc_attr( $gateway_id ) . '">';
							echo '<input type="radio" name="payment-mode" class="edd-gateway" id="edd-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . $nonce . '>' . esc_html( $label );
						echo '</label>';

					endforeach;

					do_action( 'edd_payment_mode_after_gateways' );

					?>
				</div>
				<?php do_action( 'edd_payment_mode_after_gateways_wrap' ); ?>
			</fieldset>
			<fieldset id="edd_payment_mode_submit" class="edd-no-js">
				<p id="edd-next-submit-wrap">
					<?php echo edd_checkout_button_next(); ?>
				</p>
			</fieldset>
		<?php if( edd_is_ajax_disabled() ) { ?>
		</form>
		<?php } ?>
	</div>
	<div id="edd_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->

	<?php do_action('edd_payment_mode_bottom');
}
add_action( 'edd_payment_mode_select', 'edd_payment_mode_select' );


/**
 * Show Payment Icons by getting all the accepted icons from the EDD Settings
 * then outputting the icons.
 *
 * @since 1.0
 * @return void
*/
function edd_show_payment_icons() {

	if( edd_show_gateways() && did_action( 'edd_payment_mode_top' ) ) {
		return;
	}

	$payment_methods = edd_get_option( 'accepted_cards', array() );

	if( empty( $payment_methods ) ) {
		return;
	}

	echo '<div class="edd-payment-icons">';

	foreach( $payment_methods as $key => $card ) {

		if( edd_string_is_image_url( $key ) ) {

			echo '<img class="payment-icon" src="' . esc_url( $key ) . '"/>';

		} else {

			$card = strtolower( str_replace( ' ', '', $card ) );

			if( has_filter( 'edd_accepted_payment_' . $card . '_image' ) ) {

				$image = apply_filters( 'edd_accepted_payment_' . $card . '_image', '' );

			} else {

				$image = edd_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.png', false );

				// Replaces backslashes with forward slashes for Windows systems
				$plugin_dir  = wp_normalize_path( WP_PLUGIN_DIR );
				$content_dir = wp_normalize_path( WP_CONTENT_DIR );
				$image       = wp_normalize_path( $image );

				$image = str_replace( $plugin_dir, WP_PLUGIN_URL, $image );
				$image = str_replace( $content_dir, WP_CONTENT_URL, $image );

			}

			if( edd_is_ssl_enforced() || is_ssl() ) {

				$image = edd_enforced_ssl_asset_filter( $image );

			}

			echo '<img class="payment-icon" src="' . esc_url( $image ) . '"/>';
		}

	}

	echo '</div>';
}
add_action( 'edd_payment_mode_top', 'edd_show_payment_icons' );
add_action( 'edd_checkout_form_top', 'edd_show_payment_icons' );


/**
 * Renders the Discount Code field which allows users to enter a discount code.
 * This field is only displayed if there are any active discounts on the site else
 * it's not displayed.
 *
 * @since 1.2.2
 * @return void
*/
function edd_discount_field() {

	if( isset( $_GET['payment-mode'] ) && edd_is_ajax_disabled() ) {
		return; // Only show before a payment method has been selected if ajax is disabled
	}

	if( ! edd_is_checkout() ) {
		return;
	}

	if ( edd_has_active_discounts() && edd_get_cart_total() ) :

		$color = edd_get_option( 'checkout_color', 'blue' );
		$color = ( $color == 'inherit' ) ? '' : $color;
		$style = edd_get_option( 'button_style', 'button' );
?>
		<fieldset id="edd_discount_code">
			<p id="edd_show_discount" style="display:none;">
				<?php _e( 'Have a discount code?', 'easy-digital-downloads' ); ?> <a href="#" class="edd_discount_link"><?php echo _x( 'Click to enter it', 'Entering a discount code', 'easy-digital-downloads' ); ?></a>
			</p>
			<p id="edd-discount-code-wrap" class="edd-cart-adjustment">
				<label class="edd-label" for="edd-discount">
					<?php _e( 'Discount', 'easy-digital-downloads' ); ?>
				</label>
				<span class="edd-description"><?php _e( 'Enter a coupon code if you have one.', 'easy-digital-downloads' ); ?></span>
				<span class="edd-discount-code-field-wrap">
					<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php _e( 'Enter discount', 'easy-digital-downloads' ); ?>"/>
					<input type="submit" class="edd-apply-discount edd-submit <?php echo $color . ' ' . $style; ?>" value="<?php echo _x( 'Apply', 'Apply discount at checkout', 'easy-digital-downloads' ); ?>"/>
				</span>
				<span class="edd-discount-loader edd-loading" id="edd-discount-loader" style="display:none;"></span>
				<span id="edd-discount-error-wrap" class="edd_error edd-alert edd-alert-error" aria-hidden="true" style="display:none;"></span>
			</p>
		</fieldset>
<?php
	endif;
}
add_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );

/**
 * Renders the Checkout Agree to Terms, this displays a checkbox for users to
 * agree the T&Cs set in the EDD Settings. This is only displayed if T&Cs are
 * set in the EDD Settings.
 *
 * @since 1.3.2
 * @return void
 */
function edd_terms_agreement() {

	/**
	 * No terms agreement output of any kind should ever show unless the checkbox
	 * is present for the customer to check: 'Agree to Terms' setting.
	 */
	if ( edd_get_option( 'show_agree_to_terms', false ) ) {

		$agree_text  = edd_get_option( 'agree_text', '' );
		$agree_label = edd_get_option( 'agree_label', __( 'Agree to Terms?', 'easy-digital-downloads' ) );

		ob_start();
		?>

		<fieldset id="edd_terms_agreement">

			<?php
			// Show Agreement Text output only if content exists. Remember that the Agree to Terms
			// label supports anchors tags, so the terms may be on a separate page.
			if ( ! empty( $agree_text ) ) {
				?>

				<div id="edd_terms" class="edd-terms" style="display:none;">
					<?php
					do_action( 'edd_before_terms' );
					echo wpautop( stripslashes( $agree_text ) );
					do_action( 'edd_after_terms' );
					?>
				</div>
				<div id="edd_show_terms" class="edd-show-terms">
					<a href="#" class="edd_terms_links"><?php _e( 'Show Terms', 'easy-digital-downloads' ); ?></a>
					<a href="#" class="edd_terms_links" style="display:none;"><?php _e( 'Hide Terms', 'easy-digital-downloads' ); ?></a>
				</div>
				<?php
			}
			?>

			<div class="edd-terms-agreement">
				<input name="edd_agree_to_terms" class="required" type="checkbox" id="edd_agree_to_terms" value="1"/>
				<label for="edd_agree_to_terms"><?php echo stripslashes( $agree_label ); ?></label>
			</div>
		</fieldset>

		<?php
		$html_output = ob_get_clean();

		echo apply_filters( 'edd_checkout_terms_agreement_html', $html_output );
	}
}
add_action( 'edd_purchase_form_before_submit', 'edd_terms_agreement' );


/**
 * Renders the Checkout Agree to Privacy Policy, this displays a checkbox for users to
 * agree the Privacy Policy set in the EDD Settings. This is only displayed if T&Cs are
 * set in the EDD Settings.
 *
 * @since 2.9.1
 * @return void
 */
function edd_privacy_agreement() {

	$show_privacy_policy_checkbox = edd_get_option( 'show_agree_to_privacy_policy', false );
	$show_privacy_policy_text     = edd_get_option( 'show_privacy_policy_on_checkout', false );

	/**
	 * Privacy Policy output has dual functionality, unlike Agree to Terms output:
	 *
	 * 1. A checkbox (and associated label) can show on checkout if the 'Agree to Privacy Policy' setting
	 *    is checked. This is because a Privacy Policy can be agreed upon without displaying the policy
	 *    itself. Keep in mind the label field supports anchor tags, so the policy can be linked to.
	 *
	 * 2. The Privacy Policy text, which is post_content pulled from the WP core Privacy Policy page when
	 *    you have the 'Show the Privacy Policy on checkout' setting checked, can be displayed on checkout
	 *    regardless of whether or not the customer has to explicitly agreed to the policy by checking the
	 *    checkbox from point #1 above.
	 *
	 * Because these two display options work independently, having either setting checked triggers output.
	 */
	if ( '1' === $show_privacy_policy_checkbox || '1' === $show_privacy_policy_text ) {

		$agree_label  = edd_get_option( 'privacy_agree_label', __( 'Agree to Privacy Policy?', 'easy-digital-downloads' ) );
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		$privacy_text = get_post_field( 'post_content', $privacy_page );

		ob_start();
		?>

		<fieldset id="edd-privacy-policy-agreement">

			<?php
			// Show Privacy Policy text if the setting is checked, the WP Privacy Page is set, and content exists.
			if ( '1' === $show_privacy_policy_text && ( $privacy_page && ! empty( $privacy_text ) ) ) {
				?>
				<div id="edd-privacy-policy" class="edd-terms" style="display:none;">
					<?php
					do_action( 'edd_before_privacy_policy' );
					echo wpautop( do_shortcode( stripslashes( $privacy_text ) ) );
					do_action( 'edd_after_privacy_policy' );
					?>
				</div>
				<div id="edd-show-privacy-policy" class="edd-show-terms">
					<a href="#"
					   class="edd_terms_links"><?php _e( 'Show Privacy Policy', 'easy-digital-downloads' ); ?></a>
					<a href="#" class="edd_terms_links"
					   style="display:none;"><?php _e( 'Hide Privacy Policy', 'easy-digital-downloads' ); ?></a>
				</div>
				<?php
			}

			// Show Privacy Policy checkbox and label if the setting is checked.
			if ( '1' === $show_privacy_policy_checkbox ) {
				?>
				<div class="edd-privacy-policy-agreement">
					<input name="edd_agree_to_privacy_policy" class="required" type="checkbox" id="edd-agree-to-privacy-policy" value="1"/>
					<label for="edd-agree-to-privacy-policy"><?php echo stripslashes( $agree_label ); ?></label>
				</div>
				<?php
			}
			?>

		</fieldset>

		<?php
		$html_output = ob_get_clean();

		echo apply_filters( 'edd_checkout_privacy_policy_agreement_html', $html_output );
	}
}
add_action( 'edd_purchase_form_before_submit', 'edd_privacy_agreement' );

/**
 * Shows the final purchase total at the bottom of the checkout page
 *
 * @since 1.5
 * @return void
 */
function edd_checkout_final_total() {
?>
<p id="edd_final_total_wrap">
	<strong><?php _e( 'Purchase Total:', 'easy-digital-downloads' ); ?></strong>
	<span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_total(); ?>"><?php edd_cart_total(); ?></span>
</p>
<?php
}
add_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );


/**
 * Renders the Checkout Submit section
 *
 * @since 1.3.3
 * @return void
 */
function edd_checkout_submit() {
?>
	<fieldset id="edd_purchase_submit">
		<?php do_action( 'edd_purchase_form_before_submit' ); ?>

		<?php edd_checkout_hidden_fields(); ?>

		<?php echo edd_checkout_button_purchase(); ?>

		<?php do_action( 'edd_purchase_form_after_submit' ); ?>

		<?php if ( edd_is_ajax_disabled() ) { ?>
			<p class="edd-cancel"><a href="<?php echo edd_get_checkout_uri(); ?>"><?php _e( 'Go back', 'easy-digital-downloads' ); ?></a></p>
		<?php } ?>
	</fieldset>
<?php
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_submit', 9999 );

/**
 * Renders the Next button on the Checkout
 *
 * @since 1.2
 * @return string
 */
function edd_checkout_button_next() {
	$color = edd_get_option( 'checkout_color', 'blue' );
	$color = ( $color == 'inherit' ) ? '' : $color;
	$style = edd_get_option( 'button_style', 'button' );
	$purchase_page = edd_get_option( 'purchase_page', '0' );

	ob_start();
?>
	<input type="hidden" name="edd_action" value="gateway_select" />
	<input type="hidden" name="page_id" value="<?php echo absint( $purchase_page ); ?>"/>
	<input type="submit" name="gateway_submit" id="edd_next_button" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" value="<?php _e( 'Next', 'easy-digital-downloads' ); ?>"/>
<?php
	return apply_filters( 'edd_checkout_button_next', ob_get_clean() );
}

/**
 * Renders the Purchase button on the Checkout
 *
 * @since 1.2
 * @return string
 */
function edd_checkout_button_purchase() {

	ob_start();

	$enabled_gateways = edd_get_enabled_payment_gateways();
	$cart_total       = edd_get_cart_total();

	if ( ! empty( $enabled_gateways ) || empty( $cart_total ) ) {
		$color = edd_get_option( 'checkout_color', 'blue' );
		$color = ( $color == 'inherit' ) ? '' : $color;
		$style = edd_get_option( 'button_style', 'button' );
		$label = edd_get_checkout_button_purchase_label();

		?>
		<input type="submit" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" id="edd-purchase-button" name="edd-purchase" value="<?php echo $label; ?>"/>
		<?php
	}

	return apply_filters( 'edd_checkout_button_purchase', ob_get_clean() );
}

/**
 * Retrieves the label for the purchase button
 *
 * @since 2.7.6
 * @return string
 */
function edd_get_checkout_button_purchase_label() {

	if ( edd_get_cart_total() ) {
		$label             = edd_get_option( 'checkout_label', '' );
		$complete_purchase = ! empty( $label ) ? $label : __( 'Purchase', 'easy-digital-downloads' );
	} else {
		$label             = edd_get_option( 'free_checkout_label', '' );
		$complete_purchase = ! empty( $label ) ? $label : __( 'Free Download', 'easy-digital-downloads' );
	}

	return apply_filters( 'edd_get_checkout_button_purchase_label', $complete_purchase, $label );
}

/**
 * Outputs the JavaScript code for the Agree to Terms section to toggle
 * the T&Cs text
 *
 * @since 1.0
 * @return void
 */
function edd_agree_to_terms_js() {
	if ( edd_get_option( 'show_agree_to_terms', false ) || edd_get_option( 'show_agree_to_privacy_policy', false ) ) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$( document.body ).on('click', '.edd_terms_links', function(e) {
				//e.preventDefault();
				$(this).parent().prev('.edd-terms').slideToggle();
				$(this).parent().find('.edd_terms_links').toggle();
				return false;
			});
		});
	</script>
<?php
	}
}
add_action( 'edd_checkout_form_top', 'edd_agree_to_terms_js' );

/**
 * Renders the hidden Checkout fields
 *
 * @since 1.3.2
 * @return void
 */
function edd_checkout_hidden_fields() {
?>
	<?php if ( is_user_logged_in() ) { ?>
	<input type="hidden" name="edd-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
	<input type="hidden" name="edd_action" value="purchase"/>
	<input type="hidden" name="edd-gateway" value="<?php echo edd_get_chosen_gateway(); ?>" />
	<?php wp_nonce_field( 'edd-process-checkout', 'edd-process-checkout-nonce', false, true ); ?>
<?php
}

/**
 * Filter Success Page Content
 *
 * Applies filters to the success page content.
 *
 * @since 1.0
 * @param string $content Content before filters
 * @return string $content Filtered content
 */
function edd_filter_success_page_content( $content ) {
	if ( isset( $_GET['payment-confirmation'] ) && edd_is_success_page() ) {
		if ( has_filter( 'edd_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'edd_payment_confirm_' . $_GET['payment-confirmation'], $content );
		}
	}

	return $content;
}
add_filter( 'the_content', 'edd_filter_success_page_content', 99999 );

/**
 * Show a download's files in the purchase receipt
 *
 * @since  1.8.6
 * @param  int        $item_id      The download ID
 * @param  array      $receipt_args Args specified in the [edd_receipt] shortcode
 * @param  array      $item         Cart item array
 * @return boolean
 */
function edd_receipt_show_download_files( $item_id, $receipt_args, $item = array() ) {

	$ret = true;

	/*
	 * If re-download is disabled, set return to false
	 *
	 * When the purchase session is still present AND the receipt being shown is for that purchase,
	 * file download links are still shown. Once session expires, links are disabled
	 */
	if ( edd_no_redownload() ) {

		$key     = isset( $_GET['payment_key'] ) ? sanitize_text_field( $_GET['payment_key'] ) : '';
		$session = edd_get_purchase_session();

		if ( ! empty( $key ) && ! empty( $session ) && $key != $session['purchase_key'] ) {

			// We have session data but the payment key provided is not for this session
			$ret = false;

		} elseif ( empty( $session ) ) {

			// No session data is present but a key has been provided
			$ret = false;

		}


	}

	return apply_filters( 'edd_receipt_show_download_files', $ret, $item_id, $receipt_args, $item );
}
