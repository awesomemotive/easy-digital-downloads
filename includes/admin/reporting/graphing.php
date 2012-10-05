<?php
/**
 * Graphing Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Graphing Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Show reports raph
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_graph() {

	$dates = edd_get_report_dates();

	$dates['m_start'] 	= isset( $_GET['m_start'] ) ? $_GET['m_start'] 	: 1;
	$dates['m_end'] 		= isset( $_GET['m_end'] ) 	? $_GET['m_end'] 	: 12;
	$dates['year'] 			= isset( $_GET['year'] ) 	? $_GET['year'] 	: date( 'Y' );

	$dates = edd_get_report_dates();


	echo '<h3>' . __( 'Earnings Over Time', 'edd' ) . '</h3>';

	edd_reports_graph_controls();

	ob_start(); ?>
	<script type="text/javascript">
	   jQuery( document ).ready( function($) {
	   		$.plot( 
	   			$("#edd_monthly_stats"), 
	   			[
	   				{ 
	   					data: [
		   					<?php
		   					$i = $dates['m_start'];
							while($i <= $dates['m_end']) : ?>
								[<?php echo mktime( 0,0,0,$i,0,$dates['year'] ) * 1000; ?>, <?php echo edd_get_sales_by_date( null, $i, $dates['year'] ); ?>],
								<?php $i++;
							endwhile;
		   					?>,
		   				],
		   				yaxis: 2,
	   					label: "<?php _e( 'Sales', 'edd' ); ?>",
	   					id: 'sales'
	   				}, 
	   				{ 
	   					data: [
		   					<?php
		   					$i = $dates['m_start'];
							while($i <= $dates['m_end']) : ?>
								[<?php echo mktime( 0,0,0,$i,0,$dates['year'] ) * 1000; ?>, <?php echo edd_get_earnings_by_date( null, $i, $dates['year'] ); ?>],
								<?php $i++;
							endwhile;
		   					?>
		   				],
	   					label: "<?php _e( 'Earnings', 'edd' ); ?>",
	   					id: 'earnings'
	   				}
	   			],
	   		{
               	series: {
                   lines: { show: true },
                   points: { show: true }
            	},
            	grid: {
           			show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true
           		},
            	xaxis: {
	   				mode: "time",
	   				timeFormat: "%b",
	   				minTickSize: [1, "month"]
   				},
   				yaxis: [
   					{ min: 0, tickSize: 1, tickDecimals: 2 },
   					{ min: 0, tickDecimals: 0 }
   				]
           		
             });

			$.plot( 
	   			$("#edd_daily_stats"), 
	   			[
	   				{ 
	   					data: [
		   					<?php
							$num_of_days = apply_filters( 'edd_earnings_per_day_days', 30 ); // show payments for the last 30 days
							$i = $num_of_days;
							while( $i > 1 ) : 
								$day_time 	= strtotime( '-' . $num_of_days - $i . ' days', time() );
								$day 		= date( 'd', $day_time ) + 1;
								$month 		= date( 'n', $day_time ) + 1; // I have no idea why the +1 is needed, but it is
								$dates['year'] 		= date( 'Y', $day_time );
								?>
								['<?php echo mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000; ?>', 
								<?php echo edd_get_sales_by_date( $day, $month, $dates['year'] ); ?>,
								],
								<?php $i--;
							endwhile;
							?>
		   				],
		   				yaxis: 2,
	   					label: "<?php _e( 'Sales', 'edd' ); ?>",
	   					id: 'sales'
	   				}, 
	   				{ 
	   					data: [
		   					<?php
							$i = $num_of_days;
							while( $i > 1 ) : 
								$day_time 	= strtotime( '-' . $num_of_days - $i . ' days', time() );
								$day 		= date( 'd', $day_time ) + 1;
								$month 		= date( 'n', $day_time ) + 1; // I have no idea why the +1 is needed, but it is
								$dates['year'] 		= date( 'Y', $day_time );
								?>
								['<?php echo mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000; ?>', 
								<?php echo edd_get_earnings_by_date( $day, $month, $dates['year'] ); ?>,
								],
								<?php $i--;
							endwhile;
							?>
		   				],
	   					label: "<?php _e( 'Earnings', 'edd' ); ?>",
	   					id: 'earnings'
	   				}
	   			],
	   		{
               	series: {
                   lines: { show: true },
                   points: { show: true }
            	},
            	grid: {
           			show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true
           		},
            	xaxis: {
	   				mode: "time",
	   				timeFormat: "%d/%b",
	   				minTickSize: [1, "day"]
   				},
   				yaxis: [
   					{ min: 0, tickSize: 1, tickDecimals: 2 },
   					{ min: 0, tickDecimals: 0 }
   				]
           		
             });

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
		    $("#edd_monthly_stats, #edd_daily_stats").bind("plothover", function (event, pos, item) {
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
    <div id="edd_monthly_stats" style="height: 300px;"></div>
	
    <h3><?php _e( 'Daily Stats for Last 30 Days', 'edd' ); ?></h3>
    <div id="edd_daily_stats" style="height: 300px;"></div>

	<?php
	echo ob_get_clean();
}


/**
 * Show report graph date filters
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_graph_controls() {

	$date_options = apply_filters( 'edd_report_date_options', array(
		'this_month' 	=> __( 'This Month', 'edd' ),
		'last_month' 	=> __( 'Last Month', 'edd' ),
		'this_quarter'	=> __( 'This Quarter', 'edd' ),
		'last_quarter'	=> __( 'Last Quarter', 'edd' ),
		'other'			=> __( 'Other', 'edd' )
	) );

	$dates = edd_get_report_dates();

	$display = $dates['range'] == 'other' ? '' : 'style="display:none;"';

	?>
	<form id="edd-garphs-filter" method="get">
		<div class="tablenav top">
			<div class="alignleft actions">

		       	<input type="hidden" name="post_type" value="download"/>
		       	<input type="hidden" name="page" value="edd-reports"/>
		       	<input type="hidden" name="view" value="earnings"/>

		       	<select id="edd-graphs-date-options" name="range">
		       		<?php
		       		foreach( $date_options as $key => $option ) {
		       			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $dates['range'] ) . '>' . esc_html( $option ) . '</option>';
		       		}
		       		?>
		       	</select>

		       	<div id="edd-date-range-options" <?php echo $display; ?>>
					&mdash;
				    <span><?php _e( 'From', 'edd' ); ?>&nbsp;</span>
			       	<select id="edd-graphs-month-start" name="m_start">
			       		<?php for( $i = 1; $i <= 12; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_start'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<span><?php _e( 'To', 'edd' ); ?>&nbsp;</span>
			       	<select id="edd-graphs-month-start" name="m_end">
			       		<?php for( $i = 1; $i <= 12; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_end'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<select id="edd-graphs-year" name="year">
			       		<?php for( $i = 2007; $i <= $dates['year_end']; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year'] ); ?>><?php echo $i; ?></option>
				       	<?php endfor; ?>
			       	</select>
			       </div>
		       	<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'edd' ); ?>"/>
			</div>
		</div>
	</form>
	<?php
}

function edd_get_report_dates() {

	$dates = array();

	$dates['range']		= isset( $_GET['range'] )	? $_GET['range']	: null;
	$dates['day']		= isset( $_GET['day'] ) 	? $_GET['day'] 		: null;	
	$dates['m_start'] 	= isset( $_GET['m_start'] ) ? $_GET['m_start'] 	: 1;
	$dates['m_end']		= isset( $_GET['m_end'] ) 	? $_GET['m_end'] 	: 12;
	$dates['year'] 		= isset( $_GET['year'] ) 	? $_GET['year'] 	: date( 'Y' );
	$dates['year_end']	= date( 'Y' );

	return apply_filters( 'edd_report_dates', $dates );
}

