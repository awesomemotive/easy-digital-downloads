<?php
/**
 * Checkout Template
 *
 * @package     EDD
 * @subpackage  Checkout
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get Checkout Form
 *
 * @since 1.0
 * @return string
 */
function edd_checkout_form() {
	$payment_mode = edd_get_chosen_gateway();
	$form_action  = edd_get_checkout_uri( 'payment-mode=' . $payment_mode );

	ob_start();
		echo '<div id="edd_checkout_wrap">';
		if ( edd_get_cart_contents() || edd_cart_has_fees() ) :
			edd_checkout_cart(); ?>
			<div id="edd_checkout_form_wrap" class="edd_clearfix">
				<?php do_action( 'edd_before_purchase_form' ); ?>
				<form id="edd_purchase_form" class="edd_form" action="<?php echo esc_url( $form_action ); ?>" method="POST">
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
	 * Hooks in at the top of the purchase form.
	 *
	 * @since 1.4
	 */
	do_action( 'edd_purchase_form_top' );

	// Maybe load purchase form.
	if ( edd_can_checkout() ) {

		/**
		 * Fires before the register/login form.
		 *
		 * @since 1.4
		 */
		do_action( 'edd_purchase_form_before_register_login' );

		$show_register_form = edd_get_option( 'show_register_form', 'none' );
		if ( ( 'registration' === $show_register_form || ( 'both' === $show_register_form && ! isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) : ?>
			<div id="edd_checkout_login_register">
				<?php do_action( 'edd_purchase_form_register_fields' ); ?>
			</div>
		<?php elseif ( ( 'login' === $show_register_form || ( 'both' === $show_register_form && isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) : ?>
			<div id="edd_checkout_login_register">
				<?php do_action( 'edd_purchase_form_login_fields' ); ?>
			</div>
		<?php endif; ?>

		<?php
		if ( ( ! isset( $_GET['login'] ) && is_user_logged_in() ) || ! isset( $show_register_form ) || 'none' === $show_register_form || 'login' === $show_register_form ) { // WPCS: CSRF ok.
			do_action( 'edd_purchase_form_after_user_info' );
		}

		/**
		 * Hooks in before the credit card form.
		 *
		 * @since 1.4
		 */
		do_action( 'edd_purchase_form_before_cc_form' );

		if ( edd_get_cart_total() > 0 ) {

			// Load the credit card form and allow gateways to load their own if they wish.
			if ( has_action( 'edd_' . $payment_mode . '_cc_form' ) ) {
				do_action( 'edd_' . $payment_mode . '_cc_form' );
			} else {
				do_action( 'edd_cc_form' );
			}
		}

		/**
		 * Hooks in after the credit card form.
		 *
		 * @since 1.4
		 */
		do_action( 'edd_purchase_form_after_cc_form' );

	// Can't checkout.
	} else {
		do_action( 'edd_purchase_form_no_access' );
	}

	/**
	 * Hooks in at the bottom of the purchase form.
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

	if ( is_user_logged_in() ) {
		$user_data = get_userdata( get_current_user_id() );
		foreach ( $customer as $key => $field ) {
			if ( 'email' === $key && empty( $field ) ) {
				$customer[ $key ] = $user_data->user_email;
			} elseif ( empty( $field ) ) {
				$customer[ $key ] = $user_data->$key;
			}
		}
	}

	$customer = array_map( 'sanitize_text_field', $customer );
	?>
	<fieldset id="edd_checkout_user_info">
		<legend><?php echo apply_filters( 'edd_checkout_personal_info_text', esc_html__( 'Personal info', 'easy-digital-downloads' ) ); ?></legend>
		<?php do_action( 'edd_purchase_form_before_email' ); ?>
		<p id="edd-email-wrap">
			<label class="edd-label" for="edd-email">
				<?php esc_html_e( 'Email address', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'edd_email' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description" id="edd-email-description"><?php esc_html_e( 'We will send the purchase receipt to this address.', 'easy-digital-downloads' ); ?></span>
			<input class="edd-input required" type="email" name="edd_email" placeholder="<?php esc_html_e( 'Email address', 'easy-digital-downloads' ); ?>" id="edd-email" value="<?php echo esc_attr( $customer['email'] ); ?>" aria-describedby="edd-email-description"<?php if( edd_field_is_required( 'edd_email' ) ) {  echo ' required '; } ?>/>
		</p>
		<?php do_action( 'edd_purchase_form_after_email' ); ?>
		<p id="edd-first-name-wrap">
			<label class="edd-label" for="edd-first">
				<?php esc_html_e( 'First name', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'edd_first' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description" id="edd-first-description"><?php esc_html_e( 'We will use this to personalize your account experience.', 'easy-digital-downloads' ); ?></span>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php esc_html_e( 'First name', 'easy-digital-downloads' ); ?>" id="edd-first" value="<?php echo esc_attr( $customer['first_name'] ); ?>"<?php if( edd_field_is_required( 'edd_first' ) ) {  echo ' required '; } ?> aria-describedby="edd-first-description" />
		</p>
		<p id="edd-last-name-wrap">
			<label class="edd-label" for="edd-last">
				<?php esc_html_e( 'Last name', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'edd_last' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description" id="edd-last-description"><?php esc_html_e( 'We will use this as well to personalize your account experience.', 'easy-digital-downloads' ); ?></span>
			<input class="edd-input<?php if( edd_field_is_required( 'edd_last' ) ) { echo ' required'; } ?>" type="text" name="edd_last" id="edd-last" placeholder="<?php esc_html_e( 'Last name', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['last_name'] ); ?>"<?php if( edd_field_is_required( 'edd_last' ) ) {  echo ' required '; } ?> aria-describedby="edd-last-description"/>
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
	/**
	 * Allow the credit card fields to be replaced.
	 * @since 3.1
	 */
	if ( null !== apply_filters( 'edd_pre_cc_fields', null ) ) {
		do_action( 'edd_cc_fields' );
		return;
	}
	ob_start(); ?>

	<?php do_action( 'edd_before_cc_fields' ); ?>

	<fieldset id="edd_cc_fields" class="edd-do-validate">
		<legend><?php _e( 'Credit card info', 'easy-digital-downloads' ); ?></legend>
		<?php if ( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<?php
					echo edd_get_payment_icon(
						array(
							'icon'    => 'lock',
							'width'   => 16,
							'height'  => 16,
							'title'   => __( 'Secure SSL encrypted payment', 'easy-digital-downloads' ),
							'classes' => array( 'edd-icon', 'edd-icon-lock' )
						)
					);
				?>
				<span><?php _e( 'This is a secure SSL encrypted payment', 'easy-digital-downloads' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="edd-card-number-wrap">
			<label for="card_number" class="edd-label">
				<?php _e( 'Card number', 'easy-digital-downloads' ); ?>
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
				<?php _e( 'Name on the card', 'easy-digital-downloads' ); ?>
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
				<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option value="' . absint( $i ) . '">' . sprintf ('%02d', absint( $i ) ) . '</option>'; } ?>
			</select>
			<span class="exp-divider">/</span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
				<?php for( $i = date('Y'); $i <= date('Y') + 30; $i++ ) { echo '<option value="' . absint( $i ) . '">' . substr( $i, 2 ) . '</option>'; } ?>
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
 * @since 3.0 Updated to use `edd_get_customer_address()`.
 */
function edd_default_cc_address_fields() {
	/**
	 * Allow the address fields to be replaced.
	 * @since 3.1
	 */
	if ( null !== apply_filters( 'edd_pre_cc_address_fields', null ) ) {
		do_action( 'edd_cc_address_fields' );
		return;
	}
	$logged_in = is_user_logged_in();

	$customer = EDD()->session->get( 'customer' );

	$customer = wp_parse_args( $customer, array(
		'address' => array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'zip'     => '',
			'state'   => '',
			'country' => '',
		),
	) );

	$customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

	if ( $logged_in ) {
		$user_address = edd_get_customer_address();

		foreach ( $customer['address'] as $key => $field ) {
			if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
				$customer['address'][ $key ] = $user_address[ $key ];
			} else {
				$customer['address'][ $key ] = '';
			}
		}
	}

	/**
	 * Filter the billing address details that will be pre-populated on the checkout form..
	 *
	 * @since 2.8
	 *
	 * @param array $address The customer address.
	 * @param array $customer The customer data from the session
	 */
	$customer['address'] = apply_filters( 'edd_checkout_billing_details_address', $customer['address'], $customer );

	ob_start(); ?>
	<fieldset id="edd_cc_address" class="cc-address">
		<legend><?php _e( 'Billing details', 'easy-digital-downloads' ); ?></legend>
		<?php do_action( 'edd_cc_billing_top' ); ?>
		<p id="edd-card-address-wrap">
			<label for="card_address" class="edd-label">
				<?php _e( 'Billing address', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'card_address' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description"><?php _e( 'The primary billing address for your credit card.', 'easy-digital-downloads' ); ?></span>
			<input type="text" id="card_address" name="card_address" class="card-address edd-input<?php if ( edd_field_is_required( 'card_address' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Address line 1', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['line1'] ); ?>"<?php if( edd_field_is_required( 'card_address' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-address-2-wrap">
			<label for="card_address_2" class="edd-label">
				<?php _e( 'Billing address line 2 (optional)', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_address_2' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description"><?php _e( 'The suite, apt no, PO box, etc, associated with your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 edd-input<?php if ( edd_field_is_required( 'card_address_2' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Address line 2', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['line2'] ); ?>"<?php if( edd_field_is_required( 'card_address_2' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-city-wrap">
			<label for="card_city" class="edd-label">
				<?php _e( 'Billing city', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'card_city' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description"><?php _e( 'The city for your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" id="card_city" name="card_city" class="card-city edd-input<?php if ( edd_field_is_required( 'card_city' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'City', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['city'] ); ?>"<?php if( edd_field_is_required( 'card_city' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e( 'Billing zip/Postal code', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'card_zip' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description"><?php _e( 'The zip or postal code for your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" size="4" id="card_zip" name="card_zip" class="card-zip edd-input<?php if ( edd_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Zip / Postal Code', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['zip'] ); ?>"<?php if ( edd_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?>/>
		</p>
		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e( 'Billing country', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'billing_country' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
			</label>
			<span class="edd-description"><?php _e( 'The country for your billing address.', 'easy-digital-downloads' ); ?></span>
			<select name="billing_country" id="billing_country" data-nonce="<?php echo wp_create_nonce( 'edd-country-field-nonce' ); ?>" class="billing_country edd-select<?php if ( edd_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if ( edd_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?>>
				<?php
				$selected_country = edd_get_shop_country();

				if ( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = edd_get_country_list();
				foreach ( $countries as $country_code => $country ) {
					echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . esc_html( $country ) . '</option>';
				}
				?>
			</select>
		</p>
		<p id="edd-card-state-wrap">
			<label for="card_state" class="edd-label">
				<?php _e( 'Billing state/Province', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'card_state' ) ) { ?>
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
			<select name="card_state" id="card_state" class="card_state edd-select<?php if ( edd_field_is_required( 'card_state' ) ) { echo ' required'; } ?>">
				<?php
					foreach( $states as $state_code => $state ) {
						echo '<option value="' . esc_attr( $state_code ) . '"' . selected( $state_code, $selected_state, false ) . '>' . esc_html( $state ) . '</option>';
					}
				?>
			</select>
			<?php
			else :
				$customer_state = ! empty( $customer['address']['state'] ) ? $customer['address']['state'] : ''; ?>
			<input type="text" size="6" name="card_state" id="card_state" class="card_state edd-input" value="<?php echo esc_attr( $customer_state ); ?>" placeholder="<?php _e( 'State/Province', 'easy-digital-downloads' ); ?>"/>
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
 * Renders the billing address fields for cart taxation.
 *
 * @since 1.6
 */
function edd_checkout_tax_fields() {
	if ( edd_cart_needs_tax_address_fields() && edd_get_cart_total() ) {
		edd_default_cc_address_fields();
	}
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_tax_fields', 999 );


/**
 * Renders the user registration fields. If the user is logged in, a login
 * form is displayed other a registration form is provided for the user to
 * create an account.
 *
 * @since 1.0
 *
 * @return string
 */
function edd_get_register_fields() {
	$show_register_form = edd_get_option( 'show_register_form', 'none' );

	ob_start(); ?>
	<fieldset id="edd_register_fields">

		<?php if ( 'both' === $show_register_form ) { ?>
			<p id="edd-login-account-wrap">
				<?php esc_html_e( 'Already have an account?', 'easy-digital-downloads' ); ?> <a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="edd_checkout_register_login" data-action="checkout_login" data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_checkout_login' ) ); ?>"><?php esc_html_e( 'Log in', 'easy-digital-downloads' ); ?></a>
			</p>
		<?php } ?>

		<?php do_action( 'edd_register_fields_before' ); ?>

		<fieldset id="edd_register_account_fields">
			<legend><?php _e( 'Create an account', 'easy-digital-downloads' ); if( !edd_no_guest_checkout() ) { echo ' ' . __( '(optional)', 'easy-digital-downloads' ); } ?></legend>
			<?php do_action( 'edd_register_account_fields_before' ); ?>
			<p id="edd-user-login-wrap">
				<label for="edd_user_login">
					<?php esc_html_e( 'Username', 'easy-digital-downloads' ); ?>
					<?php
					$require_login = edd_no_guest_checkout();
					if ( $require_login ) {
						echo EDD()->html->show_required();
					}
					?>
				</label>
				<span class="edd-description"><?php _e( 'The username you will use to log into your account.', 'easy-digital-downloads' ); ?></span>
				<input name="edd_user_login" id="edd_user_login" class="<?php if ( $require_login ) { echo sanitize_html_class( 'required' ) . ' '; } ?>edd-input" type="text" placeholder="<?php esc_html_e( 'Username', 'easy-digital-downloads' ); ?>" <?php echo esc_attr( $require_login ? 'required' : '' ); ?> />
			</p>
			<p id="edd-user-pass-wrap">
				<label for="edd_user_pass">
					<?php
					esc_html_e( 'Password', 'easy-digital-downloads' );
					if ( $require_login ) {
						echo EDD()->html->show_required();
					}
					?>
				</label>
				<span class="edd-description"><?php esc_html_e( 'The password used to access your account.', 'easy-digital-downloads' ); ?></span>
				<input name="edd_user_pass" id="edd_user_pass" class="<?php if ( $require_login ) { echo sanitize_html_class( 'required' ) . ' '; } ?>edd-input" placeholder="<?php esc_html_e( 'Password', 'easy-digital-downloads' ); ?>" type="password" <?php echo esc_attr( $require_login ? 'required' : '' ); ?>/>
			</p>
			<p id="edd-user-pass-confirm-wrap" class="edd_register_password">
				<label for="edd_user_pass_confirm">
					<?php
					esc_html_e( 'Password again', 'easy-digital-downloads' );
					if ( $require_login ) {
						echo EDD()->html->show_required();
					}
					?>
				</label>
				<span class="edd-description"><?php esc_html_e( 'Confirm your password.', 'easy-digital-downloads' ); ?></span>
				<input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="<?php if ( $require_login ) { echo sanitize_html_class( 'required' ) . ' '; } ?>edd-input" placeholder="<?php esc_html_e( 'Confirm password', 'easy-digital-downloads' ); ?>" type="password" <?php echo esc_attr( $require_login ? 'required' : '' ); ?>/>
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
	$color = edd_get_button_color_class( 'gray' );

	$style = edd_get_option( 'button_style', 'button' );

	$show_register_form = edd_get_option( 'show_register_form', 'none' );
	$require_login      = edd_no_guest_checkout();

	ob_start(); ?>
		<fieldset id="edd_login_fields">
			<?php if ( 'both' === $show_register_form ) : ?>
				<p id="edd-new-account-wrap">
					<?php esc_html_e( 'Need to create an account?', 'easy-digital-downloads' ); ?>
					<a href="<?php echo esc_url( remove_query_arg( 'login' ) ); ?>" class="edd_checkout_register_login" data-action="checkout_register"  data-nonce="<?php echo wp_create_nonce( 'edd_checkout_register' ); ?>">
						<?php esc_html_e( 'Register', 'easy-digital-downloads' ); if ( ! $require_login ) { echo esc_html( ' ' . __( 'or checkout as a guest', 'easy-digital-downloads' ) ); } ?>
					</a>
				</p>
			<?php endif; ?>

			<?php do_action( 'edd_checkout_login_fields_before' ); ?>

			<p id="edd-user-login-wrap">
				<label class="edd-label" for="edd_user_login">
					<?php
					esc_html_e( 'Username or email', 'easy-digital-downloads' );
					if ( $require_login ) {
						echo ' ' . EDD()->html->show_required();
					}
					?>
				</label>
				<input class="<?php if( $require_login ) { echo sanitize_html_class( 'required' ) . ' '; } ?>edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="<?php _e( 'Your username or email address', 'easy-digital-downloads' ); ?>" <?php echo esc_attr( $require_login ? 'required' : '' ); ?>/>
			</p>
			<p id="edd-user-pass-wrap" class="edd_login_password">
				<label class="edd-label" for="edd_user_pass">
					<?php
					esc_html_e( 'Password', 'easy-digital-downloads' );
					if ( $require_login ) {
						echo ' ' . EDD()->html->show_required();
					}
					?>
				</label>
				<input class="<?php if ( $require_login ) { echo sanitize_html_class( 'required') . ' '; } ?>edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="<?php esc_html_e( 'Your password', 'easy-digital-downloads' ); ?>" <?php echo esc_attr( $require_login ? 'required' : '' ); ?>/>
				<?php if ( $require_login ) : ?>
					<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
				<?php endif; ?>
			</p>
			<p id="edd-user-login-submit">
				<input type="submit" class="edd-submit <?php echo sanitize_html_class( $color ); ?> <?php echo sanitize_html_class( $style ); ?>" name="edd_login_submit" value="<?php esc_html_e( 'Log in', 'easy-digital-downloads' ); ?>"/>
				<?php wp_nonce_field( 'edd-login-form', 'edd_login_nonce', false, true ); ?>
			</p>

			<?php do_action( 'edd_checkout_login_fields_after' ); ?>
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
 */
function edd_payment_mode_select() {
	$gateways = edd_get_enabled_payment_gateways( true );
	$page_URL = edd_get_current_page_url();
	$chosen_gateway = edd_get_chosen_gateway();
	?>
	<div id="edd_payment_mode_select_wrap">
		<?php do_action('edd_payment_mode_top'); ?>

		<?php if( edd_is_ajax_disabled() ) { ?>
		<form id="edd_payment_mode" action="<?php echo esc_url( $page_URL ); ?>" method="GET">
		<?php } ?>

			<fieldset id="edd_payment_mode_select">
				<legend><?php _e( 'Select payment method', 'easy-digital-downloads' ); ?></legend>
				<?php do_action( 'edd_payment_mode_before_gateways_wrap' ); ?>
				<div id="edd-payment-mode-wrap">
					<?php
					do_action( 'edd_payment_mode_before_gateways' );

					foreach ( $gateways as $gateway_id => $gateway ) {
						$label         = apply_filters( 'edd_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] );
						$checked       = checked( $gateway_id, $chosen_gateway, false );
						$checked_class = $checked ? 'edd-gateway-option-selected' : '';
						$nonce         = ' data-' . esc_attr( $gateway_id ) . '-nonce="' . wp_create_nonce( 'edd-gateway-selected-' . esc_attr( $gateway_id ) ) .'"';

						echo '<label for="edd-gateway-' . esc_attr( $gateway_id ) . '" class="edd-gateway-option ' . esc_attr( $checked_class ) . '" id="edd-gateway-option-' . esc_attr( $gateway_id ) . '">';
							echo '<input autocomplete="off" type="radio" name="payment-mode" class="edd-gateway" id="edd-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . $nonce . '>' . esc_html( $label );
						echo '</label>';
					}

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

		<?php if ( edd_is_ajax_disabled() ) : ?>
		</form>
		<?php endif; ?>

	</div>
	<div id="edd_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->

	<?php do_action( 'edd_payment_mode_bottom' );
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

	if ( edd_show_gateways() && did_action( 'edd_payment_mode_top' ) ) {
		return;
	}

	$payment_methods = edd_get_option( 'accepted_cards', array() );

	if ( empty( $payment_methods ) ) {
		return;
	}

	// Get the icon order option
	$order = edd_get_option( 'payment_icons_order', '' );

	// If order is set, enforce it
	if ( ! empty( $order ) ) {
		$order           = array_flip( explode( ',', $order ) );
		$order           = array_intersect_key( $order, $payment_methods );
		$payment_methods = array_merge( $order, $payment_methods );
	}

	echo '<div class="edd-payment-icons">';

	foreach ( $payment_methods as $key => $option ) {
		echo edd_get_payment_image( $key, $option );
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
	if ( isset( $_GET['payment-mode'] ) && edd_is_ajax_disabled() ) {
		return; // Only show before a payment method has been selected if ajax is disabled
	}

	if ( ! edd_is_checkout() ) {
		return;
	}

	if ( edd_has_active_discounts() && edd_get_cart_total() ) :
		$color = edd_get_button_color_class();
		$style = edd_get_option( 'button_style', 'button' ); ?>
		<fieldset id="edd_discount_code">
			<p id="edd_show_discount" style="display:none;">
				<?php _e( 'Have a discount code?', 'easy-digital-downloads' ); ?> <a href="#" class="edd_discount_link"><?php echo _x( 'Click to enter it', 'Entering a discount code', 'easy-digital-downloads' ); ?></a>
			</p>
			<p id="edd-discount-code-wrap" class="edd-cart-adjustment">
				<label class="edd-label" for="edd-discount">
					<?php _e( 'Discount', 'easy-digital-downloads' ); ?>
				</label>
				<span class="edd-description"><?php _e( 'Enter a discount code if you have one.', 'easy-digital-downloads' ); ?></span>
				<span class="edd-discount-code-field-wrap">
					<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php _e( 'Enter discount', 'easy-digital-downloads' ); ?>"/>
					<input type="submit" class="edd-apply-discount edd-submit <?php echo sanitize_html_class( $color ); ?> <?php echo sanitize_html_class( $style ); ?>" value="<?php echo _x( 'Apply', 'Apply discount at checkout', 'easy-digital-downloads' ); ?>"/>
				</span>
				<span class="edd-discount-loader edd-loading" id="edd-discount-loader" style="display:none;"></span>
				<span id="edd-discount-error-wrap" class="edd_error edd-alert edd-alert-error" aria-hidden="true" style="display:none;"></span>
			</p>
		</fieldset><?php
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
				<input name="edd_agree_to_terms" class="required" type="checkbox" id="edd_agree_to_terms" value="1" required/>
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
					<input name="edd_agree_to_privacy_policy" class="required" type="checkbox" id="edd-agree-to-privacy-policy" value="1" required/>
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
 * Shows the final purchase total at the bottom of the checkout page.
 *
 * @since 1.5
 */
function edd_checkout_final_total() {
?>
<p id="edd_final_total_wrap">
	<strong><?php _e( 'Purchase Total:', 'easy-digital-downloads' ); ?></strong>
	<span class="edd_cart_amount" data-subtotal="<?php echo esc_attr( edd_get_cart_subtotal() ); ?>" data-total="<?php echo esc_attr( edd_get_cart_total() ); ?>"><?php edd_cart_total(); // Escaped ?></span>
</p>
<?php
}
add_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );

/**
 * Renders the Checkout Submit section.
 *
 * @since 1.3.3
 */
function edd_checkout_submit() {
?>
	<fieldset id="edd_purchase_submit">
		<?php do_action( 'edd_purchase_form_before_submit' ); ?>

		<?php edd_checkout_hidden_fields(); ?>

		<?php echo edd_checkout_button_purchase(); ?>

		<?php do_action( 'edd_purchase_form_after_submit' ); ?>

		<?php if ( edd_is_ajax_disabled() ) : ?>
			<p class="edd-cancel"><a href="<?php echo esc_url( edd_get_checkout_uri() ); ?>"><?php esc_html_e( 'Go back', 'easy-digital-downloads' ); ?></a></p>
		<?php endif; ?>
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
	$color         = edd_get_button_color_class();
	$style         = edd_get_option( 'button_style', 'button' );
	$purchase_page = edd_get_option( 'purchase_page', '0' );

	ob_start(); ?>
	<input type="hidden" name="edd_action" value="gateway_select" />
	<input type="hidden" name="page_id" value="<?php echo absint( $purchase_page ); ?>"/>
	<input type="submit" name="gateway_submit" id="edd_next_button" class="edd-submit <?php echo sanitize_html_class( $color ); ?> <?php echo sanitize_html_class( $style ); ?>" value="<?php esc_html_e( 'Next', 'easy-digital-downloads' ); ?>"/>

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
		$color = edd_get_button_color_class();
		$style = edd_get_option( 'button_style', 'button' );
		$label = edd_get_checkout_button_purchase_label();
		$class = implode( ' ', array_filter( array( 'edd-submit', $color, $style ) ) );

		?>
		<input type="submit" class="<?php echo esc_attr( $class ); ?>" id="edd-purchase-button" name="edd-purchase" value="<?php echo esc_html( $label ); ?>"/>
		<?php
	}

	return apply_filters( 'edd_checkout_button_purchase', ob_get_clean() );
}

/**
 * Retrieves the label for the purchase button.
 *
 * @since 2.7.6
 *
 * @return string Purchase button label.
 */
function edd_get_checkout_button_purchase_label() {
	if ( edd_get_cart_total() ) {
		$label             = edd_get_option( 'checkout_label', '' );
		$complete_purchase = ! empty( $label )
			? $label
			: __( 'Purchase', 'easy-digital-downloads' );
	} else {
		$label             = edd_get_option( 'free_checkout_label', '' );
		$complete_purchase = ! empty( $label )
			? $label
			: __( 'Free Download', 'easy-digital-downloads' );
	}

	return apply_filters( 'edd_get_checkout_button_purchase_label', $complete_purchase, $label );
}

/**
 * Renders the hidden Checkout fields
 *
 * @since 1.3.2
 */
function edd_checkout_hidden_fields() {
	if ( is_user_logged_in() ) : ?>
		<input type="hidden" name="edd-user-id" value="<?php echo esc_attr( get_current_user_id() ); ?>"/>
	<?php endif; ?>
	<input type="hidden" name="edd_action" value="purchase"/>
	<input type="hidden" name="edd-gateway" value="<?php echo esc_attr( edd_get_chosen_gateway() ); ?>" />
	<?php wp_nonce_field( 'edd-process-checkout', 'edd-process-checkout-nonce', false, true );
}

/**
 * Applies filters to the success page content.
 *
 * @since 1.0
 *
 * @param string $content Content before filters.
 * @return string $content Filtered content.
 */
function edd_filter_success_page_content( $content ) {
	if ( isset( $_GET['payment-confirmation'] ) && edd_is_success_page() ) {

		$confirm = sanitize_text_field( $_GET['payment-confirmation'] );

		if ( has_filter( 'edd_payment_confirm_' . $confirm ) ) {
			$content = apply_filters( 'edd_payment_confirm_' . $confirm, $content );
		}
	}

	return $content;
}
add_filter( 'the_content', 'edd_filter_success_page_content', 99999 );

/**
 * Show a download's files in the purchase receipt.
 *
 * @since 1.8.6
 *
 * @param  int                          $item_id      Download ID.
 * @param  array                        $receipt_args Args specified in the [edd_receipt] shortcode.
 * @param  \EDD\Orders\Order_Item|array $order_item   Order item object or cart item array.
 *
 * @return bool True if files should be shown, false otherwise.
 */
function edd_receipt_show_download_files( $item_id, $receipt_args, $order_item = array() ) {
	$ret = true;

	/*
	 * If re-download is disabled, set return to false.
	 *
	 * When the purchase session is still present AND the receipt being shown is for that purchase,
	 * file download links are still shown. Once session expires, links are disabled.
	 */
	if ( edd_no_redownload() ) {
		$key = isset( $_GET['payment_key'] )
			? sanitize_text_field( $_GET['payment_key'] )
			: '';

		$session = edd_get_purchase_session();

		// We have session data but the payment key provided is not for this session.
		if ( ! empty( $key ) && ! empty( $session ) && $key != $session['purchase_key'] ) {
			$ret = false;

		// No session data is present but a key has been provided.
		} elseif ( empty( $session ) ) {
			$ret = false;
		}
	}

	if ( has_filter( 'edd_receipt_show_download_files' ) ) {
		$item = $order_item;
		if ( ! empty( $order_item->order_id ) ) {
			$order = edd_get_order_by( 'id', $order_item->order_id );
			$cart  = edd_get_payment_meta_cart_details( $order->id, true );
			$item  = $cart[ $item->cart_index ];
		}
		$ret = apply_filters( 'edd_receipt_show_download_files', $ret, $item_id, $receipt_args, $item );
	}

	// If the $order_item is an array, get the order item object instead.
	if ( is_array( $order_item ) && ! empty( $order_item['order_item_id'] ) ) {
		$order_item = edd_get_order_item( $order_item['order_item_id'] );
	}

	/**
	 * Modifies whether the receipt should show download files.
	 *
	 * @since 3.0
	 * @param bool                   $ret          True if the download files should be shown.
	 * @param int                    $item_id      The download ID.
	 * @param array                  $receipt_args Args specified in the [edd_receipt] shortcode.
	 * @param \EDD\Orders\Order_Item $item        The order item object.
	 */
	return apply_filters( 'edd_order_receipt_show_download_files', $ret, $item_id, $receipt_args, $order_item );
}
