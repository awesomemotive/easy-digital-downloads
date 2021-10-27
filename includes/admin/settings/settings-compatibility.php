<?php

/**
 * Settings Compatibility Functions
 *
 * For managing settings compatibility in a reorganized settings structure.
 *
 * @package     EDD
 * @subpackage  Settings Compatibility
 * @copyright   Copyright (c) 2021, Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.11.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gives us an area to ensure known compatibility issues with our settings organization by giving us a hook to manage
 * and alter hooks and filters that are being run against our primary settings array.
 *
 * @since 2.11.3
 */
add_action(
	'plugins_loaded',
	function() {

		/**
		 * Ensures compatibility with EDD 2.11.3 and Recurring payments prior to Recurring being released to move
		 * settings for 'checkout' from 'misc' to 'payments'.
		 */
		if ( function_exists( 'edd_recurring_guest_checkout_description' ) && false !== has_filter( 'edd_settings_misc', 'edd_recurring_guest_checkout_description' ) ) {
			remove_filter( 'edd_settings_misc', 'edd_recurring_guest_checkout_description', 10 );
			add_filter( 'edd_settings_gateways', 'edd_recurring_guest_checkout_description', 10 );
		}

		/**
		 * Ensures compatibility with EDD 2.11.x and Recurring payments prior to Recurring being released to move
		 * settings for all extension settings to 'payments'.
		 */
		if ( function_exists( 'edd_recurring_settings_section' ) && false !== has_filter( 'edd_settings_sections_extensions', 'edd_recurring_settings_section' ) ) {
			remove_filter( 'edd_settings_sections_extensions', 'edd_recurring_settings_section' );
			add_filter( 'edd_settings_sections_gateways', 'edd_recurring_settings_section' );
			remove_filter( 'edd_settings_extensions', 'edd_recurring_settings' );
			add_filter( 'edd_settings_gateways', 'edd_recurring_settings' );
		}

		/**
		 * Ensures compatibility with EDD 2.11.x and Reviews' settings being in the extensions section.
		 */
		if ( function_exists( 'edd_reviews' ) ) {
			$reviews = edd_reviews();
			if ( false !== has_filter( 'edd_settings_sections_extensions', array( $reviews, 'register_reviews_section' ) ) ) {
				remove_filter( 'edd_settings_sections_extensions', array( $reviews, 'register_reviews_section' ) );
				add_filter( 'edd_settings_sections_marketing', array( $reviews, 'register_reviews_section' ) );
				remove_filter( 'edd_settings_extensions', array( $reviews, 'misc_settings' ) );
				add_filter( 'edd_settings_marketing', array( $reviews, 'misc_settings' ) );
			}
		}

		/**
		 * Move the Free Downloads settings to the Marketing section (EDD 2.11.x).
		 */
		if ( false !== has_filter( 'edd_settings_sections_extensions', 'edd_free_downloads_add_settings_section' ) ) {
			remove_filter( 'edd_settings_sections_extensions', 'edd_free_downloads_add_settings_section' );
			add_filter( 'edd_settings_sections_marketing', 'edd_free_downloads_add_settings_section' );
			remove_filter( 'edd_settings_extensions', 'edd_free_downloads_add_settings' );
			add_filter( 'edd_settings_marketing', 'edd_free_downloads_add_settings' );
		}

	},
	99
);
