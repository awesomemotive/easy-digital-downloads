<?php
/**
 * Handles localization for EDD admin scripts.
 *
 * @package     EDD
 * @subpackage  Admin/Assets
 * @since       3.3.0
 */

namespace EDD\Admin\Assets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Localization class.
 */
class Localization {

	/**
	 * Sets up script localization for the admin.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function admin() {
		wp_localize_script( 'edd-admin-scripts', 'edd_vars', self::get_variables() );
	}

	/**
	 * Sets up script localization for the upgrades screen.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function upgrades() {
		wp_localize_script(
			'edd-admin-upgrades',
			'edd_admin_upgrade_vars',
			array(
				'migration_complete' => esc_html__( 'Migration complete', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Get the localization variables for the admin.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_variables() {

		$edd_currency = new \EDD\Currency\Currency( self::get_currency() );

		return array(
			'post_id'                 => get_the_ID(),
			'edd_version'             => edd_admin_get_script_version(),
			'currency'                => $edd_currency->code,
			'currency_sign'           => $edd_currency->symbol,
			'currency_pos'            => $edd_currency->position,
			'currency_decimals'       => $edd_currency->number_decimals,
			'decimal_separator'       => $edd_currency->decimal_separator,
			'thousands_separator'     => $edd_currency->thousands_separator,
			'date_picker_format'      => edd_get_date_picker_format( 'js' ),
			'add_new_download'        => __( 'Add New Download', 'easy-digital-downloads' ),
			'use_this_file'           => __( 'Use This File', 'easy-digital-downloads' ),
			'quick_edit_warning'      => __( 'Sorry, not available for variable priced products.', 'easy-digital-downloads' ),
			'delete_order_item'       => __( 'Are you sure you want to delete this item?', 'easy-digital-downloads' ),
			'delete_order_adjustment' => __( 'Are you sure you want to delete this adjustment?', 'easy-digital-downloads' ),
			'delete_note'             => __( 'Are you sure you want to delete this note?', 'easy-digital-downloads' ),
			'delete_tax_rate'         => __( 'Are you sure you want to delete this tax rate?', 'easy-digital-downloads' ),
			'revoke_api_key'          => __( 'Are you sure you want to revoke this API key?', 'easy-digital-downloads' ),
			'regenerate_api_key'      => __( 'Are you sure you want to regenerate this API key?', 'easy-digital-downloads' ),
			'resend_receipt'          => __( 'Are you sure you want to resend the purchase receipt?', 'easy-digital-downloads' ),
			'disconnect_customer'     => __( 'Are you sure you want to disconnect the WordPress user from this customer record?', 'easy-digital-downloads' ),
			'copy_download_link_text' => __( 'Copy these links to your clipboard and give them to your customer', 'easy-digital-downloads' ),
			/* translators: %s: Download singular label */
			'delete_payment_download' => sprintf( __( 'Are you sure you want to delete this %s?', 'easy-digital-downloads' ), edd_get_label_singular() ),
			/* translators: %s: Downloads plural label */
			'type_to_search'          => sprintf( __( 'Type to search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			/* translators: %s: Download singular label */
			'one_option'              => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			/* translators: %s: Downloads plural label */
			'one_or_more_option'      => sprintf( __( 'Choose one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			'one_price_min'           => __( 'You must have at least one price', 'easy-digital-downloads' ),
			'one_field_min'           => __( 'You must have at least one field', 'easy-digital-downloads' ),
			'one_download_min'        => __( 'Payments must contain at least one item', 'easy-digital-downloads' ),
			'no_results_text'         => __( 'No match for:', 'easy-digital-downloads' ),
			'numeric_item_price'      => __( 'Item price must be numeric', 'easy-digital-downloads' ),
			'numeric_item_tax'        => __( 'Item tax must be numeric', 'easy-digital-downloads' ),
			'numeric_quantity'        => __( 'Quantity must be numeric', 'easy-digital-downloads' ),
			'remove_text'             => __( 'Remove', 'easy-digital-downloads' ),
			'batch_export_no_class'   => __( 'You must choose a method.', 'easy-digital-downloads' ),
			'batch_export_no_reqs'    => __( 'Required fields not completed.', 'easy-digital-downloads' ),
			'reset_stats_warn'        => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'easy-digital-downloads' ),
			'unsupported_browser'     => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'easy-digital-downloads' ),
			'show_advanced_settings'  => __( 'Show advanced settings', 'easy-digital-downloads' ),
			'hide_advanced_settings'  => __( 'Hide advanced settings', 'easy-digital-downloads' ),
			'no_downloads_error'      => __( 'There are no downloads attached to this payment', 'easy-digital-downloads' ),
			'wait'                    => __( 'Please wait &hellip;', 'easy-digital-downloads' ),
			'test_email_save_changes' => __( 'You must save your changes to send the test email.', 'easy-digital-downloads' ),
			'no_letters_or_numbers'   => __( 'Either Letters or Numbers should be selected.', 'easy-digital-downloads' ),

			// Diaglog buttons.
			'confirm_dialog_text'     => __( 'Confirm', 'easy-digital-downloads' ),
			'cancel_dialog_text'      => __( 'Cancel', 'easy-digital-downloads' ),

			// Features.
			'quantities_enabled'      => edd_item_quantities_enabled(),
			'taxes_enabled'           => edd_use_taxes(),
			'taxes_included'          => edd_use_taxes() && edd_prices_include_tax(),
			'new_media_ui'            => edd_apply_filters_deprecated( 'edd_use_35_media_ui', array( 1 ), '3.1.1', false, __( 'The edd_use_35_media_ui filter is no longer supported.', 'easy-digital-downloads' ) ),

			// REST based items.
			'restBase'                => rest_url( \EDD\API\v3\Endpoint::$namespace ),
			'restNonce'               => wp_create_nonce( 'wp_rest' ),
			'download_has_files'      => self::download_has_files(),

			// Region settings.
			'enter_region'            => __( 'Enter a region', 'easy-digital-downloads' ),
			'select_region'           => __( 'Select a region', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Gets the currency code.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_currency() {
		$currency = edd_get_currency();

		// Customize the currency on a few individual pages.
		if ( ! function_exists( 'edd_is_admin_page' ) ) {
			return $currency;
		}

		if ( edd_is_admin_page( 'reports' ) && function_exists( '\EDD\Reports\get_filter_value' ) ) {
			/*
			* For reports, use the currency currently being filtered.
			*/
			$currency_filter = \EDD\Reports\get_filter_value( 'currencies' );
			if ( ! empty( $currency_filter ) && array_key_exists( strtoupper( $currency_filter ), edd_get_currencies() ) ) {
				return strtoupper( $currency_filter );
			}
		} elseif ( edd_is_admin_page( 'payments' ) && ! empty( $_GET['id'] ) ) {
			/*
			* For orders & refunds, use the currency of the current order.
			*/
			$order = edd_get_order( absint( $_GET['id'] ) );
			if ( $order instanceof \EDD\Orders\Order ) {
				return $order->currency;
			}
		}

		return $currency;
	}

	/**
	 * Check if the current download has files attached.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private static function download_has_files() {
		if ( function_exists( 'edd_is_admin_page' ) && edd_is_admin_page( 'download', 'edit' ) ) {
			return (bool) edd_get_download_files( get_the_ID() );
		}

		return false;
	}
}
