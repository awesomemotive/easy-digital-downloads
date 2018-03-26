<?php
/**
 * Reports API - Chart Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

use EDD\Reports\Data\Charts\v2 as Chart;

/**
 * Handler for building a chart endpoint in the Reports API.
 *
 * @since 3.0
 */
class Chart_Endpoint extends Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'chart';

	/**
	 * Represents the chart type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $type;

	/**
	 * Represents the PHP manifestation of the chart data and options.
	 *
	 * @since 3.0
	 * @var   Chart\Manifest
	 */
	private $manifest;

	/**
	 * Sets up the chart endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $args Chart endpoint attributes.
	 */
	public function __construct( array $args ) {
		$this->errors = new \WP_Error();

		// ID and Label.
		$this->set_props( $args );

		// Chart props.
		$this->setup_chart( $args );

		$args = $this->parse_display_props( $args );

		parent::__construct( $args );
	}

	/**
	 * Sets up the chart props needed for rendering.
	 *
	 * @since 3.0
	 *
	 * @param array $atts Endpoint attributes.
	 */
	private function setup_chart( $atts ) {
		$view_type = $this->get_view();

		if ( ! empty( $atts['views'][ $view_type ] ) ) {

			$view_atts = $atts['views'][ $view_type ];

			if ( ! empty( $view_atts['type'] ) ) {
				$this->set_type( $view_atts['type'] );
			} else {
				// TODO: Throw exception.
			}

			if ( ! empty( $view_atts['options'] ) ) {
				$this->set_options( $view_atts['options'] );
			} else {
				// TODO: Throw exception.
			}

			$this->set_manifest();
		}
	}

	/**
	 * Sets display-related properties for the Endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $atts Endpoint attributes.
	 */
	private function parse_display_props( $atts ) {

		$view_type = $this->get_view();

		if ( ! empty( $atts['views'][ $this->view ] ) ) {

			$view_atts = $atts['views'][ $this->view ];

			// Inject the display callback if 'display'.
			if ( ! empty( $view_atts['display_callback'] ) && 'display' === $view_atts['display_callback'] ) {
				$view_atts['display_callback'] = array( $this, 'build_graph' );
			}

			$endpoint['views'][ $view_type ] = $view_atts;
		}

		return $endpoint;
	}

	/**
	 * Retrieves the graphing library options set for the current endpoint.
	 *
	 * @since 3.0
	 *
	 * @return array Options set for the current graph endpoint.
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Sets options for displaying the graph.
	 *
	 * @since 3.0
	 *
	 * @param array $options Options for displaying the graph via the graphing library.
	 */
	protected function set_options( $options ) {
		$this->options = $options;
	}

	/**
	 * Retrieves the value of a graph option if set.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Option key to retrieve a value for.
	 * @return mixed Value of the option key if set, otherwise an empty string.
	 */
	public function get( $key ) {
		if ( isset( $this->options[ $key ] ) ) {
			$value = $this->options[ $key ];
		} else {
			$value = '';
		}

		return $value;
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
	 * Sets the chart type.
	 *
	 * @since 3.0
	 *
	 * @param string $type Chart type to set.
	 */
	private function set_type( $type ) {
		$this->type = sanitize_key( $type );
	}

	/**
	 * Retrieves the manifest instance.
	 *
	 * @since 3.0
	 *
	 * @return Chart\Manifest Chart manifest.
	 */
	public function get_manifest() {
		return $this->manifest;
	}

	/**
	 * Instantiates the manifest based on chart type and options.
	 *
	 * @since 3.0
	 */
	private function set_manifest() {
		$this->manifest = new Chart\Manifest( $this->get_type(), $this->get_options() );
	}

	/**
	 * Builds and outputs the graph JS to the page.
	 *
	 * @since 3.0
	 */
	public function build_graph() {
		$data = $this->get_data();

		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		// Start parsing the manifest for output as JS.
		$manifest = $this->get_manifest();
		$manifest = json_encode( $manifest );

		$key_var = substr( md5( uniqid( rand() ) ), 0, 7 );

		$dataset_count = count( $data );
		$current       = 0;
		$target_el     = $this->get_display_arg( 'target', 'edd-reports-graph' );

		?>
		<canvas id="<?php echo esc_attr( $target_el ); ?>"></canvas>

		<script type="application/javascript">

			var date = moment( 'today', 'MMMM DD YYYY' );

			var <?php echo esc_js( $key_var ); ?> = {
				<?php foreach ( $data as $set => $atts ) : ?>
				datasets: [{
					label: <?php echo empty( $atts['label' ] ? '' : esc_js( $atts['label'] ) ); ?>,
					borderColor: <?php echo empty( $atts['border_color'] ) ? 'rgb(237,194,64)' : esc_js( $atts['border_color'] ); ?>,
					backgroundColor: <?php echo empty( $atts['bg_color'] ) ? 'rgb(237,194,64)' : esc_js( $atts['bg_color'] ); ?>,
					fill: false,

					data: [
						<?php
						if ( ! empty( $atts['data'] ) ) :
						foreach ( $atts['data'] as $index => $axes ) :
						$axes_count = count( $axes );
						?>
						{
							<?php if ( 2 === $axes_count  ) : ?>
							x: moment( <?php echo $axes[0] * 1000; ?> ),
							y: <?php echo $axes[1]; ?>
							<?php elseif ( 1 === $axes_count ) : ?>
							x: moment( <?php echo $axes[0] * 1000; ?> ),
			<?php endif; ?>
			},
			<?php
			endforeach;
			endif;
			?>
			],
			<?php if ( ++$current !== $dataset_count ) : ?>
			}, {
				<?php endif; ?>
				<?php endforeach; ?>
			};
			};

			<?php echo esc_js( $key_var . '_Chart' ); ?> = Chart.Line( $( '#<?php echo esc_js( $target_el ); ?>' ), {
				data: <?php echo esc_js( $key_var ); ?>,
				options: {
					responsive: true,
					hoverMode: 'index',
					stacked: false,
					title:{
						display: true,
						text: <?php echo esc_js( $this->get_label() ); ?>
					},
					scales: {
						yAxes: [{
							type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
							display: true,
							position: "left",
							id: "y-axis-1",
						} ],
						xAxes: [{
							type: 'time',
							display: true,
							id: 'x-axis-1',
							ticks: {
								source: 'auto',
							},
							time: {
								min: moment().startOf( 'day' ),
								max: moment().endOf( 'day' ),
								unit: 'hour',
								displayFormats: {
									day: 'MMM D',
								}
							},
						} ],
					}
				}
			});

		</script>
		<?php
	}

}
