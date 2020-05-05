<?php
/**
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Graph Class
 *
 * @since 1.9
 */
class EDD_Graph {

	/*

	Simple example:

	data format for each point: array( location on x, location on y )

	$data = array(

		'Label' => array(
			array( 1, 5 ),
			array( 3, 8 ),
			array( 10, 2 )
		),

		'Second Label' => array(
			array( 1, 7 ),
			array( 4, 5 ),
			array( 12, 8 )
		)
	);

	$graph = new EDD_Graph( $data );
	$graph->display();

	*/

	/**
	 * Data to graph
	 *
	 * @var array
	 * @since 1.9
	 */
	private $data;

	/**
	 * Unique ID for the graph
	 *
	 * @var string
	 * @since 1.9
	 */
	private $id = '';

	/**
	 * Graph options
	 *
	 * @var array
	 * @since 1.9
	 */
	private $options = array();

	/**
	 * Get things started
	 *
	 * @since 1.9
	 */
	public function __construct( $_data ) {

		$this->data = $_data;

		// Generate unique ID
		$this->id   = 'a' . md5( rand() );

		// Setup default options;
		$this->options = array(
			'y_mode'             => null,
			'x_mode'             => null,
			'y_decimals'         => 0,
			'x_decimals'         => 0,
			'y_position'         => 'right',
			'time_format'        => '%d/%b',
			'ticksize_unit'      => 'day',
			'ticksize_num'       => 1,
			'multiple_y_axes'    => false,
			'bgcolor'            => '#f9f9f9',
			'bordercolor'        => '#ccc',
			'color'              => '#bbb',
			'borderwidth'        => 2,
			'bars'               => false,
			'lines'              => true,
			'points'             => true,
			'additional_options' => '',
		);

	}

	/**
	 * Set an option
	 *
	 * @param $key The option key to set
	 * @param $value The value to assign to the key
	 * @since 1.9
	 */
	public function set( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	/**
	 * Get an option
	 *
	 * @param $key The option key to get
	 * @since 1.9
	 */
	public function get( $key ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : false;
	}

	/**
	 * Get graph data
	 *
	 * @since 1.9
	 */
	public function get_data() {
		return apply_filters( 'edd_get_graph_data', $this->data, $this );
	}

	/**
	 * Load the graphing library script
	 *
	 * @since 1.9
	 */
	public function load_scripts() {
		wp_enqueue_script( 'edd-jquery-flot',      EDD_PLUGIN_URL . 'assets/js/vendor/jquery.flot.min.js' );
		wp_enqueue_script( 'edd-jquery-flot-time', EDD_PLUGIN_URL . 'assets/js/vendor/jquery.flot.time.min.js' );

		/**
		 * Fires immediately after the legacy Flot JS graphing framework is enqueued.
		 *
		 * @since 1.9
		 */
		do_action( 'edd_graph_load_scripts' );
	}

	/**
	 * Build the graph and return it as a string
	 *
	 * @var array
	 * @since 1.9
	 * @return string
	 */
	public function build_graph() {

		$yaxis_count = 1;

		$this->load_scripts();
		ob_start();
?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				$.plot(
					$("#edd-graph-<?php echo $this->id; ?>"),
					[
						<?php foreach( $this->get_data() as $label => $data ) : ?>
						{
							label: "<?php echo esc_attr( $label ); ?>",
							id: "<?php echo sanitize_key( $label ); ?>",
							// data format is: [ point on x, value on y ]
							data: [<?php foreach( $data as $point ) { echo '[' . implode( ',', $point ) . '],'; } ?>],
							points: {
								show: <?php echo $this->options['points'] ? 'true' : 'false'; ?>,
							},
							bars: {
								show: <?php echo $this->options['bars'] ? 'true' : 'false'; ?>,
								barWidth: 12,
								aling: 'center'
							},
							lines: {
								show: <?php echo $this->options['lines'] ? 'true' : 'false'; ?>
							},
							<?php if( $this->options['multiple_y_axes'] ) : ?>
							yaxis: <?php echo $yaxis_count; ?>
							<?php endif; ?>
						},
						<?php $yaxis_count++; endforeach; ?>
					],
					{
						// Options
						grid: {
							show: true,
							aboveData: false,
							color: "<?php echo $this->options['color']; ?>",
							backgroundColor: "<?php echo $this->options['bgcolor']; ?>",
							borderColor: "<?php echo $this->options['bordercolor']; ?>",
							borderWidth: <?php echo absint( $this->options['borderwidth'] ); ?>,
							clickable: false,
							hoverable: true
						},
						xaxis: {
							mode: "<?php echo $this->options['x_mode']; ?>",
							timeFormat: "<?php echo $this->options['x_mode'] == 'time' ? $this->options['time_format'] : ''; ?>",
							tickSize: "<?php echo $this->options['x_mode'] == 'time' ? '' : $this->options['ticksize_num']; ?>",
							<?php if( $this->options['x_mode'] != 'time' ) : ?>
							tickDecimals: <?php echo $this->options['x_decimals']; ?>
							<?php endif; ?>
						},
						yaxis: {
							position: 'right',
							min: 0,
							mode: "<?php echo $this->options['y_mode']; ?>",
							timeFormat: "<?php echo $this->options['y_mode'] == 'time' ? $this->options['time_format'] : ''; ?>",
							<?php if( $this->options['y_mode'] != 'time' ) : ?>
							tickDecimals: <?php echo $this->options['y_decimals']; ?>
							<?php endif; ?>
						},
						<?php echo $this->options['additional_options']; ?>
					}

				);

				function edd_flot_tooltip(x, y, contents) {
					$('<div id="edd-flot-tooltip">' + contents + '</div>').css( {
						position: 'absolute',
						display: 'none',
						top: y + 5,
						left: x + 5,
						border: '1px solid #fdd',
						padding: '2px',
						'background-color': '#fee',
						opacity: 0.80,
						zIndex: 3,
					}).appendTo("body").fadeIn(200);
				}

				var previousPoint = null;
				$("#edd-graph-<?php echo $this->id; ?>").bind("plothover", function (event, pos, item) {
					$("#x").text(pos.x.toFixed(2));
					$("#y").text(pos.y.toFixed(2));
					if (item) {
						if (previousPoint != item.dataIndex) {
							previousPoint = item.dataIndex;
							$("#edd-flot-tooltip").remove();
							var x = item.datapoint[0].toFixed(2),
							y = item.datapoint[1].toFixed(2);
							if( item.series.id.includes('earnings') ) {
								if( edd_vars.currency_pos == 'before' ) {
									edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + edd_vars.currency_sign + y );
								} else {
									edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + edd_vars.currency_sign );
								}
							} else {
								edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y.replace( '.00', '' ) );
							}
						}
					} else {
						$("#edd-flot-tooltip").remove();
						previousPoint = null;
					}
				});

			});

		</script>
		<div id="edd-graph-<?php echo $this->id; ?>" class="edd-graph" style="height: 300px;"></div>
<?php
		return ob_get_clean();
	}

	/**
	 * Output the final graph
	 *
	 * @since 1.9
	 */
	public function display() {
		do_action( 'edd_before_graph', $this );
		echo $this->build_graph();
		do_action( 'edd_after_graph', $this );
	}

}
