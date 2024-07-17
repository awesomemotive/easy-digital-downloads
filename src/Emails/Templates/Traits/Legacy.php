<?php

namespace EDD\Emails\Templates\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Trait Legacy
 *
 * @since 3.3.0
 * @package EDD\Emails\Traits
 */
trait Legacy {

	/**
	 * Whether the email has legacy data.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	public function has_legacy_data(): bool {
		global $edd_options;
		foreach ( $this->get_options() as $option ) {
			if ( array_key_exists( $option, $edd_options ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes the legacy options from `edd_settings`.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function remove_legacy_data() {
		if ( ! $this->get_email()->id ) {
			return;
		}

		foreach ( $this->get_options() as $option ) {
			edd_delete_option( $option );
		}
	}

	/**
	 * Gets the option names for this email.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_options(): array {
		return array();
	}

	/**
	 * Gets a legacy option.
	 *
	 * @since 3.3.0
	 * @param string $key The email template key.
	 * @return mixed
	 */
	protected function get_legacy( $key ) {
		$option = $this->get_option_name( $key );
		if ( ! $option ) {
			if ( $this->installed ) {
				return false;
			}

			return $this->get_default( $key );
		}
		if ( 'status' === $key ) {
			return (bool) empty( edd_get_option( $this->get_option_name( 'disabled' ), false ) );
		}

		return stripslashes( edd_get_option( $option, $this->get_default( $key ) ) );
	}

	/**
	 * Gets the option name for a setting, if it exists.
	 *
	 * @since 3.3.0
	 * @param string $key The option key.
	 * @return string|false
	 */
	private function get_option_name( $key ) {
		$options = $this->get_options();
		if ( 'status' === $key ) {
			$key = 'disabled';
		}

		return array_key_exists( $key, $options ) && ! empty( $options[ $key ] ) ? $options[ $key ] : false;
	}
}
