<?php
namespace EDD\Reports\Data\Charts\v2;

/**
 * Represents a manifestation of a ChartJS v2 object's attributes in PHP form.
 *
 * Primarily used to simplify translating server-side arguments into client-side ones.
 *
 * @since 3.0.0
 */
class Manifest {

	/**
	 * Datasets associated with the current graph.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	public $datasets = array();

	/**
	 * Sets up the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string $type    Type of chart manifest.
	 * @param array  $options Array of options to populate the manifest with.
	 */
	public function __construct( $type, $options ) {

	}

}
