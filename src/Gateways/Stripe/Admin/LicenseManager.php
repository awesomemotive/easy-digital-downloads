<?php
/**
 * Handles admin notices/notifications related to the Stripe license.
 *
 * @package EDD\Gateways\Stripe
 */

namespace EDD\Gateways\Stripe\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle the admin notifications.
 *
 * @since 3.2.0
 */
final class LicenseManager {

	/**
	 * The license object, if found.
	 *
	 * @var \EDD\Gateways\Stripe\License
	 */
	private $license;

	/**
	 * The class constructor.
	 *
	 * @since 3.2.1
	 */
	public function __construct() {
		add_action( 'edd_daily_scheduled_events', array( $this, 'check_license' ) );
		add_action( 'edd/license/deleted', array( $this, 'license_updated' ) );
		add_action( 'edd/license/saved', array( $this, 'license_updated' ) );
		add_action( 'admin_notices', array( $this, 'register_admin_notices' ) );
		add_action( 'edd_sales_summary_widget_after_orders', array( $this, 'do_dashboard_notice' ) );
		if ( get_transient( 'edd_stripe_check_license' ) || get_transient( 'edds_stripe_check_license' ) ) {
			add_action( 'admin_init', array( $this, 'check_license' ) );
		}
	}

	/**
	 * Checks the license during the daily cron.
	 *
	 * @since 3.2.0
	 */
	public function check_license() {
		if ( ! $this->are_requirements_met() ) {
			return;
		}
		$notifications_to_dismiss = array();
		$notifications_to_reset   = array();
		$license                  = $this->get_license();
		if ( $license->is_license_valid() ) {
			$notifications_to_dismiss[] = 'edds-missing';
		} else {
			$notifications_to_reset[] = 'edds-missing';
		}
		if ( $license->is_expired() ) {
			if ( $license->is_in_grace_period() ) {
				$notifications_to_dismiss[] = 'edds-expired';
				$notifications_to_reset[]   = 'edds-grace';
			} else {
				$notifications_to_reset[]   = 'edds-expired';
				$notifications_to_dismiss[] = 'edds-grace';
			}
		}
		if ( $license->is_expiring_soon() ) {
			$notifications_to_reset[] = 'edds-expiring';
		} else {
			$notifications_to_dismiss[] = 'edds-expiring';
		}
		if ( ! empty( $notifications_to_reset ) ) {
			$this->update_notifications( $notifications_to_reset, 0 );
		}
		if ( ! empty( $notifications_to_dismiss ) ) {
			$this->update_notifications( $notifications_to_dismiss, 1 );
		}
	}

	/**
	 * When the license is updated, refresh the license object. and run the license check.
	 *
	 * @since 3.2.0
	 * @param \EDD\Licensing\License $license The license object.
	 * @return void
	 */
	public function license_updated( $license ) {
		if ( ! $this->should_check_license( $license ) ) {
			return;
		}
		$this->get_license( true );
		$this->check_license();
	}

	/**
	 * Registers admin notices for critical license issues, which show in addition to the notification.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function register_admin_notices() {
		if ( edd_is_pro() ) {
			return;
		}
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		if ( function_exists( 'edd_is_admin_page' ) && ! edd_is_admin_page() ) {
			return;
		}
		if ( ! $this->should_show_warnings() ) {
			return;
		}

		$license           = $this->get_license();
		$admin_notice_args = array(
			'id'   => 'missing',
			'type' => 'error',
		);
		if ( $license->is_in_grace_period() ) {
			$admin_notice_args['id']   = 'grace';
			$admin_notice_args['type'] = 'warning';
		} elseif ( $license->is_expired() ) {
			$admin_notice_args['id'] = 'expired';
		}

		$this->do_admin_notice( $admin_notice_args );
	}

	/**
	 * Outputs a notice on the dashboard if the license is not active.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function do_dashboard_notice() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		if ( ! $this->should_show_warnings() ) {
			return;
		}

		$license = $this->get_license();
		$message = sprintf(
			/* translators: %1$s is the opening link tag; %2$s is the closing link tag. */
			__( 'Your license is not active. Please %1$sactivate your license%2$s.', 'easy-digital-downloads' ),
			'<a href="' . $license->get_licensing_url() . '">',
			'</a>'
		);
		if ( $license->is_in_grace_period() || $license->is_expired() ) {
			$message = sprintf(
				/* translators: %1$s is the opening link tag; %2$s is the closing link tag. */
				__( 'Your license has expired. Please %1$srenew your license%2$s.', 'easy-digital-downloads' ),
				'<a href="' . $license->get_renewal_url( 'expired' ) . '" target="_blank">',
				'</a>'
			);
		}

		?>
		<div class="edd-dashboard-notice edd-dashboard-notice--error">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Whether a licensing change should trigger a license check.
	 *
	 * @param \EDD\Licensing\License $license The EDD License object.
	 * @return bool
	 */
	private function should_check_license( $license ) {
		if ( ! $this->are_requirements_met() ) {
			return false;
		}
		if ( ! empty( $license->item_id ) && 167 === (int) $license->item_id ) {
			return true;
		}
		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( $pass_manager::pass_compare( $pass_manager->highest_pass_id, $pass_manager::EXTENDED_PASS_ID, '>=' ) ) {
			return true;
		}
		if ( $pass_manager->hasExtendedPass() || $pass_manager->hasAllAccessPass() ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets the license object.
	 *
	 * @param bool $force Whether to force a new license object.
	 * @return \EDD\Gateways\Stripe\License
	 */
	private function get_license( $force = false ) {
		if ( is_null( $this->license ) || $force ) {
			$this->license = new \EDD\Gateways\Stripe\License();
		}

		return $this->license;
	}

	/**
	 * Updates the dismissed status of the given notifications.
	 * If the notification does not exist, it is ignored.
	 *
	 * @since 3.2.0
	 * @param array $notifications The notifications to update.
	 * @param int   $dismissed     Whether the notifications are dismissed.
	 */
	private function update_notifications( array $notifications, int $dismissed ) {
		if ( empty( $notifications ) ) {
			return;
		}
		$dismissed        = (int) (bool) $dismissed;
		$notifications_db = new \EDD\Database\NotificationsDB();
		foreach ( $notifications as $remote_id ) {
			$notification = $notifications_db->get_item_by( 'remote_id', $remote_id );
			if ( $notification ) {
				if ( (int) $notification->dismissed !== $dismissed ) {
					if ( $dismissed ) {
						$notifications_db->update( $notification->id, array( 'dismissed' => $dismissed ) );
					} else {
						$notifications_db->maybe_add_local_notification(
							$this->get_notification_by_remote_id( $remote_id )
						);
					}
				}
			} elseif ( empty( $dismissed ) ) {
				$notifications_db->maybe_add_local_notification(
					$this->get_notification_by_remote_id( $remote_id )
				);
			}
		}
	}

	/**
	 * Gets the notification by remote ID.
	 *
	 * @since 3.2.0
	 * @param string $remote_id The remote ID of the notification.
	 * @return array
	 */
	private function get_notification_by_remote_id( $remote_id ) {
		$license       = $this->get_license();
		$license_name  = $license->is_pass_license ? __( 'Easy Digital Downloads (Pro)', 'easy-digital-downloads' ) : __( 'Easy Digital Downloads - Stripe Pro Payment Gateway', 'easy-digital-downloads' );
		$notifications = array(
			'edds-missing'  => array(
				'remote_id' => 'edds-missing',
				'title'     => __( 'Easy Digital Downloads - Stripe Pro Payment Gateway Is Not Fully Activated!', 'easy-digital-downloads' ),
				'content'   => __( 'Activate your license key to receive important security and feature updates and remove application fees.', 'easy-digital-downloads' ),
				'buttons'   => array(
					array(
						'type' => 'primary',
						'url'  => $license->get_licensing_url(),
						'text' => __( 'Complete Activation', 'easy-digital-downloads' ),
					),
					array(
						'type' => 'secondary',
						'url'  => 'https://easydigitaldownloads.com/downloads/stripe-gateway/',
						'text' => __( 'Learn More', 'easy-digital-downloads' ),
					),
				),
				'type'      => 'warning',
			),
			'edds-grace'    => array(
				'remote_id' => 'edds-grace',
				'title'     => sprintf(
					/* translators: %s is the name of the license. */
					__( 'Your %s license has expired!', 'easy-digital-downloads' ),
					$license_name
				),
				'content'   => sprintf(
					/* translators: %s is the date the grace period ends. */
					__( 'Renew your license before %s to continue using Stripe without paying additional fees and to continue receiving important security and feature updates.', 'easy-digital-downloads' ),
					$license->get_grace_period_end_date()
				),
				'buttons'   => array(
					array(
						'type' => 'primary',
						'url'  => $license->get_renewal_url( 'expiring' ),
						'text' => __( 'Renew License', 'easy-digital-downloads' ),
					),
					array(
						'type' => 'secondary',
						'url'  => 'https://easydigitaldownloads.com/downloads/stripe-gateway/',
						'text' => __( 'Learn More', 'easy-digital-downloads' ),
					),
				),
				'type'      => 'warning',
			),
			'edds-expired'  => array(
				'remote_id' => 'edds-expired',
				'title'     => sprintf(
					/* translators: %s is the name of the license. */
					__( 'Your %s license has expired!', 'easy-digital-downloads' ),
					$license_name
				),
				'content'   => __( 'You are now paying additional fees with every Stripe transaction. You are no longer receiving important security and feature updates for Stripe Pro.', 'easy-digital-downloads' ),
				'buttons'   => array(
					array(
						'type' => 'primary',
						'url'  => $license->get_renewal_url( 'expired' ),
						'text' => __( 'Renew License', 'easy-digital-downloads' ),
					),
					array(
						'type' => 'secondary',
						'url'  => '',
						'text' => __( 'Learn More', 'easy-digital-downloads' ),
					),
				),
				'type'      => 'warning',
			),
			'edds-expiring' => array(
				'remote_id' => 'edds-expiring',
				'title'     => sprintf(
					/* translators: %s is the name of the license. */
					__( 'Your %s License Is Expiring Soon!', 'easy-digital-downloads' ),
					$license_name
				),
				'content'   => sprintf(
					/* translators: 1. the name of the license; 2. the date the license expires. */
					__( 'Your %1$s license is set to expire on %2$s. An active license key is required to create and edit payment forms, enable automatic updates, and to keep Easy Digital Downloads - Stripe Pro Payment Gateway fully activated.', 'easy-digital-downloads' ),
					$license_name,
					$license->get_expiration_date()
				),
				'buttons'   => array(
					array(
						'type' => 'primary',
						'url'  => $license->get_renewal_url( 'expiring' ),
						'text' => __( 'Renew License', 'easy-digital-downloads' ),
					),
					array(
						'type' => 'secondary',
						'url'  => 'https://easydigitaldownloads.com/downloads/stripe-gateway/',
						'text' => __( 'Learn More', 'easy-digital-downloads' ),
					),
				),
				'type'      => 'warning',
			),
		);

		return $notifications[ $remote_id ];
	}

	/**
	 * Outputs an admin notice.
	 *
	 * @since 3.2.0
	 * @param array $args The arguments.
	 * @return void
	 */
	private function do_admin_notice( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'             => '',
				'is_dismissible' => true,
				'type'           => 'info',
			)
		);
		if ( empty( $args['id'] ) ) {
			return;
		}
		$registry = edds_get_registry( 'admin-notices' );
		$registry->add(
			$args['id'],
			array(
				'type'        => $args['type'],
				'dismissible' => $args['is_dismissible'],
				'message'     => $this->get_message( $args['id'] ),
			)
		);
		wp_enqueue_script( 'edds-admin-notices' );
		$notices = new \EDD_Stripe_Admin_Notices( $registry );
		$notices->output( $args['id'] );
	}

	/**
	 * Gets the message for an admin notice.
	 *
	 * @since 3.2.0
	 * @param string $id The notification ID.
	 * @return string
	 */
	private function get_message( $id ) {
		$notification = $this->get_notification_by_remote_id( "edds-{$id}" );
		$message      = array(
			'<strong>' . $notification['title'] . '</strong>',
			$notification['content'],
		);
		if ( ! empty( $notification['buttons'] ) ) {
			$button    = reset( $notification['buttons'] );
			$message[] = sprintf(
				'<a href="%s" class="button button-%s">%s</a>',
				$button['url'],
				$button['type'],
				$button['text']
			);
		}
		$message = array_map( 'wpautop', $message );

		return implode( '', $message );
	}

	/**
	 * Whether the requirements are met to show the admin notices.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function are_requirements_met() {

		// Not connected (always false).
		if ( empty( edd_stripe()->connect()->get_connect_id() ) ) {
			return false;
		}

		// Not in country that supports the fees (always false).
		if ( true !== edds_stripe_connect_account_country_supports_application_fees() ) {
			return false;
		}

		return edd_is_gateway_active( 'stripe' );
	}

	/**
	 * Whether the admin warnings should be shown.
	 *
	 * @since 3.2.1
	 * @return bool
	 */
	private function should_show_warnings() {

		// If Stripe is not connected and active, don't show the warnings.
		if ( ! $this->are_requirements_met() ) {
			return false;
		}

		$license = $this->get_license();

		// If the requirements are met to remove the application fee, don't show the warnings.
		if ( ! edd_stripe()->application_fee->has_application_fee() ) {
			// (Unless the license is in a grace period).
			return $license->is_in_grace_period();
		}

		// There is an application fee, but Stripe Pro is active, so show the warnings.
		if ( edds_is_pro() ) {
			return true;
		}

		// There isn't a Stripe qualified license, but it's EDD Lite.
		if ( empty( $license->license_data->key ) && ! edd_is_pro() ) {
			return false;
		}

		// There is pass license active, but it's not for a pass that includes Stripe.
		if ( ! empty( $license->pass_id ) ) {
			return false;
		}

		return true;
	}
}
