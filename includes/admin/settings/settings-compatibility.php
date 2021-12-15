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
 * @since       2.11.4
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
		 * Ensures compatibility with EDD 2.11.4 and Recurring payments prior to Recurring being released to move
		 * settings for all extension settings to 'payments'.
		 */
		if ( function_exists( 'edd_recurring_settings_section' ) && false !== has_filter( 'edd_settings_sections_extensions', 'edd_recurring_settings_section' ) ) {
			remove_filter( 'edd_settings_sections_extensions', 'edd_recurring_settings_section' );
			add_filter( 'edd_settings_sections_gateways', 'edd_recurring_settings_section' );
			remove_filter( 'edd_settings_extensions', 'edd_recurring_settings' );
			add_filter( 'edd_settings_gateways', 'edd_recurring_settings' );
		}

		/**
		 * Ensures compatibility with EDD 2.11.4 and Reviews' settings being in the extensions section.
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
		 * Move the Free Downloads settings to the Marketing section (EDD 2.11.4).
		 */
		if ( false !== has_filter( 'edd_settings_sections_extensions', 'edd_free_downloads_add_settings_section' ) ) {
			remove_filter( 'edd_settings_sections_extensions', 'edd_free_downloads_add_settings_section' );
			add_filter( 'edd_settings_sections_marketing', 'edd_free_downloads_add_settings_section' );
			remove_filter( 'edd_settings_extensions', 'edd_free_downloads_add_settings' );
			add_filter( 'edd_settings_marketing', 'edd_free_downloads_add_settings' );
		}

		/**
		 * Move the ActiveCampaign settings to the Marketing section (EDD 2.11.4).
		 */
		if ( function_exists( 'edd_activecampaign' ) ) {
			$activecampaign = edd_activecampaign();
			if ( false !== has_filter( 'edd_settings_sections_extensions', array( $activecampaign, 'settings_section' ) ) ) {
				remove_filter( 'edd_settings_sections_extensions', array( $activecampaign, 'settings_section' ) );
				add_filter( 'edd_settings_sections_marketing', array( $activecampaign, 'settings_section' ) );
				remove_filter( 'edd_settings_extensions', array( $activecampaign, 'register_settings' ) );
				add_filter( 'edd_settings_marketing', array( $activecampaign, 'register_settings' ) );
			}
		}

		/**
		 * Move the GetResponse settings to the Marketing section (EDD 2.11.4).
		 */
		if ( function_exists( 'edd_getresponse_add_settings_section' ) ) {
			if ( false !== has_filter( 'edd_settings_sections_extensions', 'edd_getresponse_add_settings_section' ) ) {
				$getresponse = new EDD_GetResponse_Newsletter();
				remove_filter( 'edd_settings_sections_extensions', 'edd_getresponse_add_settings_section' );
				add_filter( 'edd_settings_sections_marketing', 'edd_getresponse_add_settings_section' );
				remove_filter( 'edd_settings_extensions', 'edd_getresponse_add_settings' );
				add_filter( 'edd_settings_marketing', 'edd_getresponse_add_settings' );
				remove_filter( 'edd_settings_extensions_sanitize', array( $getresponse, 'save_settings' ) );
				add_filter( 'edd_settings_marketing_sanitize', array( $getresponse, 'save_settings' ) );
				remove_filter( 'edd_settings_extensions-getresponse_sanitize', array( $getresponse, 'save_settings' ) );
				add_filter( 'edd_settings_marketing-getresponse_sanitize', array( $getresponse, 'save_settings' ) );
			}
		}

		/**
		 * Move the Campaign Monitor settings to the Marketing section (EDD 2.11.4).
		 */
		if ( function_exists( 'eddcp_settings_section' ) && false !== has_filter( 'edd_settings_sections_extensions', 'eddcp_settings_section' ) ) {
			remove_filter( 'edd_settings_sections_extensions', 'eddcp_settings_section' );
			add_filter( 'edd_settings_sections_marketing', 'eddcp_settings_section' );
			remove_filter( 'edd_settings_extensions', 'eddcp_add_settings' );
			add_filter( 'edd_settings_marketing', 'eddcp_add_settings' );
		}

		/**
		 * Move the ConvertKit settings to the Marketing section (EDD 2.11.4).
		 */
		if ( class_exists( 'EDD_ConvertKit' ) && method_exists( 'EDD_ConvertKit', 'instance' ) ) {
			$convertkit = EDD_ConvertKit::instance();
			if ( false !== has_filter( 'edd_settings_sections_extensions', array( $convertkit, 'subsection' ) ) ) {
				remove_filter( 'edd_settings_sections_extensions', array( $convertkit, 'subsection' ) );
				add_filter( 'edd_settings_sections_marketing', array( $convertkit, 'subsection' ) );
				remove_filter( 'edd_settings_extensions_sanitize', array( $convertkit, 'save_settings' ) );
				add_filter( 'edd_settings_marketing_sanitize', array( $convertkit, 'save_settings' ) );
				remove_filter( 'edd_settings_extensions', array( $convertkit, 'settings' ) );
				add_filter( 'edd_settings_marketing', array( $convertkit, 'settings' ) );
			}
		}

		/**
		 * Move the AWeber settings to the Marketing section (EDD 2.11.4).
		 */
		if ( class_exists( 'EDD_Aweber' ) && method_exists( 'EDD_Aweber', 'instance' ) ) {
			$aweber = EDD_Aweber::instance();
			if ( false !== has_filter( 'edd_settings_sections_extensions', array( $aweber, 'subsection' ) ) ) {
				remove_filter( 'edd_settings_sections_extensions', array( $aweber, 'subsection' ) );
				add_filter( 'edd_settings_sections_marketing', array( $aweber, 'subsection' ) );
				remove_filter( 'edd_settings_extensions', array( $aweber, 'settings' ) );
				add_filter( 'edd_settings_marketing', array( $aweber, 'settings' ) );
			}
		}

		/**
		 * Move the MailPoet settings to the Marketing section (EDD 2.11.4).
		 */
		if ( class_exists( 'EDD_MailPoet' ) && method_exists( 'EDD_MailPoet', 'instance' ) ) {
			$mailpoet = EDD_MailPoet::instance();
			if ( false !== has_filter( 'edd_settings_sections_extensions', array( $mailpoet, 'subsection' ) ) ) {
				remove_filter( 'edd_settings_sections_extensions', array( $mailpoet, 'subsection' ) );
				add_filter( 'edd_settings_sections_marketing', array( $mailpoet, 'subsection' ) );
				remove_filter( 'edd_settings_extensions', array( $mailpoet, 'settings' ) );
				add_filter( 'edd_settings_marketing', array( $mailpoet, 'settings' ) );
			}
		}

		/**
		 * Move the Invoices settings to the Payments section (EDD 2.11.4).
		 */
		if ( false !== has_filter( 'edd_settings_sections_extensions', 'edd_invoices_register_settings_section' ) ) {
			remove_filter( 'edd_settings_sections_extensions', 'edd_invoices_register_settings_section' );
			add_filter( 'edd_settings_sections_gateways', 'edd_invoices_register_settings_section', 10 );
			remove_filter( 'edd_settings_extensions', 'edd_invoices_register_settings', 1 );
			add_filter( 'edd_settings_gateways', 'edd_invoices_register_settings', 1 );
		}
	},
	99
);
