<?php
/**
 * Tracking functions for reporting plugin usage to the EDD site for users that have opted in
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.2
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Usage tracking
 *
 * @access public
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
	 * @access public
	 */
	public function __construct() {

		$this->schedule_send();

		add_action( 'edd_settings_general_sanitize', array( $this, 'check_for_settings_optin' ) );
		add_action( 'edd_opt_into_tracking', array( $this, 'check_for_optin' ) );
		add_action( 'edd_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

	}

	/**
	 * Check if the user has opted into tracking
	 *
	 * @access private
	 * @return bool
	 */
	private function tracking_allowed() {
		$allow_tracking = edd_get_option( 'allow_tracking', false );
		return $allow_tracking;
	}

	/**
	 * Setup the data that is going to be tracked
	 *
	 * @access private
	 * @return void
	 */
	private function setup_data() {

		$data = array();

		// Retrieve current theme info
		if ( get_bloginfo( 'version' ) < '3.4' ) {
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
			$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
		} else {
			$theme_data = wp_get_theme();
			$theme      = $theme_data->Name . ' ' . $theme_data->Version;
		}

		$data['url']    = home_url();
		$data['theme']  = $theme;
		$data['email']  = get_bloginfo( 'admin_email' );

		// Retrieve current plugin information
		if( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}

		$data['active_plugins']   = $active_plugins;
		$data['inactive_plugins'] = $plugins;
		$data['products']         = wp_count_posts( 'download' )->publish;

		$this->data = $data;
	}

	/**
	 * Send the data to the EDD server
	 *
	 * @access private
	 * @return void
	 */
	public function send_checkin( $override = false ) {

		if( ! $this->tracking_allowed() && ! $override )
			return;

		// Send a maximum of once per week
		$last_send = $this->get_last_send();
		if( $last_send && $last_send > strtotime( '-1 week' ) )
			return;

		$this->setup_data();

		$request = wp_remote_post( 'https://easydigitaldownloads.com/?edd_action=checkin', array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $this->data,
			'user-agent'  => 'EDD/' . EDD_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		update_option( 'edd_tracking_last_send', time() );

	}

	/**
	 * Check for a new opt-in on settings save
	 *
	 * This runs during the sanitation of General settings, thus the return
	 *
	 * @access public
	 * @return array
	 */
	public function check_for_settings_optin( $input ) {
		// Send an intial check in on settings save

		if( isset( $input['allow_tracking'] ) ) {
			$this->send_checkin( true );
		}

		return $input;

	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @access public
	 * @return void
	 */
	public function check_for_optin( $data ) {

		global $edd_options;

		$edd_options['allow_tracking'] = '1';

		update_option( 'edd_settings', $edd_options );

		$this->send_checkin( true );

		update_option( 'edd_tracking_notice', '1' );

	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @access public
	 * @return void
	 */
	public function check_for_optout( $data ) {

		global $edd_options;
		if( isset( $edd_options['allow_tracking'] ) ) {
			unset( $edd_options['allow_tracking'] );
			update_option( 'edd_settings', $edd_options );
		}

		update_option( 'edd_tracking_notice', '1' );

		wp_redirect( remove_query_arg( 'edd_action' ) ); exit;

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
	 * @access private
	 * @return void
	 */
	private function schedule_send() {
		// We send once a week (while tracking is allowed) to check in, which can be used to determine active sites
		add_action( 'edd_weekly_scheduled_events', array( $this, 'send_checkin' ) );
	}

	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @access public
	 * @return void
	 */
	public function admin_notice() {

		$hide_notice = get_option( 'edd_tracking_notice' );

		if( $hide_notice ) {
			return;
		}

		if( edd_get_option( 'allow_tracking', false ) ) {
			return;
		}

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if(
			stristr( network_site_url( '/' ), 'dev'       ) !== false ||
			stristr( network_site_url( '/' ), 'localhost' ) !== false ||
			stristr( network_site_url( '/' ), ':8888'     ) !== false // This is common with MAMP on OS X
		) {
			update_option( 'edd_tracking_notice', '1' );
		} else {
			$optin_url  = add_query_arg( 'edd_action', 'opt_into_tracking' );
			$optout_url = add_query_arg( 'edd_action', 'opt_out_of_tracking' );

			$source         = substr( md5( get_bloginfo( 'name' ) ), 0, 10 );
			$extensions_url = 'https://easydigitaldownloads.com/extensions?utm_source=' . $source . '&utm_medium=admin&utm_term=notice&utm_campaign=EDDUsageTracking';
			echo '<div class="updated"><p>';
				echo sprintf( __( 'Allow Easy Digital Downloads to track plugin usage? Opt-in to tracking and our newsletter and immediately be emailed a 20%% discount to the EDD shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'easy-digital-downloads' ), $extensions_url );
				echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Allow', 'easy-digital-downloads' ) . '</a>';
				echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'easy-digital-downloads' ) . '</a>';
			echo '</p></div>';
		}
	}

}
$edd_tracking = new EDD_Tracking;
