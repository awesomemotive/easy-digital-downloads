<?php
/**
 * Reports API - Table Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Handler for building a table endpoint in the Reports API.
 *
 * @since 3.0
 */
final class Table_Endpoint extends Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'table';

	/**
	 * List table instance.
	 *
	 * @since 3.0
	 * @var   WP_List_Table
	 */
	private $list_table;

	/**
	 * Represents the full path to the list table class file.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $class_file;

	/**
	 * Sets up the table endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $args Table endpoint attributes.
	 */
	public function __construct( array $args ) {
		$this->errors = new \WP_Error();

		// ID and Label.
		$this->set_props( $args );

		// List table set up and dumping display args.
		$this->setup_list_table( $args );

		// Parse display attributes from defaults.
		$args = $this->parse_display_props( $args );

		parent::__construct( $args );
	}

	/**
	 * Sets display-related properties for the Endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $endpoint Endpoint record from the registry.
	 */
	private function parse_display_props( $endpoint ) {

		$view_type = $this->get_view();

		if ( ! empty( $endpoint['views'][ $view_type ] ) ) {

			$view_atts = $endpoint['views'][ $view_type ];

			$list_table = $this->get_list_table();

			if ( null === $list_table ) {
				return $endpoint;
			}

			$endpoint['views'][ $view_type ] = $this->maybe_convert_callbacks_to_methods( $view_atts, $list_table );
		}

		return $endpoint;
	}

	/**
	 * Sets attributes related to the list table.
	 *
	 * @since 3.0
	 *
	 * @param array $endpoint Table endpoint arguments.
	 */
	private function setup_list_table( $endpoint ) {

		if ( ! empty( $endpoint['views'][ $this->view ]['display_args'] ) ) {

			$display_args = $endpoint['views'][ $this->view ]['display_args'];

			if ( ! empty( $display_args['class_name'] ) ) {

				if ( ! empty( $display_args['class_file'] ) ) {

					$this->set_class_file( $display_args['class_file'] );

					$this->set_list_table( $display_args['class_name'] );

				} else {

					$this->errors->add(
						'missing_table_class_file',
						sprintf( 'The list table class file for the \'%1$s\' endpoint is missing.', $this->get_id() )
					);

				}

			} else {

				$this->errors->add(
					'missing_table_class_name',
					sprintf( 'The list table class name for the \'%1$s\' endpoint is missing.',
						$this->get_id()
					)
				);

			}

			// Dump the display args as they're no longer needed.
			$endpoint['views'][ $this->view ]['display_args'] = array();

		}

	}

	/**
	 * Retrieves the list table class file.
	 *
	 * @since 3.0
	 *
	 * @return string|null Class file if set, otherwise null.
	 */
	public function get_class_file() {
		return $this->class_file;
	}

	/**
	 * Sets the list table class file.
	 *
	 * @since 3.0
	 *
	 * @param string $file Class file.
	 */
	private function set_class_file( $file ) {
		if ( false === strpos( $file, '..' ) && false === strpos( $file, './' ) ) {
			$this->class_file = $file;
		}
	}

	/**
	 * Retrieves the list table instance.
	 *
	 * @since 3.0
	 *
	 * @return WP_List_Table|null List table instance if set, otherwise null.
	 */
	public function get_list_table() {
		return $this->list_table;
	}

	/**
	 * Sets the list table instance.
	 *
	 * @since 3.0
	 *
	 * @see get_class_file()
	 *
	 * @param string $class List table class name.
	 */
	private function set_list_table( $class ) {
		if ( ! class_exists( $class ) ) {
			$path_to_file = $this->get_class_file();

			if ( file_exists( $path_to_file ) ) {
				require_once $path_to_file;
			}
		}
		$this->list_table = new $class;
	}

	/**
	 * Display logic for the current table endpoint.
	 *
	 * @since 3.0
	 */
	public function display() {
		$callback = $this->get_display_callback();

		if ( is_callable( $callback ) ) {
			$table = $this->get_list_table();

			if ( null !== $table ) {
				// Prep the table data for display (prepare_items).
				$this->get_data();

				call_user_func_array( $callback, array(
					$this, // Endpoint
					$table, // Table
					$this->get_display_args(), // Args
				) );
			}
		}
	}

}
