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
	 * Represents the chart type to be manifested.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $type;

	/**
	 * Represents the unfiltered chart options for the manifest.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $options = array();

	/**
	 * Datasets associated with the current graph.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	private $datasets = array();

	/**
	 * Sets up the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string $type    Type of chart manifest.
	 * @param array  $options Array of options to populate the manifest with.
	 */
	public function __construct( $type, $options ) {
		$this->set_type( $type );
		$this->set_options( $options );
	}

	/**
	 * Sets the chart type for the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string $type Chart type to be manifested.
	 */
	private function set_type( $type ) {
		$this->type = sanitize_key( $type );
	}

	/**
	 * Stores the unfiltered chart options for later access.
	 *
	 * @since 3.0
	 *
	 * @param array $options Array of chart options to be populated into datasets.
	 */
	private function set_options( $options ) {
		$this->options = $options;
	}

}
