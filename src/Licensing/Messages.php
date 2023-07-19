<?php
/**
 * Messages for license key activation results.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD\Licensing;

class Messages {

	/**
	 * The array of license data.
	 *
	 * @var array
	 */
	private $license_data = array();

	/**
	 * The license expiration as a timestamp, or false if no expiration.
	 *
	 * @var bool|int
	 */
	private $expiration = false;

	/**
	 * The current timestamp.
	 *
	 * @var int
	 */
	private $now;

	public function __construct( $license_data = array() ) {
		$this->license_data = wp_parse_args(
			$license_data,
			array(
				'status'       => '',
				'expires'      => '',
				'name'         => '',
				'license_key'  => '',
				'subscription' => false,
				'api_url'      => null,
				'uri'          => null,
			)
		);
		$this->now          = current_time( 'timestamp' );
		if ( ! empty( $this->license_data['expires'] ) && 'lifetime' !== $this->license_data['expires'] ) {
			if ( ! is_numeric( $this->license_data['expires'] ) ) {
				$this->expiration = strtotime( $this->license_data['expires'], $this->now );
			} else {
				$this->expiration = $this->license_data['expires'];
			}
		}
	}

	/**
	 * Gets the appropriate licensing message from an array of license data.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_message() {

		$message = $this->build_message();
		if ( ! $this->is_third_party_license() || empty( $this->license_data['name'] ) ) {
			return $message;
		}

		$name = $this->sanitize_third_party_name();
		/**
		 * Filters the message for a third-party license.
		 *
		 * Example: If your plugin name is "My Extension for Easy Digital Downloads" you
		 * would use the filter edd_licensing_third_party_message_my_extension_for_easy_digital_downloads
		 * @since 3.1.3
		 * @param string $message  The message.
		 * @param array  $data     The license data.
		 */
		return apply_filters( "edd_licensing_third_party_message_{$name}", $message, $this->license_data );
	}

	/**
	 * Builds the message based on the license data.
	 *
	 * @sinc 3.1.3
	 * @return string
	 */
	private function build_message() {
		$name = $this->license_data['name'] ?: __( 'license key', 'easy-digital-downloads' ); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		switch ( $this->license_data['status'] ) {

			case 'expired':
				$message = $this->get_expired_message();
				break;

			case 'revoked':
			case 'disabled':
				$message = $this->get_disabled_message();
				break;

			case 'missing':
				$message = $this->get_missing_message();
				break;

			case 'site_inactive':
				$message = $this->get_inactive_message();
				break;

			case 'invalid':
			case 'invalid_item_id':
			case 'item_name_mismatch':
			case 'key_mismatch':
				$message = sprintf(
					/* translators: the extension name. */
					__( 'This appears to be an invalid license key for %s.', 'easy-digital-downloads' ),
					$name
				);
				break;

			case 'no_activations_left':
				$message = $this->get_no_activations_message();
				break;

			case 'license_not_activable':
				$message = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'easy-digital-downloads' );
				break;

			case 'deactivated':
				$message = __( 'Your license key has been deactivated.', 'easy-digital-downloads' );
				break;

			case 'valid':
				$message = $this->get_valid_message();
				if ( $this->license_data['subscription'] && 'lifetime' !== $this->license_data['subscription'] ) {
					$message .= $this->get_subscription_message();
				}
				break;

			default:
				$message = __( 'Unlicensed: currently not receiving updates.', 'easy-digital-downloads' );
				break;
		}

		return $message;
	}

	/**
	 * Gets the message text for a valid license.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_valid_message() {
		if ( ! empty( $this->license_data['expires'] ) && 'lifetime' === $this->license_data['expires'] ) {
			return __( 'License key never expires.', 'easy-digital-downloads' );
		}

		if ( ( $this->expiration > $this->now ) && ( $this->expiration - $this->now < ( DAY_IN_SECONDS * 30 ) ) ) {
			return sprintf(
				/* translators: the license expiration date. */
				__( 'Your license key expires soon! It expires on %s.', 'easy-digital-downloads' ),
				edd_date_i18n( $this->expiration )
			);
		}

		return sprintf(
			/* translators: the license expiration date. */
			__( 'Your license key expires on %s.', 'easy-digital-downloads' ),
			edd_date_i18n( $this->expiration )
		);
	}

	/**
	 * Gets the message for a license's subscription.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_subscription_message() {
		if ( 'active' === $this->license_data['subscription'] ) {
			return ' ' . __( 'Your license subscription is active and will automatically renew.', 'easy-digital-downloads' );
		}

		return ' ' . sprintf(
			/* translators: the license subscription status. */
			__( 'Your license subscription is %s and will not automatically renew.', 'easy-digital-downloads' ),
			$this->get_subscription_status_label( $this->license_data['subscription'] )
		);
	}

	/**
	 * Gets the message for an expired license.
	 *
	 * @since 3.1.3
	 * @return string
	 */
	private function get_expired_message() {
		$url = $this->get_plugin_uri();
		if ( empty( $url ) && $this->is_third_party_license() ) {
			if ( $this->expiration ) {
				return sprintf(
					/* translators: 1. license expiration date. */
					__( 'Your license key expired on %1$s. Please renew your license key.', 'easy-digital-downloads' ),
					edd_date_i18n( $this->expiration )
				);
			}

			return __( 'Your license key has expired. Please renew your license key.', 'easy-digital-downloads' );
		}

		if ( empty( $url ) ) {
			$args = array(
				'utm_medium'  => 'license-notice',
				'utm_content' => 'expired',
			);
			if ( ! empty( $this->license_data['license_key'] ) ) {
				$args['license_key'] = $this->license_data['license_key'];
			}
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/checkout/',
				$args
			);
		}
		if ( $this->expiration ) {
			return sprintf(
				/* translators: 1. license expiration date; 2. opening link tag; 3. closing link tag. */
				__( 'Your license key expired on %1$s. Please %2$srenew your license key%3$s.', 'easy-digital-downloads' ),
				edd_date_i18n( $this->expiration ),
				'<a href="' . $url . '" target="_blank">',
				'</a>'
			);
		}

		return sprintf(
			/* translators: 1. opening link tag; 2. closing link tag. */
			__( 'Your license key has expired. Please %1$srenew your license key%2$s.', 'easy-digital-downloads' ),
			'<a href="' . $url . '" target="_blank">',
			'</a>'
		);
	}

	/**
	 * Gets the message for a disabled license.
	 *
	 * @since 3.1.3
	 * @return string
	 */
	private function get_disabled_message() {
		$url = $this->get_plugin_uri();
		if ( empty( $url ) && $this->is_third_party_license() ) {
			return __( 'Your license key has been disabled.', 'easy-digital-downloads' );
		}

		if ( empty( $url ) ) {
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/support/',
				array(
					'utm_medium'  => 'license-notice',
					'utm_content' => 'revoked',
				)
			);
		}

		return sprintf(
			/* translators: 1. opening link tag; 2. closing link tag. */
			__( 'Your license key has been disabled. Please %1$scontact support%2$s for more information.', 'easy-digital-downloads' ),
			'<a href="' . $url . '" target="_blank">',
			'</a>'
		);
	}

	/**
	 * Gets the message for a license at its activation limit.
	 *
	 * @since 3.1.3
	 * @return string
	 */
	private function get_no_activations_message() {
		$url = $this->get_plugin_uri();
		if ( empty( $url ) && $this->is_third_party_license() ) {
			return __( 'Your license key has reached its activation limit.', 'easy-digital-downloads' );
		}

		if ( empty( $url ) ) {
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/your-account/',
				array(
					'utm_medium'  => 'license-notice',
					'utm_content' => 'at-limit',
				)
			);
		}

		return sprintf(
			/* translators: 1. opening link tag; 2 closing link tag. */
			__( 'Your license key has reached its activation limit. %1$sView possible upgrades%2$s now.', 'easy-digital-downloads' ),
			'<a href="' . $url . '">',
			'</a>'
		);
	}

	/**
	 * Gets the message for an inactive license.
	 *
	 * @since 3.1.3
	 * @return string
	 */
	private function get_inactive_message() {
		$url = $this->get_plugin_uri();
		if ( empty( $url ) && $this->is_third_party_license() ) {
			return __( 'Your license key is not active for this URL.', 'easy-digital-downloads' );
		}

		if ( empty( $url ) ) {
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/your-account/',
				array(
					'utm_medium'  => 'license-notice',
					'utm_content' => 'inactive',
				)
			);
		}

		if ( empty( $this->license_data['name'] ) ) {
			return sprintf(
				/* translators: 1. opening link tag; 2. closing link tag. */
				__( 'Your license key is not active for this URL. Please %1$svisit your account page%2$s to manage your license keys.', 'easy-digital-downloads' ),
				'<a href="' . $url . '" target="_blank">',
				'</a>'
			);
		}

		return sprintf(
			/* translators: 1. the extension name; 2. opening link tag; 3. closing link tag. */
			__( 'Your %1$s license key is not active for this URL. Please %2$svisit your account page%3$s to manage your license keys.', 'easy-digital-downloads' ),
			esc_html( $this->license_data['name'] ),
			'<a href="' . $url . '" target="_blank">',
			'</a>'
		);
	}

	/**
	 * Gets the message for a missing license.
	 *
	 * @since 3.1.3
	 * @return string
	 */
	private function get_missing_message() {
		if ( $this->is_third_party_license() ) {
			return __( 'Invalid license. Please verify it.', 'easy-digital-downloads' );
		}

		$url = edd_link_helper(
			'https://easydigitaldownloads.com/your-account/',
			array(
				'utm_medium'  => 'license-notice',
				'utm_content' => 'missing',
			)
		);

		return sprintf(
			/* translators: 1. opening link tag; 2. closing link tag. */
			__( 'Invalid license. Please %1$svisit your account page%2$s and verify it.', 'easy-digital-downloads' ),
			'<a href="' . $url . '" target="_blank">',
			'</a>'
		);
	}

	/**
	 * Gets the subscription status label as a translatable string.
	 *
	 * @since 3.1.1
	 * @param string $status
	 * @return string
	 */
	private function get_subscription_status_label( $status ) {
		$statii = array(
			'pending'   => __( 'pending', 'easy-digital-downloads' ),
			'active'    => __( 'active', 'easy-digital-downloads' ),
			'cancelled' => __( 'cancelled', 'easy-digital-downloads' ),
			'expired'   => __( 'expired', 'easy-digital-downloads' ),
			'trialling' => __( 'trialling', 'easy-digital-downloads' ),
			'failing'   => __( 'failing', 'easy-digital-downloads' ),
			'completed' => __( 'completed', 'easy-digital-downloads' ),
		);

		return array_key_exists( $status, $statii ) ? $statii[ $status ] : $status;
	}

	/**
	 * Whether the license is a third-party license.
	 *
	 * @since 3.1.3
	 * @return bool
	 */
	private function is_third_party_license() {
		return ! empty( $this->license_data['api_url'] );
	}

	/**
	 * Gets the custom plugin URI for a third-party license.
	 *
	 * @since 3.1.3
	 * @return string
	 */
	private function get_plugin_uri() {
		return $this->is_third_party_license() && ! empty( $this->license_data['uri'] ) ? $this->license_data['uri'] : '';
	}

	/**
	 * Sanitizes the third-party license name for use as a hook.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	private function sanitize_third_party_name() {
		$name = str_replace( ' ', '_', strtolower( $this->license_data['name'] ) );

		return preg_replace( '/[^a-zA-Z0-9_]/', '', $name );
	}
}
