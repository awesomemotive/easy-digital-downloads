<?php
/**
 * Checkout Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Checkout Template
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Checkout Form
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_checkout_form() {

	global $edd_options, $user_ID, $post;

	ob_start(); ?>

		<?php if( edd_get_cart_contents() ) : ?>

			<?php edd_checkout_cart(); ?>

			<div id="edd_checkout_form_wrap" class="edd_clearfix">

				<?php
				do_action( 'edd_checkout_form_top' );

				if( edd_show_gateways() ) {
					do_action( 'edd_payment_payment_mode_select'  );
				} else {

					do_action( 'edd_before_purchase_form' );

					do_action( 'edd_purchase_form' );

					do_action( 'edd_after_purchase_form' );
				}
				do_action( 'edd_checkout_form_bottom' ); ?>
			</div><!--end #edd_checkout_form_wrap-->
		<?php
		else:
			do_action( 'edd_empty_cart' );
		endif;
	return ob_get_clean();
}



/**
 * Determines if a user can checkout or not
 *
 * @access      private
 * @since       1.3.3
 * @return      bool
*/

function edd_can_checkout() {

	global $edd_options;

	$can_checkout = true; // always true for now

	return (bool) apply_filters( 'edd_can_checkout', $can_checkout );

}



/**
 * Shows the User Info Fields
 *
 * @access      private
 * @since       1.3.3
 * @return      void
*/

function edd_user_info_fields() {
	if( is_user_logged_in() ) :
		$user_data = get_userdata( get_current_user_id() );
	endif;
	?>
	<fieldset id="edd_checkout_user_info">
		<legend><?php echo apply_filters( 'edd_checkout_personal_info_text', __('Personal Info', 'edd') ); ?></legend>
		<?php do_action( 'edd_purchase_form_before_email' ); ?>
		<p id="edd-email-wrap">
			<input class="edd-input required" type="email" name="edd_email" placeholder="<?php _e('Email address', 'edd'); ?>" id="edd-email" value="<?php echo is_user_logged_in() ? $user_data->user_email : ''; ?>"/>
			<label class="edd-label" for="edd-email"><?php _e('Email Address', 'edd'); ?></label>
		</p>
		<?php do_action( 'edd_purchase_form_after_email' ); ?>
		<p id="edd-first-name-wrap">
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e('First Name', 'edd'); ?>" id="edd-first" value="<?php echo is_user_logged_in() ? $user_data->first_name : ''; ?>"/>
			<label class="edd-label" for="edd-first"><?php _e('First Name', 'edd'); ?></label>
		</p>
		<p id="edd-last-name-wrap">
			<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="<?php _e('Last name', 'edd'); ?>" value="<?php echo is_user_logged_in() ? $user_data->last_name : ''; ?>"/>
			<label class="edd-label" for="edd-last"><?php _e('Last Name', 'edd'); ?></label>
		</p>
		<?php do_action( 'edd_purchase_form_user_info' ); ?>
	</fieldset>
	<?php
}
add_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );


/**
 * Get CC Form
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_get_cc_form() {
	ob_start(); ?>

	<?php do_action( 'edd_before_cc_fields' ); ?>

	<fieldset id="edd_cc_fields">
		<legend><?php _e('Credit Card Info', 'edd'); ?></legend>
		<p>
			<input type="text" autocomplete="off" name="card_name" class="card-name edd-input required" placeholder="<?php _e('Card name', 'edd'); ?>" />
			<label class="edd-label"><?php _e('Name on the Card', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" autocomplete="off" name="card_number" class="card-number edd-input required" placeholder="<?php _e('Card number', 'edd'); ?>" />
			<label class="edd-label"><?php _e('Card Number', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" size="4" autocomplete="off" name="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e('Security code', 'edd'); ?>" />
			<label class="edd-label"><?php _e('CVC', 'edd'); ?></label>
		</p>

		<?php do_action( 'edd_before_cc_expiration' ); ?>

		<p class="card-expiration">
			<input type="text" size="2" name="card_exp_month"  placeholder="<?php _e('Month', 'edd'); ?>" class="card-expiry-month edd-input required"/>
			<span class="exp-divider"> / </span>
			<input type="text" size="4" name="card_exp_year" placeholder="<?php _e('Year', 'edd'); ?>" class="card-expiry-year edd-input required"/>
			<label class="edd-label"><?php _e('Expiration (MM/YYYY)', 'edd'); ?></label>
		</p>

		<?php do_action( 'edd_after_cc_expiration' ); ?>

	</fieldset>

	<?php do_action( 'edd_after_cc_fields' ); ?>

	<?php
	echo ob_get_clean();
}
add_action( 'edd_cc_form', 'edd_get_cc_form' );


/**
 * Default CC Address fields
 *
 * Outputs the default credit card address fields
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_default_cc_address_fields() {
	ob_start(); ?>
	<fieldset id="edd_cc_address" class="cc-address">
		<?php do_action( 'edd_cc_billing_top' ); ?>
		<p>
			<input type="text" name="card_address" class="card-address edd-input required" placeholder="<?php _e('Address line 1', 'edd'); ?>"/>
			<label class="edd-label"><?php _e('Billing Address', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" name="card_address_2" class="card-address-2 edd-input required" placeholder="<?php _e('Address line 2', 'edd'); ?>"/>
			<label class="edd-label"><?php _e('Billing Address Line 2', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" name="card_city" class="card-city edd-input required" placeholder="<?php _e('City', 'edd'); ?>"/>
			<label class="edd-label"><?php _e('Billing City', 'edd'); ?></label>
		</p>
		<p>
			<select name="billing_country" class="billing-country edd-select required">
				<?php
				$countries = edd_get_country_list();
				foreach( $countries as $country_code => $country ) {
				  echo '<option value="' . $country_code . '">' . $country . '</option>';
				}
				?>
			</select>
			<label class="edd-label"><?php _e('Billing Country', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" size="6" name="card_state_other" id="card_state_other" class="card-state edd-input" placeholder="<?php _e('State / Province', 'edd'); ?>" style="display:none;"/>
            <select name="card_state_us" id="card_state_us" class="card-state edd-select required">
                <?php
                    $states = edd_get_states_list();
                    foreach( $states as $state_code => $state ) {
                        echo '<option value="' . $state_code . '">' . $state . '</option>';
                    }
                ?>
            </select>
            <select name="card_state_ca" id="card_state_ca" class="card-state edd-select required" style="display: none;">
                <?php
                    $provinces = edd_get_provinces_list();
                    foreach( $provinces as $province_code => $province ) {
                        echo '<option value="' . $province_code . '">' . $province . '</option>';
                    }
                ?>
            </select>
			<label class="edd-label"><?php _e('Billing State / Province', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" size="4" name="card_zip" class="card-zip edd-input required" placeholder="<?php _e('Zip / Postal code', 'edd'); ?>"/>
			<label class="edd-label"><?php _e('Billing Zip / Postal Code', 'edd'); ?></label>
		</p>
		<?php do_action( 'edd_cc_billing_bottom' ); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}
add_action('edd_after_cc_fields', 'edd_default_cc_address_fields');


/**
 * Get Register Fields
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_get_register_fields() {
	global $edd_options;
	global $user_ID;

	if ( is_user_logged_in() )
	$user_data = get_userdata( $user_ID );

	ob_start(); ?>
	<fieldset id="edd_register_fields">
		<p><?php _e('Already have an account?', 'edd'); ?> <a href="<?php echo add_query_arg('login', 1); ?>" class="edd_checkout_register_login" data-action="checkout_login"><?php _e('Login', 'edd'); ?></a></p>
		<fieldset id="edd_register_account_fields">
			<legend><?php _e('Create an account', 'edd'); if( !edd_no_guest_checkout() ) { echo ' ' . __('(optional)', 'edd'); } ?></legend>
			<?php do_action('edd_register_account_fields_before'); ?>
			<p>
				<input name="edd_user_login" id="edd_user_login" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" placeholder="<?php _e('Username', 'edd'); ?>" title="<?php _e('Username', 'edd'); ?>"/>
				<label for="edd_user_login"><?php _e('Username', 'edd'); ?></label>
			</p>
			<p>
				<input name="edd_user_pass" id="edd_user_pass" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e('Password', 'edd'); ?>" type="password"/>
				<label for="password"><?php _e('Password', 'edd'); ?></label>
			</p>
			<p class="edd_register_password">
				<input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e('Confirm password', 'edd'); ?>" type="password"/>
				<label for="password_again"><?php _e('Password Again', 'edd'); ?></label>
			</p>
			<?php do_action( 'edd_register_account_fields_after' ); ?>
		</fieldset>
		<p>
			<input name="edd_email" id="edd-email" class="required edd-input" type="email" placeholder="<?php _e('Email', 'edd'); ?>" title="<?php _e('Email', 'edd'); ?>"/>
			<label for="edd-email"><?php _e('Email', 'edd'); ?></label>
		</p>
		<p>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e('First Name', 'edd'); ?>" id="edd-first" value="<?php echo is_user_logged_in() ? $user_data->user_firstname : ''; ?>"/>
			<label class="edd-label" for="edd-first"><?php _e('First Name', 'edd'); ?></label>
		</p>
		<p>
			<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="<?php _e('Last name', 'edd'); ?>" value="<?php echo is_user_logged_in() ? $user_data->user_lastname : ''; ?>"/>
			<label class="edd-label" for="edd-last"><?php _e('Last Name', 'edd'); ?></label>
		</p>
		<input type="hidden" name="edd-purchase-var" value="needs-to-register"/>
		<?php do_action( 'edd_purchase_form_user_info' ); ?>
	</fieldset>
	<?php

	$fields = ob_get_clean();

	echo $fields;

}
add_action( 'edd_purchase_form_register_fields', 'edd_get_register_fields' );


/**
 * Get Login Fields
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_get_login_fields() {
	ob_start(); ?>
		<fieldset id="edd_login_fields">
			<legend><?php _e('Login to your account', 'edd'); ?></legend>
			<?php do_action('edd_checkout_login_fields_before'); ?>
			<p>
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="<?php _e('Your username', 'edd'); ?>"/>
				<label class="edd-label" for="edd-username"><?php _e('Username', 'edd'); ?></label>
			</p>
			<p class="edd_login_password">
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="<?php _e('Your password', 'edd'); ?>"/>
				<label class="edd-label" for="edd-password"><?php _e('Password', 'edd'); ?></label>
				<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
			</p>
			<?php do_action('edd_checkout_login_fields_after'); ?>
		</fieldset><!--end #edd_login_fields-->
		<p>
			<?php _e('Need to create an account?', 'edd'); ?>
			<a href="<?php echo remove_query_arg('login'); ?>" class="edd_checkout_register_login" data-action="checkout_register">
				<?php _e('Register', 'edd'); if(!edd_no_guest_checkout()) { echo ' ' . __('or checkout as a guest.', 'edd'); } ?>
			</a>
		</p>
	<?php

	$fields = ob_get_clean();

	echo $fields;
}
add_action( 'edd_purchase_form_login_fields', 'edd_get_login_fields' );


/**
 * The payment mode select form
 *
 * @access      public
 * @since       1.2.2
 * @return      void
*/

function edd_payment_mode_select() {
	$gateways = edd_get_enabled_payment_gateways();
	$page_URL = edd_get_current_page_url();
	do_action('edd_payment_mode_top'); ?>
	<form id="edd_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
		<fieldset id="edd_payment_mode_select">
			<?php do_action('edd_payment_mode_before_gateways'); ?>
			<p id="edd-payment-mode-wrap">
				<?php
					echo '<select class="edd-select" name="payment-mode" id="edd-gateway">';
						echo '<option value="0">' . __( 'Select payment method', 'edd' ) . '</option>';
						foreach($gateways as $gateway_id => $gateway) :
							echo '<option value="' . $gateway_id . '">' . $gateway['checkout_label'] . '</option>';
						endforeach;
					echo '</select>';
				?>
			</p>
			<?php do_action('edd_payment_mode_after_gateways'); ?>
		</fieldset>
		<fieldset id="edd_payment_mode_submit" class="edd-no-js">
			<p id="edd-next-submit-wrap">
				<?php echo edd_checkout_button_next(); ?>
			</p>
		</fieldset>
	</form>
	<div id="edd_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->
	<?php do_action('edd_payment_mode_bottom');
}
add_action( 'edd_payment_payment_mode_select', 'edd_payment_mode_select' );


/**
 * The discount field
 *
 * @access      public
 * @since       1.2.2
 * @return      void
*/

function edd_discount_field() {
	if(edd_has_active_discounts()) { // only show if we have at least one active discount ?>
		<fieldset id="edd_discount_code">
			<p id="edd-discount-code-wrap">
				<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php _e('Enter discount', 'edd'); ?>"/>
				<label class="edd-label" for="edd-discount">
					<?php _e('Discount', 'edd'); ?>
					<?php if(edd_is_ajax_enabled()) { ?>
						- <a href="#" class="edd-apply-discount"><?php _e('Apply Discount', 'edd'); ?></a>
					<?php } ?>
				</label>
			</p>
		</fieldset>
	<?php
	}
}
add_action( 'edd_purchase_form_before_cc_form', 'edd_discount_field' );


/**
 * The checkout Agree to Terms section
 *
 * @access      public
 * @since       1.3.2
 * @return      void
 */

function edd_terms_agreement() {
	global $edd_options;
	if( isset( $edd_options['show_agree_to_terms'] ) ) {
?>
		<fieldset id="edd_terms_agreement">
			<p>
				<div id="edd_terms" style="display:none;">
					<?php
						do_action( 'edd_before_terms' );
						echo wpautop( $edd_options['agree_text'] );
						do_action( 'edd_after_terms' );
					?>
				</div>
				<div id="edd_show_terms">
					<a href="#" class="edd_terms_links"><?php _e('Show Terms', 'edd'); ?></a>
					<a href="#" class="edd_terms_links" style="display:none;"><?php _e('Hide Terms', 'edd'); ?></a>
				</div>
				<input name="edd_agree_to_terms" class="required" type="checkbox" id="edd_agree_to_terms" value="1"/>
				<label for="edd_agree_to_terms"><?php echo isset( $edd_options['agree_label'] ) ? $edd_options['agree_label'] : __('Agree to Terms?', 'edd'); ?></label>
			</p>
		</fieldset>
<?php
	}
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_terms_agreement' );


function edd_show_local_tax_opt_in() {
	global $edd_options;
	if( isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local' ) {
?>
		<fieldset id="edd_tax_opt_in_fields">
			<p>
				<input name="edd_tax_opt_in" type="checkbox" id="edd_tax_opt_in" value="1"/>
				<label for="edd_tax_opt_in"><?php echo isset( $edd_options['tax_location'] ) ? $edd_options['tax_location'] : __('Opt Into Taxes?', 'edd'); ?></label>
			</p>
		</fieldset>
<?php
	}
}
add_action( 'edd_purchase_form_before_submit', 'edd_show_local_tax_opt_in' );


/**
 * The checkout submit section
 *
 * @access      public
 * @since       1.3.3
 * @return      void
 */
function edd_checkout_submit() {
?>
	<fieldset id="edd_purchase_submit">
		<p>
			<?php do_action( 'edd_purchase_form_before_submit' ); ?>

			<?php edd_checkout_hidden_fields(); ?>

			<?php echo edd_checkout_button_purchase(); ?>

			<?php do_action( 'edd_purchase_form_after_submit' ); ?>
		</p>

		<?php if( ! edd_is_ajax_enabled() ) { ?>
			<p class="edd-cancel"><a href="javascript:history.go(-1)"><?php _e( 'Go back', 'edd' ); ?></a></p>
		<?php } ?>

	</fieldset>
<?php
}
add_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_submit', 100 );


/**
 * The checkout Next button
 *
 * @access      public
 * @since       1.2
 * @return      string
 */
function edd_checkout_button_next() {
	global $edd_options;

	$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
	$style = isset( $edd_options[ 'button_style' ] ) ? $edd_options[ 'button_style' ] : 'button';

	ob_start();
?>
	<input type="submit" id="edd_next_button" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" value="<?php _e('Next', 'edd'); ?>"/>
<?php
	return apply_filters( 'edd_checkout_button_next', ob_get_clean() );
}


/**
 * The checkout Purchase button
 *
 * @access      public
 * @since       1.2
 * @return      string
 */
function edd_checkout_button_purchase() {
	global $edd_options;

	$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
	$style = isset( $edd_options[ 'button_style' ] ) ? $edd_options[ 'button_style' ] : 'button';

	$complete_purchase = isset( $edd_options['checkout_label'] ) && strlen( trim( $edd_options['checkout_label'] ) ) > 0 ? $edd_options['checkout_label'] : __('Purchase', 'edd');
	ob_start();
?>
	<input type="submit" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" id="edd-purchase-button" name="edd-purchase" value="<?php echo $complete_purchase; ?>"/>
<?php
	return apply_filters( 'edd_checkout_button_purchase', ob_get_clean() );
}


/**
 * Show Payment Icons
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_show_payment_icons() {
	global $edd_options;

	if( isset( $edd_options['accepted_cards'] ) ) {
		echo '<div class="edd-payment-icons">';
		foreach( $edd_options['accepted_cards'] as $key => $card ) {
			if( edd_string_is_image_url( $key ) ) {
				echo '<img class="payment-icon" src="' . $key . '"/>';
			} else {
				echo '<img class="payment-icon" src="' . EDD_PLUGIN_URL . 'includes/images/icons/' . strtolower( str_replace( ' ', '', $card ) ) . '.png"/>';
			}
		}
		echo '</div>';
	}
}
add_action( 'edd_payment_mode_top', 'edd_show_payment_icons' );
add_action( 'edd_before_purchase_form', 'edd_show_payment_icons' );

function edd_show_purchase_form() { ?>
	<form id="edd_purchase_form" action="<?php echo esc_url( edd_get_current_page_url() ); ?>" method="POST">

		<?php

		do_action( 'edd_purchase_form_top' );

		if( edd_can_checkout() ) { ?>

			<?php if( isset( $edd_options['show_register_form'] ) && !is_user_logged_in() && !isset( $_GET['login'] ) ) { ?>
				<div id="edd_checkout_login_register"><?php do_action( 'edd_purchase_form_register_fields' ); ?></div>
			<?php } elseif( isset( $edd_options['show_register_form'] ) && !is_user_logged_in() && isset( $_GET['login'] ) ) { ?>
				<div id="edd_checkout_login_register"><?php do_action( 'edd_purchase_form_login_fields' ); ?></div>
			<?php } ?>

			<?php if( ( !isset( $_GET['login'] ) && is_user_logged_in() ) || !isset( $edd_options['show_register_form'] ) ) {

				do_action( 'edd_purchase_form_after_user_info' );
			}

			do_action( 'edd_purchase_form_before_cc_form' );

			$payment_mode = edd_get_chosen_gateway();

			// load the credit card form and allow gateways to load their own if they wish
			if( has_action( 'edd_' . $payment_mode . '_cc_form' ) ) {
				do_action( 'edd_' . $payment_mode . '_cc_form' );
			} else {
				do_action( 'edd_cc_form' );
			}

			do_action( 'edd_purchase_form_after_cc_form' );

		} else {
			// can't checkout
			do_action( 'edd_purchase_form_no_access' );
		}

		do_action( 'edd_purchase_form_bottom' ); ?>

	</form> <?php
}

add_action( 'edd_purchase_form', 'edd_show_purchase_form' );


/**
 * Agree To Terms JS
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_agree_to_terms_js() {
	global $edd_options;

	if( isset( $edd_options['show_agree_to_terms'] ) ) { ?>
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
 * Hidden checkout fields
 *
 * @access      private
 * @since       1.3.2 * @return      void
*/

function edd_checkout_hidden_fields() {

?>
	<?php if( is_user_logged_in() ) { ?>
	<input type="hidden" name="edd-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
	<input type="hidden" name="edd_action" value="purchase"/>
	<input type="hidden" name="edd-gateway" value="<?php echo edd_get_chosen_gateway(); ?>" />
	<input type="hidden" name="edd-nonce" value="<?php echo wp_create_nonce('edd-purchase-nonce'); ?>"/>
<?php

}