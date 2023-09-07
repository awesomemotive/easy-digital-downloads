<?php
/**
 * Gets the custom table data for the Site Health data.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Loads custom table data into Site Health
 *
 * @since 3.1.2
 */
class Tables {

	/**
	 * Gets the table data array.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Custom Tables', 'easy-digital-downloads' ),
			'fields' => $this->get_tables(),
		);
	}

	/**
	 * Gets the name/version of each EDD table that's registered as a component.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_tables() {
		$tables = array(
			'default' => array(
				'label' => 'Table Name',
				'value' => 'Version / Count',
			),
		);
		foreach ( EDD()->components as $component ) {

			// Object.
			$thing = $component->get_interface( 'table' );
			if ( ! empty( $thing ) ) {
				$tables[ $thing->name ] = array(
					'label' => $thing->name,
					'value' => $this->get_value_string( $thing ),
				);
			}

			// Meta.
			$thing = $component->get_interface( 'meta' );
			if ( ! empty( $thing ) ) {
				$tables[ $thing->name ] = array(
					'label' => $thing->name,
					'value' => $this->get_value_string( $thing ),
				);
			}
		}

		return $tables;
	}

	/**
	 * Gets the value string for the table data.
	 *
	 * @since 3.1.2
	 * @param object $thing The table or meta object.
	 * @return string
	 */
	private function get_value_string( $thing ) {
		return $thing->exists() ?
			sprintf( '%s / %s', $thing->get_version(), $thing->count() ) :
			'Error &mdash; table is missing';
	}
}
