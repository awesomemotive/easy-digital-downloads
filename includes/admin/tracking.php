<?php
/**
 * Tracking functions for reporting plugin usage to the EDD site for users that have opted in
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.2
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class EDD_Tracking {

	private $data;

	public function __construct() {

		$this->schedule_send();

		add_action( 'edd_settings_general_sanitize', array( $this, 'check_for_optin' ) );

	}

	private function tracking_allowed() {
		global $edd_options;
		return isset( $edd_options['allow_tracking'] );
	}

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

		$this->data = $data;
	}

	private function send_remote( $override = false ) {

		if( ! $this->tracking_allowed() && ! $override )
			return;

		// Send a maximum of once per week
		$last_send = $this->get_last_send();
		if( $last_send && $last_send > strtotime( '-1 week' ) )
			return;

		$this->setup_data();

		//$request = wp_remote_post( 'https://easydigitaldownloads.com/?edd_action=checkin', array(
		$request = wp_remote_post( 'https://easydigitaldownloads.com/?edd_action=checkin', array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => false,
			'body'        => $this->data,
			'user-agent'  => 'EDD/' . EDD_VERSION . '; ' . get_bloginfo( 'url' )
		    )
		);

		update_option( 'edd_tracking_last_send', time() );

	}

	public function check_for_optin( $input ) {
		// Send a optin on settings save

		if( isset( $input['allow_tracking'] ) ) {
			$this->send_remote( true );
		}

		return $input;

	}

	private function get_last_send() {
		return get_option( 'edd_tracking_last_send' );
	}

	private function schedule_send() {
		// We send once a week (while tracking is allowed) to check in, which can be used to determine active sites
		add_action( 'edd_weekly_scheduled_events', array( $this, 'send_remote' ) );
	}

}
new EDD_Tracking;