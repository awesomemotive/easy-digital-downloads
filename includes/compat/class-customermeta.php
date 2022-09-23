<?php
/**
 * Backwards Compatibility Handler for Customer Meta.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

use EDD\Database\Table;

class CustomerMeta extends Base {

	protected $component = 'customermeta';

	/**
	 * Magic method to handle calls to properties that no longer exist.
	 *
	 * @since 3.0
	 *
	 * @param string $property Name of the property.
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		switch( $property ) {
			case 'table_name' :
				global $wpdb;
				return $wpdb->edd_customermeta;

			case 'primary_key' :
				return 'meta_id';

			case 'version' :
				$table = edd_get_component_interface( 'customer', 'meta' );

				return $table instanceof Table ? $table->get_version() : false;
		}

		return null;
	}

	/**
	 * Magic method to handle calls to method that no longer exist.
	 *
	 * @since 3.0
	 *
	 * @param string $name      Name of the method.
	 * @param array  $arguments Enumerated array containing the parameters passed to the $name'ed method.
	 * @return mixed Dependent on the method being dispatched to.
	 */
	public function __call( $name, $arguments ) {
		switch ( $name ) {
			case 'get_meta' :
				return edd_get_customer_meta(
					isset( $arguments[0] ) ? $arguments[0] : 0,
					isset( $arguments[1] ) ? $arguments[1] : '',
					isset( $arguments[2] ) ? $arguments[2] : false
				);

			case 'add_meta' :
				return edd_add_customer_meta(
					isset( $arguments[0] ) ? $arguments[0] : 0,
					isset( $arguments[1] ) ? $arguments[1] : '',
					isset( $arguments[2] ) ? $arguments[2] : false,
					isset( $arguments[3] ) ? $arguments[3] : false
				);

			case 'update_meta' :
				return edd_update_customer_meta(
					isset( $arguments[0] ) ? $arguments[0] : 0,
					isset( $arguments[1] ) ? $arguments[1] : '',
					isset( $arguments[2] ) ? $arguments[2] : false,
					isset( $arguments[3] ) ? $arguments[3] : ''
				);

			case 'delete_meta' :
				return edd_delete_customer_meta(
					isset( $arguments[0] ) ? $arguments[0] : 0,
					isset( $arguments[1] ) ? $arguments[1] : '',
					isset( $arguments[2] ) ? $arguments[2] : ''
				);
		}

		return null;
	}

	/**
	 * Backwards compatibility hooks for customer meta.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {
		// No hooks.
	}
}
