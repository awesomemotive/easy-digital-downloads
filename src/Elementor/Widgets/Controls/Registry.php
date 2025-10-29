<?php
/**
 * Control Registry for EDD Elementor Widgets.
 *
 * @package     EDD\Elementor\Widgets\Controls
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Controls;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Widgets\Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;

/**
 * Control Registry for managing widget controls without tight coupling.
 *
 * @since 3.6.0
 */
class Registry {

	/**
	 * The widget instance.
	 *
	 * @since 3.6.0
	 * @var Base
	 */
	private $widget;

	/**
	 * Selector prefix for the widget.
	 *
	 * @since 3.6.0
	 * @var string
	 */
	private $selector_prefix;

	/**
	 * Selector mappings for the widget.
	 *
	 * @since 3.6.0
	 * @var array
	 */
	private $selector_mappings;

	/**
	 * Constructor.
	 *
	 * @since 3.6.0
	 * @param Base   $widget The widget instance.
	 * @param string $selector_prefix Optional. Selector prefix. Default '{{WRAPPER}}'.
	 * @param array  $selector_mappings Optional. Widget-specific selector mappings. Default empty array.
	 */
	public function __construct( Base $widget, string $selector_prefix = '{{WRAPPER}}', array $selector_mappings = array() ) {
		$this->widget            = $widget;
		$this->selector_prefix   = $selector_prefix;
		$this->selector_mappings = $selector_mappings;
	}

	/**
	 * Register controls from configuration array.
	 *
	 * @since 3.6.0
	 * @param array $config Configuration array.
	 */
	public function register_from_config( array $config ) {
		foreach ( $config as $section_id => $section_config ) {
			$this->register_section( $section_id, $section_config );
		}
	}

	/**
	 * Register a controls section.
	 *
	 * @since 3.6.0
	 * @param string $section_id Section ID.
	 * @param array  $section_config Section configuration.
	 */
	public function register_section( string $section_id, array $section_config ) {

		if ( empty( $section_config['controls'] ) ) {
			return;
		}

		// Start section.
		$tab = Controls_Manager::TAB_STYLE;
		if ( ! empty( $section_config['tab'] ) && 'content' === $section_config['tab'] ) {
			$tab = Controls_Manager::TAB_CONTENT;
		}

		$this->widget->start_controls_section(
			$section_id,
			array(
				'label' => $section_config['label'] ?? '',
				'tab'   => $tab,
			)
		);

		$this->register_controls( $section_config['controls'] );

		$this->widget->end_controls_section();
	}

	/**
	 * Register controls.
	 *
	 * @since 3.6.0
	 * @param array $controls Controls configuration.
	 */
	public function register_controls( array $controls ) {
		foreach ( $controls as $control_id => $control_config ) {
			// Skip empty configurations.
			if ( empty( $control_config ) ) {
				continue;
			}

			if ( $this->is_group_control( $control_config ) ) {
				$this->register_control_group( $control_id, $control_config );

				continue;
			}

			$this->register_control( $control_id, $control_config );
		}
	}

	/**
	 * Register a single control.
	 *
	 * @since 3.6.0
	 * @param string     $control_id     Control ID.
	 * @param array|null $control_config Control configuration.
	 */
	public function register_control( string $control_id, ?array $control_config ) {
		if ( empty( $control_config ) ) {
			return;
		}

		// Process selectors if present.
		if ( ! empty( $control_config['selectors'] ) ) {
			$control_config['selectors'] = $this->process_selectors( $control_config['selectors'] );
		}

		// Handle responsive controls.
		if ( ! empty( $control_config['responsive'] ) ) {
			$this->widget->add_responsive_control( $control_id, $control_config );
		} else {
			$this->widget->add_control( $control_id, $control_config );
		}
	}

	/**
	 * Register a single control group.
	 *
	 * Note: This method is called automatically by register_controls() when it
	 * detects a control with a group type (typography, border, etc.).
	 *
	 * @since 3.6.0
	 * @param string $group_id Group ID.
	 * @param array  $group_config Group configuration.
	 */
	public function register_control_group( string $group_id, array $group_config ) {
		$group_type = $group_config['type'] ?? 'typography';

		// Process selectors if present.
		if ( ! empty( $group_config['selector'] ) ) {
			$group_config['selector'] = $this->process_selector( $group_config['selector'] );
		}

		// Remove 'type' from config as it's not needed for add_group_control.
		unset( $group_config['type'] );

		$control_args = array_merge( array( 'name' => $group_id ), $group_config );

		// Register the appropriate group control.
		switch ( $group_type ) {
			case 'typography':
				$this->widget->add_group_control( Group_Control_Typography::get_type(), $control_args );
				break;
			case 'border':
				$this->widget->add_group_control( Group_Control_Border::get_type(), $control_args );
				break;
			case 'box_shadow':
				$this->widget->add_group_control( Group_Control_Box_Shadow::get_type(), $control_args );
				break;
			case 'text_shadow':
				$this->widget->add_group_control( Group_Control_Text_Shadow::get_type(), $control_args );
				break;
			case 'background':
				$this->widget->add_group_control( Group_Control_Background::get_type(), $control_args );
				break;
			case 'image_size':
				$this->widget->add_group_control( Group_Control_Image_Size::get_type(), $control_args );
				break;
			case 'css_filter':
				$this->widget->add_group_control( Group_Control_Css_Filter::get_type(), $control_args );
				break;
		}
	}

	/**
	 * Process selectors to add widget prefix.
	 *
	 * @since 3.6.0
	 * @param array|string $selectors Selectors to process.
	 * @return array|string Processed selectors.
	 */
	public function process_selectors( $selectors ) {
		if ( is_array( $selectors ) ) {
			$processed = array();
			foreach ( $selectors as $selector => $properties ) {
				$processed[ $this->process_selector( $selector ) ] = $properties;
			}
			return $processed;
		}

		return $this->process_selector( $selectors );
	}

	/**
	 * Process a single selector to add widget prefix.
	 *
	 * @since 3.6.0
	 * @param string $selector Selector to process.
	 * @return string Processed selector.
	 */
	public function process_selector( string $selector ): string {
		// Apply any selector mapping first.
		$selector = $this->map_selector( $selector );

		// Don't double-prefix if already prefixed.
		if ( strpos( $selector, $this->selector_prefix ) === 0 ) {
			return $selector;
		}

		return $this->selector_prefix . ' ' . $selector;
	}

	/**
	 * Map selectors using widget-specific mappings.
	 *
	 * @since 3.6.0
	 * @param string $selector The selector to map.
	 * @return string The mapped selector.
	 */
	private function map_selector( string $selector ): string {
		if ( empty( $this->selector_mappings ) ) {
			return $selector;
		}

		return str_replace( array_keys( $this->selector_mappings ), array_values( $this->selector_mappings ), $selector );
	}

	/**
	 * Determine if a control is a group control.
	 *
	 * @since 3.6.0
	 * @param array $control_config Control configuration.
	 * @return bool
	 */
	private function is_group_control( array $control_config ): bool {
		$group_types = array( 'typography', 'border', 'box_shadow', 'text_shadow', 'background', 'image_size', 'css_filter' );

		return ! empty( $control_config['type'] ) && in_array( $control_config['type'], $group_types, true );
	}
}
