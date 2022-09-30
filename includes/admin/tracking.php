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
	 * Get things going
	 *
	 */
	public function __construct() {

		// WordPress core actions.
		add_action( 'init',          array( $this, 'schedule_send' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice'  ) );

		// Sanitize setting.
		add_action( 'edd_settings_general_sanitize', array( $this, 'check_for_settings_optin' ) );

		// Handle opting in and out.
		add_action( 'edd_opt_into_tracking',   array( $this, 'check_for_optin'  ) );
		add_action( 'edd_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
	}

	/**
	 * Check if the user has opted into tracking
	 *
	 * @access private
	 * @return bool
	 */
	private function tracking_allowed() {
		return (bool) edd_get_option( 'allow_tracking', false );
	}

	/**
	 * Setup the data that is going to be tracked
	 *
	 * @access private
	 * @return void
	 */
	private function setup_data() {

		// Retrieve current theme info.
		$theme_data    = wp_get_theme();
		$theme         = $theme_data->Name . ' ' . $theme_data->Version;
		$checkout_page = edd_get_option( 'purchase_page', false );
		$date          = ( false !== $checkout_page )
			? get_post_field( 'post_date', $checkout_page )
			: 'not set';
		$server        = isset( $_SERVER['SERVER_SOFTWARE'] )
			? $_SERVER['SERVER_SOFTWARE']
			: '';

		// Setup data.
		$data = array(
			'php_version'  => phpversion(),
			'edd_version'  => EDD_VERSION,
			'wp_version'   => get_bloginfo( 'version' ),
			'server'       => $server,
			'install_date' => $date,
			'multisite'    => is_multisite(),
			'url'          => home_url(),
			'theme'        => $theme,
			'email'        => get_bloginfo( 'admin_email' )
		);

		// Retrieve current plugin information.
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Get plugins
		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		// Remove active plugins from list so we can show active and inactive separately.
		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				unset( $plugins[ $key ] );
			}
		}

		$data['active_plugins']   = $active_plugins;
		$data['inactive_plugins'] = $plugins;
		$data['active_gateways']  = array_keys( edd_get_enabled_payment_gateways() );
		$data['products']         = wp_count_posts( 'download' )->publish;
		$data['download_label']   = edd_get_label_singular( true );
		$data['locale']           = get_locale();

		$this->data = $data;
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

		$home_url = trailingslashit( home_url() );

		// Allows us to stop our own site from checking in, and a filter for our additional sites.
		if ( $home_url === 'https://easydigitaldownloads.com/' || apply_filters( 'edd_disable_tracking_checkin', false ) ) {
			return false;
		}

		if ( ! $this->tracking_allowed() && ! $override ) {
			return false;
		}

		// Send a maximum of once per week.
		$last_send = $this->get_last_send();
		if ( is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) && ! $ignore_last_checkin ) {
			return false;
		}

		$this->setup_data();

		wp_remote_post( 'https://easydigitaldownloads.com/?edd_action=checkin', array(
			'method'      => 'POST',
			'timeout'     => 8,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => false,
			'body'        => $this->data,
			'user-agent'  => 'EDD/' . EDD_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		update_option( 'edd_tracking_last_send', time() );

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

		update_option( 'edd_tracking_notice', '1' );
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
		update_option( 'edd_tracking_notice', '1' );
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
			update_option( 'edd_tracking_notice', '1' );

		// Notify the user.
		} elseif ( edd_is_admin_page() && ! edd_is_admin_page( 'index.php' ) && ! edd_is_insertable_admin_page() ) {
			$optin_url      = add_query_arg( 'edd_action', 'opt_into_tracking' );
			$optout_url     = add_query_arg( 'edd_action', 'opt_out_of_tracking' );

			$base_url_slug = EDD\Admin\Pass_Manager::isPro() ? 'pricing' : 'lite-upgrade';
			$pass_url      = edd_link_helper(
				'https://easydigitaldownloads.com/' . $base_url_slug,
				array(
					'utm_medium'  => 'telemetry',
					'utm_content' => 'notice',
				)
			);

			// Add the notice.
			EDD()->notices->add_notice( array(
				'id'      => 'edd-allow-tracking',
				'class'   => 'updated',
				'message' => array(
					'<strong>' . __( 'Help us improve Easy Digital Downloads!', 'easy-digital-downloads' ) . '</strong>',
					sprintf( __( 'Opt-in to sending the EDD team some information about your store, and immediately be emailed a discount, valid towards the <a href="%s" target="_blank">purchase of a pass</a>.', 'easy-digital-downloads' ), $pass_url ),
					__( 'No sensitive data is tracked.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Allow', 'easy-digital-downloads' ) . '</a> <a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'easy-digital-downloads' ) . '</a>'
				),
				'is_dismissible' => false,
			) );
		}
	}
}
