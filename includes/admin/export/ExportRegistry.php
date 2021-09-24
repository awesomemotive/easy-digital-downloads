<?php
/**
 * ExportRegistry.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.x
 */

namespace EDD\Admin\Export;

use EDD\PrioritySortable;
use EDD\Utils\Exception;
use EDD\Utils\Exceptions\Invalid_Argument;
use EDD\Utils\Registry;
use EDD\Utils\Static_Registry;

class ExportRegistry extends Registry implements Static_Registry {

	use PrioritySortable;

	/**
	 * @since 3.x
	 * @var ExportRegistry
	 */
	private static $instance;

	/**
	 * Retrieves the one true ExportRegistry instance.
	 *
	 * @since 3.x
	 *
	 * @return ExportRegistry
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new ExportRegistry();
		}

		return self::$instance;
	}


	/**
	 * Registers a new exporter.
	 *
	 * @since 3.x
	 *
	 * @param string $exporter_id Unique ID for this exporter.
	 * @param array  $args        {
	 *
	 * @type string  $label       Display label for this exporter.
	 * @type string  $description Optional description.
	 * @type int     $priority    Priority in which it should appear. (Lower appears first.)
	 * @type string  $class       Exporter class name.
	 * @type string  $class_path  Optional. File path to the class. If provided, we'll use this to include
	 *                            it when necessary.
	 *                            }
	 *
	 * @throws Exception
	 * @throws Invalid_Argument
	 * @throws \EDD_Exception
	 */
	public function register_exporter( $exporter_id, $args ) {
		$args = wp_parse_args( $args, array(
			'label'       => null,
			'description' => null,
			'priority'    => 60,
			'class'       => null,
			'class_path'  => null,
		) );

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
	 * @param string $exporter_id
	 * @param array  $args
	 *
	 * @throws Invalid_Argument
	 * @throws Exception
	 */
	private function validate_exporter( $exporter_id, $args ) {
		if ( $this->offsetExists( $exporter_id ) ) {
			throw new Exception( sprintf( 'The %s exporter already exists and cannot be registered.', $exporter_id ) );
		}

		foreach ( array( 'label', 'class' ) as $required_arg ) {
			if ( empty( $args[ $required_arg ] ) ) {
				throw Invalid_Argument::from( $required_arg, __METHOD__, $exporter_id );
			}
		}

		if ( ! empty( $args['class_path'] ) ) {
			if ( ! file_exists( $args['class_path'] ) ) {
				throw Invalid_Argument::from( 'class_path', __METHOD__, $exporter_id );
			}
		} elseif ( ! class_exists( $args['class'] ) ) {
			// Class is only required to exist if a path hasn't been provided.
			throw Invalid_Argument::from( 'class', __METHOD__, $exporter_id );
		}
	}
}
