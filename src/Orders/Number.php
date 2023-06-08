<?php
/**
 * Class for getting/assigning order numbers.
 */
namespace EDD\Orders;

class Number {

	/**
	 * Whether core sequential order numbers are enabled.
	 *
	 * @since 3.1.2
	 * @var bool
	 */
	private $sequential;

	/**
	 * The order number prefix.
	 *
	 * @since 3.1.2
	 * @var string
	 */
	private $prefix = '';

	/**
	 * The order number postfix.
	 *
	 * @since 3.1.2
	 * @var string
	 */
	private $postfix = '';

	public function __construct() {
		$this->sequential = edd_get_option( 'enable_sequential', false );

		// If sequential order numbers are enabled, we need to make sure the prefix and suffix are loaded.
		if ( $this->sequential ) {
			$this->prefix  = $this->get_prefix();
			$this->postfix = $this->get_postfix();
		}
	}

	/**
	 * Gets the formatted order number; if sequential order numbers are enabled,
	 * this function also updates the last payment number in the database.
	 *
	 * @since 3.1.2
	 * @return string|bool A formatted order number, or false if sequential order numbers are disabled.
	 */
	public function apply() {
		if ( false === $this->sequential ) {
			return '';
		}

		$next_order_number = $this->get_next_payment_number();
		if ( ! $next_order_number ) {
			return '';
		}

		return $this->format( $next_order_number );
	}

	/**
	 * Gets the unformatted next order number from the database.
	 *
	 * @since 3.1.2
	 * @return false|int False if sequential order numbers are disabled, otherwise the next order number to apply.
	 */
	public function get_next_payment_number() {
		if ( false === $this->sequential ) {
			return false;
		}

		return (int) apply_filters( 'edd_get_next_payment_number', $this->get_next() );
	}

	/**
	 * Formats the order number with the sequential pre/postfixes.
	 *
	 * @since 3.1.2
	 * @param int $number
	 * @return string|int
	 */
	public function format( $number ) {

		if ( ! $this->sequential || ! is_numeric( $number ) ) {
			return $number;
		}

		$prefix  = $this->prefix;
		$number  = absint( $number );
		$postfix = $this->postfix;

		$formatted_number = $prefix . $number . $postfix;

		return apply_filters( 'edd_format_payment_number', $formatted_number, $prefix, $number, $postfix );
	}

	/**
	 * Given an order number, unformat it by removing the pre/postfix.
	 *
	 * @since 3.1.2
	 * @param string $number
	 * @return int
	 */
	public function unformat( $number ) {

		if ( ! $this->sequential ) {
			return $number;
		}

		$prefix  = $this->prefix;
		$postfix = $this->postfix;

		// Remove prefix
		$number = preg_replace( '/' . $prefix . '/', '', $number, 1 );

		// Remove the postfix
		$length      = strlen( $number );
		$postfix_pos = strrpos( $number, strval( $postfix ) );
		if ( false !== $postfix_pos ) {
			$number = substr_replace( $number, '', $postfix_pos, $length );
		}

		return apply_filters( 'edd_remove_payment_prefix_postfix', intval( $number ), $prefix, $postfix );
	}

	/**
	 * Gets the next order number from the database. This also updates the "next"
	 * order number in the database with the number which is being returned.
	 *
	 * @since 3.1.2
	 * @return int
	 */
	private function get_next() {
		global $wpdb;
		$number = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'edd_next_order_number' ) );

		// The next order number exists, so increment it and update the database.
		if ( ! is_null( $number ) ) {
			$number = (int) $number;

			// Update the option for the next order number now.
			$this->update( $number + 1 );

			return $number;
		}

		// If the option is not set for the next order number, we need to get the last order number from the database.
		$order_number = (int) $this->get_last();
		$next_number  = $order_number + 1;
		$this->insert( $next_number );

		return $order_number;
	}

	/**
	 * Updates the last order number in the database.
	 *
	 * This doesn't use $wpdb->update() and instead opts for using $wpdb->query() because
	 * in our testing we're a consistent improvment in performance. While it's measured in microseconds
	 * it is in the effort to remove any race condition we are running into here.
	 *
	 * @since 3.1.2
	 * @param int $value
	 * @return bool
	 */
	private function update( $value ) {
		global $wpdb;

		// We should never hit this....but just in case, we need to unformat it.
		if ( ! is_numeric( $value ) ) {
			$value = $this->unformat( $value );
		}

		$value = $wpdb->prepare( '%d', $value );

		return $wpdb->query(
			"UPDATE {$wpdb->options} SET option_value = {$value} WHERE option_name = 'edd_next_order_number'"
		);
	}

	/**
	 * Adds the last order number to the database.
	 *
	 * @since 3.1.2
	 * @param int $value
	 * @return bool
	 */
	private function insert( $value ) {
		global $wpdb;

		return $wpdb->insert(
			$wpdb->options,
			array(
				'option_name'  => 'edd_next_order_number',
				'option_value' => $value,
			),
			array( '%s', '%d' )
		);
	}

	/**
	 * Gets the last payment number from the database, or from the option.
	 *
	 * @return string
	 */
	private function get_last() {
		// If this was the first order after switching to useing the 'next' order number option, we need to get the last order number from the database.
		$last_payment_number = $this->get_last_payment_number();

		if ( ! is_null( $last_payment_number ) ) {
			return $last_payment_number + 1;
		}

		// If they enabled sequential order numbers after having orders, we need to get the last order number from the database.
		$last_order = edd_get_orders(
			array(
				'number'  => 1,
				'orderby' => 'id',
				'order'   => 'DESC',
			)
		);

		if ( ! empty( $last_order ) ) {
			$last_order = reset( $last_order );

			if ( $last_order instanceof EDD\Orders\Order && ! empty( $last_order->order_number ) ) {
				return $this->unformat( $last_order->order_number );
			}
		}

		// If all else fails, just get the starting number from the setting.
		return $this->get_start();
	}

	/**
	 * Gets the EDD sequential starting number.
	 * Used when the last order number cannot otherwise be found.
	 *
	 * @return int
	 */
	private function get_start() {
		return (int) edd_get_option( 'sequential_start', 1 );
	}

	/**
	 * Gets the sequential prefix.
	 *
	 * @since 3.1.2
	 * @return string
	 */
	private function get_prefix() {
		return (string) edd_get_option( 'sequential_prefix' );
	}

	/**
	 * Gets the sequential postfix.
	 *
	 * @since 3.1.2
	 * @return string
	 */
	private function get_postfix() {
		return (string) edd_get_option( 'sequential_postfix' );
	}

	/**
	 * Gets the last payment number from the database.
	 *
	 * @since 3.1.2
	 *
	 * @return int
	 */
	private function get_last_payment_number() {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'edd_last_payment_number' ) );
	}
}
