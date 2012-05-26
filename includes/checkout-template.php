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


/**
 * Get Checkout Form
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_checkout_form() {

	global $edd_options, $user_ID, $post;
	
	if (is_singular()) :
		$page_URL =  get_permalink($post->ID);
	else :
		$page_URL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
		$page_URL .= "://";
		if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else $page_URL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	endif;	
	
	if(is_user_logged_in()) :
		global $user_ID;
		$user_data = get_userdata($user_ID);
	endif;
	
	ob_start(); ?>
		
		<?php if(edd_get_cart_contents()) : ?>
				
			<?php 
			do_action('edd_before_checkout_cart');
			edd_checkout_cart(); 
			do_action('edd_after_checkout_cart');
			?>
			
			<div id="edd_checkout_form_wrap" class="edd_clearfix">
			
				<?php 				
				do_action('edd_checkout_form_top');
			
				$gateways = edd_get_enabled_payment_gateways();
				$show_gateways = false;
				if(count($gateways) > 1 && !isset($_GET['payment-mode'])) {
					$show_gateways = true;
					if(edd_get_cart_amount() <= 0) {
						$show_gateways = false;
					}
				}
				if($show_gateways) { ?>
					<?php do_action('edd_payment_mode_top'); ?>
					<form id="edd_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
						<fieldset id="edd_payment_mode_select">
							<?php do_action('edd_payment_mode_before_gateways'); ?>
							<p>
								<?php								
									echo '<select class="edd-select" name="payment-mode" id="edd-gateway">';
										foreach($gateways as $gateway_id => $gateway) :
											echo '<option value="' . $gateway_id . '">' . $gateway['checkout_label'] . '</option>';
										endforeach;
									echo '</select>';
									echo '<label for="edd-gateway">' . __('Choose Your Payment Method', 'edd') . '</label>';
								?>
							</p>
							<?php do_action('edd_payment_mode_after_gateways'); ?>
						</fieldset>
						<fieldset id="edd_payment_mode_submit">
							<p>
								<?php $color = isset($edd_options['checkout_color']) ? $edd_options['checkout_color'] : 'gray'; ?> 
								<span class="edd_button edd_<?php echo $color; ?>">
									<span class="edd_button_outer">
										<span class="edd_button_inner">
											<input type="submit" id="edd_next_button" class="edd_button_text edd-submit" value="<?php _e('Next', 'edd'); ?>"/>
										</span>
									</span>
								</span>
							</p>
						</fieldset>
					</form>
					<?php do_action('edd_payment_mode_bottom'); ?>
			
				<?php } else { ?>
		
					<?php					
						foreach($gateways as $gateway_id => $gateway) :
							$enabled_gateway = $gateway_id;
							if(edd_get_cart_amount() <= 0) {
								$enabled_gateway = 'manual'; // this allows a free download by filling in the info
							}
						endforeach;
						$payment_mode = isset($_GET['payment-mode']) ? urldecode($_GET['payment-mode']) : $enabled_gateway;	
					?>
					
					<?php do_action('edd_before_purchase_form'); ?>
					<form id="edd_purchase_form" action="<?php echo $page_URL; ?>" method="POST">					
					
						<?php do_action('edd_purchase_form_top'); ?>
					
						<?php 
						if(isset($edd_options['logged_in_only']) && !isset($edd_options['show_register_form'])) {
							if(is_user_logged_in()) {
								$can_checkout = true;
							} else {
								$can_checkout = false;
							}
						} elseif(isset($edd_options['show_register_form']) && isset($edd_options['logged_in_only'])) {
							$can_checkout = true;
						} elseif(!isset($edd_options['logged_in_only'])) {
							$can_checkout = true;
						}
						if($can_checkout) { ?>
							<?php if(isset($edd_options['show_register_form']) && !is_user_logged_in() && !isset($_GET['login'])) { ?>
								<div id="edd_checkout_login_register"><?php echo edd_get_register_fields(); ?></div>
							<?php } elseif(isset($edd_options['show_register_form']) && !is_user_logged_in() && isset($_GET['login'])) { ?>
								<div id="edd_checkout_login_register"><?php echo edd_get_login_fields(); ?></div>
							<?php } ?>
							<?php if( (!isset($_GET['login']) && is_user_logged_in()) || !isset($edd_options['show_register_form'])) { ?>											
							<fieldset id="edd_checkout_user_info">
								<p>
									<input class="edd-input required" type="email" name="edd_email" placeholder="<?php _e('Email address', 'edd'); ?>" id="edd-email" value="<?php echo is_user_logged_in() ? $user_data->user_email : ''; ?>"/>
									<label class="edd-label" for="edd-email"><?php _e('Email Address', 'edd'); ?></label>
								</p>
								<p>
									<input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e('First Name', 'edd'); ?>" id="edd-first" value="<?php echo is_user_logged_in() ? $user_data->first_name : ''; ?>"/>
									<label class="edd-label" for="edd-first"><?php _e('First Name', 'edd'); ?></label>
								</p>
								<p>
									<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="<?php _e('Last name', 'edd'); ?>" value="<?php echo is_user_logged_in() ? $user_data->last_name : ''; ?>"/>
									<label class="edd-label" for="edd-last"><?php _e('Last Name', 'edd'); ?></label>
								</p>	
								<?php do_action('edd_purchase_form_user_info'); ?>
							</fieldset>				
							<?php } ?>
							<?php if(edd_has_active_discounts()) { // only show if we have at least one active discount ?>
							<fieldset id="edd_discount_code">
								<p>
									<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php _e('Enter discount', 'edd'); ?>"/>
									<label class="edd-label" for="edd-discount">
										<?php _e('Discount', 'edd'); ?>
										<?php if(edd_is_ajax_enabled()) { ?>
											- <a href="#" class="edd-apply-discount"><?php _e('Apply Discount', 'edd'); ?></a>
										<?php } ?>
									</label>
								</p>
							</fieldset>	
							<?php } ?>
							<?php 
								// load the credit card form and allow gateways to load their own if they wish
								if(has_action('edd_' . $payment_mode . '_cc_form')) {
									do_action('edd_' . $payment_mode . '_cc_form'); 
								} else {
									do_action('edd_cc_form');
								}
							?>			
							
							<?php if(isset($edd_options['show_agree_to_terms'])) { ?>
								<fieldset id="edd_terms_agreement">
									<p>
										<div id="edd_terms" style="display:none;">
											<?php 
												do_action('edd_before_terms');
												echo wpautop($edd_options['agree_text']); 
												do_action('edd_after_terms');
											?>
										</div>
										<div id="edd_show_terms">
											<a href="#" class="edd_terms_links"><?php _e('Show Terms', 'edd'); ?></a>
											<a href="#" class="edd_terms_links" style="display:none;"><?php _e('Hide Terms', 'edd'); ?></a>
										</div>
										<input name="edd_agree_to_terms" class="required" type="checkbox" id="edd_agree_to_terms" value="1"/>
										<label for="edd_agree_to_terms"><?php echo isset($edd_options['agree_label']) ? $edd_options['agree_label'] : __('Agree to Terms?', 'edd'); ?></label>
									</p>
								</fieldset>
							<?php } ?>	
							<fieldset id="edd_purchase_submit">
								<p>
									<?php do_action('edd_purchase_form_before_submit'); ?>
									<?php if(is_user_logged_in()) { ?>
									<input type="hidden" name="edd-user-id" value="<?php echo $user_data->ID; ?>"/>
									<?php } ?>
									<input type="hidden" name="edd_action" value="purchase"/>
									<input type="hidden" name="edd-gateway" value="<?php echo $payment_mode; ?>" />
									<input type="hidden" name="edd-nonce" value="<?php echo wp_create_nonce('edd-purchase-nonce'); ?>"/>
									<?php $color = isset($edd_options['checkout_color']) ? $edd_options['checkout_color'] : 'gray'; ?>
									<span class="edd_button edd_<?php echo $color; ?>">
										<span class="edd_button_outer">
											<span class="edd_button_inner">
												<input type="submit" class="edd_button_text edd-submit" id="edd-purchase-button" name="edd-purchase" value="<?php _e('Purchase', 'edd'); ?>"/>
											</span>
										</span>
									</span>
									<?php do_action('edd_purchase_form_after_submit'); ?>
								</p>
								<?php if(!edd_is_ajax_enabled()) { ?>
									<p class="edd-cancel"><a href="javascript:history.go(-1)"><?php _e('Go back', 'edd'); ?></a></p>
								<?php } ?>				
							</fieldset>
						<?php } else { ?>
							<p><?php _e('You must be logged in to complete your purchase', 'edd'); ?></p>
						<?php } ?>
						<?php do_action('edd_purchase_form_bottom'); ?>
					</form>
					<?php do_action('edd_after_purchase_form'); ?>
			<?php } ?>
		</div><!--end #edd_checkout_form_wrap-->
		<?php
		else:
			do_action('edd_empty_cart');
		endif;
	return ob_get_clean();
}


/**
 * Get CC Form
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_get_cc_form() {
	ob_start(); ?>
	
	<?php do_action('edd_before_cc_fields'); ?>
	<fieldset id="edd_cc_fields">
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
		<?php do_action('edd_before_cc_expiration'); ?>
		<p class="card-expiration">
			<input type="text" size="2" name="card_exp_month"  placeholder="<?php _e('Month', 'edd'); ?>" class="card-expiry-month edd-input required"/>
			<span class="exp-divider"> / </span>
			<input type="text" size="4" name="card_exp_year" placeholder="<?php _e('Year', 'edd'); ?>" class="card-expiry-year edd-input required"/>
			<label class="edd-label"><?php _e('Expiration (MM/YYYY)', 'edd'); ?></label>
		</p>
		<?php do_action('edd_after_cc_expiration'); ?>
	</fieldset>

	<?php do_action('edd_cc_form_address_fields'); ?>

	<?php do_action('edd_after_cc_fields'); ?>
		
	<?php
	echo ob_get_clean();
}
add_action('edd_cc_form', 'edd_get_cc_form');


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
		<legend><?php _e('Credit Card Info', 'edd'); ?></legend>
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
			<input type="text" size="6" name="card_state" class="card-state edd-input required" placeholder="<?php _e('State / Province', 'edd'); ?>"/>
			<label class="edd-label"><?php _e('Billing State / Province', 'edd'); ?></label>
		</p>
		<p>
			<input type="text" size="4" name="card_zip" class="card-zip edd-input required" placeholder="<?php _e('Zip / Postal code', 'edd'); ?>"/>
			<label class="edd-label"><?php _e('Billing Zip / Postal Code', 'edd'); ?></label>
		</p>
	</fieldset>
	<?php
	echo ob_get_clean();
}
add_action('edd_cc_form_address_fields', 'edd_default_cc_address_fields');


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
	$user_data = get_userdata($user_ID);	

	ob_start(); ?>
	<fieldset id="edd_register_fields">
		<fieldset id="edd_register_account_fields">		
			<legend><?php _e('Create an account', 'edd'); if(!edd_no_guest_checkout()) { echo ' ' . __('(optional)', 'edd'); } ?></legend>
			<p>
				<input name="edd_user_login" id="edd_user_login" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" placeholder="<?php _e('Username', 'edd'); ?>" title="<?php _e('Username', 'edd'); ?>"/>
				<label for="edd_user_Login"><?php _e('Username', 'edd'); ?></label>
			</p>
			<p>
				<input name="edd_user_pass" id="edd_user_pass" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e('Password', 'edd'); ?>" type="password"/>
				<label for="password"><?php _e('Password', 'edd'); ?></label>
			</p>
			<p class="edd_register_password">
				<input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" placeholder="<?php _e('Confirm password', 'edd'); ?>" type="password"/>
				<label for="password_again"><?php _e('Password Again', 'edd'); ?></label>
			</p>
		</fieldset>
		<p><?php _e('Already have an account?', 'edd'); ?> <a href="<?php echo add_query_arg('login', 1); ?>" class="edd_checkout_register_login" data-action="checkout_login"><?php _e('Login', 'edd'); ?></a></p>
		<p>
			<input name="edd_email" id="edd_email" class="required edd-input" type="email" placeholder="<?php _e('Email', 'edd'); ?>" title="<?php _e('Email', 'edd'); ?>"/>
			<label for="edd_email"><?php _e('Email', 'edd'); ?></label>
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
		<?php do_action('edd_purchase_form_register_fields'); ?>
		<?php do_action('edd_purchase_form_user_info');	?>				
	</fieldset>
	<?php
	return ob_get_clean();
}


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
			<p>
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="text" name="edd-username" id="edd-username" value="" placeholder="<?php _e('Your username', 'edd'); ?>"/>
				<label class="edd-label" for="edd-username"><?php _e('Username', 'edd'); ?></label>
			</p>
			<p class="edd_login_password">
				<input class="<?php if(edd_no_guest_checkout()) { echo 'required '; } ?>edd-input" type="password" name="edd-password" id="edd-password" placeholder="<?php _e('Your password', 'edd'); ?>"/>
				<label class="edd-label" for="edd-password"><?php _e('Password', 'edd'); ?></label>
				<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
			</p>
			<?php do_action('edd_purchase_form_login_fields'); ?>		
		</fieldset><!--end #edd_login_fields-->
		<p>
			<?php _e('Need to create an account?', 'edd'); ?> 
			<a href="<?php echo remove_query_arg('login'); ?>" class="edd_checkout_register_login" data-action="checkout_register">
				<?php _e('Register', 'edd'); if(!edd_no_guest_checkout()) { echo ' ' . __('or checkout as a guest.', 'edd'); } ?>
			</a>
		</p>	
	<?php
	return ob_get_clean();
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
	if(isset($edd_options['accepted_cards'])) {
		foreach($edd_options['accepted_cards'] as $key => $card) {
			echo '<img class="payment-icon" src="' . EDD_PLUGIN_URL . 'includes/images/icons/' . strtolower(str_replace(' ', '', $card)) . '.png"/>';
		}
	}
}
add_action('edd_payment_mode_top', 'edd_show_payment_icons');
add_action('edd_before_purchase_form', 'edd_show_payment_icons');


/**
 * Agree To Terms JS
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_agree_to_terms_js() {
	global $edd_options;
	if(isset($edd_options['show_agree_to_terms'])) { ?>
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
add_action('edd_checkout_form_top', 'edd_agree_to_terms_js');