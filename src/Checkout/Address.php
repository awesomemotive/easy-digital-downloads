<?php
/**
 * Address handling for the checkout.
 *
 * @package     EDD\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Checkout\Registry;
use EDD\Forms\Handler;

/**
 * Address class.
 */
class Address {

	/**
	 * Address fields.
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Whether the address is being displayed in a block.
	 *
	 * @var bool
	 */
	public $is_block;

	/**
	 * Customer data.
	 *
	 * @var array
	 */
	private $customer = array(
		'address' => array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'zip'     => '',
			'state'   => '',
			'country' => '',
			'phone'   => '',
		),
	);

	/**
	 * Get the fields to display.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public function get_fields() {
		$fields = $this->set_fields();
		// If no fields are set, and the cart is taxed, default to full address.
		if ( empty( $fields ) && edd_is_cart_taxed() ) {
			$fields = array( 'country', 'address', 'address_2', 'city', 'state', 'zip' );
		}

		return array_intersect( $fields, Registry::get_allowed_fields() );
	}

	/**
	 * Render the address fields.
	 *
	 * @since 3.3.8
	 */
	public function render() {
		$fields = $this->get_fields_to_render();
		if ( false === $fields ) {
			return;
		}

		$this->get_customer();

		?>
		<fieldset id="edd_cc_address" class="<?php echo esc_attr( implode( ' ', $this->get_fieldset_classes( $fields ) ) ); ?>">
			<legend><?php esc_html_e( 'Billing Details', 'easy-digital-downloads' ); ?></legend>
			<?php
			do_action( 'edd_cc_billing_top', $this->customer );
			$registered_fields = Registry::get_fields();
			$allowed_fields    = Registry::get_allowed_fields();
			foreach ( $fields as $field ) {
				if ( ! in_array( $field, $allowed_fields, true ) || ! isset( $registered_fields[ $field ]['class'] ) ) {
					continue;
				}
				Handler::render_field( $registered_fields[ $field ]['class'], $this->customer );
			}
			do_action( 'edd_cc_billing_bottom' );
			wp_nonce_field( 'edd-checkout-address-fields', 'edd-checkout-address-fields-nonce', false, true );
			?>
		</fieldset>
		<?php
	}

	/**
	 * Sets up the customer data.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function set_up_customer() {
		$customer = EDD()->session->get( 'customer' );
		$customer = wp_parse_args(
			$customer,
			$this->customer
		);

		$customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

		if ( ! is_user_logged_in() ) {
			return $customer;
		}

		$user_address = edd_get_customer_address( get_current_user_id() );
		if ( empty( $user_address ) ) {
			return $customer;
		}

		foreach ( $customer['address'] as $key => $field ) {
			if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
				$customer['address'][ $key ] = $user_address[ $key ];
			}
		}

		return $customer;
	}

	/**
	 * Get the customer data.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private function get_customer() {
		$this->customer             = $this->set_up_customer();
		$this->customer['address']  = $this->filter_address( $this->customer['address'] );
		$this->customer['is_block'] = $this->is_block_checkout();

		return $this->customer;
	}

	/**
	 * Get the fields to display.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public function set_fields() {
		if ( ! empty( $this->fields ) ) {
			return $this->fields;
		}

		$this->fields = array_keys( array_filter( Registry::get_checkout_fields() ) );

		if ( array_intersect( array( 'zip', 'country' ), $this->fields ) ) {
			add_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );
		} elseif ( array_intersect( array( 'city', 'country', 'zip' ), $this->fields ) ) {
			add_filter( 'edd_require_billing_address', '__return_true' );
		}

		return $this->fields;
	}

	/**
	 * Filter the address.
	 *
	 * @since 3.3.8
	 * @param array $address The address.
	 * @return array
	 */
	private function filter_address( $address ) {
		/**
		 * Filter the billing address details that will be pre-populated on the checkout form.
		 *
		 * @since 3.3.8
		 *
		 * @param array $address The customer address.
		 * @param array $customer The customer data from the session
		 */
		return array_map( 'sanitize_text_field', apply_filters( 'edd_checkout_billing_details_address', $address, $this->customer ) );
	}

	/**
	 * Get the classes for the fieldset.
	 *
	 * @since 3.3.8
	 * @param array $fields The fields.
	 * @return array
	 */
	private function get_fieldset_classes( $fields ) {
		$classes = array( 'cc-address' );
		if ( $this->is_block_checkout() ) {
			$classes[] = 'edd-blocks-form';
		}

		$number_of_fields = count( $fields );
		if ( 1 === $number_of_fields ) {
			$field     = reset( $fields );
			$classes[] = "cc-address--{$field}";
		} elseif ( 2 === $number_of_fields ) {
			sort( $fields );
			$field_classes = implode( '-', $fields );
			$classes[]     = "cc-address--{$field_classes}";
		}

		if ( in_array( 'state', $fields, true ) && in_array( 'zip', $fields, true ) ) {
			$classes[] = 'cc-address--state-zip';
		}

		return $classes;
	}

	/**
	 * Checks if the checkout is a block.
	 * Currently this is used for managing the classes on the checkout form.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	private function is_block_checkout() {
		if ( ! is_null( $this->is_block ) ) {
			return $this->is_block;
		}

		$this->is_block = \EDD\Checkout\Validator::has_block();

		return $this->is_block;
	}

	/**
	 * Get the fields to render.
	 *
	 * @since 3.3.8
	 * @return array|false
	 */
	private function get_fields_to_render() {
		if ( did_action( 'edd_cc_billing_top' ) ) {
			return false;
		}

		$fields = $this->get_fields();

		return ! empty( $fields ) ? $fields : false;
	}
}
