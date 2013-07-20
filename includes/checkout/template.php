<?php
/**
 * Checkout Template
 *
 * @package     EDD
 * @subpackage  Checkout
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Checkout Form
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD options
 * @global $user_ID ID of current logged in user
 * @global $post Current Post Object
 * @return string
 */
function edd_checkout_form() {
	global $edd_options, $user_ID, $post;

	$payment_mode = edd_get_chosen_gateway();
	$form_action  = esc_url( edd_get_checkout_uri( 'payment-mode=' . $payment_mode ) );

	ob_start();
		echo '<div id="edd_checkout_wrap">';
		if ( edd_get_cart_contents() ) :
			edd_checkout_cart();
		?>
			<div id="edd_checkout_form_wrap" class="edd_clearfix">
				<?php do_action( 'edd_before_purchase_form' ); ?>
				<form id="edd_purchase_form" action="<?php echo $form_action; ?>" method="POST">
					<?php
					do_action( 'edd_checkout_form_top' );

					if ( edd_show_gateways() ) {
						do_action( 'edd_payment_mode_select'  );
					} else {
						do_action( 'edd_purchase_form' );
					}

					do_action( 'edd_checkout_form_bottom' )
					?>
				</form>
				<?php do_action( 'edd_after_purchase_form' ); ?>
			</div><!--end #edd_checkout_form_wrap-->
		<?php
		else:
			do_action( 'edd_cart_empty' );
		endif;
		echo '</div><!--end #edd_checkout_wrap-->';
	return ob_get_clean();
}

/**
 * Renders the Purchase Form, hooks are provided to add to the purchase form.
 * The default Purchase Form rendered deisplays a list of the enabled payment
 * gateways, a user registration form (if enable) and a credit card info form
 * if credit cards are enabled
 *
 * @since 1.4
 * @global $edd_options Array of all the EDD options
 * @return string
 */
function edd_show_purchase_form() {
	global $edd_options;

	$payment_mode = edd_get_chosen_gateway();

	do_action( 'edd_purchase_form_top' );

	if ( edd_can_checkout() ) {

		do_action( 'edd_purchase_form_before_register_login' );

		if( isset( $edd_options['show_register_form'] ) && ! is_user_logged_in() && ! isset( $_GET['login'] ) ) : ?>
			<div id="edd_checkout_login_register">
				<?php do_action( 'edd_purchase_form_register_fields' ); ?>
			</div>
		<?php elseif( isset( $edd_options['show_register_form'] ) && ! is_user_logged_in() && isset( $_GET['login'] ) ) : ?>
			<div id="edd_checkout_login_register">
				<?php do_action( 'edd_purchase_form_login_fields' ); ?>
			</div>
		<?php endif; ?>

		<?php if( ( !isset( $_GET['login'] ) && is_user_logged_in() ) || !isset( $edd_options['show_register_form'] ) ) {
			do_action( 'edd_purchase_form_after_user_info' );
		}

		do_action( 'edd_purchase_form_before_cc_form' );

		// Load the credit card form and allow gateways to load their own if they wish
		if ( has_action( 'edd_' . $payment_mode . '_cc_form' ) ) {
			do_action( 'edd_' . $payment_mode . '_cc_form' );
		} else {
			do_action( 'edd_cc_form' );
		}

		do_action( 'edd_purchase_form_after_cc_form' );
	} else {
		// Can't checkout
		do_action( 'edd_purchase_form_no_access' );
	}

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
	if ( is_user_logged_in() ) :
		$user_data = get_userdata( get_current_user_id() );
	endif;
	?>
	<fieldset id="edd_checkout_user_info">
		<span><legend><?php echo apply_filters( 'edd_checkout_personal_info_text', __( 'Personal Info', 'edd' ) ); ?></legend></span>
		<?php do_action( 'edd_purchase_form_before_email' ); ?>
		<p id="edd-email-wrap">
			<label class="edd-label" for="edd-email">
				<?php _e( 'Email Address', 'edd' ); ?>
				<?php if( edd_field_is_required( 'edd_email' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'We will send the purchase receipt to this address.', 'edd' ); ?></span>
			<input class="edd-input required" type="email" name="edd_email" placeholder="<?php _e( 'Email address', 'edd' ); ?>" id="edd-email" value="<?php echo is_user_logged_in() ? $user_data->user_email : ''; ?>"/>
		</p>
		<?php do_action( 'edd_purchase_form_after_email' ); ?>
		<p id="edd-first-name-wrap">
			<label class="edd-label" for="edd-first">
				<?php _e( 'First Name', 'edd' ); ?>
				<?php if( edd_field_is_required( 'edd_first' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'We will use this to personalize your account experience.', 'edd' ); ?></span>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e( 'First Name', 'edd' ); ?>" id="edd-first" value="<?php echo is_user_logged_in() ? $user_data->first_name : ''; ?>"/>
		</p>
		<p id="edd-last-name-wrap">
			<label class="edd-label" for="edd-last">
				<?php _e( 'Last Name', 'edd' ); ?>
				<?php if( edd_field_is_required( 'edd_last' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'We will use this as well to personalize your account experience.', 'edd' ); ?></span>
			<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="<?php _e( 'Last name', 'edd' ); ?>" value="<?php echo is_user_logged_in() ? $user_data->last_name : ''; ?>"/>
		</p>
		<?php do_action( 'edd_purchase_form_user_info' ); ?>
	</fieldset>
	<?php
}
add_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );

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
		<span><legend><?php _e( 'Credit Card Info', 'edd' ); ?></legend></span>
		<?php if( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock"></span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'edd' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="edd-card-number-wrap">
			<label class="edd-label">
				<?php _e( 'Card Number', 'edd' ); ?>
				<span class="edd-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="edd-description"><?php _e( 'The (typically) 16 digits on the front of your credit card.', 'edd' ); ?></span>
			<input type="text" autocomplete="off" name="card_number" class="card-number edd-input required" placeholder="<?php _e( 'Card number', 'edd' ); ?>" />
		</p>
		<p id="edd-card-cvc-wrap">
			<label class="edd-label">
				<?php _e( 'CVC', 'edd' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'edd' ); ?></span>
			<input type="text" size="4" autocomplete="off" name="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e( 'Security code', 'edd' ); ?>" />
		</p>
		<p id="edd-card-name-wrap">
			<label class="edd-label">
				<?php _e( 'Name on the Card', 'edd' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The name printed on the front of your credit card.', 'edd' ); ?></span>
			<input type="text" autocomplete="off" name="card_name" class="card-name edd-input required" placeholder="<?php _e( 'Card name', 'edd' ); ?>" />
		</p>
		<?php do_action( 'edd_before_cc_expiration' ); ?>
		<p class="card-expiration">
			<label class="edd-label">
				<?php _e( 'Expiration (MM/YY)', 'edd' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The date your credit card expires, typically on the front of the card.', 'edd' ); ?></span>
			<select name="card_exp_month" class="card-expiry-month edd-select edd-select-small required">
				<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option value="' . $i . '">' . sprintf ('%02d', $i ) . '</option>'; } ?>
			</select>
			<span class="exp-divider"> / </span>
			<select name="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
				<?php for( $i = date('Y'); $i <= date('Y') + 10; $i++ ) { echo '<option value="' . $i . '">' . substr( $i, 2 ) . '</option>'; } ?>
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

	if( $logged_in ) {
		$user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );
	}
	$line1 = $logged_in && ! empty( $user_address['line1'] ) ? $user_address['line1'] : '';
	$line2 = $logged_in && ! empty( $user_address['line2'] ) ? $user_address['line2'] : '';
	$city  = $logged_in && ! empty( $user_address['city']  ) ? $user_address['city']  : '';
	$zip   = $logged_in && ! empty( $user_address['zip']   ) ? $user_address['zip']   : '';
	ob_start(); ?>
	<fieldset id="edd_cc_address" class="cc-address">
		<span><legend><?php _e( 'Billing Details', 'edd' ); ?></legend></span>
		<?php do_action( 'edd_cc_billing_top' ); ?>
		<p id="edd-card-address-wrap">
			<label class="edd-label"><?php _e( 'Billing Address', 'edd' ); ?></label>
			<span class="edd-description"><?php _e( 'The primary billing address for your credit card.', 'edd' ); ?></span>
			<input type="text" name="card_address" class="card-address edd-input required" placeholder="<?php _e( 'Address line 1', 'edd' ); ?>" value="<?php echo $line1; ?>"/>
		</p>
		<p id="edd-card-address-2-wrap">
			<label class="edd-label"><?php _e( 'Billing Address Line 2 (optional)', 'edd' ); ?></label>
			<span class="edd-description"><?php _e( 'The suite, apt no, PO box, etc, associated with your billing address.', 'edd' ); ?></span>
			<input type="text" name="card_address_2" class="card-address-2 edd-input" placeholder="<?php _e( 'Address line 2', 'edd' ); ?>" value="<?php echo $line2; ?>"/>
		</p>
		<p id="edd-card-city-wrap">
			<label class="edd-label"><?php _e( 'Billing City', 'edd' ); ?></label>
			<span class="edd-description"><?php _e( 'The city for your billing address.', 'edd' ); ?></span>
			<input type="text" name="card_city" class="card-city edd-input required" placeholder="<?php _e( 'City', 'edd' ); ?>" value="<?php echo $city; ?>"/>
		</p>
		<p id="edd-card-zip-wrap">
			<label class="edd-label"><?php _e( 'Billing Zip / Postal Code', 'edd' ); ?></label>
			<span class="edd-description"><?php _e( 'The zip or postal code for your billing address.', 'edd' ); ?></span>
			<input type="text" size="4" name="card_zip" class="card-zip edd-input required" placeholder="<?php _e( 'Zip / Postal code', 'edd' ); ?>"/>
		</p>
		<p id="edd-card-country-wrap">
			<label class="edd-label"><?php _e( 'Billing Country', 'edd' ); ?></label>
			<span class="edd-description"><?php _e( 'The country for your billing address.', 'edd' ); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country edd-select required">
				<?php

				$selected_country = edd_get_shop_country();

				if( $logged_in && ! empty( $user_address['country'] ) ) {
					$selected_country = $user_address['country'];
				}

				$countries = edd_get_country_list();
				foreach( $countries as $country_code => $country ) {
				  echo '<option value="' . $country_code . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>
		<p id="edd-card-state-wrap">
			<label class="edd-label"><?php _e( 'Billing State / Province', 'edd' ); ?></label>
			<span class="edd-description"><?php _e( 'The state or province for your billing address.', 'edd' ); ?></span>
            <?php
            $selected_state = edd_get_shop_state();
            $states         = edd_get_shop_states();

            if( $logged_in && ! empty( $user_address['state'] ) ) {
				$selected_state = $user_address['state'];
			}

            if( ! empty( $states ) ) : ?>
            <select name="card_state" id="card_state" class="card_state edd-select required">
                <?php
                    foreach( $states as $state_code => $state ) {
                        echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
                    }
                ?>
            </select>
        	<?php else : ?>
			<input type="text" size="6" name="card_state" id="card_state" class="card_state edd-input" placeholder="<?php _e( 'State / Province', 'edd' ); ?>"/>
			<?php endif; ?>
		</p>
		<?php do_action( 'edd_cc_billing_bottom' ); ?>
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
	global $edd_options;
	global $user_ID;

	if ( is_user_logged_in() )
	$user_data = get_userdata( $user_ID );

	ob_start(); ?>
	<fieldset id="edd_register_fields">
		<p id="edd-login-account-wrap"><?php _e( 'Already have an account?', 'edd' ); ?> <a href="<?php echo add_query_arg('login', 1); ?>" class="edd_checkout_register_login" data-action="checkout_login"><?php _e( 'Login', 'edd' ); ?></a></p>
		<?php do_action('edd_register_fields_before'); ?>
		<p id="edd-user-email-wrap">
			<label for="edd-email">
				<?php _e( 'Email', 'edd' ); ?>
				<?php if( edd_field_is_required( 'edd_email' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'We will send the purchase receipt to this address.', 'edd' ); ?></span>
			<input name="edd_email" id="edd-email" class="required edd-input" type="email" placeholder="<?php _e( 'Email', 'edd' ); ?>" title="<?php _e( 'Email', 'edd' ); ?>"/>
		</p>
		<p id="edd-user-first-name-wrap">
			<label class="edd-label" for="edd-first">
				<?php _e( 'First Name', 'edd' ); ?>
				<?php if( edd_field_is_required( 'edd_first' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'We will use this to personalize your account experience.', 'edd' ); ?></span>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e( 'First Name', 'edd' ); ?>" id="edd-first" value="<?php echo is_user_logged_in() ? $user_data->user_firstname : ''; ?>"/>
		</p>
		<p id="edd-user-last-name-wrap">
			<label class="edd-label" for="edd-last">
				<?php _e( 'Last Name', 'edd' ); ?>
				<?php if( edd_field_is_required( 'edd_last' ) ) { ?>
				<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'We will use this as well to personalize your account experience.', 'edd' ); ?></span>
			<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="<?php _e( 'Last name', 'edd' ); ?>" value="<?php echo is_user_logged_in() ? $user_data->user_lastname : ''; ?>"/>
		</p>
		<?php do_action('edd_register_fields_after'); ?>
		<fieldset id="edd_register_account_fields">
			<span><legend><?php _e( 'Create an account', 'edd' ); if( !edd_no_guest_checkout() ) { echo ' ' . __( '(optional)', 'edd' ); } ?></legend></span>
			<?php do_action('edd_register_account_fields_before'); ?>
			<p id="edd-user-login-wrap">
				<label for="edd_user_login">
					<?php _e( 'Username', 'edd' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="edd-description"><?php _e( 'The username you will use to log into your account.', 'edd' ); ?></span>
				<input name="edd_user_login" id="edd_user_login" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" placeholder="<?php _e( 'Username', 'edd' ); ?>" title="<?php _e( 'Username', 'edd' ); ?>"/>
			</p>
			<p id="edd-user-pass-wrap">
				<label for="password">
					<?php _e( 'Password', 'edd' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="edd-description"><?php _e( 'The password used to access your account.', 'edd' ); ?></span>
				<input name="edd_user_pass" id="edd_user_pass" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e( 'Password', 'edd' ); ?>" type="password"/>
			</p>
			<p id="edd-user-pass-confirm-wrap" class="edd_register_password">
				<label for="password_again">
					<?php _e( 'Password Again', 'edd' ); ?>
					<?php if( edd_no_guest_checkout() ) { ?>
					<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="edd-description"><?php _e( 'Confirm your password.', 'edd' ); ?></span>
				<input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e( 'Confirm password', 'edd' ); ?>" type="password"/>
			</p>
			<?php do_action( 'edd_register_account_fields_after' ); ?>
		</fieldset>
		<input type="hidden" name="edd-purchase-var" value="needs-to-register"/>

		<?php do_action( 'edd_purchase_form_user_info' ); ?>
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
	ob_start(); ?>
		<fieldset id="edd_login_fields">
			<p id="edd-new-account-wrap">
				<?php _e( 'Need to create an account?', 'edd' ); ?>
				<a href="<?php echo remove_query_arg('login'); ?>" class="edd_checkout_register_login" data-action="checkout_register">
					<?php _e( 'Register', 'edd' ); if(!edd_no_guest_checkout()) { echo ' ' . __( 'or checkout as a guest.', 'edd' ); } ?>
				</a>
			</p>
			<?php do_action('edd_checkout_login_fields_before'); ?>
			<p id="edd-user-login-wrap">
				<label class="edd-label" for="edd-username"><?php _e( 'Username', 'edd' ); ?></label>
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="<?php _e( 'Your username', 'edd' ); ?>"/>
			</p>
			<p id="edd-user-pass-wrap" class="edd_login_password">
				<label class="edd-label" for="edd-password"><?php _e( 'Password', 'edd' ); ?></label>
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="<?php _e( 'Your password', 'edd' ); ?>"/>
				<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
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
	$gateways = edd_get_enabled_payment_gateways();
	$page_URL = edd_get_current_page_url();
	do_action('edd_payment_mode_top'); ?>
	<?php if( ! edd_is_ajax_enabled() ) { ?>
	<form id="edd_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
	<?php } ?>
		<fieldset id="edd_payment_mode_select">
			<?php do_action( 'edd_payment_mode_before_gateways_wrap' ); ?>
			<div id="edd-payment-mode-wrap">
				<span class="edd-payment-mode-label"><?php _e( 'Select Payment Method', 'edd' ); ?></span><br/>
				<?php

				do_action( 'edd_payment_mode_before_gateways' );

				foreach ( $gateways as $gateway_id => $gateway ) :
					$checked = checked( $gateway_id, edd_get_default_gateway(), false );
					echo '<label for="edd-gateway-' . esc_attr( $gateway_id ) . '" class="edd-gateway-option" id="edd-gateway-option-' . esc_attr( $gateway_id ) . '">';
						echo '<input type="radio" name="payment-mode" class="edd-gateway" id="edd-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . '>' . esc_html( $gateway['checkout_label'] ) . '</option>';
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
	<?php if( ! edd_is_ajax_enabled() ) { ?>
	</form>
	<?php } ?>
	<div id="edd_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->
	<?php do_action('edd_payment_mode_bottom');
}
add_action( 'edd_payment_mode_select', 'edd_payment_mode_select' );


/**
 * Show Payment Icons by getting all the accepted icons from the EDD Settings
 * then ouputting the icons.
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
*/
function edd_show_payment_icons() {
	global $edd_options;

	if ( isset( $edd_options['accepted_cards'] ) ) {
		echo '<div class="edd-payment-icons">';
		foreach( $edd_options['accepted_cards'] as $key => $card ) {
			if( edd_string_is_image_url( $key ) ) {
				echo '<img class="payment-icon" src="' . $key . '"/>';
			} else {
				echo '<img class="payment-icon" src="' . EDD_PLUGIN_URL . 'assets/images/icons/' . strtolower( str_replace( ' ', '', $card ) ) . '.gif"/>';
			}
		}
		echo '</div>';
	}
}
add_action( 'edd_payment_mode_before_gateways', 'edd_show_payment_icons' );


/**
 * Renders the Discount Code field which allows users to enter a discount code.
 * This field is only displayed if there are any active discounts on the site else
 * it's not displayed.
 *
 * @since 1.2.2
 * @return void
*/
function edd_discount_field() {

	if( ! isset( $_GET['payment-mode'] ) && ! edd_is_ajax_enabled() )
		return; // Only show once a payment method has been selected if ajax is disabled

	if ( edd_has_active_discounts() && edd_get_cart_total() ) {
	?>
	<fieldset id="edd_discount_code">
		<p id="edd_show_discount" style="display:none;">
			<?php _e( 'Have a discount code?', 'edd' ); ?> <a href="#" class="edd_discount_link"><?php echo _x( 'Click to enter it', 'Entering a discount code', 'edd' ); ?></a>
		</p>
		<p id="edd-discount-code-wrap">
			<label class="edd-label" for="edd-discount">
				<?php _e( 'Discount', 'edd' ); ?>
				<img src="<?php echo EDD_PLUGIN_URL; ?>assets/images/loading.gif" id="edd-discount-loader" style="display:none;"/>
			</label>
			<span class="edd-description"><?php _e( 'Enter a coupon code if you have one.', 'edd' ); ?></span>
			<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php _e( 'Enter discount', 'edd' ); ?>"/>
		</p>
	</fieldset>
	<?php
	}
}
add_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );

/**
 * Renders the Checkout Agree to Terms, this displays a checkbox for users to
 * agree the T&Cs set in the EDD Settings. This is only displayed if T&Cs are
 * set in the EDD Settigs.
 *
 * @since 1.3.2
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_terms_agreement() {
	global $edd_options;
	if ( isset( $edd_options['show_agree_to_terms'] ) ) {
?>
		<fieldset id="edd_terms_agreement">
			<div id="edd_terms" style="display:none;">
				<?php
					do_action( 'edd_before_terms' );
					echo wpautop( $edd_options['agree_text'] );
					do_action( 'edd_after_terms' );
				?>
			</div>
			<div id="edd_show_terms">
				<a href="#" class="edd_terms_links"><?php _e( 'Show Terms', 'edd' ); ?></a>
				<a href="#" class="edd_terms_links" style="display:none;"><?php _e( 'Hide Terms', 'edd' ); ?></a>
			</div>
			<label for="edd_agree_to_terms"><?php echo isset( $edd_options['agree_label'] ) ? $edd_options['agree_label'] : __( 'Agree to Terms?', 'edd' ); ?></label>
			<input name="edd_agree_to_terms" class="required" type="checkbox" id="edd_agree_to_terms" value="1"/>
		</fieldset>
<?php
	}
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_terms_agreement', 999 );

/**
 * Shows the final purchase total at the bottom of the checkout page
 *
 * @since 1.5
 * @return void
 */
function edd_checkout_final_total() {
?>
<p id="edd_final_total_wrap">
	<strong><?php _e( 'Purchase Total:', 'edd' ); ?></strong>
	<span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_amount( false ); ?>" data-total="<?php echo edd_get_cart_amount( true, true ); ?>"><?php edd_cart_total(); ?></span>
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

		<?php if ( ! edd_is_ajax_enabled() ) { ?>
			<p class="edd-cancel"><a href="javascript:history.go(-1)"><?php _e( 'Go back', 'edd' ); ?></a></p>
		<?php } ?>
	</fieldset>
<?php
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_submit', 9999 );

/**
 * Renders the Next button on the Checkout
 *
 * @since 1.2
 * @global $edd_options Array of all the EDD Options
 * @return string
 */
function edd_checkout_button_next() {
	global $edd_options;

	$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
	$color = ( $color == 'inherit' ) ? '' : $color;
	$style = isset( $edd_options[ 'button_style' ] ) ? $edd_options[ 'button_style' ] : 'button';

	ob_start();
?>
	<input type="hidden" name="edd_action" value="gateway_select" />
	<input type="hidden" name="page_id" value="<?php echo absint( $edd_options['purchase_page'] ); ?>"/>
	<input type="submit" name="gateway_submit" id="edd_next_button" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" value="<?php _e( 'Next', 'edd' ); ?>"/>
<?php
	return apply_filters( 'edd_checkout_button_next', ob_get_clean() );
}

/**
 * Renders the Purchase button on the Checkout
 *
 * @since 1.2
 * @global $edd_options Array of all the EDD Options
 * @return string
 */
function edd_checkout_button_purchase() {
	global $edd_options;

	$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
	$color = ( $color == 'inherit' ) ? '' : $color;
	$style = isset( $edd_options[ 'button_style' ] ) ? $edd_options[ 'button_style' ] : 'button';

	if ( edd_get_cart_total() ) {
		$complete_purchase = ! empty( $edd_options['checkout_label'] ) ? $edd_options['checkout_label'] : __( 'Purchase', 'edd' );
	} else {
		$complete_purchase = ! empty( $edd_options['checkout_label'] ) ? $edd_options['checkout_label'] : __( 'Free Download', 'edd' );
	}

	ob_start();
?>
	<input type="submit" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" id="edd-purchase-button" name="edd-purchase" value="<?php echo $complete_purchase; ?>"/>
<?php
	return apply_filters( 'edd_checkout_button_purchase', ob_get_clean() );
}

/**
 * Outputs the JavaScript code for the Agree to Terms section to toggle
 * the T&Cs text
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_agree_to_terms_js() {
	global $edd_options;

	if ( isset( $edd_options['show_agree_to_terms'] ) ) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('body').on('click', '.edd_terms_links', function(e) {
				//e.preventDefault();
				$('#edd_terms').slideToggle();
				$('.edd_terms_links').toggle();
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
<?php
}
