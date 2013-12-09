<?php
/**
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Graph Class
 *
 * @since 1.9
 */
class EDD_Graph {

	/*

	Should look something like this:

	$data = array(

		// X axis
		array(
			array( 1, 5 ),
			array( 3, 8 ),
			array( 10, 2 )
		),

		// Y axis
		array(
			array( 1, 7 ),
			array( 4, 5 ),
			array( 12, 8 )
		)
	);

	$graph = new EDD_Graph( $data );

	// Include optional methods for setting colors, sizes, etc

	$graph->display();

	*/

	private $data;

	private $id;

	private $options = array();

	/**
	 * Get things started
	 *
	 * @since 1.9
	 */
	public function __construct( $_data ) {
		
		$this->data = $_data; 
		
		// Generate unique ID
		$this->id   = md5( rand() );

		// Setup default options;
		$this->options = array(
			'ymode'         => null,
			'xmode'         => null,
			'time_format'   => '%d/%b',
			'ticksize_unit' => 'day',
			'ticksize_num'  => 1
		);

	}

	public function set( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	public function build_graph() {

		$yaxis_count = 1;
		ob_start();
?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				$.plot(
					$("#edd-graph-<?php echo $this->id; ?>"),
					[
						<?php foreach( $this->data as $label => $data ) : ?>
						{ 
							// data format is: [ point on x, value on y ]
							label: "<?php echo esc_attr( $label ); ?>",
							id: "<?php echo sanitize_key( $label ); ?>",
							//data: [[1, 2], [4, 5], [7, 8], [17, 0]],
							data: [<?php foreach( $data as $point ) { echo '[' . implode( ',', $point ) . '],'; } ?>],
							points: {
								show: true,
							},
							lines: {
								show: true
							},
							yaxis: <?php echo $yaxis_count; ?>
						},
						<?php $yaxis_count++; endforeach; ?>
					],
					{
						// Options
						grid: {
							show: true,
							aboveData: false,
							color: '#ccc',
							backgroundColor: '#f9f9f9',
							borderWidth: 2,
							borderColor: '#ccc',
							clickable: false,
							hoverable: true
						},
						xaxis: {
							mode: "<?php echo $this->options['xmode']; ?>",
							timeFormat: "<?php echo $this->options['xmode'] == 'time' ? $this->options['time_format'] : ''; ?>",
						},
						yaxis: {
							min: 0,
							mode: "<?php echo $this->options['ymode']; ?>",
							timeFormat: "<?php echo $this->options['ymode'] == 'time' ? $this->options['time_format'] : ''; ?>",
						}
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
						opacity: 0.80
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
							if( item.series.id == 'earnings' ) {
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
		<div id="edd-graph-<?php echo $this->id; ?>" style="height: 300px;"></div>
<?php
		return ob_get_clean();
	}


	public function display() {
		echo $this->build_graph();
	}
	

}

// Just for simple testing

function edd_test_graph_class() {

	$data = array(

		// Line one
		'Foo' => array(
			array( 1386048624, 3 ),
			array( 1386307825, 5 ),
			array( 1386394224, 8 ),
			array( 1386480624, 2 )
		),

		// Line 2
		'Bar' => array(
			array( 1386048624, 1 ),
			array( 1386307825, 2 ),
			array( 1386394224, 10 ),
			array( 1386480624, 3 )
		),

		// Line 3
		'Puppy' => array(
			array( 1386048624, 10 ),
			array( 1386307825, 12 ),
			array( 1386394224, 2 ),
			array( 1386480624, 4 )
		)
	);

	$graph = new EDD_Graph( $data );
	$graph->set( 'xmode', 'time' );
	$graph->display();
}
//add_action( 'edd_reports_view_earnings', 'edd_test_graph_class', -1 );