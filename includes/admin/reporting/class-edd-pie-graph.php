<?php
/**
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Graph Class
 *
 * @since 2.4
 */
class EDD_Pie_Graph extends EDD_Graph {

	/*

	Simple example:

	data format for each point: array( location on x, location on y )

	$data = array(
		array( 'Label'   => 'value' ),
		array( 'Label 2' => 'value 2' ),
	);

	$graph = new EDD_Pie_Graph( $data );
	$graph->display();

	*/

	/**
	 * Data to graph
	 *
	 * @var array
	 * @since 2.4
	 */
	private $data;

	/**
	 * Unique ID for the graph
	 *
	 * @var string
	 * @since 2.4
	 */
	private $id = '';

	/**
	 * Graph options
	 *
	 * @var array
	 * @since 2.4
	 */
	private $options = array();

	/**
	 * Get things started
	 *
	 * @since 2.4
	 */
	public function __construct( $_data, $options ) {

		$this->data = $_data;
		// Set this so filters recieving $this can quickly know if it's a graph they want to modify
		$this->type = 'pie';

		// Generate unique ID, add 'a' since md5 can leave a numerical first character
		$this->id = 'a' . md5( rand() );

		// Setup default options;
		$defaults = array(
			'radius'           => 1,
			'legend'           => true,
			'legend_formatter' => false,
			'label_formatter'  => false,
			'height'           => '300',
		);

		$this->options = wp_parse_args( $options, $defaults );

		add_action( 'edd_graph_load_scripts', array( $this, 'load_additional_scripts' ) );

	}

	/**
	 * Load the graphing library script
	 *
	 * @since 2.4
	 */
	public function load_additional_scripts() {
		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'jquery-flot-pie', EDD_PLUGIN_URL . 'assets/js/jquery.flot.pie' . $suffix . '.js' );
	}

	/**
	 * Build the graph and return it as a string
	 *
	 * @var array
	 * @since 2.4
	 * @return string
	 */
	public function build_graph() {

		$this->load_scripts();

		ob_start();
		?>
		<script type="text/javascript">
			var <?php echo $this->id; ?>_data = [
			<?php foreach ( $this->data as $label => $value ) : ?>
				<?php echo '{ label: "' . esc_attr( $label ) . '", data: "' . $value . '" },' . "\n"; ?>
			<?php endforeach; ?>
			];

			var <?php echo $this->id; ?>_options = {
				series: {
					pie: {
						show: true,
						radius: <?php echo $this->options['radius']; ?>,
					}
				},
				legend: {
					show: <?php echo $this->options['legend']; ?>,
				},
			};

			<?php if ( true === $this->options['legend'] && ! empty( $this->options['legend_formatter'] ) ) : ?>
				<?php echo $this->id; ?>_options.legend.labelFormatter = <?php echo $this->options['legend_formatter']; ?>;
			<?php endif; ?>

			jQuery( document ).ready( function($) {
				var <?php echo $this->id; ?>Chart = $('#edd-pie-graph-<?php echo $this->id; ?>');
				$.plot( <?php echo $this->id; ?>Chart, <?php echo $this->id; ?>_data, <?php echo $this->id; ?>_options );
			});

		</script>
		<div id="edd-pie-graph-<?php echo $this->id; ?>" class="edd-pie-graph" style="height: <?php echo $this->options['height']; ?>px;"></div>
		<?php

		return apply_filters( 'edd_pie_graph_output', ob_get_clean(), $this->id, $this->data, $this->options );

	}

}
