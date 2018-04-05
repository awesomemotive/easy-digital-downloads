<?php
namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports\Data\Chart_Endpoint;
use EDD\Utils\Error_Logger_Interface as Error_Logger;

/**
 * Represents a manifestation of a ChartJS v2 object's attributes in PHP form.
 *
 * Primarily used to simplify translating server-side arguments into client-side ones.
 *
 * @since 3.0.0
 *
 * @see Hydrator
 */
class Manifest implements Error_Logger {

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
	 * @since 3.0
	 * @var   Dataset[]
	 */
	private $datasets = array();

	/**
	 * Represents the current Chart_Endpoint instance.
	 *
	 * @since 3.0
	 * @var   Chart_Endpoint
	 */
	private $endpoint;

	/**
	 * Holds errors related to instantiating the manifest.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	protected $errors;

	/**
	 * Sets up the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string         $type    Type of chart manifest.
	 * @param Chart_Endpoint $endpoint Chart endpoint.
	 * @param array          $options Array of options to populate the manifest with.
	 */
	public function __construct( $type, $endpoint, $options ) {
		$this->setup_error_logger();

		$this->set_type( $type );
		$this->set_endpoint( $endpoint );
		$this->set_options( $options );
	}

	/**
	 * Retrieves the chart type.
	 *
	 * @since 3.0
	 *
	 * @return string Chart type.
	 */
	public function get_type() {
		return $this->type;
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
	 * Retrieves the chart endpoint object for this manifest.
	 *
	 * @since 3.0
	 *
	 * @return Chart_Endpoint Chart endpoint.
	 */
	public function get_endpoint() {
		return $this->endpoint;
	}

	/**
	 * Sets the chart endpoint object.
	 *
	 * @since 3.0
	 *
	 * @param EDD\Reports\Data\Chart_Endpoint $endpoint Chart_Endpoint object.
	 */
	private function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Stores the unfiltered chart options for later access.
	 *
	 * @since 3.0
	 *
	 * @param array $options Chart options and datasets.
	 */
	private function set_options( $options ) {
		if ( ! empty( $options['datasets'] ) && is_array( $options['datasets'] ) ) {

			foreach ( $options['datasets'] as $id => $data ) {
				$this->add_dataset( $id, $data );
			}

		} else {

			$message = sprintf( 'The %s endpoint has no datasets.', $this->get_endpoint()->get_id() );

			$this->errors->add( 'missing_chart_datasets', $message, $this->get_endpoint() );

		}

		unset( $options['datasets'] );

		$this->options = $options;
	}

	/**
	 * Retrieves parsed options for the chart manifest.
	 *
	 * @since 3.0
	 *
	 * @return array Chart options.
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Retrieves the manifest datasets.
	 *
	 * @since 3.0
	 *
	 * @return Dataset[] Datasets for this chart if any are defined, otherwise an empty array.
	 */
	public function get_datasets() {
		return $this->datasets;
	}

	/**
	 * Determines whether the current chart manifest contains any datasets.
	 *
	 * @since 3.0
	 *
	 * @return bool True if there are datasets, otherwise false.
	 */
	public function has_datasets() {
		return ! empty( $this->get_datasets() );
	}

	/**
	 * Adds a dataset.
	 *
	 * @since 3.0
	 *
	 * @param string $dataset_id ID to associate the dataset with.
	 * @param array  $options    Dataset options.
	 * @return bool True if the dataset was added, otherwise false.
	 */
	public function add_dataset( $dataset_id, $options ) {
		$handler = $this->get_dataset_handler();

		if ( ! empty( $handler ) && class_exists( $handler ) ) {
			/** @var Dataset $dataset */
			$dataset = new $handler( $dataset_id, $this->get_endpoint(), $options );

			if ( ! $dataset->has_errors() ) {

				$this->datasets[ $dataset_id ] = $dataset;

				return true;

			} else {

				$this->errors->add( 'dataset_errors_passthrough', 'Errors have been passed through from dataset parsing.', $dataset->get_errors() );

			}

		}

		return false;
	}

	/**
	 * Retrieves the handler class for the current dataset type.
	 *
	 * @since 3.0
	 *
	 * @return string Dataset handler class.
	 */
	public function get_dataset_handler() {
		$handler = '';

		switch( $this->get_type() ) {

			case 'bar':
				$handler = 'EDD\Reports\Data\Charts\v2\Bar_Dataset';
				break;

			case 'line':
			default:
				$handler = 'EDD\Reports\Data\Charts\v2\Line_Dataset';
				break;


		}

		return $handler;
	}


	/**
	 * Renders the manifest in JS form.
	 *
	 * @since 3.0
	 */
	public function render() {

		$hydrator_data = array(
			'data'    => $this->get_chart_data(),
			'options' => $this->get_chart_options()
		);

		try {

			$hydrator = EDD()->utils->get_hydrator();

			$chart  = $hydrator->hydrate( 'EDD\Reports\Data\Charts\v2\Chart_Template', $hydrator_data );
			$config = $hydrator->extract( $chart );

		} catch ( \ReflectionException $exception ) {

			edd_debug_log( $exception->getMessage() );

			return;

		}

		$endpoint  = $this->get_endpoint();
		$default   = "edd_reports_graph_{$endpoint->get_id()}";
		$target_el = $endpoint->get_display_arg( 'target', $default );

		// Add the chart type to the config.
		$config['type'] = $this->get_type();

		$config = json_encode( $config );
		$type   = $this->get_type();
		?>
		<canvas id="<?php echo esc_attr( $target_el ); ?>"></canvas>

		<script type="application/javascript">

			// Bring in chart config.
			<?php echo esc_js( $target_el ); ?> = <?php echo $config; ?>;

			// Convert dataset x-axis values to moment() objects.
			<?php echo esc_js( $target_el ); ?>.data.datasets.forEach( function( dataset ) {

				dataset.data.forEach( function( pair, index ) {
					pair.x = moment( pair.x );
				} );

			} );

			// Set min and max moment() values for the x-axis.
			<?php echo esc_js( $target_el ); ?>.options.scales.xAxes.forEach( function( xaxis ) {
				// TODO hook up the date range.
				xaxis.time.min = moment().startOf( 'month' );
				xaxis.time.max = moment().endOf( 'month' );
			} );

			// Instantiate the chart.
			<?php echo esc_js( $target_el ); ?>_chart = new Chart(
				$( '#<?php echo esc_js( $target_el ); ?>' ),
				<?php echo esc_js( $target_el ); ?>
			);

		</script>
		<?php
	}

	/**
	 * Retrieves the parsed chart datasets as an object.
	 *
	 * @since 3.0
	 *
	 * @return array Parsed chart data.
	 */
	public function get_chart_data() {
		$data = array();

		if ( $this->has_datasets() ) {
			$datasets = $this->get_datasets();

			$data['datasets'] = array();

			foreach ( $datasets as $id => $set ) {
				if ( $set->has_errors() ) {
					continue;
				}

				$data['datasets'][] = $set->get_options();
			}
		}

		return $data;
	}

	/**
	 * Retrieves the parsed chart options as an object.
	 *
	 * @since 3.0
	 *
	 * @return array Parsed chart options.
	 */
	public function get_chart_options() {
		$defaults = array(
			'responsive' => true,
			'hoverMode'  => 'index',
			'stacked'    => false,
			'title'      => array(
				'display' => true,
				'text'    => $this->get_endpoint()->get_label(),
			),
			'scales'     => array(
				'xAxes' => array(
					array(
						'type'     => "time",
						'display'  => true,
						'ticks'    => array(
							'source' => 'auto',
						),
						'position' => 'bottom',
						'time'     => array(
							'unit' => 'day',
							'displayFormats' => array(
								'day' => 'MMM D',
							),
						),
					),
				),
				'yAxes' => array(
					array(
						'type'     => 'linear',
						'display'  => true,
						'position' => 'left',
					),
				),
			)
		);

		return array_merge( $defaults, $this->get_options() );
	}

	/**
	 * Determines whether the dataset has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		if ( method_exists( $this->errors, 'has_errors' ) ) {
			return $this->errors->has_errors();
		} else {
			$errors = $this->errors->get_error_codes();

			return ! empty( $errors );
		}
	}

	/**
	 * Retrieves any logged errors for the dataset.
	 *
	 * @since 3.0
	 *
	 * @return \WP_Error WP_Error object for the current dataset.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Sets up the WP_Error instance.
	 *
	 * @since 3.0
	 */
	public function setup_error_logger() {
		if ( ! isset( $this->errors ) ) {
			$this->errors = new \WP_Error();
		}
	}

}
