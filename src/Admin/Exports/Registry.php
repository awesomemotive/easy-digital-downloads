<?php
/**
 * Registry.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2024, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.8
 */

namespace EDD\Admin\Exports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Traits\PrioritySortable;
use EDD\Utils\Exception;
use EDD\Utils\Exceptions\Invalid_Argument;
use EDD\Utils\Registry as UtilityRegistry;
use EDD\Utils\Static_Registry;

/**
 * Class Registry
 *
 * @since 3.3.8
 */
class Registry extends UtilityRegistry implements Static_Registry {

	use PrioritySortable;

	/**
	 * @since 3.3.8
	 * @var Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Registry instance.
	 *
	 * @since 3.3.8
	 *
	 * @return Registry
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Registry();
		}

		return self::$instance;
	}

	/**
	 * Registers a new exporter.
	 *
	 * @since 3.3.8
	 * @param string $exporter_id Unique ID for this exporter.
	 * @param array  $args        The arguments for this exporter.
	 * {
	 *
	 * @type string  $label       Display label for this exporter.
	 * @type string  $description Optional description.
	 * @type int     $priority    Priority in which it should appear. (Lower appears first.)
	 * @type string  $class       Exporter class name.
	 * @type string  $class_path  Optional. File path to the class. If provided, we'll use this to include
	 *                            it when necessary.
	 *                            }
	 *
	 * @throws Exception        If the exporter already exists.
	 * @throws Invalid_Argument If required arguments are missing or invalid.
	 * @throws \EDD_Exception   If the class file doesn't exist.
	 */
	public function register_exporter( string $exporter_id, array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'label'       => null,
				'description' => null,
				'priority'    => 60,
				'class'       => null,
				'class_path'  => null,
				'view'        => null,
				'button'      => __( 'Generate CSV', 'easy-digital-downloads' ),
			)
		);

		$this->validate_exporter( $exporter_id, $args );

		parent::add_item( $exporter_id, $args );
	}

	/**
	 * Validates an exporter to see if it can be added.
	 *
	 * - Checks required arguments.
	 * - If `class_path` is provided then the file must exist.
	 * - Otherwrise, `class` must be provided and exist.
	 *
	 * @since 3.3.8
	 * @param string $exporter_id Unique ID for this exporter.
	 * @param array  $args        Arguments for this exporter.
	 *
	 * @throws Invalid_Argument If required arguments are missing or invalid.
	 * @throws Exception        If the exporter already exists.
	 */
	private function validate_exporter( string $exporter_id, array $args ) {
		if ( $this->offsetExists( $exporter_id ) ) {
			throw new Exception( sprintf( 'The %s exporter already exists and cannot be registered.', $exporter_id ) );
		}

		foreach ( array( 'label', 'class' ) as $required_arg ) {
			if ( empty( $args[ $required_arg ] ) ) {
				throw Invalid_Argument::from( $required_arg, __METHOD__, $exporter_id );
			}
		}

		if ( ! empty( $args['class_path'] ) && ! file_exists( $args['class_path'] ) ) {
			throw Invalid_Argument::from( 'class_path', __METHOD__, $exporter_id );
		} elseif ( empty( $args['class_path'] ) && ! class_exists( $args['class'] ) ) {
			throw Invalid_Argument::from( 'class', __METHOD__, $exporter_id );
		}
	}
}
