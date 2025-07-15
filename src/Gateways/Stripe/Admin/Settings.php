<?php

namespace EDD\Gateways\Stripe\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\HTML\Multicheck;
use EDD\Gateways\Stripe\PaymentMethods;

/**
 * Settings class.
 *
 * @since 3.3.5
 */
class Settings {

	/**
	 * Whether the Payment Elements mode is enabled.
	 *
	 * @var bool
	 */
	private $is_payment_elements_mode;

	/**
	 * Gets the settings.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	public function get() {
		$stripe_settings = array(
			'stripe_connect_button'          => array(
				'id'    => 'stripe_connect_button',
				'name'  => __( 'Connection Status', 'easy-digital-downloads' ),
				'desc'  => edds_stripe_connect_setting_field(),
				'type'  => 'descriptive_text',
				'class' => 'edd-stripe-connect-row',
			),
			'test_publishable_key'           => array(
				'id'    => 'test_publishable_key',
				'name'  => __( 'Test Publishable Key', 'easy-digital-downloads' ),
				'desc'  => __( 'Enter your test publishable key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'edd-hidden edds-api-key-row',
			),
			'test_secret_key'                => array(
				'id'    => 'test_secret_key',
				'name'  => __( 'Test Secret Key', 'easy-digital-downloads' ),
				'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'edd-hidden edds-api-key-row',
			),
			'live_publishable_key'           => array(
				'id'    => 'live_publishable_key',
				'name'  => __( 'Live Publishable Key', 'easy-digital-downloads' ),
				'desc'  => __( 'Enter your live publishable key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'edd-hidden edds-api-key-row',
			),
			'live_secret_key'                => array(
				'id'    => 'live_secret_key',
				'name'  => __( 'Live Secret Key', 'easy-digital-downloads' ),
				'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'edd-hidden edds-api-key-row',
			),
			'statement_descriptor'           => array(
				'id'          => 'stripe_statement_descriptor',
				'name'        => __( 'Statement Descriptor', 'easy-digital-downloads' ),
				'desc'        => sprintf(
					/* translators: 1: opening link tag (do not translate), 2: closing link tag (do not translate) */
					__( 'You can change the description of charges on a customer\'s bank statement in your %1$sStripe Settings%2$s.', 'easy-digital-downloads' ),
					'<a href="https://dashboard.stripe.com/settings/public" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'type'        => 'text',
				'faux'        => true,
				'disabled'    => true,
				'class'       => $this->get_connected_class(),
				'field_class' => 'edd-text-loading',
			),
			'include_purchase_summary'       => array(
				'id'    => 'stripe_include_purchase_summary_in_statement_descriptor',
				'name'  => __( 'Include Purchase Summary', 'easy-digital-downloads' ),
				'check' => __( 'Include the product name(s) purchased in the payment descriptor for card payments. If the product name(s) are too long they will be shortened automatically.', 'easy-digital-downloads' ),
				'desc'  => __( 'Note: This setting does not affect non-card payment methods. Non-card payment methods will always use the Statement Descriptor above.', 'easy-digital-downloads' ),
				'type'  => 'checkbox_toggle',
				'class' => $this->get_connected_class(),
			),
			'statement_descriptor_prefix'    => array(
				'id'          => 'stripe_statement_descriptor_prefix',
				'name'        => __( 'Shortened Descriptor', 'easy-digital-downloads' ),
				'desc'        => __( 'When including the purchase summary in the payment descriptor for card payments, Stripe will use this shortened description as a prefix to the purchase summary.', 'easy-digital-downloads' ),
				'type'        => 'text',
				'faux'        => true,
				'disabled'    => true,
				'class'       => $this->is_connected() && ! empty( edd_get_option( 'stripe_include_purchase_summary_in_statement_descriptor', false ) ) ? 'statement-descriptor-prefix' : 'edd-hidden statement-descriptor-prefix',
				'field_class' => 'edd-text-loading',
			),
			'stripe_more_settings_header'    => array(
				'id'   => 'stripe_additional_settings_header',
				'name' => __( 'Additional Settings', 'easy-digital-downloads' ),
				'type' => 'header',
			),
			'stripe_restrict_assets'         => array(
				'id'    => 'stripe_restrict_assets',
				'name'  => ( __( 'Restrict Stripe Assets', 'easy-digital-downloads' ) ),
				'check' => ( __( 'Only load Stripe.com hosted assets on pages that specifically utilize Stripe functionality.', 'easy-digital-downloads' ) ),
				'type'  => 'checkbox_toggle',
				'desc'  => sprintf(
					/* translators: 1: opening link tag, 2: closing link tag */
					__( 'Stripe advises that their Javascript library be loaded on every page to take advantage of their advanced fraud detection rules. If you are not concerned with this, enable this setting to only load the Javascript when necessary. %1$sLearn more about Stripe\'s recommended setup.%2$s', 'easy-digital-downloads' ),
					'<a href="https://docs.stripe.com/js/including" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
			),
			'stripe_payment_elements_layout' => $this->get_layout_setting(),
		);

		$payment_methods = $this->get_payment_methods_setting();
		if ( $payment_methods ) {
			$stripe_settings['stripe_payment_methods'] = $payment_methods;
		}

		if ( _edds_legacy_elements_enabled() ) {
			if ( ! edds_stripe_connect_can_manage_keys() ) {
				$stripe_settings['stripe_elements_mode'] = array(
					'id'            => 'stripe_elements_mode',
					'name'          => __( 'Elements Mode', 'easy-digital-downloads' ),
					'desc'          => __( 'Toggle between using the legacy Card Elements Stripe integration and the new Payment Elements experience.', 'easy-digital-downloads' ),
					'type'          => 'select',
					'options'       => array(
						'card-elements'    => __( 'Card Element', 'easy-digital-downloads' ),
						'payment-elements' => __( 'Payment Element', 'easy-digital-downloads' ),
					),
					'class'         => 'stripe-elements-mode',
					'tooltip_title' => __( 'Transitioning to Payment Elements', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'You are seeing this option because your store has been using Card Elements prior to the EDD Stripe 2.9.0 update.<br /><br />To ensure that we do not affect your current checkout experience, you can use this setting to toggle between the Card Elements (legacy) and Payment Elements (updated version) to ensure that any customizations or theming you have done still function properly.<br /><br />Please be advised, that in a future version of the Stripe extension, we will deprecate the Card Elements, so take this time to update your store!', 'easy-digital-downloads' ),
				);
			}

			$stripe_settings['stripe_allow_prepaid'] = array(
				'id'    => 'stripe_allow_prepaid',
				'name'  => __( 'Prepaid Cards', 'easy-digital-downloads' ),
				'desc'  => __( 'Allow prepaid cards as valid payment method.', 'easy-digital-downloads' ),
				'type'  => 'checkbox',
				'class' => $this->is_payment_elements_mode() ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
			);

			$radar_rules_url = sprintf(
				'https://dashboard.stripe.com%s/settings/radar/rules',
				edd_is_test_mode() ? '/test' : ''
			);

			$stripe_settings['stripe_allow_prepaid_elements_note'] = array(
				'id'    => 'stripe_allow_prepaid_elements_note',
				'name'  => __( 'Prepaid Cards', 'easy-digital-downloads' ),
				'desc'  => sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'Prepaid card allowance can now be managed in your %1$sStripe Radar Rules%2$s.', 'easy-digital-downloads' ),
					'<a href="' . $radar_rules_url . '" target="_blank">',
					'</a>'
				),
				'type'  => 'descriptive_text',
				'class' => $this->is_payment_elements_mode() ? 'payment-elements-feature' : 'edd-hidden payment-elements-feature',
			);

			$stripe_settings['stripe_split_payment_fields'] = array(
				'id'    => 'stripe_split_payment_fields',
				'name'  => __( 'Split Credit Card Form', 'easy-digital-downloads' ),
				'desc'  => __( 'Use separate card number, expiration, and CVC fields in payment forms.', 'easy-digital-downloads' ),
				'type'  => 'checkbox',
				'class' => $this->is_payment_elements_mode() ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
			);

			$stripe_settings['stripe_use_existing_cards'] = array(
				'id'    => 'stripe_use_existing_cards',
				'name'  => __( 'Show Previously Used Cards', 'easy-digital-downloads' ),
				'desc'  => __( 'Provides logged in customers with a list of previously used payment methods for faster checkout.', 'easy-digital-downloads' ),
				'type'  => 'checkbox',
				'class' => $this->is_payment_elements_mode() ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
			);

			$stripe_settings['stripe_use_existing_cards_elements_note'] = array(
				'id'    => 'stripe_use_existing_cards_elements_note',
				'name'  => __( 'Show Previously Used Cards', 'easy-digital-downloads' ),
				'desc'  => sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'Previously used cards are now managed by %1$sLink by Stripe%2$s, for even better conversions and security.', 'easy-digital-downloads' ),
					'<a href="https://link.co/" target="_blank">',
					'</a>'
				),
				'type'  => 'descriptive_text',
				'class' => $this->is_payment_elements_mode() ? 'payment-elements-feature' : 'edd-hidden payment-elements-feature',
			);
		}

		$debug_setting = $this->get_debug_mode_setting();
		if ( $debug_setting ) {
			$stripe_settings = array_merge( $stripe_settings, $debug_setting );
		}

		return $stripe_settings;
	}

	/**
	 * Inserts the Test Mode toggle notice after the Test Mode checkbox.
	 *
	 * @since 3.3.5
	 * @param array $settings The settings array.
	 * @return array The modified settings array.
	 */
	public function insert_toggle_notice( $settings ) {
		if ( ! $this->is_gateway_settings_screen() ) {
			return $settings;
		}

		// Set up the new setting field for the Test Mode toggle notice.
		$notice = array(
			'stripe_connect_test_mode_toggle_notice' => array(
				'id'          => 'stripe_connect_test_mode_toggle_notice',
				'desc'        => '<p>' . __( 'You have disabled the "Test Mode" option. Once you have saved your changes, please verify your Stripe connection, especially if you have not previously connected in with "Test Mode" disabled.', 'easy-digital-downloads' ) . '</p>',
				'type'        => 'stripe_connect_notice',
				'field_class' => 'edd-hidden',
			),
		);

		// Insert the new setting after the Test Mode checkbox.
		$position = array_search( 'test_mode', array_keys( $settings['main'] ), true );

		return array_merge(
			array_slice( $settings['main'], $position, 1, true ),
			$notice,
			$settings
		);
	}

	/**
	 * Outputs the payment method settings.
	 *
	 * @since 3.3.5
	 * @param array $args The settings arguments.
	 */
	public static function render_payment_methods( $args ) {
		$configuration = PaymentMethods::get_base_configuration();
		if ( ! $configuration ) {
			esc_html_e( 'Unable to retrieve payment method configuration.', 'easy-digital-downloads' );
			return;
		}

		$options = self::get_payment_method_options( $configuration );
		if ( empty( $options ) ) {
			esc_html_e( 'No payment methods available.', 'easy-digital-downloads' );
			return;
		}

		// Resort the $options array by the label.
		uasort(
			$options,
			function ( $a, $b ) {
				return strcasecmp( $a['label'], $b['label'] );
			}
		);

		$is_recurring_active = function_exists( 'edd_recurring' );
		?>
		<div class="edd-stripe-payment-methods__description">
			<p><?php esc_html_e( 'The methods actually displayed to your customers will vary based on multiple factors, such as currency, country, and what\'s in their cart.', 'easy-digital-downloads' ); ?></p>
			<?php
			if ( edd_is_test_mode() ) {
				?>
				<p><?php esc_html_e( 'Changes you make here will update your payment methods in Stripe\'s test mode. When you disable EDD\'s Test Mode, please revisit these settings.', 'easy-digital-downloads' ); ?></p>
				<?php
			}
			?>
			<div class="button-group">
				<button class="button edd-stripe-payment-method-toggle active" data-toggle=""><?php esc_html_e( 'All', 'easy-digital-downloads' ); ?></button>
				<?php
				if ( $is_recurring_active ) {
					?>
					<button class="button edd-stripe-payment-method-toggle" data-toggle="subscriptions"><?php esc_html_e( 'Subscriptions', 'easy-digital-downloads' ); ?></button>
					<button class="button edd-stripe-payment-method-toggle" data-toggle="trials"><?php esc_html_e( 'Trials', 'easy-digital-downloads' ); ?></button>
					<?php
				} else {
					?>
					<button class="button edd-stripe-payment-method-toggle edd-promo-notice__trigger">
						<?php esc_html_e( 'Subscriptions', 'easy-digital-downloads' ); ?>
					</button>
					<?php
				}
				?>
			</div>
		</div>
		<?php

		$multicheck = new Multicheck(
			array(
				'name'    => 'edd_settings[' . edd_sanitize_key( $args['id'] ) . ']',
				'options' => $options,
				'toggle'  => true,
			)
		);
		$multicheck->output();
	}

	/**
	 * Gets the layout setting.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	private function get_layout_setting() {
		$layout_setting = array(
			'id'      => 'stripe_payment_elements_layout',
			'name'    => __( 'Payment Methods Style', 'easy-digital-downloads' ),
			'type'    => 'select',
			'options' => array(
				''          => __( 'Tabs', 'easy-digital-downloads' ),
				'accordion' => __( 'Accordion', 'easy-digital-downloads' ),
			),
			'desc'    => __( 'Select the layout style for the Payment Methods section on the checkout form.', 'easy-digital-downloads' ),
			'class'   => $this->is_payment_elements_mode() ? 'payment-elements-feature' : 'payment-elements-feature edd-hidden',
		);
		if ( has_filter( 'edds_stripe_payment_elements_layout' ) ) {
			$layout_setting['tooltip_title']    = __( 'Payment Methods Style', 'easy-digital-downloads' );
			$layout_setting['tooltip_desc']     = __( 'The Payment Methods Style setting is being overridden by a third-party plugin or custom code.', 'easy-digital-downloads' );
			$layout_setting['tooltip_dashicon'] = 'dashicons-warning';
		}

		return $layout_setting;
	}

	/**
	 * Gets the debug mode setting.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	private function get_debug_mode_setting() {
		if ( ! edd_is_debug_mode() ) {
			return false;
		}
		if ( ! $this->is_gateway_settings_screen() ) {
			return false;
		}

		$debug_settings = array(
			'stripe_debug' => array(
				'id'   => 'stripe_debug',
				'name' => __( 'Debugging Settings', 'easy-digital-downloads' ),
				'desc' => '<div class="notice inline notice-warning">' .
					'<p>' . __( 'The following settings are available while Easy Digital Downloads is in debug mode. They are not designed to be primary settings and should be used only while debugging or when instructed to be used by the Easy Digital Downloads Team.', 'easy-digital-downloads' ) . '</p>' .
					'<p>' . __( 'There is no guarantee that these settings will remain available in future versions of Easy Digital Downloads. Easy Digital Downloads Debug Mode should be disabled once changes to these settings have been made.', 'easy-digital-downloads' ) . '</p>' .
				'</p></div>',
				'type' => 'descriptive_text',
			),
		);

		$card_elements_action       = 'enable-card-elements';
		$card_elements_button_label = __( 'Enable access to Card Elements', 'easy-digital-downloads' );
		$card_elements_state_label  = __( 'Access to Legacy Card Elements is Disabled', 'easy-digital-downloads' );
		$link_class                 = 'edd-button__toggle--disabled';
		if ( get_option( '_edds_legacy_elements_enabled', false ) ) {
			$card_elements_action       = 'disable-card-elements';
			$card_elements_button_label = __( 'Disable access to Card Elements', 'easy-digital-downloads' );
			$card_elements_state_label  = __( 'Access to Legacy Card Elements is Enabled', 'easy-digital-downloads' );
			$link_class                 = 'edd-button__toggle--enabled';
		}

		$debug_settings['stripe_toggle_card_elements'] = array(
			'id'   => 'stripe_toggle_card_elements',
			'name' => __( 'Toggle Card Elements', 'easy-digital-downloads' ),
			'type' => 'descriptive_text',
			'desc' => sprintf(
				'%1$s<span class="screen-reader-text">' . $card_elements_button_label . '</span>%2$s',
				'<a class="edd-button__toggle ' . $link_class . '" href="' . wp_nonce_url(
					edd_get_admin_url(
						array(
							'page'    => 'edd-settings',
							'tab'     => 'gateways',
							'section' => 'edd-stripe',
							'flag'    => $card_elements_action,
						)
					),
					$card_elements_action
				) . '">',
				'</a>'
			) . '<strong>' . $card_elements_state_label . '</strong><br />' . __( 'Card Elements is the legacy Stripe integration. Easy Digital Downloads has updated to use the more secure and reliable Payment Elements feature of Stripe. This toggle allows sites without access to Card Elements to enable or disable it.', 'easy-digital-downloads' ),
		);

		return $debug_settings;
	}

	/**
	 * Whether the Payment Elements mode is enabled.
	 *
	 * @since 3.3.5
	 * @return bool
	 */
	private function is_payment_elements_mode() {
		if ( is_null( $this->is_payment_elements_mode ) ) {
			$this->is_payment_elements_mode = 'payment-elements' === edds_get_elements_mode();
		}

		return $this->is_payment_elements_mode;
	}

	/**
	 * Whether the current screen is the gateway settings screen.
	 *
	 * @since 3.3.5
	 * @return bool
	 */
	private function is_gateway_settings_screen() {
		return edd_is_admin_page( 'settings', 'gateways' );
	}

	/**
	 * Gets the CSS class for the connected accounts.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	private function get_connected_class() {
		return $this->is_connected() ? '' : 'edd-hidden';
	}

	/**
	 * Gets the payment methods setting.
	 *
	 * @since 3.3.5
	 * @return array|false
	 */
	private function get_payment_methods_setting() {
		if ( ! $this->is_gateway_settings_screen() ) {
			return false;
		}

		if ( ! $this->is_connected() ) {
			return false;
		}

		$configuration = PaymentMethods::get_base_configuration();
		if ( ! $configuration ) {
			return false;
		}

		return array(
			'id'    => 'stripe_payment_methods',
			'name'  => __( 'Payment Methods', 'easy-digital-downloads' ),
			'type'  => 'hook',
			'class' => $this->is_payment_elements_mode() ? 'payment-elements-feature' : 'edd-hidden payment-elements-feature',
		);
	}

	/**
	 * Gets the payment method options.
	 *
	 * @since 3.3.5
	 * @param object $configuration The payment method configuration.
	 * @return array
	 */
	private static function get_payment_method_options( $configuration ) {
		$options             = array();
		$is_recurring_active = function_exists( 'edd_recurring' );
		$capabilities        = self::get_account_capabilities();
		$capability_keys     = wp_list_pluck( $capabilities, 'id' );
		$tooltip_args        = array(
			'dashicon' => 'dashicons-admin-site',
		);
		$is_test_mode        = edd_is_test_mode();
		foreach ( $configuration as $method => $parameters ) {
			$payment_method = PaymentMethods::get_payment_method( $method );
			if ( ! $payment_method ) {
				continue;
			}
			$payment_types = __( 'One-time payments', 'easy-digital-downloads' );
			if ( $payment_method::$subscriptions ) {
				$payment_types .= ', ' . __( 'subscriptions', 'easy-digital-downloads' );
			}
			if ( $payment_method::$trials ) {
				$payment_types .= ', ' . __( 'subscriptions with trials', 'easy-digital-downloads' );
			}
			$description = array(
				sprintf(
					/* translators: 1: opening strong tag, 2: closing strong tag, 3: payment types */
					__( '%1$sPayment types:%2$s %3$s', 'easy-digital-downloads' ),
					'<strong>',
					'</strong>',
					$payment_types
				),
			);
			if ( ! empty( $payment_method::$currencies ) ) {
				$description[] .= sprintf(
					/* translators: 1: opening strong tag, 2: closing strong tag, 3: supported currencies */
					__( '%1$sSupported currencies:%2$s %3$s', 'easy-digital-downloads' ),
					'<strong>',
					'</strong>',
					implode( ', ', $payment_method::$currencies )
				);
			}
			if ( ! empty( $payment_method::$countries ) ) {
				$description[] .= sprintf(
					/* translators: 1: opening strong tag, 2: closing strong tag, 3: supported countries */
					__( '%1$sSupported countries:%2$s %3$s', 'easy-digital-downloads' ),
					'<strong>',
					'</strong>',
					implode( ', ', array_map( 'strtoupper', $payment_method::$countries ) )
				);
			}

			$disabled = 'card' === $method || empty( $parameters['display_preference']['overridable'] );
			if ( in_array( "{$method}_payments", $capability_keys, true ) ) {
				$capability = $capabilities[ array_search( "{$method}_payments", $capability_keys, true ) ];
				if ( $capability instanceof \EDD\Vendor\Stripe\Capability && 'active' !== $capability->status ) {
					$disabled      = ! $is_test_mode;
					$description[] = $is_test_mode ?
						sprintf(
							/* translators: 1: opening strong tag, 2: closing strong tag */
							__( '%1$sStatus:%2$s You can test this, but to use it in Live mode, you must request access to this payment method from your Stripe account.', 'easy-digital-downloads' ),
							'<strong>',
							'</strong>'
						) :
						sprintf(
							/* translators: 1: opening strong tag, 2: closing strong tag */
							__( '%1$sStatus:%2$s You must request access to this payment method from your Stripe account.', 'easy-digital-downloads' ),
							'<strong>',
							'</strong>'
						);
				}
			}

			$options[ $method ] = array(
				'label'    => $payment_method::get_label(),
				'disabled' => $disabled,
				'checked'  => ! empty( $parameters['available'] ) && 'on' === $parameters['display_preference']['value'],
				'icon'     => $payment_method::get_icon(),
				'classes'  => self::get_payment_method_classes( $payment_method ),
			);

			if ( ! empty( $description ) ) {
				$tooltip_args['content']       = implode( '<br><br>', $description );
				$options[ $method ]['tooltip'] = $tooltip_args;
			}
		}

		return $options;
	}

	/**
	 * Gets the account capabilities.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	private static function get_account_capabilities() {
		$transient    = new \EDD\Utils\Transient( 'edd_stripe_account_capabilities', '+1 week' );
		$capabilities = $transient->get();
		if ( $capabilities ) {
			return $capabilities;
		}

		try {
			$stripe = edds_api_request(
				'account',
				'allCapabilities',
				edd_stripe()->connect->get_connect_id()
			);
		} catch ( \Exception $e ) {
			return array();
		}

		$transient->set( $stripe->data );

		return $stripe->data;
	}

	/**
	 * Gets the CSS classes for the payment method toggle.
	 *
	 * @since 3.3.5
	 * @param \EDD\Gateways\Stripe\PaymentMethods\Method $payment_method The payment method.
	 * @return array
	 */
	private static function get_payment_method_classes( $payment_method ) {
		$classes = array(
			'edd-stripe-payment-method',
		);
		if ( $payment_method::$scope ) {
			$classes[] = "edd-stripe-payment-method--{$payment_method::$scope}";
		}
		if ( $payment_method::$subscriptions ) {
			$classes[] = 'edd-stripe-payment-method--subscriptions';
		}
		if ( $payment_method::$trials ) {
			$classes[] = 'edd-stripe-payment-method--trials';
		}

		return $classes;
	}

	/**
	 * Whether the Stripe connection is active.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	private function is_connected(): bool {
		return edd_stripe()->connect() && edd_stripe()->connect->is_connected;
	}
}
