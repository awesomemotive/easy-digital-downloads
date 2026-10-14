<?php
/**
 * Hook naming convention tests.
 *
 * @package   EDD\Tests\Hooks
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.1
 */

namespace EDD\Tests\Hooks;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Hook naming convention tests.
 *
 * New actions in `src/` use the slash-delimited `edd/` namespace. The baseline below is the set of
 * `edd_` names that already shipped from `src/`, so it is frozen rather than approved: names leave
 * it as they are replaced, and nothing new joins it.
 *
 * @since 3.7.1
 *
 * @group edd_hooks
 */
class HookNames extends EDD_UnitTestCase {

	/**
	 * Stands in for the variable part of a dynamically built hook name.
	 *
	 * `'edd_' . $section->id . 'section_contents'` is recorded as `edd_{var}section_contents`.
	 *
	 * @since 3.7.1
	 * @var string
	 */
	const DYNAMIC_PLACEHOLDER = '{var}';

	/**
	 * The `edd_` names dispatched from `src/` when this test was written.
	 *
	 * @since 3.7.1
	 * @var array
	 */
	private static $baseline = array(
		'edd_30_migrate_file_download_log',
		'edd_30_migrate_order',
		'edd_acr_cart_restored',
		'edd_acr_email_before_send',
		'edd_acr_email_clicked',
		'edd_acr_email_manager_bottom',
		'edd_acr_email_opened',
		'edd_acr_process_bulk_action',
		'edd_acr_unsubscribed',
		'edd_add_discount_form_after_code_field',
		'edd_add_discount_form_after_code_field_wrapper',
		'edd_after_ajax_activate_extension',
		'edd_after_cc_fields',
		'edd_after_checkout_cart',
		'edd_after_order_actions',
		'edd_after_payment_actions',
		'edd_after_price_field',
		'edd_after_purchase_form',
		'edd_after_submit_refund_table',
		'edd_before_cc_fields',
		'edd_before_checkout_cart',
		'edd_before_purchase_form',
		'edd_blocks_checkout_address_fields',
		'edd_cart_contents_loaded_from_session',
		'edd_cart_discounts_loaded_from_session',
		'edd_cart_discounts_removed',
		'edd_cart_empty',
		'edd_cart_preview_after_actions',
		'edd_cart_preview_after_items',
		'edd_cart_preview_after_render',
		'edd_cart_preview_after_summary',
		'edd_cart_preview_assets_enqueued',
		'edd_cart_preview_before_actions',
		'edd_cart_preview_before_render',
		'edd_cart_preview_before_summary',
		'edd_cart_preview_empty',
		'edd_cart_preview_templates',
		'edd_cart_recovery_advanced_filters_row',
		'edd_cart_recovery_email_logs_advanced_filters_row',
		'edd_cc_billing_bottom',
		'edd_cc_billing_top',
		'edd_checkout_before_gateway',
		'edd_checkout_cart_item_title_after',
		'edd_checkout_error_checks',
		'edd_checkout_form_bottom',
		'edd_checkout_form_top',
		'edd_checkout_user_error_checks',
		'edd_cron_migrated_to_action_scheduler',
		'edd_cron_migrated_to_wp_cron',
		'edd_discounts_page_bottom',
		'edd_discounts_page_top',
		'edd_download_price_option_row',
		'edd_duplicate_product',
		'edd_edit_discount_form_before_amount',
		'edd_edit_discount_form_before_code',
		'edd_edit_discount_form_before_expiration',
		'edd_edit_discount_form_before_notes',
		'edd_edit_discount_form_before_start_date',
		'edd_edit_discount_form_before_type',
		'edd_edit_discount_form_bottom',
		'edd_edit_discount_form_top',
		'edd_elementor_checkout_sections',
		'edd_email_body',
		'edd_email_bounced',
		'edd_email_conditional_tags_register_conditions',
		'edd_email_footer',
		'edd_email_header',
		'edd_email_manager_bottom',
		'edd_email_send_after',
		'edd_email_send_before',
		'edd_email_sent_{var}',
		'edd_email_template_{var}',
		'edd_empty_cart_{var}',
		'edd_export_form',
		'edd_export_init',
		'edd_gateway_square',
		'edd_generate_structured_data',
		'edd_insert_user',
		'edd_log_user_in',
		'edd_logs_{var}_bottom',
		'edd_logs_{var}_top',
		'edd_meta_box_fields',
		'edd_meta_box_files_fields',
		'edd_meta_box_price_fields',
		'edd_meta_box_settings_fields',
		'edd_metabox_download_details',
		'edd_modal_rendered',
		'edd_order_stats_parse_query',
		'edd_paypal_v3_order_vaulted',
		'edd_paypal_webhook_event_{var}',
		'edd_plugin_installed',
		'edd_post_add_customer_email',
		'edd_post_add_fee',
		'edd_post_insert_log',
		'edd_post_remove_fee',
		'edd_post_update_log',
		'edd_pre_insert_log',
		'edd_pre_log_user_in',
		'edd_pre_process_purchase',
		'edd_pre_update_log',
		'edd_price_field',
		'edd_profile_editor_logged_out',
		'edd_purchase_form',
		'edd_purchase_form_after_email',
		'edd_purchase_form_user_info_fields',
		'edd_recurring_payment_failed',
		'edd_render_price_row',
		'edd_reports_init',
		'edd_rest_cart_item_added',
		'edd_rest_cart_item_removed',
		'edd_rest_cart_quantity_updated',
		'edd_settings_tab_bottom',
		'edd_settings_tab_bottom_emails_{var}',
		'edd_settings_tab_bottom_{var}',
		'edd_settings_tab_top',
		'edd_settings_tab_top_emails_{var}',
		'edd_settings_tab_top_{var}',
		'edd_square_dispute_created',
		'edd_square_event_{var}',
		'edd_stats_meta_box',
		'edd_stripe_dispute_created',
		'edd_stripe_early_fraud_warning',
		'edd_stripe_review_closed',
		'edd_stripe_review_opened',
		'edd_tools_tab_{var}',
		'edd_transition_order_status',
		'edd_vat_delayed_validation_reverse_charged',
		'edd_{var}section_contents',
	);

	/**
	 * The scan of `src/`.
	 *
	 * @since 3.7.1
	 * @var array|null
	 */
	private static $dispatched_actions = null;

	/**
	 * No action dispatched from `src/` carries an `edd_` prefix unless the baseline already lists it.
	 *
	 * @since 3.7.1
	 */
	public function test_src_dispatches_no_unlisted_edd_prefixed_action() {
		$unlisted = array_diff_key( $this->get_dispatched_actions(), array_flip( self::$baseline ) );

		$this->assertSame( array(), array_keys( $unlisted ), $this->describe_unlisted( $unlisted ) );
	}

	/**
	 * The baseline lists nothing that `src/` has stopped dispatching.
	 *
	 * Without this the baseline keeps granting names that no longer exist, and a later hook could
	 * reclaim one of them without the first test noticing.
	 *
	 * @since 3.7.1
	 */
	public function test_baseline_lists_no_name_that_left_src() {
		$stale = array_values( array_diff( self::$baseline, array_keys( $this->get_dispatched_actions() ) ) );

		$this->assertSame(
			array(),
			$stale,
			'These names are in the baseline but no longer dispatched from src/; remove them: ' . implode( ', ', $stale )
		);
	}

	/**
	 * Builds the failure message for unlisted names, naming where each one is dispatched.
	 *
	 * @since 3.7.1
	 *
	 * @param array $unlisted Name to list-of-locations map.
	 * @return string
	 */
	private function describe_unlisted( array $unlisted ): string {
		$lines = array( 'Use the edd/ namespace for new actions instead of an edd_ prefix. See .claude/rules/hooks.md.' );

		foreach ( $unlisted as $name => $locations ) {
			$lines[] = sprintf( '  %s (%s)', $name, implode( ', ', $locations ) );
		}

		return implode( "\n", $lines );
	}

	/**
	 * Collects every `edd_` prefixed action dispatched from `src/`.
	 *
	 * @since 3.7.1
	 *
	 * @return array Name to list-of-locations map, keyed by hook name and sorted.
	 */
	private function get_dispatched_actions(): array {
		if ( null !== self::$dispatched_actions ) {
			return self::$dispatched_actions;
		}

		$actions = array();

		foreach ( $this->get_src_files() as $file ) {
			foreach ( $this->get_dispatches_in_file( $file ) as $dispatch ) {
				if ( 0 !== strpos( $dispatch['name'], 'edd_' ) ) {
					continue;
				}

				$location                       = str_replace( '\\', '/', substr( $file, strlen( EDD_PLUGIN_DIR ) ) );
				$actions[ $dispatch['name'] ][] = $location . ':' . $dispatch['line'];
			}
		}

		ksort( $actions );

		self::$dispatched_actions = $actions;

		return self::$dispatched_actions;
	}

	/**
	 * Lists the PHP files under `src/`.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	private function get_src_files(): array {
		$files    = array();
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( EDD_PLUGIN_DIR . 'src', \FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( 'php' === strtolower( $file->getExtension() ) ) {
				$files[] = $file->getPathname();
			}
		}

		sort( $files );

		return $files;
	}

	/**
	 * Finds the action dispatches in a single file.
	 *
	 * The file is tokenized rather than matched with a pattern, because a dispatched name assembled
	 * from a variable is the case that most needs catching and the one a pattern reads wrong.
	 *
	 * @since 3.7.1
	 *
	 * @param string $file Absolute path.
	 * @return array List of name and line pairs.
	 */
	private function get_dispatches_in_file( string $file ): array {
		$tokens     = token_get_all( file_get_contents( $file ) );
		$dispatches = array();

		foreach ( array_keys( $tokens ) as $index ) {
			if ( ! $this->is_action_dispatch( $tokens, $index ) ) {
				continue;
			}

			$open = $this->get_next_significant( $tokens, $index + 1 );
			if ( null === $open || '(' !== $tokens[ $open ] ) {
				continue;
			}

			$dispatches[] = array(
				'name' => $this->get_dispatched_name( $tokens, $open ),
				'line' => $tokens[ $index ][2],
			);
		}

		return $dispatches;
	}

	/**
	 * Whether the token at this position is a call to one of the action dispatchers.
	 *
	 * @since 3.7.1
	 *
	 * @param array $tokens Token list.
	 * @param int   $index  Position to test.
	 * @return bool
	 */
	private function is_action_dispatch( array $tokens, int $index ): bool {
		if ( ! is_array( $tokens[ $index ] ) || T_STRING !== $tokens[ $index ][0] ) {
			return false;
		}

		$dispatchers = array( 'do_action', 'do_action_ref_array', 'do_action_deprecated', 'do_action_ref_array_deprecated' );
		if ( ! in_array( strtolower( $tokens[ $index ][1] ), $dispatchers, true ) ) {
			return false;
		}

		$previous = $this->get_previous_significant( $tokens, $index - 1 );
		if ( null === $previous || ! is_array( $tokens[ $previous ] ) ) {
			return true;
		}

		// A method or function of the same name is not the WordPress dispatcher.
		$disqualifiers = array( T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION );

		return ! in_array( $tokens[ $previous ][0], $disqualifiers, true );
	}

	/**
	 * Reads the dispatched hook name out of the call's first argument.
	 *
	 * @since 3.7.1
	 *
	 * @param array $tokens Token list.
	 * @param int   $open   Position of the opening parenthesis.
	 * @return string
	 */
	private function get_dispatched_name( array $tokens, int $open ): string {
		$name  = '';
		$depth = 0;

		for ( $index = $open + 1, $count = count( $tokens ); $index < $count; $index++ ) {
			$token = $tokens[ $index ];

			if ( ! is_array( $token ) ) {
				if ( in_array( $token, array( '(', '[', '{' ), true ) ) {
					++$depth;
					continue;
				}

				if ( in_array( $token, array( ')', ']', '}' ), true ) ) {
					if ( 0 === $depth ) {
						break;
					}

					--$depth;
					continue;
				}

				if ( ',' === $token && 0 === $depth ) {
					break;
				}
			} elseif ( in_array( $token[0], array( T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES ), true ) ) {
				++$depth;
				$name .= self::DYNAMIC_PLACEHOLDER;
				continue;
			}

			// Anything nested is part of an expression the enclosing token already stood in for.
			if ( $depth > 0 ) {
				continue;
			}

			if ( ! is_array( $token ) ) {
				continue;
			}

			if ( T_CONSTANT_ENCAPSED_STRING === $token[0] ) {
				$name .= $this->get_literal_value( $token[1] );
				continue;
			}

			if ( T_ENCAPSED_AND_WHITESPACE === $token[0] ) {
				$name .= $token[1];
				continue;
			}

			if ( $this->is_insignificant( $token ) ) {
				continue;
			}

			$name .= self::DYNAMIC_PLACEHOLDER;
		}

		return preg_replace( '/(?:' . preg_quote( self::DYNAMIC_PLACEHOLDER, '/' ) . ')+/', self::DYNAMIC_PLACEHOLDER, $name );
	}

	/**
	 * Unwraps a quoted string literal.
	 *
	 * @since 3.7.1
	 *
	 * @param string $literal Literal including its quotes.
	 * @return string
	 */
	private function get_literal_value( string $literal ): string {
		$quote = substr( $literal, 0, 1 );
		$value = substr( $literal, 1, -1 );

		if ( "'" === $quote ) {
			return str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $value );
		}

		return $value;
	}

	/**
	 * Finds the next token that carries meaning.
	 *
	 * @since 3.7.1
	 *
	 * @param array $tokens Token list.
	 * @param int   $index  Position to start from.
	 * @return int|null
	 */
	private function get_next_significant( array $tokens, int $index ): ?int {
		for ( $count = count( $tokens ); $index < $count; $index++ ) {
			if ( ! $this->is_insignificant( $tokens[ $index ] ) ) {
				return $index;
			}
		}

		return null;
	}

	/**
	 * Finds the previous token that carries meaning.
	 *
	 * @since 3.7.1
	 *
	 * @param array $tokens Token list.
	 * @param int   $index  Position to start from.
	 * @return int|null
	 */
	private function get_previous_significant( array $tokens, int $index ): ?int {
		for ( ; $index >= 0; $index-- ) {
			if ( ! $this->is_insignificant( $tokens[ $index ] ) ) {
				return $index;
			}
		}

		return null;
	}

	/**
	 * Whether a token is whitespace or a comment.
	 *
	 * @since 3.7.1
	 *
	 * @param array|string $token Token to test.
	 * @return bool
	 */
	private function is_insignificant( $token ): bool {
		return is_array( $token ) && in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true );
	}
}
