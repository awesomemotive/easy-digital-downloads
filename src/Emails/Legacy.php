<?php

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Legacy
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class Legacy implements SubscriberInterface {

	/**
	 * Gets the events to subscribe to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_email_legacy_data_cleanup' => 'remove_legacy_data',
			'edd_get_option'                => array( 'get_option', 10, 2 ),
		);
	}

	/**
	 * Removes legacy data associated with the specified email ID.
	 *
	 * @param int $email_id The ID of the email to remove legacy data for.
	 * @return void
	 */
	public function remove_legacy_data( $email_id ) {
		$email = edd_get_email_by( 'id', $email_id );
		if ( ! $email ) {
			return;
		}
		$template = $email->get_template();
		if ( ! $template ) {
			return;
		}

		$template->remove_legacy_data();
	}

	/**
	 * Retrieves the value of a specific option from the Legacy class.
	 *
	 * @since 3.3.0
	 * @param string $value The value to retrieve.
	 * @param string $key   The key of the option.
	 * @return mixed The value of the option.
	 */
	public function get_option( $value, $key ) {
		if ( ! did_action( 'edd_setup_components' ) ) {
			return $value;
		}
		static $legacy = null;

		if ( is_null( $legacy ) || edd_is_doing_unit_tests() ) {
			global $wpdb;
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}edd_emailmeta'" );
			if ( $table_exists ) {
				// Get all email meta with a meta_key of 'legacy'.
				$legacy = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_emailmeta WHERE meta_key = %s", 'legacy' ) );
			}
		}
		if ( empty( $legacy ) ) {
			$legacy = false;

			return $value;
		}

		// Create an array of legacy option names and their corresponding email IDs.
		$option_names = wp_list_pluck( $legacy, 'edd_email_id', 'meta_value' );
		if ( ! array_key_exists( $key, $option_names ) ) {
			return $value;
		}

		// Get the email object for the option.
		$email = edd_get_email_registry()->get_email_by_id( $option_names[ $key ] );
		if ( ! $email ) {
			return $value;
		}

		// Deprecate the use of edd_get_option() for email options.
		_edd_deprecated_function( 'edd_get_option( \'' . $key . '\' )', '3.3.0' );

		// Get the options for the email.
		$options = $email->options;
		if ( empty( $options ) || ! in_array( $key, $options, true ) ) {
			return $value;
		}

		// Get the option key for the email.
		$option_key = array_search( $key, $options, true );
		if ( ! $option_key ) {
			return $value;
		}

		return $email->{$option_key};
	}
}
