<?php
/**
 * Email Verification Handler
 *
 * Handles user email verification resend functionality with rate limiting.
 *
 * @package     EDD\Users
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.3
 */

namespace EDD\Users;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Verification class.
 *
 * @since 3.5.3
 */
class Verification implements SubscriberInterface {

	/**
	 * Maximum number of verification emails that can be sent.
	 *
	 * @since 3.5.3
	 * @var int
	 */
	const MAX_EMAILS = 5;

	/**
	 * Cooldown period in seconds between resends.
	 *
	 * @since 3.5.3
	 * @var int
	 */
	const COOLDOWN_SECONDS = 120;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.5.3
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_resend_verification_email' => 'ajax_resend_verification_email',
			'wp_ajax_edd_check_verification_status' => 'ajax_check_verification_status',
			'edd_resend_verification_email_admin'   => 'process_admin_verification_resend',
			'edd_modal_rendered'                    => 'enqueue_scripts',
		);
	}

	/**
	 * Render the verification button and modal.
	 *
	 * @since 3.5.3
	 * @return void
	 */
	public static function render() {
		?>
		<p class="edd-account-pending">
			<button type="button" class="button edd-submit edd-verification-resend-button" id="edd-verification-resend">
				<?php esc_html_e( 'Resend Verification Email', 'easy-digital-downloads' ); ?>
			</button>
		</p>

		<?php
		\EDD\Utils\Modal::render(
			'verification',
			array(
				'title' => __( 'Resend Verification Email', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Enqueue scripts and styles for user verification.
	 *
	 * @since 3.5.3
	 * @return void
	 */
	public function enqueue_scripts( $type = '' ) {
		if ( empty( $type ) || 'verification' !== $type ) {
			return;
		}

		if ( ! is_user_logged_in() || ! edd_user_pending_verification() ) {
			return;
		}

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		// Enqueue verification-specific script.
		wp_enqueue_script(
			'edd-user-verification',
			edd_get_assets_url( 'js/frontend/' ) . 'user-verification.js',
			array( 'edd-modal' ),
			EDD_VERSION,
			true
		);

		// Localize script with necessary data.
		wp_localize_script(
			'edd-user-verification',
			'eddVerification',
			array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'edd-verification-resend' ),
				'masked_email'  => $this->mask_email( $user->user_email ),
				'user_id'       => $user_id,
				'countdown'     => self::COOLDOWN_SECONDS,
				'poll_interval' => 15,
				'strings'       => array(
					'sending'      => __( 'Sending verification email...', 'easy-digital-downloads' ),
					'sent'         => __( 'Verification email sent!', 'easy-digital-downloads' ),
					/* translators: %s: masked email address */
					'sent_to'      => __( 'A verification email has been sent to %s', 'easy-digital-downloads' ),
					'check_email'  => __( 'Please check your email and click the verification link.', 'easy-digital-downloads' ),
					'resend_email' => __( 'Resend Email', 'easy-digital-downloads' ),
					'verifying'    => __( 'Checking verification status...', 'easy-digital-downloads' ),
					'verified'     => __( 'Email verified! Redirecting...', 'easy-digital-downloads' ),
					'error'        => __( 'An error occurred. Please try again.', 'easy-digital-downloads' ),
					'close'        => __( 'Close', 'easy-digital-downloads' ),
				),
			)
		);
	}

	/**
	 * Handle AJAX request to resend verification email.
	 *
	 * @since 3.5.3
	 * @return void
	 */
	public function ajax_resend_verification_email() {
		// Verify nonce.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd-verification-resend' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security verification failed.', 'easy-digital-downloads' ),
				)
			);
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in.', 'easy-digital-downloads' ),
				)
			);
		}

		$user_id = get_current_user_id();

		// Check if user is pending verification.
		if ( ! edd_user_pending_verification( $user_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Your account is already verified.', 'easy-digital-downloads' ),
				)
			);
		}

		// Check rate limits.
		$rate_limit_check = $this->check_rate_limits( $user_id );
		if ( is_wp_error( $rate_limit_check ) ) {
			wp_send_json_error(
				array(
					'message'        => $rate_limit_check->get_error_message(),
					'seconds_remain' => $rate_limit_check->get_error_data(),
				)
			);
		}

		// Send the verification email.
		edd_send_user_verification_email( $user_id );

		// Update rate limit counters.
		$this->update_rate_limits( $user_id );

		$user = get_userdata( $user_id );

		wp_send_json_success(
			array(
				'message'      => sprintf(
					/* translators: %s: masked email address */
					__( 'Verification email sent to %s', 'easy-digital-downloads' ),
					$this->mask_email( $user->user_email )
				),
				'masked_email' => $this->mask_email( $user->user_email ),
			)
		);
	}

	/**
	 * Handle AJAX request to check verification status.
	 *
	 * @since 3.5.3
	 * @return void
	 */
	public function ajax_check_verification_status() {
		// Verify nonce.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd-verification-resend' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security verification failed.', 'easy-digital-downloads' ),
				)
			);
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in.', 'easy-digital-downloads' ),
				)
			);
		}

		$user_id = get_current_user_id();

		// Check if user is still pending verification.
		$is_pending = edd_user_pending_verification( $user_id );

		wp_send_json_success(
			array(
				'is_verified' => ! $is_pending,
			)
		);
	}

	/**
	 * Resolve the WordPress account a customer record belongs to.
	 *
	 * Returns the customer's own user ID when it is already set. Otherwise, falls back to the
	 * account that owns the customer's email addresses, since a guest customer's record is only
	 * claimed once that account verifies. An account that is already linked to another customer is
	 * never returned.
	 *
	 * @since 3.7.3
	 *
	 * @param \EDD_Customer $customer     The customer record.
	 * @param bool          $pending_only Whether to resolve only an account pending verification.
	 * @return int The resolved user ID, or 0 if none is found.
	 */
	public static function resolve_customer_user_id( $customer, $pending_only = false ) {
		if ( ! $customer instanceof \EDD_Customer ) {
			return 0;
		}

		$user_id = (int) $customer->user_id;

		if ( ! $user_id ) {
			$user_id = self::get_user_id_by_customer_emails( $customer );

			if ( $user_id && edd_get_customer_by( 'user_id', $user_id ) ) {
				return 0;
			}
		}

		if ( $user_id && $pending_only && ! edd_user_pending_verification( $user_id ) ) {
			return 0;
		}

		return $user_id;
	}

	/**
	 * Process admin verification resend.
	 *
	 * @since 3.5.3
	 * @since 3.7.3 Resolves the account through the customer's email when the record
	 *                       is not yet claimed, rather than refusing a pending customer outright.
	 * @return void
	 */
	public function process_admin_verification_resend() {
		// Verify nonce.
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edd-resend-verification' ) ) {
			wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		// Check permissions.
		if ( ! current_user_can( edd_get_edit_customers_role() ) ) {
			wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
		}

		// Get customer.
		if ( empty( $_GET['id'] ) ) {
			wp_die( __( 'Invalid customer ID.', 'easy-digital-downloads' ) );
		}

		$customer = edd_get_customer_by( 'id', absint( $_GET['id'] ) );

		if ( ! $customer ) {
			wp_die( __( 'Customer not found.', 'easy-digital-downloads' ) );
		}

		$user_id = self::resolve_customer_user_id( $customer );

		if ( ! $user_id ) {
			wp_die( __( 'Customer does not have a user account.', 'easy-digital-downloads' ) );
		}

		// Check if user is pending verification.
		if ( ! edd_user_pending_verification( $user_id ) ) {
			$url = edd_get_admin_url(
				array(
					'page'        => 'edd-customers',
					'view'        => 'overview',
					'id'          => absint( $customer->id ),
					'edd-message' => 'user-already-verified',
				)
			);

			edd_redirect( $url );
			return;
		}

		// Send the verification email.
		edd_send_user_verification_email( $user_id );

		// Redirect with success message.
		$url = edd_get_admin_url(
			array(
				'page'        => 'edd-customers',
				'view'        => 'overview',
				'id'          => absint( $customer->id ),
				'edd-message' => 'verification-email-sent',
			)
		);

		edd_redirect( $url );
	}

	/**
	 * Gets the WordPress user that owns the customer's email addresses.
	 *
	 * Every address on the customer is checked, secondary ones included. Only members of the current
	 * site count on multisite, and when the addresses belong to more than one user there is no single
	 * owner, so nothing is returned.
	 *
	 * @since 3.7.3
	 *
	 * @param \EDD_Customer $customer The customer record.
	 * @return int The user ID, or 0 if none is found or more than one user owns the addresses.
	 */
	private static function get_user_id_by_customer_emails( $customer ) {
		if ( ! $customer instanceof \EDD_Customer ) {
			return 0;
		}

		$user_ids = array();
		foreach ( $customer->emails as $email ) {
			$user = get_user_by( 'email', $email );

			if ( ! $user instanceof \WP_User ) {
				continue;
			}

			if ( is_multisite() && ! is_user_member_of_blog( $user->ID ) ) {
				continue;
			}

			$user_ids[ $user->ID ] = (int) $user->ID;
		}

		return 1 === count( $user_ids ) ? reset( $user_ids ) : 0;
	}

	/**
	 * Check rate limits for verification email resends.
	 *
	 * @since 3.5.3
	 * @param int $user_id User ID.
	 * @return true|\WP_Error True if allowed, WP_Error with seconds remaining if rate limited.
	 */
	private function check_rate_limits( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new \WP_Error( 'invalid_user', __( 'Invalid user.', 'easy-digital-downloads' ) );
		}

		// Check cooldown.
		$cooldown_key = 'edd_verification_resend_cooldown_' . $user_id;
		$cooldown     = get_transient( $cooldown_key );

		if ( false !== $cooldown ) {
			$seconds_remaining = $cooldown - time();
			if ( $seconds_remaining > 0 ) {
				return new \WP_Error(
					'rate_limited',
					sprintf(
						/* translators: %s: number of seconds wrapped in span */
						__( 'Please wait %s seconds before requesting another verification email.', 'easy-digital-downloads' ),
						'<span class="edd-countdown__seconds">' . $seconds_remaining . '</span>'
					),
					$seconds_remaining
				);
			}
		}

		// Check max emails sent using LogEmail query.
		$count = $this->get_verification_email_count( $user_id );

		if ( $count >= self::MAX_EMAILS ) {
			return new \WP_Error(
				'max_emails_reached',
				__( 'Maximum verification emails sent. Please contact support if you need further assistance.', 'easy-digital-downloads' ),
				0
			);
		}

		return true;
	}

	/**
	 * Update rate limit counters after sending email.
	 *
	 * @since 3.5.3
	 * @param int $user_id User ID.
	 * @return void
	 */
	private function update_rate_limits( $user_id ) {
		$cooldown_key = 'edd_verification_resend_cooldown_' . $user_id;
		set_transient( $cooldown_key, time() + self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS );
	}

	/**
	 * Get count of verification emails sent to an email address.
	 *
	 * @since 3.5.3
	 * @param int $user_id User ID.
	 * @return int Number of verification emails sent.
	 */
	private function get_verification_email_count( $user_id ): int {
		$query = new \EDD\Database\Queries\LogEmail(
			array(
				'email_id'    => 'user_verification',
				'object_id'   => $user_id,
				'object_type' => 'user',
				'count'       => true,
			)
		);

		return absint( $query->found_items );
	}

	/**
	 * Mask email address for display.
	 *
	 * Format: c*****@test.com
	 *
	 * @since 3.5.3
	 * @param string $email Email address to mask.
	 * @return string Masked email address.
	 */
	private function mask_email( $email ): string {
		if ( ! is_email( $email ) ) {
			return $email;
		}

		$parts = explode( '@', $email );

		if ( count( $parts ) !== 2 ) {
			return $email;
		}

		$local_part = $parts[0];
		$domain     = $parts[1];

		// Get first character and mask the rest.
		$first_char = substr( $local_part, 0, 1 );
		$masked     = $first_char . '*****';

		return $masked . '@' . $domain;
	}
}
