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

		$name = $this->license_data['name'] ?: __( 'license key', 'easy-digital-downloads' );

		switch ( $this->license_data['status'] ) {

			case 'expired':
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
				if ( $this->expiration ) {
					$message = sprintf(
						/* translators: 1. license expiration date; 2. opening link tag; 3. closing link tag. */
						__( 'Your license key expired on %1$s. Please %2$srenew your license key%3$s.', 'easy-digital-downloads' ),
						edd_date_i18n( $this->expiration ),
						'<a href="' . $url . '" target="_blank">',
						'</a>'
					);
				} else {
					$message = sprintf(
						/* translators: 1. opening link tag; 2. closing link tag. */
						__( 'Your license key has expired. Please %1$srenew your license key%2$s.', 'easy-digital-downloads' ),
						'<a href="' . $url . '" target="_blank">',
						'</a>'
					);
				}
				break;

			case 'revoked':
			case 'disabled':
				$url     = edd_link_helper(
					'https://easydigitaldownloads.com/support/',
					array(
						'utm_medium'  => 'license-notice',
						'utm_content' => 'revoked',
					)
				);
				$message = sprintf(
					/* translators: 1. opening link tag; 2. closing link tag. */
					__( 'Your license key has been disabled. Please %1$scontact support%2$s for more information.', 'easy-digital-downloads' ),
					'<a href="' . $url . '" target="_blank">',
					'</a>'
				);
				break;

			case 'missing':
				$url     = edd_link_helper(
					'https://easydigitaldownloads.com/your-account/',
					array(
						'utm_medium'  => 'license-notice',
						'utm_content' => 'missing',
					)
				);
				$message = sprintf(
					/* translators: 1. opening link tag; 2. closing link tag. */
					__( 'Invalid license. Please %1$svisit your account page%2$s and verify it.', 'easy-digital-downloads' ),
					'<a href="' . $url . '" target="_blank">',
					'</a>'
				);
				break;

			case 'site_inactive':
				$url     = edd_link_helper(
					'https://easydigitaldownloads.com/your-account/',
					array(
						'utm_medium'  => 'license-notice',
						'utm_content' => 'inactive',
					)
				);
				$message = sprintf(
					/* translators: 1. the extension name; 2. opening link tag; 3. closing link tag. */
					__( 'Your %1$s is not active for this URL. Please %2$svisit your account page%3$s to manage your license keys.', 'easy-digital-downloads' ),
					esc_html( $name ),
					'<a href="' . $url . '" target="_blank">',
					'</a>'
				);
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
				$url     = edd_link_helper(
					'https://easydigitaldownloads.com/your-account/',
					array(
						'utm_medium'  => 'license-notice',
						'utm_content' => 'at-limit',
					)
				);
				$message = sprintf(
					/* translators: 1. opening link tag; 2 closing link tag. */
					__( 'Your license key has reached its activation limit. %1$sView possible upgrades%2$s now.', 'easy-digital-downloads' ),
					'<a href="' . $url . '">',
					'</a>'
				);
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
}
