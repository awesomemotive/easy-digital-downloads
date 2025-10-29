<?php
/**
 * Elementor Integration
 *
 * @package     EDD\Integrations
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Integrations;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Elementor class
 *
 * @since 3.6.0
 */
class Elementor implements Integration {

	/**
	 * Check if Elementor is loaded.
	 *
	 * @since 3.6.0
	 * @return bool
	 */
	public function can_load(): bool {
		return defined( 'ELEMENTOR_VERSION' ) && class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @since 3.6.0
	 */
	public function subscribe() {
		// EDD Blocks are loaded in 500.
		add_action( 'plugins_loaded', array( $this, 'load_elementor' ), 550 );
	}

	/**
	 * Load Elementor blocks.
	 *
	 * @since 3.6.0
	 */
	public function load_elementor() {
		// Elementor widget is based on EDD Blocks.
		if ( ! defined( 'EDD_BLOCKS_DIR' ) ) {
			return;
		}
		$loader = new \EDD\Elementor\Loader();
		$loader->add_events();
	}
}
