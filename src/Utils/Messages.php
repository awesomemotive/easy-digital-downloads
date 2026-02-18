<?php
/**
 * Class to handle user facing messages.
 *
 * @package EDD\Utils
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Messages
 *
 * Handles all EDD error/success/info messaging via session storage with retrieval by code or type,
 * and consistent HTML output using existing EDD alert CSS.
 */
class Messages {

	/**
	 * Session key for the unified messages array.
	 *
	 * @var string
	 */
	const SESSION_KEY = 'edd_messages';

	/**
	 * Legacy session key for errors (migrated into edd_messages).
	 *
	 * @var string
	 */
	const LEGACY_ERRORS_KEY = 'edd_errors';

	/**
	 * Legacy session key for success messages (migrated into edd_messages).
	 *
	 * @var string
	 */
	const LEGACY_SUCCESS_KEY = 'edd_success_errors';

	/**
	 * Allowed message types (match CSS: edd-alert-error, edd-alert-success, edd-alert-info, edd-alert-warn).
	 *
	 * @var string[]
	 */
	const TYPES = array( 'error', 'success', 'info', 'warn' );

	/**
	 * Default CSS wrapper class per type (without edd-alert-*).
	 *
	 * @var string[]
	 */
	const WRAPPER_CLASSES = array(
		'error'   => 'edd-errors',
		'success' => 'edd-success',
		'info'    => 'edd-info',
		'warn'    => 'edd-warn',
	);

	/**
	 * Add a message for a given type and code.
	 *
	 * @since 3.6.5
	 * @param string $type    One of error, success, info, warn.
	 * @param string $code    Unique code for the message (e.g. error_id).
	 * @param string $message Message text (will be sanitized for storage).
	 * @return void
	 */
	public static function add( $type, $code, $message ) {
		if ( ! in_array( $type, self::TYPES, true ) ) {
			return;
		}

		$storage                   = self::get_storage();
		$storage[ $type ][ $code ] = sanitize_text_field( $message );
		self::persist( $storage );
	}

	/**
	 * Get messages for a single type.
	 *
	 * @since 3.6.5
	 * @param string $type One of error, success, info, warn.
	 * @return array<string, string> Map of code => message.
	 */
	public static function get_by_type( $type ) {
		if ( ! in_array( $type, self::TYPES, true ) ) {
			return array();
		}

		$storage = self::get_storage();
		return isset( $storage[ $type ] ) ? $storage[ $type ] : array();
	}

	/**
	 * Get the first message matching the given code across any type.
	 *
	 * @since 3.6.5
	 * @param string $code Message code.
	 * @return array|null Associative array with 'type' and 'message', or null if not found.
	 */
	public static function get_by_code( $code ): ?array {
		$storage = self::get_storage();
		foreach ( self::TYPES as $type ) {
			if ( ! empty( $storage[ $type ][ $code ] ) ) {
				return array(
					'type'    => $type,
					'message' => $storage[ $type ][ $code ],
				);
			}
		}

		return null;
	}

	/**
	 * Get all messages, optionally filtered.
	 *
	 * @since 3.6.5
	 * @return array<string, array<string, string>> Full storage shape [ type => [ code => message ] ].
	 */
	public static function get_all(): array {
		return self::get_storage();
	}

	/**
	 * Remove a message by code. If type is omitted, only the error type is updated (backward compatibility with edd_unset_error).
	 *
	 * @since 3.6.5
	 * @param string      $code Message code.
	 * @param string|null $type Optional. One of error, success, info, warn. Default 'error'.
	 * @return void
	 */
	public static function remove( string $code, ?string $type = 'error' ): void {
		$storage = self::get_storage();
		if ( null !== $type && in_array( $type, self::TYPES, true ) ) {
			unset( $storage[ $type ][ $code ] );
		} else {
			unset( $storage['error'][ $code ] );
		}
		self::persist( $storage );
	}

	/**
	 * Clear all messages and legacy keys.
	 * If a type is provided, only clear that type.
	 * Otherwise, clear all types.
	 *
	 * @since 3.6.5
	 * @param string|null $type Optional. One of error, success, info, warn. Default null.
	 * @return void
	 */
	public static function clear( $type = null ): void {
		if ( $type ) {
			self::clear_by_type( $type );
			return;
		}

		EDD()->session->set( self::SESSION_KEY, null );
		EDD()->session->set( self::LEGACY_ERRORS_KEY, null );
		EDD()->session->set( self::LEGACY_SUCCESS_KEY, null );
	}

	/**
	 * Whether any messages exist (any type).
	 *
	 * @since 3.6.5
	 * @return bool
	 */
	public static function has_any(): bool {
		$storage = self::get_storage();
		foreach ( self::TYPES as $type ) {
			if ( ! empty( $storage[ $type ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build HTML for messages. One block per type (error, success, info, warn) when present.
	 *
	 * Uses existing CSS: edd_errors/edd_success/edd_info/edd_warn, edd-alert, edd-alert-{type}.
	 * Applies edd_error_class filter for error block; edd_message_class for other types.
	 *
	 * @since 3.6.5
	 * @param string|null $type Optional. If provided, only output this type; otherwise all types.
	 * @return string HTML markup (escaped).
	 */
	public static function to_html( ?string $type = null ): string {
		$storage         = self::get_storage();
		$types_to_render = ( null !== $type && in_array( $type, self::TYPES, true ) )
			? array( $type )
			: self::TYPES;

		$html = '';
		foreach ( $types_to_render as $render ) {
			if ( empty( $storage[ $render ] ) || ! is_array( $storage[ $render ] ) ) {
				continue;
			}
			$html .= self::build_block_html( $render, $storage[ $render ] );
		}

		return $html;
	}

	/**
	 * Build HTML for a raw array of messages (for backward compatibility with edd_build_errors_html / edd_build_successes_html).
	 * Uses the same markup as to_html() for the given type.
	 *
	 * @since 3.6.5
	 * @param array<string, string> $messages Map of code => message.
	 * @param string                $type    One of error, success, info, warn.
	 * @return string
	 */
	public static function build_html_for_messages( array $messages, string $type = 'error' ): string {
		if ( ! in_array( $type, self::TYPES, true ) || empty( $messages ) ) {
			return '';
		}

		return self::build_block_html( $type, $messages );
	}

	/**
	 * Build HTML for a single message type block.
	 *
	 * @since 3.6.5
	 * @param string                $type    One of error, success, info, warn.
	 * @param array<string, string> $messages Map of code => message.
	 * @return string
	 */
	private static function build_block_html( string $type, array $messages ): string {
		$wrapper_class = isset( self::WRAPPER_CLASSES[ $type ] ) ? self::WRAPPER_CLASSES[ $type ] : 'edd_messages';

		if ( 'error' === $type ) {
			$classes = apply_filters(
				'edd_error_class',
				array(
					$wrapper_class,
					'edd-alert',
					'edd-alert-error',
				)
			);
		} else {
			$alert_class     = 'edd-alert edd-alert-' . $type;
			$default_classes = array( $wrapper_class, 'edd-alert', $alert_class );
			$classes         = apply_filters( 'edd_message_class', $default_classes, $type );
		}

		$classes = array_map( 'esc_attr', $classes );
		$label   = self::get_translated_label( $type );

		$html = '<div class="' . implode( ' ', $classes ) . '" role="alert" aria-live="assertive">';
		foreach ( $messages as $code => $message ) {
			$id      = ( 'error' === $type ? 'edd_error_' : '' ) . esc_attr( $code );
			$p_class = ( 'error' === $type ? 'edd_error' : '' );
			$html   .= '<p class="' . $p_class . '" id="' . $id . '">';
			$html   .= '<strong>' . esc_html( $label ) . '</strong>: ';
			$html   .= esc_html( $message );
			$html   .= '</p>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get translated label for a message type.
	 *
	 * @since 3.6.5
	 * @param string $type One of error, success, info, warn.
	 * @return string Translated label.
	 */
	private static function get_translated_label( string $type ): string {
		$labels = array(
			'error'   => __( 'Error', 'easy-digital-downloads' ),
			'success' => __( 'Success', 'easy-digital-downloads' ),
			'info'    => __( 'Info', 'easy-digital-downloads' ),
			'warn'    => __( 'Warning', 'easy-digital-downloads' ),
		);

		return isset( $labels[ $type ] ) ? $labels[ $type ] : esc_html( ucfirst( $type ) );
	}

	/**
	 * Get the full messages array from session, migrating legacy keys if present.
	 *
	 * @since 3.6.5
	 * @return array<string, array<string, string>> Shape: [ type => [ code => message ] ].
	 */
	private static function get_storage(): array {
		$messages = EDD()->session->get( self::SESSION_KEY );

		if ( ! is_array( $messages ) ) {
			$messages = array();
		}

		// Ensure all types exist.
		foreach ( self::TYPES as $type ) {
			if ( ! isset( $messages[ $type ] ) || ! is_array( $messages[ $type ] ) ) {
				$messages[ $type ] = array();
			}
		}

		// One-time migration from legacy keys.
		$legacy_errors  = EDD()->session->get( self::LEGACY_ERRORS_KEY );
		$legacy_success = EDD()->session->get( self::LEGACY_SUCCESS_KEY );

		if ( ( ! empty( $legacy_errors ) && is_array( $legacy_errors ) ) || ( ! empty( $legacy_success ) && is_array( $legacy_success ) ) ) {
			if ( ! empty( $legacy_errors ) && is_array( $legacy_errors ) ) {
				$messages['error'] = array_merge( $messages['error'], $legacy_errors );
			}
			if ( ! empty( $legacy_success ) && is_array( $legacy_success ) ) {
				$messages['success'] = array_merge( $messages['success'], $legacy_success );
			}
			self::persist( $messages );
			EDD()->session->set( self::LEGACY_ERRORS_KEY, null );
			EDD()->session->set( self::LEGACY_SUCCESS_KEY, null );
		}

		return $messages;
	}

	/**
	 * Persist the full messages array to session.
	 *
	 * @since 3.6.5
	 * @param array<string, array<string, string>> $messages Full storage array.
	 * @return void
	 */
	private static function persist( array $messages ): void {
		EDD()->session->set( self::SESSION_KEY, $messages );
	}

	/**
	 * Clear messages for a single type only (e.g. errors). Use this when callers
	 * intend to reset only one kind of message (e.g. "clear errors from previous attempt")
	 * without wiping success/info messages.
	 *
	 * @since 3.6.5
	 * @param string $type One of error, success, info, warn.
	 * @return void
	 */
	private static function clear_by_type( string $type ): void {
		if ( ! in_array( $type, self::TYPES, true ) ) {
			return;
		}

		$storage          = self::get_storage();
		$storage[ $type ] = array();
		self::persist( $storage );

		// Clear legacy key when clearing that type for consistency.
		if ( 'error' === $type ) {
			EDD()->session->set( self::LEGACY_ERRORS_KEY, null );
		}
		if ( 'success' === $type ) {
			EDD()->session->set( self::LEGACY_SUCCESS_KEY, null );
		}
	}
}
