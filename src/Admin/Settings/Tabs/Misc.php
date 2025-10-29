<?php
/**
 * Easy Digital Downloads Miscellaneous Settings
 *
 * @package     EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Misc settings tab class.
 *
 * @since 3.1.4
 */
class Misc extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 *
	 * @var string
	 */
	protected $id = 'misc';

	/**
	 * Misc constructor.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		parent::__construct();
		add_filter( "edd_settings_{$this->id}_sanitize", array( $this, 'sanitize' ) );
	}

	/**
	 * Updates the documentation link.
	 *
	 * @since 3.3.0
	 * @param string $link The current documentation link.
	 * @return string
	 */
	public function update_docs_link( $link ) {
		if ( $this->is_admin_page( 'settings', 'misc' ) && 'file_downloads' === $this->get_section() ) {
			return 'https://easydigitaldownloads.com/docs/misc-settings/';
		}

		return parent::update_docs_link( $link );
	}

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {

		$settings = array(
			'main'           => array(
				'debug_mode'          => array(
					'id'    => 'debug_mode',
					'name'  => __( 'Debug Mode', 'easy-digital-downloads' ),
					'check' => __( 'Record important information to the debug log while troubleshooting.', 'easy-digital-downloads' ) . ' ' . $this->get_debug_log_link(),
					'type'  => 'checkbox_toggle',
				),
				'session_handling'    => array(
					'id'      => 'session_handling',
					'name'    => __( 'Session Handling', 'easy-digital-downloads' ),
					'type'    => 'select',
					'std'     => get_option( 'edd_session_handling', 'php' ),
					'options' => array(
						'php' => __( 'PHP Sessions', 'easy-digital-downloads' ),
						'db'  => __( 'Database Sessions', 'easy-digital-downloads' ),
					),
					'desc'    => __( 'Choose how you want to handle sessions. PHP based sessions are generally faster, but if you are experiencing issues with empty carts, database sessions may be more reliable.', 'easy-digital-downloads' ),
				),
				'disable_styles'      => array(
					'id'            => 'disable_styles',
					'name'          => __( 'Disable Styles', 'easy-digital-downloads' ),
					'check'         => __( 'Disable general EDD core styles for buttons, checkout fields, product pages, and other elements. EDD blocks will still load minimal styles.', 'easy-digital-downloads' ),
					'type'          => 'checkbox_toggle',
					'tooltip_title' => __( 'Disabling Styles', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( "If your theme has a complete custom CSS file for Easy Digital Downloads, you may wish to disable our default styles. This is not recommended unless you're sure your theme has a complete custom CSS.", 'easy-digital-downloads' ),
				),
				'item_quantities'     => array(
					'id'    => 'item_quantities',
					'name'  => __( 'Cart Item Quantities', 'easy-digital-downloads' ),
					/* translators: %s: Downloads plural label */
					'check' => sprintf( __( 'Allow quantities to be adjusted when adding %s to the cart, and while viewing the checkout cart.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ),
					'type'  => 'checkbox_toggle',
					'desc'  => '',
				),
				'uninstall_on_delete' => array(
					'id'    => 'uninstall_on_delete',
					'name'  => __( 'Remove Data on Uninstall', 'easy-digital-downloads' ),
					'check' => __( 'Completely remove all EDD core data when the plugin is deleted.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_toggle',
				),
			),
			'button_text'    => array(
				'button_style'         => array(
					'id'      => 'button_style',
					'name'    => __( 'Default Button Style', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the style you want to use for the buttons.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => edd_get_button_styles(),
				),
				'checkout_color'       => array(
					'id'      => 'checkout_color',
					'name'    => __( 'Default Button Color', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the color you want to use for the buttons.', 'easy-digital-downloads' ),
					'type'    => 'color_select',
					'options' => edd_get_button_colors(),
					'std'     => 'blue',
				),
				'checkout_label'       => array(
					'id'   => 'checkout_label',
					'name' => __( 'Complete Purchase Text', 'easy-digital-downloads' ),
					'desc' => __( 'The button label for completing a purchase.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Purchase', 'easy-digital-downloads' ),
				),
				'free_checkout_label'  => array(
					'id'   => 'free_checkout_label',
					'name' => __( 'Complete Free Purchase Text', 'easy-digital-downloads' ),
					'desc' => __( 'The button label for completing a free purchase.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Free Download', 'easy-digital-downloads' ),
				),
				'add_to_cart_text'     => array(
					'id'   => 'add_to_cart_text',
					'name' => __( 'Add to Cart Text', 'easy-digital-downloads' ),
					'desc' => __( 'Text shown on the Add to Cart Buttons.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Add to Cart', 'easy-digital-downloads' ),
				),
				'checkout_button_text' => array(
					'id'   => 'checkout_button_text',
					'name' => __( 'Checkout Button Text', 'easy-digital-downloads' ),
					'desc' => __( 'Text shown on the Add to Cart Button when the product is already in the cart.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => _x( 'Checkout', 'text shown on the Add to Cart Button when the product is already in the cart', 'easy-digital-downloads' ),
				),
				'buy_now_text'         => $this->get_buy_now_text(),
			),
			'file_downloads' => array(
				'require_login_to_download' => array(
					'id'            => 'require_login_to_download',
					'name'          => __( 'Require Login', 'easy-digital-downloads' ),
					'check'         => __( 'Require a user to login before file download links deliver the file.', 'easy-digital-downloads' ),
					'tooltip_title' => __( 'Require Login', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Download links expire after the link expiration setting, but you can restrict file downloads to only logged in users. Note: This may affect links from purchase receipts and customers if you have guest checkout enabled.', 'easy-digital-downloads' ),
					'type'          => 'checkbox_toggle',
				),
				'download_method'           => array(
					'id'            => 'download_method',
					'name'          => __( 'Download Method', 'easy-digital-downloads' ),
					'desc'          => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'easy-digital-downloads' ), edd_get_label_singular() ),
					'type'          => 'select',
					'tooltip_title' => __( 'Download Method', 'easy-digital-downloads' ),
					'tooltip_desc'  => _x(
						'Due to its consistency in multiple platforms and better file protection, \'forced\' is the default method. Because Easy Digital Downloads uses PHP to process the file with the \'forced\' method, larger files can cause problems with delivery, resulting in hitting the \'max execution time\' of the server. If users are getting 404 or 403 errors when trying to access their purchased files when using the \'forced\' method, changing to the \'redirect\' method can help resolve this.',
						"Tooltip Display: Quotations must use escaped single quotes, for example \'forced\'",
						'easy-digital-downloads'
					),
					'options'       => array(
						'direct'   => __( 'Forced', 'easy-digital-downloads' ),
						'redirect' => __( 'Redirect', 'easy-digital-downloads' ),
					),
				),
				'symlink_file_downloads'    => array(
					'id'   => 'symlink_file_downloads',
					'name' => __( 'Symbolically Link Files', 'easy-digital-downloads' ),
					'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads' ),
					'type' => 'checkbox',
				),
				'file_download_limit'       => array(
					'id'            => 'file_download_limit',
					'name'          => __( 'File Download Limit', 'easy-digital-downloads' ),
					/* translators: %s: Download singular label */
					'desc'          => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'easy-digital-downloads' ), edd_get_label_singular() ),
					'type'          => 'number',
					'size'          => 'small',
					'tooltip_title' => __( 'File Download Limits', 'easy-digital-downloads' ),
					/* translators: %s: Download singular label */
					'tooltip_desc'  => sprintf( __( 'Set the global default for the number of times a customer can download items they purchase. Using a value of 0 is unlimited. This can be defined on a %s-specific level as well. Download limits can also be reset for an individual purchase.', 'easy-digital-downloads' ), edd_get_label_singular( true ) ),
				),
				'download_link_expiration'  => array(
					'id'            => 'download_link_expiration',
					'name'          => __( 'Download Link Expiration', 'easy-digital-downloads' ),
					'desc'          => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'easy-digital-downloads' ),
					'tooltip_title' => __( 'Download Link Expiration', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'When a customer receives a link to their downloads via email, in their receipt, or in their purchase history, the link will only be valid for the timeframe (in hours) defined in this setting. Sending a new purchase receipt or visiting the account page will re-generate a valid link for the customer.', 'easy-digital-downloads' ),
					'type'          => 'number',
					'size'          => 'small',
					'std'           => '24',
					'min'           => '0',
				),
				'disable_redownload'        => array(
					'id'            => 'disable_redownload',
					'name'          => __( 'Limit File Access', 'easy-digital-downloads' ),
					'check'         => __( 'Only give customers access to download links right after they make a purchase.', 'easy-digital-downloads' ),
					'type'          => 'checkbox_toggle',
					'tooltip_title' => __( 'Limiting Access', 'easy-digital-downloads' ),
					'tooltip_desc'  => _x(
						'This will prevent customers from viewing download links on your site after their initial purchase session has expired. This does not restrict the number of times a file can be downloaded; you can set a limit on the number of times a user can download a file with the \'File Download Limit\' setting.',
						"It is important to escape any quotations within this string, specifically \'File Download Limit\'",
						'easy-digital-downloads'
					),
				),
			),
			'captcha'        => array(
				'recaptcha'            => array(
					'id'   => 'recaptcha',
					'name' => __( 'reCAPTCHA v3', 'easy-digital-downloads' ),
					'desc' => sprintf(
					/* translators: 1. opening anchor tag; 2. closing anchor tag */
						__( '%1$sRegister with Google%2$s to get reCAPTCHA v3 keys. Setting the keys here will enable reCAPTCHA on your registration block and when a user requests a password reset using the login block.', 'easy-digital-downloads' ),
						'<a href="https://www.google.com/recaptcha/admin#list" target="_blank">',
						'</a>'
					),
					'type' => 'descriptive_text',
				),
				'recaptcha_site_key'   => array(
					'id'   => 'recaptcha_site_key',
					'name' => __( 'reCAPTCHA Site Key', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => '',
				),
				'recaptcha_secret_key' => array(
					'id'   => 'recaptcha_secret_key',
					'name' => __( 'reCAPTCHA Secret Key', 'easy-digital-downloads' ),
					'type' => 'password',
					'std'  => '',
				),
				'recaptcha_checkout'   => $this->get_recaptcha_checkout_setting(),
			),
		);

		$rate_limiting = $this->get_recaptcha_rate_limiting_setting();
		if ( $rate_limiting ) {
			$settings['captcha']['recaptcha_rate_limiting'] = $rate_limiting;
		}

		return $settings;
	}

	/**
	 * Save the session handling setting.
	 *
	 * @since 3.3.0
	 * @param array $input The form data.
	 * @return array
	 */
	public function sanitize( $input ) {
		if ( empty( $input['session_handling'] ) ) {
			return $input;
		}

		update_option( 'edd_session_handling', $input['session_handling'] );
		unset( $input['session_handling'] );

		return $input;
	}

	/**
	 * Gets the buy now text setting.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_buy_now_text() {
		$text = array(
			'id'   => 'buy_now_text',
			'name' => __( 'Buy Now Text', 'easy-digital-downloads' ),
			'desc' => __( 'Text shown on the Buy Now Buttons.', 'easy-digital-downloads' ),
			'type' => 'text',
			'std'  => __( 'Buy Now', 'easy-digital-downloads' ),
		);

		if ( edd_shop_supports_buy_now() ) {
			return $text;
		}

		$text['disabled']      = true;
		$text['tooltip_title'] = __( 'Buy Now Disabled', 'easy-digital-downloads' );
		$text['tooltip_desc']  = __( 'Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'easy-digital-downloads' );

		return $text;
	}

	/**
	 * Gets the link for the debug log.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	private function get_debug_log_link() {
		$debug_log_url = edd_get_admin_url(
			array(
				'page' => 'edd-tools',
				'tab'  => 'debug_log',
			)
		);

		return '<a href="' . esc_url( $debug_log_url ) . '">' . __( 'View the Log', 'easy-digital-downloads' ) . '</a>';
	}

	/**
	 * Gets the recaptcha checkout setting.
	 *
	 * @since 3.5.3
	 * @return array
	 */
	private function get_recaptcha_checkout_setting(): array {
		$checkout_has_block = \EDD\Checkout\Validator::has_block();

		return array(
			'id'       => 'recaptcha_checkout',
			'name'     => __( 'reCAPTCHA on Checkout', 'easy-digital-downloads' ),
			'desc'     => $checkout_has_block ?
				__( 'Enable reCAPTCHA on the checkout block.', 'easy-digital-downloads' ) :
				__( 'This setting requires the checkout block and will not work with the shortcode.', 'easy-digital-downloads' ),
			'type'     => 'select',
			'options'  => array(
				''       => __( 'Never', 'easy-digital-downloads' ),
				'always' => __( 'Always', 'easy-digital-downloads' ),
				'guests' => __( 'Only for guests', 'easy-digital-downloads' ),
			),
			'disabled' => ! $checkout_has_block,
		);
	}

	/**
	 * Gets the recaptcha rate limiting setting.
	 *
	 * @since 3.5.3
	 * @return false|array
	 */
	private function get_recaptcha_rate_limiting_setting() {
		if ( ! \EDD\Checkout\Validator::has_block() || ! edd_is_gateway_active( 'stripe' ) ) {
			return false;
		}

		return array(
			'id'      => 'recaptcha_rate_limiting',
			'name'    => __( 'reCAPTCHA on Demand', 'easy-digital-downloads' ),
			'check'   => __( 'Enable reCAPTCHA on checkout when Stripe determines that your site is experiencing card testing.', 'easy-digital-downloads' ),
			'desc'    => __( 'If reCAPTCHA is always enabled on checkout, this setting is ignored. When needed, this will affect both guests and logged in users.', 'easy-digital-downloads' ),
			'type'    => 'checkbox_toggle',
			'options' => array(
				'disabled' => 'always' === edd_get_option( 'recaptcha_checkout' ),
			),
		);
	}
}
