<?php
/**
 * Tracking functions for reporting plugin usage to the EDD site for users that
 * have opted in.
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Usage tracking
 *
 * @since  1.8.2
 * @return void
 */
class EDD_Tracking {

	/**
	 * The data to send to the EDD site
	 *
	 * @access private
	 */
	private $data;

	/**
	 * The server URL to receive the telemetry data.
	 *
	 * @var string
	 */
	protected $telemetry_server = 'https://telemetry.easydigitaldownloads.com/v1/checkin/';

	/**
	 * Get things going
	 *
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'schedule_send' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		// Sanitize setting.
		add_action( 'edd_settings_general_sanitize', array( $this, 'check_for_settings_optin' ) );
		add_filter( 'edd_settings_misc', array( $this, 'register_setting' ), 50 );

		// Handle opting in and out.
		add_action( 'edd_opt_into_tracking', array( $this, 'check_for_optin'  ) );
		add_action( 'edd_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
	}

	/**
	 * Check if the user has opted into tracking.
	 *
	 * @return bool
	 */
	protected function tracking_allowed() {
		return (bool) edd_get_option( 'allow_tracking', false );
	}

	/**
	 * Setup the data that is going to be tracked.
	 *
	 * @access private
	 * @return void
	 */
	private function setup_data() {
		$data = new EDD\Telemetry\Data();

		$this->data = $data->get();
	}

	/**
	 * Send the data to the EDD server
	 *
	 * @access private
	 *
	 * @param  bool $override If we should override the tracking setting.
	 * @param  bool $ignore_last_checkin If we should ignore when the last check in was.
	 *
	 * @return bool
	 */
	public function send_checkin( $override = false, $ignore_last_checkin = false ) {

		if ( ! $this->can_send_data( $override, $ignore_last_checkin ) ) {
			return false;
		}

		$this->setup_data();

		if ( empty( $this->data ) ) {
			return false;
		}

		wp_remote_post(
			$this->telemetry_server,
			array(
				'method'      => 'POST',
				'timeout'     => 8,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => false,
				'body'        => $this->data,
				'user-agent'  => 'EDD/' . EDD_VERSION . '; ' . $this->data['id'],
			)
		);

		update_option( 'edd_tracking_last_send', time(), false );

		return true;
	}

	/**
	 * Check for a new opt-in on settings save
	 *
	 * This runs during the sanitation of General settings, thus the return
	 *
	 * @return array
	 */
	public function check_for_settings_optin( $input ) {

		// Send an intial check in on settings save
		if ( isset( $input['allow_tracking'] ) && $input['allow_tracking'] == 1 ) {
			$this->send_checkin( true );
		}

		return $input;
	}

	/**
	 * Adds the tracking setting to the miscellaneous settings section.
	 *
	 * @since 3.1.1
	 * @param array $settings
	 * @return array
	 */
	public function register_setting( $settings ) {
		$hidden = edd_get_option( 'allow_tracking', false ) ? '' : 'edd-hidden';

		$settings['main']['allow_tracking'] = array(
			'id'    => 'allow_tracking',
			'name'  => __( 'Join the EDD Community', 'easy-digital-downloads' ),
			'check' => __( 'Yes, I want to help!', 'easy-digital-downloads' ) . ' <span class="allow_tracking edd-heart ' . $hidden . '"><img src="' . esc_url( EDD_PLUGIN_URL . 'assets/images/icons/icon-edd-heart.svg' ) . '" alt="" class="emoji" /></span>',
			'desc'  => $this->get_telemetry_description(),
			'type'  => 'checkbox_description',
		);

		return $settings;
	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @return void
	 */
	public function check_for_optin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		edd_update_option( 'allow_tracking', 1 );

		$this->send_checkin( true );

		update_option( 'edd_tracking_notice', 1, false );
	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @return void
	 */
	public function check_for_optout() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		edd_delete_option( 'allow_tracking' );
		update_option( 'edd_tracking_notice', 1, false );
		edd_redirect( remove_query_arg( 'edd_action' ) );
	}

	/**
	 * Get the last time a checkin was sent
	 *
	 * @access private
	 * @return false|string
	 */
	private function get_last_send() {
		return get_option( 'edd_tracking_last_send' );
	}

	/**
	 * Schedule a weekly checkin
	 *
	 * We send once a week (while tracking is allowed) to check in, which can be
	 * used to determine active sites.
	 *
	 * @return void
	 */
	public function schedule_send() {
		if ( edd_doing_cron() ) {
			add_action( 'edd_weekly_scheduled_events', array( $this, 'send_checkin' ) );
		}
	}

	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @return void
	 */
	public function admin_notice() {
		static $once = null;

		// Only 1 notice.
		if ( ! is_null( $once ) ) {
			return;
		}

		// Already ran once.
		$once = true;

		// Bail if already noticed.
		if ( get_option( 'edd_tracking_notice' ) ) {
			return;
		}

		// Bail if already allowed.
		if ( edd_get_option( 'allow_tracking', false ) ) {
			return;
		}

		// Bail if user cannot decide.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// No notices for local installs.
		if ( edd_is_dev_environment() ) {
			update_option( 'edd_tracking_notice', 1, false );
			return;
		}

		if ( edd_is_admin_page() && ! edd_is_admin_page( 'index.php' ) && ! edd_is_insertable_admin_page() ) {

			// Add the notice.
			EDD()->notices->add_notice(
				array(
					'id'             => 'edd-allow-tracking',
					'class'          => 'updated',
					'message'        => $this->get_admin_notice_message(),
					'is_dismissible' => false,
				)
			);
		}
	}

	/**
	 * Build the admin notice message.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_admin_notice_message() {

		return array(
			'<strong>' . __( 'Join the EDD Community', 'easy-digital-downloads' ) . '</strong>',
			$this->get_telemetry_description(),
			sprintf(
				'<a href="%s" class="button button-primary">%s</a> <a href="%s" class="button button-secondary">%s</a>',
				esc_url( add_query_arg( 'edd_action', 'opt_into_tracking' ) ),
				__( 'Allow', 'easy-digital-downloads' ),
				esc_url( add_query_arg( 'edd_action', 'opt_out_of_tracking' ) ),
				__( 'Do not allow', 'easy-digital-downloads' )
			),
		);
	}

	/**
	 * Gets the telemetry description.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_telemetry_description() {

		return __( 'Help us provide a better experience and faster fixes by sharing some anonymous data about how you use Easy Digital Downloads.', 'easy-digital-downloads' ) .
			' ' .
			sprintf(
				/* translators: %1$s Link to tracking information, do not translate. %2$s clsoing link tag, do not translate */
				__( '%1$sHere is what we track.%2$s', 'easy-digital-downloads' ),
				'<a href="' . edd_link_helper( 'https://easydigitaldownloads.com/docs/what-information-will-be-tracked-by-opting-into-usage-tracking/', array( 'utm_medium' => 'telemetry', 'utm_content' => 'option' ) ) . '" target="_blank">',
				'</a>'
			);
	}

	/**
	 * Whether we can send the data.
	 *
	 * @since 3.1.1
	 * @param bool $override            If we should override the tracking setting.
	 * @param bool $ignore_last_checkin If we should ignore when the last check in was.
	 * @return bool
	 */
	public function can_send_data( $override, $ignore_last_checkin ) {

		// Never send data from a dev site.
		if ( edd_is_dev_environment() || edd_is_test_mode() ) {
			return false;
		}

		if ( 'staging' === wp_get_environment_type() ) {
			return false;
		}

		if ( ! $this->tracking_allowed() && ! $override ) {
			return false;
		}

		// Send a maximum of once per week.
		$last_send = $this->get_last_send();
		if ( ! $ignore_last_checkin && is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) ) {
			return false;
		}

		return true;
	}
}
