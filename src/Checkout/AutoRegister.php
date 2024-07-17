<?php

namespace EDD\Checkout;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\Subscriber;

/**
 * Class AutoRegister
 *
 * @since 3.3.0
 * @package EDD\Checkout
 */
class AutoRegister extends Subscriber {

	/**
	 * Whether the feature is enabled.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	private static $is_enabled;

	/**
	 * Gets the events this subscriber should be subscribed to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_built_order'                          => 'create_user_and_add_to_order',
			'edd_free_downloads_post_complete_payment' => 'create_user_and_add_to_order',
			'edd_post_add_manual_order'                => array( 'insert_user_during_manual_order', 10, 3 ),
			'edd_get_option_show_register_form'        => 'remove_register_form',
			'edd_settings_marketing'                   => array( 'update_free_downloads_settings', 100 ),
			'edd_batch_import_order_created'           => 'create_user_during_import',
			'edd_checkout_user_error_checks'           => array( 'check_existing_user', 10, 3 ),
		);
	}

	/**
	 * Determines if the auto registration is enabled.
	 *
	 * @since 3.3.0
	 * @return bool Returns true if the method can be executed, false otherwise.
	 */
	public static function is_enabled() {
		if ( is_null( self::$is_enabled ) || edd_is_doing_unit_tests() ) {
			self::$is_enabled = 'auto' === edd_get_option( 'logged_in_only', false );
		}

		return self::$is_enabled;
	}

	/**
	 * Hide the registration form on checkout.
	 * This is a legacy method from the original Auto Register plugin.
	 *
	 * @since 3.3.0
	 * @param string $value The value of the `show_register_form` option.
	 */
	public function remove_register_form( $value ) {
		if ( ! self::is_enabled() ) {
			return $value;
		}

		if ( 'both' === $value ) {
			return 'login';
		}
		if ( 'registration' === $value ) {
			return 'none';
		}

		return $value;
	}

	/**
	 * Maybe registers the user.
	 * Legacy method from the original Auto Register plugin.
	 *
	 * @since 3.3.0
	 * @param int $order_id The order ID.
	 * @return void
	 */
	public function create_user_and_add_to_order( $order_id ) {
		if ( ! self::is_enabled() ) {
			return;
		}
		if ( is_user_logged_in() ) {
			return;
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order->email ) ) {
			return;
		}

		$this->maybe_remove_user_registration_actions( $order->email, $order_id );

		$user_id = $this->maybe_create_user( $order_id );
		if ( $user_id ) {
			$this->assign_user_to_order( $user_id, $order_id );
		}
	}

	/**
	 * Updates the settings for Free Downloads.
	 *
	 * @since 3.3.0
	 * @param array $settings The settings array.
	 * @return array
	 */
	public function update_free_downloads_settings( $settings ) {
		if ( ! function_exists( 'edd_free_downloads' ) ) {
			return $settings;
		}
		if ( ! self::is_enabled() ) {
			return $settings;
		}
		if ( empty( $settings['free_downloads'] ) ) {
			return $settings;
		}
		foreach ( $settings['free_downloads'] as $key => $setting ) {
			if ( ! empty( $setting['id'] ) && 'edd_free_downloads_bypass_auto_register' === $setting['id'] ) {
				$settings['free_downloads'][ $key ]['tooltip_desc'] = __( 'Your site registers a new user account when an order is placed. You may allow free downloads without account creation.', 'easy-digital-downloads' );
			}
		}

		return $settings;
	}

	/**
	 * Adds a new user when an order is manually created in EDD 3.0.
	 * This is a legacy method from the original Auto Register plugin.
	 *
	 * @since 3.3.0
	 * @param int   $order_id   The order ID.
	 * @param array $order_data The array of order data.
	 * @param array $args       The original form data.
	 * @return void
	 */
	public function insert_user_during_manual_order( $order_id, $order_data, $args ) {
		if ( empty( $args['edd-new-customer'] ) ) {
			return;
		}
		if ( ! self::is_enabled() ) {
			return;
		}

		$user_id = $this->maybe_create_user( $order_id );
		if ( $user_id ) {
			$this->assign_user_to_order( $user_id, $order_id );
		}
	}

	/**
	 * Creates a user account during payment import.
	 * This is a legacy method from the original Auto Register plugin.
	 *
	 * @since 3.3.0
	 * @param int $order_id   The order ID.
	 * @return void
	 */
	public function create_user_during_import( $order_id ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		remove_action( 'edd_customer_post_attach_payment', 'edd_connect_guest_customer_to_existing_user' );
		remove_action( 'edd_insert_user', 'edd_new_user_notification' );
		remove_action( 'user_register', 'edd_add_past_purchases_to_new_user' );
		add_filter( 'edd_auto_register_login_user', '__return_false' );

		$this->maybe_create_user( $order_id );
	}

	/**
	 * Processes the supplied payment data to possibly register a user
	 *
	 * @since 3.3.0
	 * @param int   $order_id      The order ID.
	 * @param array $purchase_data The purchase data.
	 * @return int|false The User ID created false if the insert fails.
	 */
	protected function maybe_create_user( $order_id = 0, $purchase_data = array() ) {
		if ( ! self::is_enabled() ) {
			return false;
		}
		$order_data = $this->get_order_data( $order_id, $purchase_data );
		if ( ! $this->can_create_user( $order_data ) ) {
			return false;
		}

		/**
		 * Allow developers to modify the user data before it's inserted.
		 *
		 * @since 3.3.0
		 * @param array $user_data The user data.
		 * @param int   $order_id  The order ID.
		 * @param array $order_data The order data. Note that this is different than the $purchase_data array.
		 */
		$user_data = apply_filters(
			'edd_auto_register_insert_user_args',
			array(
				'user_login' => $order_data['user_name'],
				'user_pass'  => wp_generate_password( 32 ),
				'user_email' => $order_data['email'],
				'first_name' => $order_data['first_name'],
				'last_name'  => $order_data['last_name'],
			),
			$order_id,
			$order_data
		);

		// Insert new user.
		if ( $this->should_log_user_in() ) {
			$user_id = edd_register_and_login_new_user( $user_data );
		} else {
			$user_id = wp_insert_user( $user_data );
			if ( ! is_wp_error( $user_id ) ) {
				do_action( 'edd_insert_user', $user_id, $user_data );
			}
		}

		// Depending on how the user was added, the user ID may be a WP_Error object, empty, or -1.
		if ( is_wp_error( $user_id ) || empty( $user_id ) || $user_id < 0 ) {
			return false;
		}

		$customer = edd_get_customer_by( 'email', $order_data['email'] );
		if ( $customer ) {
			$customer->update( array( 'user_id' => $user_id ) );
		}

		return $user_id;
	}

	/**
	 * Checks if a user already exists during checkout.
	 *
	 * @since 3.3.0
	 * @param mixed $user       The user object.
	 * @param array $valid_data The valid data.
	 * @param array $posted     The posted data.
	 * @return void
	 */
	public function check_existing_user( $user, $valid_data, $posted ) {
		if ( is_user_logged_in() || empty( $user ) || ! self::is_enabled() ) {
			return;
		}
		$email = false;
		if ( ! empty( $valid_data['guest_user_data']['user_email'] ) ) {
			$email = $valid_data['guest_user_data']['user_email'];
		} elseif ( ! empty( $posted['edd_email'] ) ) {
			$email = $posted['edd_email'];
		}
		if ( ! $email ) {
			return;
		}

		if ( get_user_by( 'email', $email ) ) {
			edd_set_error( 'email_used', __( 'Email already used. Login or use a different email to complete your purchase.', 'easy-digital-downloads' ) );
		}
	}

	/**
	 * Whether a new user account can be created for an order.
	 *
	 * @since 3.3.0
	 * @param array $data The order data to use for validation.
	 * @return bool
	 */
	private function can_create_user( $data ) {

		if ( empty( $data['user_name'] ) || ! self::is_enabled() ) {
			return false;
		}

		// If there is already a user ID assigned to the order, do not create a new user.
		if ( ! empty( $data['order']->user_id ) ) {
			return false;
		}

		if ( ! empty( $data['email'] ) ) {
			$user = get_user_by( 'email', $data['email'] );
			// User account already exists.
			if ( $user instanceof WP_User ) {
				// For multisite, associate the user with the site.
				if ( is_multisite() ) {
					add_user_to_blog( get_current_blog_id(), $user->ID, get_option( 'default_role' ) );
				}
				return false;
			}
		}

		// Username already exists.
		if ( username_exists( $data['user_name'] ) ) {
			return false;
		}

		/**
		 * Allow developers to modify whether a user can be created.
		 *
		 * @since 3.3.0
		 * @param bool                         Whether the user can be registered.
		 * @param \EDD\Orders\Order $order     The order object.
		 * @param string            $user_name The new user name that's been checked.
		 */
		$can_create_user = apply_filters( 'edd_can_create_user_for_order', true, $data['order'], $data['user_name'] );
		if ( has_filter( 'edd_auto_register_can_create_user' ) ) {
			_deprecated_hook( 'edd_auto_register_can_create_user', '3.3.0', 'edd_can_create_user_for_order' );
			$can_create_user = apply_filters( 'edd_auto_register_can_create_user', $can_create_user, edd_get_payment( $data['order']->id ), $data['user_name'] );
		}

		return $can_create_user;
	}

	/**
	 * Whether the user should be logged in after registration.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function should_log_user_in() {
		if ( is_user_logged_in() || ! self::is_enabled() ) {
			return false;
		}
		$maybe_login_user = false;
		if ( did_action( 'edd_purchase' ) || did_action( 'edd_straight_to_gateway' ) || did_action( 'edd_free_download_process' ) ) {
			$maybe_login_user = true;
		}

		return apply_filters( 'edd_auto_register_login_user', $maybe_login_user );
	}

	/**
	 * Assigns a user to an order.
	 *
	 * @since 3.3.0
	 * @param int $user_id  The user ID.
	 * @param int $order_id The order ID.
	 * @return bool
	 */
	private function assign_user_to_order( $user_id, $order_id ) {
		return edd_update_order(
			$order_id,
			array(
				'user_id' => $user_id,
			)
		);
	}

	/**
	 * Removes EDD core's user registration actions for the user's initial purchase.
	 *
	 * @since 3.3.0
	 * @param string $email    The customer's email address.
	 * @param int    $order_id The current order ID.
	 * @return void
	 */
	private function maybe_remove_user_registration_actions( $email, $order_id ) {

		$customer = edd_get_customer_by( 'email', $email );
		if ( ! $customer ) {
			return;
		}
		$orders = edd_count_orders(
			array(
				'customer_id' => $customer->id,
				'type'        => 'sale',
				'id__not_in'  => array( $order_id ),
				'status__in'  => edd_get_complete_order_statuses(),
			)
		);

		// If the new order is the only order, remove the actions that would otherwise create a new user.
		if ( empty( $orders ) ) {
			remove_action( 'user_register', 'edd_connect_existing_customer_to_new_user' );
			remove_action( 'user_register', 'edd_add_past_purchases_to_new_user' );
		}
	}

	/**
	 * Gets the order data for a given order ID or purchase data to validate user creation.
	 *
	 * @since 3.3.0
	 * @param int   $order_id      The order ID.
	 * @param array $purchase_data The purchase data.
	 * @return array|bool
	 */
	private function get_order_data( $order_id, $purchase_data = array() ) {
		$order_data = array(
			'order'      => false,
			'email'      => '',
			'user_name'  => '',
			'first_name' => '',
			'last_name'  => '',
		);
		if ( ! empty( $order_id ) ) {
			$order = edd_get_order( $order_id );
			if ( empty( $order->customer_id ) ) {
				return false;
			}
			$order_data['order']     = $order;
			$order_data['email']     = $order->email;
			$order_data['user_name'] = sanitize_user( $order->email );
			$customer                = edd_get_customer( $order->customer_id );
			if ( ! empty( $customer->name ) ) {
				$names                    = explode( ' ', $customer->name );
				$order_data['first_name'] = array_shift( $names );
				$order_data['last_name']  = implode( ' ', $names );
			}

			return $order_data;
		}

		if ( ! empty( $purchase_data['user_info']['email'] ) ) {
			$order_data['email']     = $purchase_data['user_info']['email'];
			$order_data['user_name'] = sanitize_user( $purchase_data['user_info']['email'] );
		}
		if ( ! empty( $purchase_data['user_info']['first_name'] ) ) {
			$order_data['first_name'] = $purchase_data['user_info']['first_name'];
		}
		if ( ! empty( $purchase_data['user_info']['last_name'] ) ) {
			$order_data['last_name'] = $purchase_data['user_info']['last_name'];
		}

		return $order_data;
	}

	/**
	 * Checks if a user should be inserted and inserts the user if necessary.
	 * This is a legacy method from the original Auto Register plugin.
	 *
	 * @deprecated 3.3.0
	 * @param int  $payment_id The ID of the payment.
	 * @param bool $payment Optional. The payment object. Default is false.
	 */
	public function maybe_insert_user( $payment_id = 0, $payment = false ) {
		_edd_deprecated_function( __METHOD__, '3.3.0', 'EDD\\Checkout\\AutoRegister::create_user_during_import' );
	}

	/**
	 * Creates the user if necessary.
	 * If the order ID is provided, the user is created and assigned to the order.
	 * This method is needed for creating a user before the order is created (eg when purchasing
	 * a subscription with PayPal).
	 * This is a legacy method from the original Auto Register plugin.
	 *
	 * @since 3.3.0
	 * @param array $purchase_data The purchase data. Used when creating a user before the order is created.
	 * @param int   $order_id      The order ID.
	 * @return int|bool The user ID if the user was created, false otherwise.
	 */
	public function create_user( $purchase_data = array(), $order_id = 0 ) {
		if ( ! self::is_enabled() ) {
			return false;
		}
		if ( is_user_logged_in() ) {
			return false;
		}
		if ( ! empty( $order_id ) ) {
			return $this->maybe_create_user( $order_id );
		}

		return $this->maybe_create_user( 0, $purchase_data );
	}
}
