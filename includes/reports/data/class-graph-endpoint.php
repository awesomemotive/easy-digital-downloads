<?php
/**
 * Reports API - Graph Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

/**
 * Handler for building a graph endpoint in the Reports API.
 *
 * @since 3.0
 */
final class Graph_Endpoint extends Chart_Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'graph';

	/**
	 * Represents the options used by the graphing library.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $options = array();

	/**
	 * Sets up the graph endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $args Graph endpoint attributes.
	 */
	public function __construct( array $args ) {
		parent::__construct( $args );
	}

	/**
	 * Sets up the options manifest.
	 *
	 * @since 3.0
	 *
	 * @param array $options Options to feed into the manifest.
	 */
	protected function set_options( $options ) {
		parent::set_options( $options );
	}

	/**
	 * Builds and outputs the graph JS to the page.
	 *
	 * @since 3.0
	 */
	public function build_graph() {
		$data = $this->get_data();

		if ( empty( $data ) ) {
			return;
		}

		$dataset_count = count( $data );
		$current       = 0;
		$target_el     = $this->get_display_arg( 'target', 'edd-reports-graph' );
		?>
		<canvas id="<?php echo esc_attr( $target_el ); ?>"></canvas>

		<script type="application/javascript">

			var date = moment( 'today', 'MMMM DD YYYY' );

			var lineChartData = {
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

			myLine = Chart.Line( $( '#<?php echo esc_js( $target_el ); ?>' ), {
				data: lineChartData,
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
