<?php

function edd_options_page() {

	global $edd_options;

	ob_start(); ?>
	<div class="wrap">
		
		<h2 class="nav-tab-wrapper">
			<?php _e('Easy Digital Downloads', 'edd'); ?>
			<a href="#pages" class="nav-tab nav-tab-active"><?php _e('Pages', 'edd'); ?></a>
			<a href="#gateways" class="nav-tab"><?php _e('Payment Gateways', 'edd'); ?></a>
			<a href="#misc" class="nav-tab"><?php _e('Misc', 'edd'); ?></a>
		</h2>
			
		<div id="tab_container">
			
			<form method="post" action="options.php">
	
				<?php settings_fields('edd_settings_group'); ?>
				<?php $pages = get_pages(); ?>
				
				<div class="tab_content" id="pages">	
					<h3><?php _e('Purchase Form', 'edd'); ?></h3>
	
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('Purchase Page', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Purchase Page', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[purchase_page]">
											<select id="edd_settings[purchase_page]" name="edd_settings[purchase_page]">
												<?php
												if($pages) :
													foreach ( $pages as $page ) {
													  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $edd_options['purchase_page'], false) . '>';
														$option .= $page->post_title;
														$option .= '</option>';
														echo $option;
													}
												else :
													echo '<option>' . __('No pages found', 'edd') . '</option>';
												endif;
												?>
											</select>
											<?php _e('Choose the page that holds the checkout form short code.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Success Page', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Success Page', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[success_page]">
											<select id="edd_settings[success_page]" name="edd_settings[success_page]">
												<?php
												if($pages) :
													foreach ( $pages as $page ) {
													  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $edd_options['success_page'], false) . '>';
														$option .= $page->post_title;
														$option .= '</option>';
														echo $option;
													}
												else :
													echo '<option>' . __('No pages found', 'edd') . '</option>';
												endif;
												?>
											</select>
											<?php _e('Choose the page users are directed to after a successful purchase.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!--end #purchase-form-->
				
				<div class="tab_content" style="display:none;" id="gateways">	
			
					<?php do_action('edd_gateway_settings_top', $edd_options); ?>
			
					<h3><?php _e('Test Mode', 'edd'); ?></h3>
	
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('Test Mode', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Test Mode', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[test_mode]">
											<input id="edd_settings[test_mode]" name="edd_settings[test_mode]" type="checkbox" value="1" <?php if(isset($edd_options['test_mode'])) checked(1, $edd_options['test_mode']); ?> />
											<?php _e('Check this to use the plugin in test mode.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
	
					<h3><?php _e('Payment Gateways', 'edd'); ?></h3>
	
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('Gateways', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Gateways', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[gateways]">
											<?php
												$gateways = edd_get_payment_gateways();
												foreach($gateways as $key => $gateway) :
													if(isset($edd_options['gateways'][$key])) { $enabled = '1'; } else { $enabled = NULL; }
													echo '<input name="edd_settings[gateways][' . $key . ']" id="edd_settings[gateways][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
													echo '<label for="edd_settings[gateways][' . $key . ']">' . $gateway . '</label><br/>';
												endforeach;
											?>
											<?php _e('Check each of the payment gateways you would like to enable. Configure the selected gateways below.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
	
					<h3><?php _e('PayPal Settings', 'edd'); ?></h3>
				
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('PayPal Email', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('PayPal Email', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[paypal_email]">
											<input id="edd_settings[paypal_email]" name="edd_settings[paypal_email]" type="text" class="regular-text" value="<?php echo $edd_options['paypal_email']; ?>"/>
											<?php _e('Enter your PayPal Email.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
	
					<?php do_action('edd_gateway_settings_bottom', $edd_options); ?>
					
					
				</div><!--end #settings-->
			
				<div class="tab_content" style="display:none;" id="misc">	
					<h3><?php _e('Miscellaneous', 'edd'); ?></h3>
	
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('Enable Ajax', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Enable Ajax', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[ajax_cart]">
											<input type="checkbox" id="edd_settings[ajax_cart]" name="edd_settings[ajax_cart]" value="1" <?php if(isset($edd_options['ajax_cart'])) checked(1, $edd_options['ajax_cart']); ?>/>
											<?php _e('Check this to enable AJAX for the shopping cart.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Logged-In Only', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Logged-In Only', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[logged_in_only]">
											<input type="checkbox" id="edd_settings[logged_in_only]" name="edd_settings[logged_in_only]" value="1" <?php if(isset($edd_options['logged_in_only'])) checked(1, $edd_options['logged_in_only']); ?>/>
											<?php _e('Require that users be logged-in to purchase files.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
					<h4><?php _e('Currency Settings', 'edd'); ?></h4>
				
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('Currency', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Currency', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[currency]">
											<select id="edd_settings[currency]" name="edd_settings[currency]">
												<?php 
												$currencies = array(
													'USD' => __('US Dollars (&#36;)', 'edd'),
													'EUR' => __('Euros (&euro;)', 'edd'),
													'GBP' => __('Pounds Sterling (&pound;)', 'edd'),
													'AUD' => __('Australian Dollars (&#36;)', 'edd'),
													'BRL' => __('Brazilian Real (&#36;)', 'edd'),
													'CAD' => __('Canadian Dollars (&#36;)', 'edd'),
													'CZK' => __('Czech Koruna', 'edd'),
													'DKK' => __('Danish Krone', 'edd'),
													'HKD' => __('Hong Kong Dollar (&#36;)', 'edd'),
													'HUF' => __('Hungarian Forint', 'edd'),
													'ILS' => __('Israeli Shekel', 'edd'),
													'JPY' => __('Japanese Yen (&yen;)', 'edd'),
													'MYR' => __('Malaysian Ringgits', 'edd'),
													'MXN' => __('Mexican Peso (&#36;)', 'edd'),
													'NZD' => __('New Zealand Dollar (&#36;)', 'edd'),
													'NOK' => __('Norwegian Krone', 'edd'),
													'PHP' => __('Philippine Pesos', 'edd'),
													'PLN' => __('Polish Zloty', 'edd'),
													'SGD' => __('Singapore Dollar (&#36;)', 'edd'),
													'SEK' => __('Swedish Krona', 'edd'),
													'CHF' => __('Swiss Franc', 'edd'),
													'TWD' => __('Taiwan New Dollars', 'edd'),
													'THB' => __('Thai Baht', 'edd')
												);
												foreach($currencies as $key => $currency) {
													echo '<option value="' . $key . '" ' . selected($key, $edd_options['currency'], false) . '>' . $currency . '</option>';
												}				
												?>
											</select>
											<?php _e('Choose your currency.', 'edd'); ?> <strong><?php _e('Note', 'edd'); ?></strong>: <?php _e('If you use Stripe, you MUST use USD.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php _e('Currency Position', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Currency Position', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[currency_position]">
											<select id="edd_settings[currency_position]" name="edd_settings[currency_position]">
												<option value="before" <?php selected('before', $edd_options['currency_position']); ?>><?php _e('Before - $10', 'edd'); ?></option>
												<option value="after" <?php selected('after', $edd_options['currency_position']); ?>><?php _e('After - 10$', 'edd'); ?></option>
											</select>
											<?php _e('Choose the location of the currency sign.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
	
					<h4><?php _e('Messages', 'edd'); ?></h4>
				
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<?php _e('Payment Confirmation', 'edd'); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span><?php _e('Payment Confirmation', 'edd'); ?></span>
										</legend>
										<label for="edd_settings[payment_confirmation]">
											<?php wp_editor($edd_options['payment_confirmation'], 'edd_settings[payment_confirmation]', array('textarea_name' => 'edd_settings[payment_confirmation]')); ?>
											<?php _e('Enter the message displayed after a user makes a successful purchase. HTML is accepted.', 'edd'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!--end #misc-->
			
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Options', 'edd'); ?>" />
				</p>
			</form>
		</div><!--end #tab_container-->
		
	</div>
	<?php
	echo ob_get_clean();
}

function edd_register_settings() {
	// creates our settings in the options table
	register_setting('edd_settings_group', 'edd_settings');
}
add_action('admin_init', 'edd_register_settings');