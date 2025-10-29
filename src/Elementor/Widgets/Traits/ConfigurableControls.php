<?php
/**
 * Configurable Controls Trait for EDD Elementor Widgets.
 *
 * @package     EDD\Elementor\Widgets\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Widgets\Controls\Registry;

/**
 * Configurable Controls Trait for EDD Elementor Widgets.
 *
 * Provides a clean way to register controls using configuration arrays
 * without the need to pass widget instances around.
 *
 * @since 3.6.0
 */
trait ConfigurableControls {

	/**
	 * Control registry instance.
	 *
	 * @since 3.6.0
	 * @var Registry
	 */
	private $control_registry;

	/**
	 * Get the control registry instance.
	 *
	 * @since 3.6.0
	 * @return Registry
	 */
	private function get_control_registry(): Registry {
		if ( ! $this->control_registry ) {
			$this->control_registry = new Registry( $this, $this->get_selector_prefix(), $this->get_selector_mappings() );
		}

		return $this->control_registry;
	}

	/**
	 * Get the selector prefix for this widget.
	 *
	 * Override this method in your widget to customize the selector prefix.
	 *
	 * @since 3.6.0
	 * @return string
	 */
	protected function get_selector_prefix(): string {
		return '{{WRAPPER}}';
	}

	/**
	 * Get the selector mappings for this widget.
	 *
	 * Override this method in your widget to provide custom selector mappings.
	 * Useful for widgets that need to transform certain selectors for compatibility.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected function get_selector_mappings(): array {
		return array();
	}

	/**
	 * Register controls from configuration.
	 *
	 * @since 3.6.0
	 * @param array $config Configuration array.
	 */
	protected function register_controls_from_config( array $config ) {
		$this->get_control_registry()->register_from_config( $config );
	}

	/**
	 * Register a control section from configuration.
	 *
	 * @since 3.6.0
	 * @param string $section_id Section ID.
	 * @param array  $section_config Section configuration.
	 */
	protected function register_section_from_config( string $section_id, array $section_config ) {
		$this->get_control_registry()->register_section( $section_id, $section_config );
	}

	/**
	 * Register multiple control configurations.
	 *
	 * Useful for combining configurations from different sources.
	 *
	 * @since 3.6.0
	 * @param array ...$configs Multiple configuration arrays.
	 */
	protected function register_multiple_configs( ...$configs ) {
		foreach ( $configs as $config ) {
			$this->register_controls_from_config( $config );
		}
	}

	/**
	 * Merge multiple control configurations.
	 *
	 * @since 3.6.0
	 * @param array ...$configs Multiple configuration arrays.
	 * @return array Merged configuration.
	 */
	protected function merge_control_configs( ...$configs ): array {
		$merged = array();
		foreach ( $configs as $config ) {
			$merged = array_merge( $merged, $config );
		}
		return $merged;
	}

	/**
	 * Get the selectors with widget prefix applied.
	 *
	 * @since 3.6.0
	 * @param string|array $selector The selector to process.
	 * @return string|array The processed selectors.
	 */
	protected function selectors( $selector ) {
		return $this->get_control_registry()->process_selectors( $selector );
	}

	/**
	 * Register controls using a callback that provides the registry.
	 *
	 * Useful for more complex control registration scenarios.
	 *
	 * @since 3.6.0
	 * @param callable $callback Callback function that receives the registry.
	 */
	protected function register_with_registry( callable $callback ) {
		$callback( $this->get_control_registry() );
	}
}
