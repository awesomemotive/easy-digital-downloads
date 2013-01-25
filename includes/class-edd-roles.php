<?php

class EDD_Roles {

	function __construct() { }

	/**
	 * Get capabilities for EDD
	 *
	 * @since  1.4.2
	 * @return void
	 */

	public function get_core_capabilities() {

		$capabilities = array();

		$capabilities['core'] = array(
			"edd_manage",
			"edd_view_reports"
		);

		$capability_types = array( 'edd_product', 'edd_order', 'edd_discount' );

		foreach( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(

				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}

		return apply_filters( 'edd_get_core_capabilities', $capabilities );
	}

}