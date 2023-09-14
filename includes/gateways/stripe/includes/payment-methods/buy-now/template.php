<?php
/**
 * Buy Now: Template
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Adds "Buy Now" modal markup to the bottom of the page.
 *
 * @since 2.8.0
 */
function edds_buy_now_modal() {
	// Check if Stripe Buy Now is enabled.
	global $edds_has_buy_now;

	if ( true !== $edds_has_buy_now ) {
		return;
	}

	if ( ! edds_buy_now_is_enabled() ) {
		return;
	}

	// Enqueue core scripts.
	add_filter( 'edd_is_checkout', '__return_true' );

	edd_enqueue_scripts();
	edd_localize_scripts();

	remove_filter( 'edd_is_checkout', '__return_true' );

	// Enqueue scripts.
	edd_stripe_js( true );
	edd_stripe_css( true );

	echo edds_modal( array(
		'id'      => 'edds-buy-now',
		'title'   => __( 'Buy Now', 'easy-digital-downloads' ),
		'class'   => array(
			'edds-buy-now-modal',
		),
		'content' => '<span class="edd-loading-ajax edd-loading"></span>',
	) ); // WPCS: XSS okay.
}
add_action( 'wp_print_footer_scripts', 'edds_buy_now_modal', 0 );

/**
 * Outputs a custom "Buy Now"-specific Checkout form.
 *
 * @since 2.8.0
 */
function edds_buy_now_checkout() {
	$total = (int) edd_get_cart_total();

	$form_mode      = $total > 0
		? 'payment-mode=stripe'
		: 'payment-mode=manual';

	$form_action    = edd_get_checkout_uri( $form_mode );
	$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );

	$customer = EDD()->session->get( 'customer' );
	$customer = wp_parse_args(
		$customer,
		array(
			'email' => '',
		)
	);

	if ( is_user_logged_in() ) {
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

	remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );
	remove_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );

	// Filter purchase button label.
	add_filter( 'edd_get_checkout_button_purchase_label', 'edds_buy_now_checkout_purchase_label' );

	ob_start();
?>

<div id="edd_checkout_form_wrap">
	<form
		id="edd_purchase_form"
		class="edd_form edds-buy-now-form"
		action="<?php echo esc_url( $form_action ); ?>"
		method="POST"
	>
		<?php if ( is_user_logged_in() && ! empty( $customer['email'] ) ) : ?>
			<input type="hidden" name="edd_email" id="edd-email" value="<?php echo esc_attr( $customer['email'] ); ?>" required/>
		<?php else: ?>
		<p>
			<label class="edd-label" for="edd-email">
				<?php esc_html_e( 'Email Address', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'edd_email' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif ?>
			</label>

			<input
				id="edd-email"
				class="edd-input required"
				type="email"
				name="edd_email"
				value="<?php echo esc_attr( $customer['email'] ); ?>"
				<?php if ( edd_field_is_required( 'edd_email' ) ) : ?>
					required
				<?php endif; ?>
			/>
		</p>
		<?php endif; ?>

		<?php if ( $total > 0 ) : ?>

			<?php if ( ! empty( $existing_cards ) ) : ?>
				<?php edd_stripe_existing_card_field_radio( get_current_user_id() ); ?>
			<?php endif; ?>

			<div
				class="edd-stripe-new-card"
				<?php if ( ! empty( $existing_cards ) ) : ?>
					style="display: none;"
				<?php endif; ?>
			>
				<?php do_action( 'edd_stripe_new_card_form' ); ?>
				<?php do_action( 'edd_after_cc_expiration' ); ?>
			</div>

		<?php endif; ?>

		<?php
		edd_terms_agreement();
		edd_privacy_agreement();
		edd_checkout_hidden_fields();
		?>

		<div id="edd_purchase_submit">
			<?php echo edds_get_tokenizer_input(); // WPCS: XSS okay. ?>
			<?php echo edd_checkout_button_purchase(); // WPCS: XSS okay. ?>
		</div>

		<div class="edd_cart_total" style="display: none;">
			<div
				class="edd_cart_amount"
				data-total="<?php echo edd_get_cart_total(); ?>"
				data-total-currency="<?php echo edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ); ?>"
			>
			</div>
		</div>

		<input type="hidden" name="edds-gateway" value="buy-now" />
	</form>
</div>

<?php
		return ob_get_clean();
}

/**
 * Filters the label of the of the "Purchase" button in the "Buy Now" modal.
 *
 * @since 2.8.0
 *
 * @param string $label Purchase label.
 * @return string
 */
function edds_buy_now_checkout_purchase_label( $label ) {
	$total = edd_get_cart_total();

	if ( 0 === (int) $total ) {
		return $label;
	}

	$label = sprintf(
		'%s - %s',
		edd_currency_filter(
			edd_format_amount( $total )
		),
		$label
	);

	return $label;
}

/**
 * Adds additional script variables needed for the Buy Now flow.
 *
 * @since 2.8.0
 *
 * @param array $vars Script variables.
 * @return array
 */
function edds_buy_now_vars( $vars ) {
	if ( ! isset( $vars['i18n'] ) ) {
		$vars['i18n'] = array();
	}

	// Non-zero amount.
	$label             = edd_get_option( 'checkout_label', '' );
	$complete_purchase = ! empty( $label )
		? $label
	  : esc_html__( 'Purchase', 'easy-digital-downloads' );

	/* This filter is documented in easy-digital-downloads/includes/checkout/template.php */
	$complete_purchase = apply_filters(
		'edd_get_checkout_button_purchase_label',
		$complete_purchase,
		$label
	);

	$vars['i18n']['completePurchase'] = $complete_purchase;

	return $vars;
}
add_filter( 'edd_stripe_js_vars', 'edds_buy_now_vars' );
