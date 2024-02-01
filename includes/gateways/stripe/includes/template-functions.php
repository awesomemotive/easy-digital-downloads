<?php
/**
 * Add an errors div
 *
 * @since       1.0
 * @return      void
 */
function edds_add_stripe_errors() {
	echo '<div id="edd-stripe-payment-errors"></div>';
}
add_action( 'edd_after_cc_fields', 'edds_add_stripe_errors', 999 );

/**
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to prevent them from getting posted to the server
 *
 * @since       1.7.5
 * @return      void
 */
function edds_credit_card_form( $echo = true ) {

	if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		edd_set_error( 'edd_stripe_error_limit', __( 'We are unable to process your payment at this time, please try again later or contact support.', 'easy-digital-downloads' ) );
		return;
	}

	ob_start(); ?>

	<?php if ( ! wp_script_is( 'edd-stripe-js' ) ) : ?>
		<?php edd_stripe_js( true ); ?>
	<?php endif; ?>

	<?php do_action( 'edd_before_cc_fields' ); ?>

	<fieldset id="edd_cc_fields" class="edd-do-validate">
		<?php $elements_mode = edds_get_elements_mode(); ?>
		<?php if ( 'card-elements' === $elements_mode ) : ?>
			<legend><?php esc_html_e( 'Credit Card Info', 'easy-digital-downloads' ); ?></legend>
		<?php else: ?>
			<legend><?php esc_html_e( 'Payment Info', 'easy-digital-downloads' ); ?></legend>
		<?php endif; ?>
		<?php if ( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock">
				<?php
				echo edd_get_payment_icon(
					array(
						'icon'    => 'lock',
						'width'   => 18,
						'height'  => 28,
						'classes' => array(
							'edd-icon',
							'edd-icon-lock',
						),
					)
				);
				?>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'easy-digital-downloads' ); ?></span>
			</div>
		<?php endif; ?>

		<?php
		if ( 'card-elements' === $elements_mode ) {
			$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );
			?>
			<?php if ( ! empty( $existing_cards ) ) { edd_stripe_existing_card_field_radio( get_current_user_id() ); } ?>
		<?php } ?>

		<div class="edd-stripe-new-card" <?php if ( ! empty( $existing_cards ) ) { echo 'style="display: none;"'; } ?>>
			<?php do_action( 'edd_stripe_new_card_form' ); ?>
			<?php do_action( 'edd_after_cc_expiration' ); ?>
		</div>

	</fieldset>
	<?php
	echo edds_get_tokenizer_input();

	do_action( 'edd_after_cc_fields' );

	$form = ob_get_clean();

	if ( false !== $echo ) {
		echo $form;
	}

	return $form;
}
add_action( 'edd_stripe_cc_form', 'edds_credit_card_form' );

/**
 * Display the markup for the Stripe new card form
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_new_card_form() {
	if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		edd_set_error( 'edd_stripe_error_limit', __( 'Adding new payment methods is currently unavailable.', 'easy-digital-downloads' ) );
		edd_print_errors();
		return;
	}

	$elements_mode = edds_get_elements_mode();
	if ( 'payment-elements' === $elements_mode ) {
		edds_output_payment_elements_form();
	} else {
		edds_output_legacy_new_card_form();
	}
}
add_action( 'edd_stripe_new_card_form', 'edd_stripe_new_card_form' );

/**
 * Add the element for the Stripe Payment Elements to attach to.
 *
 * @since 2.9.0
 */
function edds_output_payment_elements_form() {
	// Payment Elements needs to not allow checking out with mixed carts or multiple subscriptions.
	if ( ! edd_gateway_supports_cart_contents( 'stripe' ) ) {
		add_filter( 'edd_checkout_button_purchase', '__return_empty_string', 999 );
		?>
		<div class="edd_errors edd-alert edd-alert-info">
			<p class="edd_error" id="edd_error_edd-stripe-incompatible-cart"><?php echo edds_get_single_subscription_cart_error(); ?></p>
		</div>
		<?php
		return;
	}

	// Clear any errors that might be sitting from a previous AJAX loading of errors.
	edd_clear_errors();

	?>

	<div id="edd-card-wrap">
		<?php if ( edd_stripe()->has_regional_support() && edd_stripe()->regional_support->requires_card_name ) : ?>
		<p id="edd-card-name-wrap">
			<label for="card_name" class="edd-label">
				<?php esc_html_e( 'Name on the Card', 'easy-digital-downloads' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<input type="text" name="card_name" id="card_name" class="card-name edd-input required" placeholder="<?php esc_attr_e( 'Card name', 'easy-digital-downloads' ); ?>" autocomplete="cc-name" required/>
		</p>
		<?php endif; ?>
		<div id="edd-stripe-payment-element"></div>
		<p class="edds-field-spacer-shim"></p><!-- Extra spacing -->
	</div>
	<?php
}

/**
 * Add the legacy Card Element fields for users who are still on Card Elements.
 *
 * @since 2.9.0
 */
function edds_output_legacy_new_card_form() {
	$split = edd_get_option( 'stripe_split_payment_fields', false );
	?>
	<p id="edd-card-name-wrap">
		<label for="card_name" class="edd-label">
			<?php esc_html_e( 'Name on the Card', 'easy-digital-downloads' ); ?>
			<span class="edd-required-indicator">*</span>
		</label>
		<span class="edd-description"><?php esc_html_e( 'The name printed on the front of your credit card.', 'easy-digital-downloads' ); ?></span>
		<input type="text" name="card_name" id="card_name" class="card-name edd-input required" placeholder="<?php esc_attr_e( 'Card name', 'easy-digital-downloads' ); ?>" autocomplete="cc-name" />
	</p>

	<div id="edd-card-wrap">
	<label for="edd-card-element" class="edd-label">
		<?php
		if ( '1' === $split ) :
			esc_html_e( 'Credit Card Number', 'easy-digital-downloads' );
		else :
			esc_html_e( 'Credit Card', 'easy-digital-downloads' );
		endif;
		?>
		<span class="edd-required-indicator">*</span>
	</label>

	<div id="edd-stripe-card-element-wrapper">
		<?php if ( '1' === $split ) : ?>
			<span class="card-type"></span>
		<?php endif; ?>

		<div id="edd-stripe-card-element" class="edd-stripe-card-element"></div>
	</div>

	<p class="edds-field-spacer-shim"></p><!-- Extra spacing -->
	</div>

	<?php if ( '1' === $split ) : ?>

	<div id="edd-card-details-wrap">
	<p class="edds-field-spacer-shim"></p><!-- Extra spacing -->

	<div id="edd-card-exp-wrap">
		<label for="edd-card-exp-element" class="edd-label">
			<?php esc_html_e( 'Expiration', 'easy-digital-downloads' ); ?>
			<span class="edd-required-indicator">*</span>
		</label>

		<div id="edd-stripe-card-exp-element" class="edd-stripe-card-exp-element"></div>
	</div>

	<div id="edd-card-cvv-wrap">
		<label for="edd-card-exp-element" class="edd-label">
			<?php esc_html_e( 'CVC', 'easy-digital-downloads' ); ?>
			<span class="edd-required-indicator">*</span>
		</label>

		<div id="edd-stripe-card-cvc-element" class="edd-stripe-card-cvc-element"></div>
	</div>
	</div>

	<?php endif; ?>

	<div id="edd-stripe-card-errors" role="alert"></div>

	<?php
	/**
	 * Allow output of extra content before the credit card expiration field.
	 *
	 * This content no longer appears before the credit card expiration field
	 * with the introduction of Stripe Elements.
	 *
	 * @deprecated 2.7
	 * @since unknown
	 */
	do_action( 'edd_before_cc_expiration' );
}

/**
 * Show the checkbox for updating the billing information on an existing Stripe card
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_update_billing_address_field() {
	$payment_mode   = strtolower( edd_get_chosen_gateway() );
	if ( edd_is_checkout() && 'stripe' !== $payment_mode ) {
		return;
	}

	$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );
	if ( empty( $existing_cards ) ) {
		return;
	}

	if ( ! did_action( 'edd_stripe_cc_form' ) ) {
		return;
	}

	$default_card = false;

	foreach ( $existing_cards as $existing_card ) {
		if ( $existing_card['default'] ) {
			$default_card = $existing_card['source'];
			break;
		}
	}
	?>
	<p class="edd-stripe-update-billing-address-current">
		<?php
		if ( $default_card ) :
			$address_fields = array(
				'line1'   => isset( $default_card->address_line1 ) ? $default_card->address_line1 : null,
				'line2'   => isset( $default_card->address_line2 ) ? $default_card->address_line2 : null,
				'city'    => isset( $default_card->address_city ) ? $default_card->address_city : null,
				'state'   => isset( $default_card->address_state ) ? $default_card->address_state : null,
				'zip'     => isset( $default_card->address_zip ) ? $default_card->address_zip : null,
				'country' => isset( $default_card->address_country ) ? $default_card->address_country : null,
			);

			$address_fields = array_filter( $address_fields );

			echo esc_html( implode( ', ', $address_fields ) );
		endif;
		?>
	</p>

	<p class="edd-stripe-update-billing-address-wrapper">
		<input type="checkbox" name="edd_stripe_update_billing_address" id="edd-stripe-update-billing-address" value="1" />
		<label for="edd-stripe-update-billing-address">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Card type. %2$s Card last 4. */
					__( 'Update card billing address for %1$s •••• %2$s', 'easy-digital-downloads' ),
					'<span class="edd-stripe-update-billing-address-brand">' . ( $default_card ? $default_card->brand : '' ) . '</span>',
					'<span class="edd-stripe-update-billing-address-last4">' . ( $default_card ? $default_card->last4 : '' ) . '</span>'
				),
				array(
					'strong' => true,
					'span' => array(
						'class' => true,
					),
				)
			);
			?>
		</label>
	</p>
	<?php
}
add_action( 'edd_cc_billing_top', 'edd_stripe_update_billing_address_field', 10 );

/**
 * Display a radio list of existing cards on file for a user ID
 *
 * @since 2.6
 * @param int $user_id
 *
 * @return void
 */
function edd_stripe_existing_card_field_radio( $user_id = 0 ) {
	if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		edd_set_error( 'edd_stripe_error_limit', __( 'We are unable to process your payment at this time, please try again later or contacts support.', 'easy-digital-downloads' ) );
		return;
	}

	// Can't use just edd_is_checkout() because this could happen in an AJAX request.
	$is_checkout = edd_is_checkout() || ( isset( $_REQUEST['action'] ) && 'edd_load_gateway' === $_REQUEST['action'] );

	edd_stripe_css( true );
	$existing_cards = edd_stripe_get_existing_cards( $user_id );
	if ( ! empty( $existing_cards ) ) : ?>
	<div class="edd-stripe-card-selector edd-card-selector-radio">
		<?php foreach ( $existing_cards as $card ) : ?>
			<?php $source = $card['source']; ?>
			<div class="edd-stripe-card-radio-item existing-card-wrapper <?php if ( $card['default'] ) { echo ' selected'; } ?>">
				<input
					type="hidden"
					id="<?php echo esc_attr( $source->id ); ?>-billing-details"
					data-address_city="<?php echo esc_attr( $source->address_city ); ?>"
					data-address_country="<?php echo esc_attr( $source->address_country ); ?>"
					data-address_line1="<?php echo esc_attr( $source->address_line1 ); ?>"
					data-address_line2="<?php echo esc_attr( $source->address_line2 ); ?>"
					data-address_state="<?php echo esc_attr( $source->address_state ); ?>"
					data-address_zip="<?php echo esc_attr( $source->address_zip ); ?>"
					data-brand="<?php echo esc_attr( $source->brand ); ?>"
					data-last4="<?php echo esc_attr( $source->last4 ); ?>"
				/>
				<label for="<?php echo esc_attr( $source->id ); ?>">
					<input <?php checked( true, $card['default'], true ); ?> type="radio" id="<?php echo esc_attr( $source->id ); ?>" name="edd_stripe_existing_card" value="<?php echo esc_attr( $source->id ); ?>" class="edd-stripe-existing-card">
					<span class="card-label">
						<span class="card-data">
							<span class="card-name-number">
								<?php
										echo wp_kses(
											sprintf(
												/* translators: %1$s Card type. %2$s Card last 4. */
												__( '%1$s •••• %2$s', 'easy-digital-downloads' ),
												'<span class="card-brand">' . $source->brand . '</span>',
												'<span class="card-last-4">' . $source->last4 . '</span>'
											),
											array(
												'span' => array(
													'class' => true,
												),
											)
										);
								?>
							</span>
							<small class="card-expires-on">
								<span class="default-card-sep"><?php echo '&nbsp;&nbsp;&nbsp;'; ?></span>
								<span class="card-expiration">
									<?php echo $source->exp_month . '/' . $source->exp_year; ?>
								</span>
							</small>
						</span>
						<?php
							$current  = strtotime( date( 'm/Y' ) );
							$exp_date = strtotime( $source->exp_month . '/' . $source->exp_year );
							if ( $exp_date < $current ) :
							?>
							<span class="card-expired">
									<?php _e( 'Expired', 'easy-digital-downloads' ); ?>
								</span>
							<?php
							endif;
						?>
					</span>
				</label>
			</div>
		<?php endforeach; ?>
		<div class="edd-stripe-card-radio-item new-card-wrapper">
			<label for="edd-stripe-add-new">
				<input type="radio" id="edd-stripe-add-new" class="edd-stripe-existing-card" name="edd_stripe_existing_card" value="new" />
				<span class="add-new-card"><?php _e( 'Add New Card', 'easy-digital-downloads' ); ?></span>
			</label>
		</div>
	</div>
	<?php endif;
}

/**
 * Output the management interface for a user's Stripe card
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_manage_cards() {
	if ( false === edds_is_gateway_active() ) {
		return;
	}

	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		return;
	}

	$stripe_customer_id = edds_get_stripe_customer_id( get_current_user_id() );
	if ( empty( $stripe_customer_id ) ) {
		return;
	}

	if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		edd_set_error( 'edd_stripe_error_limit', __( 'Payment method management is currently unavailable.', 'easy-digital-downloads' ) );
		edd_print_errors();
		return;
	}

	$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );

	edd_stripe_css( true );
	edd_stripe_js( true );
	$display = edd_get_option( 'stripe_billing_fields', 'full' );
?>
	<div id="edd-stripe-manage-cards">
		<fieldset>
			<legend><?php _e( 'Manage Payment Methods', 'easy-digital-downloads' ); ?></legend>
			<input type="hidden" id="stripe-update-card-user_id" name="stripe-update-user-id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
			<?php if ( ! empty( $existing_cards ) ) : ?>
				<?php foreach( $existing_cards as $card ) : ?>
				<?php $source = $card['source']; ?>
				<div id="<?php echo esc_attr( $source->id ); ?>_card_item" class="edd-stripe-card-item">
					<span class="card-details">
						<?php
								echo wp_kses(
									sprintf(
										__( '%1$s •••• %2$s', 'easy-digital-downloads' ),
										'<span class="card-brand">' . esc_html( $source->brand ) . '</span>',
										'<span class="card-last-4">' . esc_html( $source->last4 ) . '</span>'
									),
									array(
										'span' => array(
											'class' => true,
										),
									)
								);
						?>

						<?php if ( $card['default'] ) { ?>
							<span class="default-card-sep"><?php echo '&mdash; '; ?></span>
							<span class="card-is-default"><?php _e( 'Default', 'easy-digital-downloads'); ?></span>
						<?php } ?>
					</span>

					<span class="card-meta">
						<span class="card-expiration"><span class="card-expiration-label"><?php _e( 'Expires', 'easy-digital-downloads' ); ?>: </span><span class="card-expiration-date"><?php echo $source->exp_month; ?>/<?php echo $source->exp_year; ?></span></span>
						<span class="card-address">
							<?php
							$address_fields = array(
								'line1'   => isset( $source->address_line1 ) ? $source->address_line1 : '',
								'zip'     => isset( $source->address_zip ) ? $source->address_zip : '',
								'country' => isset( $source->address_country ) ? $source->address_country : '',
							);

							echo esc_html( implode( ' ', $address_fields ) );
							?>
						</span>
					</span>

					<span id="<?php echo esc_attr( $source->id ); ?>-card-actions" class="card-actions">
						<span class="card-update">
							<a href="#" class="edd-stripe-update-card" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Update', 'easy-digital-downloads' ); ?></a>
						</span>

						<?php if ( ! $card['default'] ) : ?>
						 |
						<span class="card-set-as-default">
							<a href="#" class="edd-stripe-default-card" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Set as Default', 'easy-digital-downloads' ); ?></a>
						</span>
						<?php
						endif;

						$can_delete = apply_filters( 'edd_stripe_can_delete_card', true, $card, $existing_cards );
						if ( $can_delete ) :
						?>
						|
						<span class="card-delete">
							<a href="#" class="edd-stripe-delete-card delete" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Delete', 'easy-digital-downloads' ); ?></a>
						</span>
						<?php endif; ?>

						<span style="display: none;" class="edd-loading-ajax edd-loading"></span>
					</span>

					<form id="<?php echo esc_attr( $source->id ); ?>-update-form" class="card-update-form" data-source="<?php echo esc_attr( $source->id ); ?>">
						<label><?php _e( 'Billing Details', 'easy-digital-downloads' ); ?></label>

						<div class="card-address-fields">
							<p class="edds-card-address-field edds-card-address-field--address1">
							<?php
							echo EDD()->html->text( array(
								'id'    => sprintf( 'edds_address_line1_%1$s', $source->id ),
								'value' => sanitize_text_field( isset( $source->address_line1 ) ? $source->address_line1 : '' ),
								'label' => esc_html__( 'Address Line 1', 'easy-digital-downloads' ),
								'name'  => 'address_line1',
								'class' => 'card-update-field address_line1 text edd-input',
								'data'  => array(
									'key' => 'address_line1',
								)
							) );
							?>
							</p>
							<p class="edds-card-address-field edds-card-address-field--address2">
							<?php
							echo EDD()->html->text( array(
								'id'    => sprintf( 'edds_address_line2_%1$s', $source->id ),
								'value' => sanitize_text_field( isset( $source->address_line2 ) ? $source->address_line2 : '' ),
								'label' => esc_html__( 'Address Line 2', 'easy-digital-downloads' ),
								'name'  => 'address_line2',
								'class' => 'card-update-field address_line2 text edd-input',
								'data'  => array(
									'key' => 'address_line2',
								)
							) );
							?>
							</p>
							<p class="edds-card-address-field edds-card-address-field--city">
							<?php
							echo EDD()->html->text( array(
								'id'    => sprintf( 'edds_address_city_%1$s', $source->id ),
								'value' => sanitize_text_field( isset( $source->address_city ) ? $source->address_city : '' ),
								'label' => esc_html__( 'City', 'easy-digital-downloads' ),
								'name'  => 'address_city',
								'class' => 'card-update-field address_city text edd-input',
								'data'  => array(
									'key' => 'address_city',
								)
							) );
							?>
							</p>
							<p class="edds-card-address-field edds-card-address-field--zip">
							<?php
							echo EDD()->html->text( array(
								'id'    => sprintf( 'edds_address_zip_%1$s', $source->id ),
								'value' => sanitize_text_field( isset( $source->address_zip ) ? $source->address_zip : '' ),
								'label' => esc_html__( 'ZIP Code', 'easy-digital-downloads' ),
								'name'  => 'address_zip',
								'class' => 'card-update-field address_zip text edd-input',
								'data'  => array(
									'key' => 'address_zip',
								)
							) );
							?>
							</p>
							<p class="edds-card-address-field edds-card-address-field--country">
								<label for="<?php echo esc_attr( sprintf( 'edds_address_country_%1$s', $source->id ) ); ?>">
									<?php esc_html_e( 'Country', 'easy-digital-downloads' ); ?>
								</label>

								<?php
								$countries = array_filter( edd_get_country_list() );
								$country   = isset( $source->address_country ) ? $source->address_country : edd_get_shop_country();
								echo EDD()->html->select( array(
									'id'               => sprintf( 'edds_address_country_%1$s', $source->id ),
									'name'             => 'address_country',
									'label'            => esc_html__( 'Country', 'easy-digital-downloads' ),
									'options'          => $countries,
									'selected'         => $country,
									'class'            => 'card-update-field address_country',
									'data'             => array(
										'key'   => 'address_country',
										'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
									),
									'show_option_all'  => false,
									'show_option_none' => false,
								) );
								?>
							</p>

							<p class="edds-card-address-field edds-card-address-field--state">
								<label for="<?php echo esc_attr( sprintf( 'edds_address_state_%1$s', $source->id ) ); ?>">
									<?php esc_html_e( 'State', 'easy-digital-downloads' ); ?>
								</label>

								<?php
								$selected_state = isset( $source->address_state ) ? $source->address_state : edd_get_shop_state();
								$states         = edd_get_shop_states( $country );
								echo EDD()->html->select( array(
									'id'               => sprintf( 'edds_address_state_%1$s', $source->id ),
									'name'             => 'address_state',
									'options'          => $states,
									'selected'         => $selected_state,
									'class'            => 'card-update-field address_state card_state',
									'data'             => array( 'key' => 'address_state' ),
									'show_option_all'  => false,
									'show_option_none' => false,
								) );
								?>
							</p>
						</div>

						<p class="card-expiration-fields">
							<label for="<?php echo esc_attr( sprintf( 'edds_card_exp_month_%1$s', $source->id ) ); ?>" class="edd-label">
								<?php _e( 'Expiration (MM/YY)', 'easy-digital-downloads' ); ?>
							</label>

							<?php
								$months = array_combine( $r = range( 1, 12 ), $r );
								echo EDD()->html->select( array(
									'id'               => sprintf( 'edds_card_exp_month_%1$s', $source->id ),
									'name'             => 'exp_month',
									'options'          => $months,
									'selected'         => $source->exp_month,
									'class'            => 'card-expiry-month edd-select edd-select-small card-update-field exp_month',
									'data'             => array( 'key' => 'exp_month' ),
									'show_option_all'  => false,
									'show_option_none' => false,
								) );
							?>

							<span class="exp-divider"> / </span>

							<?php
								$years = array_combine( $r = range( date( 'Y' ), date( 'Y' ) + 30 ), $r );
								echo EDD()->html->select( array(
									'id'               => sprintf( 'edds_card_exp_year_%1$s', $source->id ),
									'name'             => 'exp_year',
									'options'          => $years,
									'selected'         => $source->exp_year,
									'class'            => 'card-expiry-year edd-select edd-select-small card-update-field exp_year',
									'data'             => array( 'key' => 'exp_year' ),
									'show_option_all'  => false,
									'show_option_none' => false,
								) );
							?>
						</p>

						<p>
							<input
								type="submit"
								class="edd-stripe-submit-update"
								data-loading="<?php echo esc_attr__( 'Please Wait…', 'easy-digital-downloads' ); ?>"
								data-submit="<?php echo esc_attr__( 'Update Card', 'easy-digital-downloads' ); ?>"
								value="<?php echo esc_attr__( 'Update Card', 'easy-digital-downloads' ); ?>"
							/>

							<a href="#" class="edd-stripe-cancel-update" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>

							<input type="hidden" name="card_id" data-key="id" value="<?php echo esc_attr( $source->id ); ?>" />
							<?php
							wp_nonce_field( $source->id . '_update', 'card_update_nonce_' . $source->id, true );
							echo edds_get_tokenizer_input( $source->id );
							?>
						</p>
					</form>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<form id="edd-stripe-add-new-card">
				<div class="edd-stripe-add-new-card edd-stripe-new-card" style="display: none;">
					<label><?php _e( 'Add New Card', 'easy-digital-downloads' ); ?></label>
					<fieldset id="edd_cc_card_info" class="cc-card-info">
						<legend><?php _e( 'Credit Card Details', 'easy-digital-downloads' ); ?></legend>
						<?php do_action( 'edd_stripe_new_card_form' ); ?>
					</fieldset>
					<?php
					switch( $display ) {
					case 'full' :
						edd_default_cc_address_fields();
						break;

					case 'zip_country' :
						edd_stripe_zip_and_country();
						add_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );

						break;
					}
					?>
				</div>
				<div class="edd-stripe-add-card-errors"></div>
				<div class="edd-stripe-add-card-actions">

					<input
						type="submit"
						class="edd-button edd-stripe-add-new"
						data-loading="<?php echo esc_attr__( 'Please Wait…', 'easy-digital-downloads' ); ?>"
						data-submit="<?php echo esc_attr__( 'Add new card', 'easy-digital-downloads' ); ?>"
						value="<?php echo esc_attr__( 'Add new card', 'easy-digital-downloads' ); ?>"
					/>
					<a href="#" id="edd-stripe-add-new-cancel" style="display: none;"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
					<?php
					wp_nonce_field( 'edd-stripe-add-card', 'edd-stripe-add-card-nonce', false, true );
					echo edds_get_tokenizer_input();
					?>
				</div>
			</form>
		</fieldset>
	</div>
	<?php
}
add_action( 'edd_profile_editor_after', 'edd_stripe_manage_cards' );

/**
 * Determines if the default Profile Editor's "Billing Address"
 * fields should be hidden.
 *
 * If using Stripe + Saved Cards (and Stripe is the only active gateway)
 * the information set in "Billing Address" is never utilized:
 *
 * - When using an existing Card that Card's billing address is used.
 * - When adding a new Card the address form is blanked.
 *
 * @since 2.8.0
 */
function edd_stripe_maybe_hide_profile_editor_billing_address() {
	if ( false === edds_is_gateway_active() ) {
		return;
	}

	// Only hide if Stripe is the only active gateway.
	$active_gateways = edd_get_enabled_payment_gateways();

	if ( ! ( 1 === count( $active_gateways ) && isset( $active_gateways['stripe'] ) ) ) {
		return;
	}

	// Only hide if using Saved Cards.
	$use_saved_cards = edd_stripe_existing_cards_enabled();

	if ( false === $use_saved_cards ) {
		return;
	}

	// Allow a default addres to be entered for the first Card
	// if the Profile Editor is found before Checkout.
	$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );

	if ( empty( $existing_cards ) ) {
		return;
	}

	echo '<style>#edd_profile_address_fieldset { display: none; }</style>';
}
add_action( 'edd_profile_editor_after', 'edd_stripe_maybe_hide_profile_editor_billing_address' );

/**
 * Zip / Postal Code field for when full billing address is disabled
 *
 * @since       2.5
 * @return      void
 */
function edd_stripe_zip_and_country() {

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
		$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );
		if ( empty( $existing_cards ) ) {

			$user_address = edd_get_customer_address( get_current_user_id() );

			foreach( $customer['address'] as $key => $field ) {

				if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
					$customer['address'][ $key ] = $user_address[ $key ];
				} else {
					$customer['address'][ $key ] = '';
				}

			}
		} else {
			foreach ( $existing_cards as $card ) {
				if ( false === $card['default'] ) {
					continue;
				}

				$source = $card['source'];
				$customer['address'] = array(
					'line1'   => $source->address_line1,
					'line2'   => $source->address_line2,
					'city'    => $source->address_city,
					'zip'     => $source->address_zip,
					'state'   => $source->address_state,
					'country' => $source->address_country,
				);
			}
		}

	}
?>
	<fieldset id="edd_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'easy-digital-downloads' ); ?></legend>
		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e( 'Billing Country', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'billing_country' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The country for your billing address.', 'easy-digital-downloads' ); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country edd-select<?php if( edd_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if( edd_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?> autocomplete="billing country">
				<?php

				$selected_country = edd_get_shop_country();

				if( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = edd_get_country_list();
				foreach( $countries as $country_code => $country ) {
					echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . esc_html( $country ) . '</option>';
				}
				?>
			</select>
		</p>
		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e( 'Billing Zip / Postal Code', 'easy-digital-downloads' ); ?>
				<?php if( edd_field_is_required( 'card_zip' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The zip or postal code for your billing address.', 'easy-digital-downloads' ); ?></span>
			<input type="text" size="4" name="card_zip" id="card_zip" class="card-zip edd-input<?php if( edd_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Zip / Postal Code', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['zip'] ); ?>"<?php if( edd_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?> autocomplete="billing postal-code" />
		</p>
	</fieldset>
<?php
}

/**
 * Determine how the billing address fields should be displayed
 *
 * @access      public
 * @since       2.5
 * @return      void
 */
function edd_stripe_setup_billing_address_fields() {
	if ( 'stripe' !== edd_get_chosen_gateway() || ! edd_get_cart_total() > 0 ) {
		return;
	}

	$hook = 'payment-elements' === edds_get_elements_mode() || apply_filters( 'edds_address_before_payment', false ) ? 'edd_before_cc_fields' : 'edd_after_cc_fields';

	if ( edd_use_taxes() ) {
		remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
		add_action( $hook, 'edd_default_cc_address_fields' );
		return;
	}

	$display = edd_get_option( 'stripe_billing_fields', 'full' );

	switch ( $display ) {

		case 'full':
			// Make address fields required.
			add_filter( 'edd_require_billing_address', '__return_true' );
			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
			add_action( $hook, 'edd_default_cc_address_fields' );

			break;

		case 'zip_country':
			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );
			add_action( $hook, 'edd_stripe_zip_and_country', 9 );

			// Make Zip required.
			add_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );

			break;

		case 'none':
			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );

			break;

	}

}
add_action( 'init', 'edd_stripe_setup_billing_address_fields', 9 );

/**
 * Force zip code and country to be required when billing address display is zip only
 *
 * @access      public
 * @since       2.5
 * @return      array $fields The required fields
 */
function edd_stripe_require_zip_and_country( $fields ) {

	$fields['card_zip'] = array(
		'error_id' => 'invalid_zip_code',
		'error_message' => __( 'Please enter your zip / postal code', 'easy-digital-downloads' )
	);

	$fields['billing_country'] = array(
		'error_id' => 'invalid_country',
		'error_message' => __( 'Please select your billing country', 'easy-digital-downloads' )
	);

	return $fields;
}
