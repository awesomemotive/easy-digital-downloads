<?php
namespace EDD\Integrations;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles registering the `easydigitaldownloads` username in the WPCode snippets library.
 *
 * @since 3.2.4
 */
class WPCode {

	/**
	 * Registers the event subscribers.
	 *
	 * @since 3.2.4
	 * @return void
	 */
	public function subscribe() {
		add_action( 'load-code-snippets_page_wpcode-snippet-manager', array( $this, 'register_username' ), 10, 3 );
		add_action( 'load-code-snippets_page_wpcode-library', array( $this, 'register_username' ), 10, 3 );
	}

	/**
	 * Registers our username in the WPCode snippets library.
	 *
	 * @since 3.2.4
	 * @return void
	 */
	public function register_username() {
		if ( ! function_exists( 'wpcode_register_library_username' ) ) {
			return;
		}

		wpcode_register_library_username( 'easydigitaldownloads', 'EDD', EDD_VERSION );
	}
}
