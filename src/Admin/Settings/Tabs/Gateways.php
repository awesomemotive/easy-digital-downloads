<?php
/**
 * Easy Digital Downloads Gateway Settings
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

/**
 * Gateway settings tab.
 *
 * @since 3.1.4
 */
class Gateways extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @var string
	 */
	protected $id = 'gateways';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {

		$gateways = edd_get_payment_gateways();

		return array(
			'main'       => array(
				'test_mode'       => $this->get_test_mode(),
				'gateways'        => array(
					'id'      => 'gateways',
					'name'    => __( 'Active Gateways', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the payment gateways you want to enable.', 'easy-digital-downloads' ),
					'type'    => 'gateways',
					'options' => $gateways,
				),
				'default_gateway' => array(
					'id'      => 'default_gateway',
					'name'    => __( 'Default Gateway', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the gateway your checkout will use by default.<br />If you choose Automatic, the first enabled gateway from the Active Gateways will be used.', 'easy-digital-downloads' ),
					'type'    => 'gateway_select',
					'options' => $gateways,
				),
				'accepted_cards'  => array(
					'id'      => 'accepted_cards',
					'name'    => __( 'Payment Method Icons', 'easy-digital-downloads' ),
					'desc'    => __( 'Display icons for the selected payment methods.', 'easy-digital-downloads' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards.', 'easy-digital-downloads' ),
					'type'    => 'payment_icons',
					'options' => apply_filters(
						'edd_accepted_payment_icons',
						array(
							'mastercard'      => 'Mastercard',
							'visa'            => 'Visa',
							'americanexpress' => 'American Express',
							'discover'        => 'Discover',
							'paypal'          => 'PayPal',
						)
					),
				),
			),
			'checkout'   => array(
				'enforce_ssl'         => array(
					'id'    => 'enforce_ssl',
					'name'  => __( 'Enforce SSL on Checkout', 'easy-digital-downloads' ),
					'check' => __( 'Enforced', 'easy-digital-downloads' ),
					'desc'  => __( 'Redirect all customers to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_description',
				),
				'redirect_on_add'     => array(
					'id'            => 'redirect_on_add',
					'name'          => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
					'desc'          => __( 'Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads' ),
					'type'          => 'checkbox',
					'tooltip_title' => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'When enabled, once an item has been added to the cart, the customer will be redirected directly to your checkout page. This is useful for stores that sell single items.', 'easy-digital-downloads' ),
				),
				'logged_in_only'      => array(
					'id'            => 'logged_in_only',
					'name'          => __( 'Require Login', 'easy-digital-downloads' ),
					'desc'          => __( 'Require that users be logged-in to purchase files.', 'easy-digital-downloads' ),
					'type'          => 'checkbox',
					'tooltip_title' => __( 'Require Login', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'You can require that customers create and login to user accounts prior to purchasing from your store by enabling this option. When unchecked, users can purchase without being logged in by using their name and email address.', 'easy-digital-downloads' ),
				),
				'show_register_form'  => array(
					'id'      => 'show_register_form',
					'name'    => __( 'Show Register / Login Form', 'easy-digital-downloads' ),
					'desc'    => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'std'     => 'none',
					'options' => array(
						'both'         => __( 'Registration and Login Forms', 'easy-digital-downloads' ),
						'registration' => __( 'Registration Form Only', 'easy-digital-downloads' ),
						'login'        => __( 'Login Form Only', 'easy-digital-downloads' ),
						'none'         => __( 'None', 'easy-digital-downloads' ),
					),
				),
				'enable_cart_saving'  => array(
					'id'            => 'enable_cart_saving',
					'name'          => __( 'Enable Cart Saving', 'easy-digital-downloads' ),
					'desc'          => __( 'Check this to enable cart saving on the checkout.', 'easy-digital-downloads' ),
					'type'          => 'checkbox',
					'tooltip_title' => __( 'Cart Saving', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Cart saving allows shoppers to create a temporary link to their current shopping cart so they can come back to it later, or share it with someone.', 'easy-digital-downloads' ),
				),
				'geolocation'         => array(
					'id'       => 'geolocation',
					'name'     => __( 'Geolocation Detection', 'easy-digital-downloads' ),
					'desc'     => $this->get_geolocation_description(),
					'type'     => 'select',
					'options'  => array(
						'' => __( 'Disabled', 'easy-digital-downloads' ),
					),
					'disabled' => true,
				),
				'moderation_settings' => array(
					'id'            => 'moderation_settings',
					'name'          => '<h3>' . __( 'Moderation', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Moderation', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'It is sometimes necessary to temporarily prevent certain potential customers from checking out. Use these settings to control who can make purchases.', 'easy-digital-downloads' ),
				),
				'banned_emails'       => array(
					'id'          => 'banned_emails',
					'name'        => __( 'Banned Emails', 'easy-digital-downloads' ),
					'desc'        => __( 'Emails placed in the box above will not be allowed to make purchases.', 'easy-digital-downloads' ) . '<br>' . __( 'One per line, enter: email addresses, domains (<code>@example.com</code>), or TLDs (<code>.gov</code>).', 'easy-digital-downloads' ),
					'type'        => 'textarea',
					'placeholder' => __( '@example.com', 'easy-digital-downloads' ),
				),
			),
			'refunds'    => array(
				'refunds_settings' => array(
					'id'            => 'refunds_settings',
					'name'          => '<h3>' . __( 'Refunds', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Refunds', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'As a shop owner, sometimes refunds are necessary. Use these settings to decide how refunds will work in your shop.', 'easy-digital-downloads' ),
				),
				'refundability'    => array(
					'id'      => 'refundability',
					'name'    => __( 'Default Status', 'easy-digital-downloads' ),
					'desc'    => __( 'This will be the store default. It can be changed at a per-product level.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'std'     => 'refundable',
					'options' => edd_get_refundability_types(),
				),
				'refund_window'    => array(
					'id'   => 'refund_window',
					'name' => __( 'Refund Window', 'easy-digital-downloads' ),
					'desc' => __( 'Number of days (after a sale) when refunds can be processed.<br>Default is <code>30</code> days. Set to <code>0</code> for infinity. It can be changed at a per-product level.', 'easy-digital-downloads' ),
					'std'  => 30,
					'type' => 'number',
					'size' => 'small',
					'max'  => 3650, // Ten year maximum, because why explicitly support longer
					'min'  => 0,
					'step' => 1,
				),
			),
			'accounting' => $this->get_accounting_settings(),
		);
	}

	/**
	 * Get the test mode setting.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_test_mode() {

		$test_mode = array(
			'id'    => 'test_mode',
			'name'  => __( 'Test Mode', 'easy-digital-downloads' ),
			'check' => __( 'Enabled', 'easy-digital-downloads' ),
			'desc'  => __( 'While test mode is enabled, no live transactions are processed.<br>Use test mode in conjunction with the sandbox/test account for the payment gateways to test.', 'easy-digital-downloads' ),
			'type'  => 'checkbox_description',
		);
		// If test_mode is being forced to true, alter the setting so it cannot be modified.
		if ( ! edd_is_test_mode_forced() ) {
			return $test_mode;
		}

		return array_merge(
			array(
				'options'       => array(
					'disabled' => true,
					'readonly' => true,
				),
				'tooltip_title' => __( 'Forced Test Mode', 'easy-digital-downloads' ),
				'tooltip_desc'  => __( 'You currently cannot modify the Test Mode setting, as the \'EDD_TEST_MODE\' constant has been defined as \'true\' or the edd_is_test_mode filter is being forced to \'true\'.', 'easy-digital-downloads' ),
			),
			$test_mode
		);
	}

	/**
	 * Gets the accounting settings.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_accounting_settings() {
		$settings = array(
			'enable_skus'        => array(
				'id'    => 'enable_skus',
				'name'  => __( 'Enable SKU Entry', 'easy-digital-downloads' ),
				'check' => __( 'Check this box to allow entry of product SKUs.', 'easy-digital-downloads' ),
				'desc'  => __( 'SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
				'type'  => 'checkbox_description',
			),
			'enable_sequential'  => array(
				'id'    => 'enable_sequential',
				'name'  => __( 'Enable Sequental Numbering', 'easy-digital-downloads' ),
				'check' => __( 'Check this box to enable sequential order numbers.', 'easy-digital-downloads' ),
				'desc'  => __( 'Does not impact previous orders. Future orders will be sequential.', 'easy-digital-downloads' ),
				'type'  => 'checkbox_description',
			),
			'sequential_start'   => $this->get_sequential_start(),
			'sequential_prefix'  => array(
				'id'   => 'sequential_prefix',
				'name' => __( 'Sequential Number Prefix', 'easy-digital-downloads' ),
				'desc' => __( 'A prefix to prepend to all sequential order numbers.', 'easy-digital-downloads' ),
				'type' => 'text',
			),
			'sequential_postfix' => array(
				'id'   => 'sequential_postfix',
				'name' => __( 'Sequential Number Postfix', 'easy-digital-downloads' ),
				'desc' => __( 'A postfix to append to all sequential order numbers.', 'easy-digital-downloads' ),
				'type' => 'text',
			),
		);

		if ( ! defined( 'EDD_SON_VERSION' ) ) {
			$settings['sequential_help'] = array(
				'id'   => 'sequential_help',
				'name' => __( 'Advanced Order Numbers', 'easy-digital-downloads' ),
				'desc' => $this->get_sequential_help_text(),
				'type' => 'descriptive_text',
			);
		}

		return $settings;
	}

	/**
	 * Gets the sequential starting number setting.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_sequential_start() {

		$setting = array(
			'id'   => 'sequential_start',
			'name' => __( 'Sequential Starting Number', 'easy-digital-downloads' ),
			'desc' => __( 'The number at which the sequence should begin.', 'easy-digital-downloads' ),
			'type' => 'number',
			'size' => 'small',
			'std'  => 1,
		);

		if ( (bool) get_option( 'edd_next_order_number' ) ) {
			$order_number    = new \EDD\Orders\Number();
			$setting['desc'] = __( 'Once sequential order numbering is active, the starting number cannot be updated to an order number that\'s smaller than the highest order number. Update this with care.', 'easy-digital-downloads' ) .
				'<br />' .
				sprintf(
					/* translators: %s: next order number, wrapped in code tags */
					__( 'The next order number will be %s.', 'easy-digital-downloads' ),
					'<code>' . $order_number->format( get_option( 'edd_next_order_number' ) ) . '</code>'
				);
		}

		return $setting;
	}

	/**
	 * Gets the sequential help text.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	private function get_sequential_help_text() {
		$text     = __( 'Gain access to even more control over your order numbering!', 'easy-digital-downloads' );
		$benefits = array(
			__( 'Track free orders in a separate sequential series', 'easy-digital-downloads' ),
			__( 'Assign temporary numbers to incomplete orders', 'easy-digital-downloads' ),
			__( 'Abandoned orders do not interrupt the complete order series', 'easy-digital-downloads' ),
		);
		$text    .= '<ul class="edd-settings__list--disc">';
		foreach ( $benefits as $benefit ) {
			$text .= '<li>' . $benefit . '</li>';
		}
		$text .= '</ul>';

		if ( ! edd_is_pro() ) {

			$url = edd_link_helper(
				'https://easydigitaldownloads.com/lite-upgrade/',
				array(
					'utm_medium'  => 'accounting-settings',
					'utm_content' => 'upgrade-to-pro',
				)
			);

			$text .= sprintf( '<a href="%s" target="_blank" class="edd-pro-upgrade">' . __( 'Upgrade to Pro', 'easy-digital-downloads' ) . '</a>', $url );
		} else {
			$text .= sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
				__( 'Access %1$sAdvanced Sequential Order Numbers%2$s today.', 'easy-digital-downloads' ),
				'<a href="' . esc_url(
					edd_get_admin_url(
						array(
							'page'   => 'edd-addons',
							'filter' => 'sequential',
						)
					)
				) . '">',
				'</a>'
			);
		}

		return $text;
	}

	/**
	 * Get the geolocation description.
	 *
	 * @since 3.2.8
	 * @return string
	 */
	private function get_geolocation_description() {
		if ( ! empty( $this->get_pass_id() ) ) {
			$settings_url = add_query_arg(
				array(
					'page' => 'edd-settings',
				),
				edd_get_admin_url()
			);

			return sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
				__( 'GeoLocation Detection is only available in Easy Digital Downloads Pro. %1$sVerify your pass to get access to pro features.%2$s', 'easy-digital-downloads' ),
				'<a href="' . esc_url( $settings_url ) . '">',
				'</a>'
			);
		}

		$upgrade_link = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade',
			array(
				'utm_medium'  => 'settings',
				'utm-content' => 'geolocation',
			)
		);

		return sprintf(
			/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
			__( 'Increase conversions by auto-filling address information for customers during checkout. To enable GeoLocation Dectection, %1$sUpgrade to Pro%2$s.', 'easy-digital-downloads' ),
			'<a href="' . $upgrade_link . '" class="edd-pro-upgrade" target="_blank">',
			'</a>'
		);
	}
}
