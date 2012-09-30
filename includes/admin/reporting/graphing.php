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

	$month_start 	= isset( $_GET['m_start'] ) ? $_GET['m_start'] 	: 1;
	$month_end 		= isset( $_GET['m_end'] ) 	? $_GET['m_end'] 	: 12;
	$year 			= isset( $_GET['year'] ) 	? $_GET['year'] 	: date( 'Y' );

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
		   					$i = $month_start;
							while($i <= $month_end) : ?>
								[<?php echo mktime( 0,0,0,$i,0,$year ) * 1000; ?>, <?php echo edd_get_sales_by_date( null, $i, $year ); ?>],
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
		   					$i = $month_start;
							while($i <= $month_end) : ?>
								[<?php echo mktime( 0,0,0,$i,0,$year ) * 1000; ?>, <?php echo edd_get_earnings_by_date( null, $i, $year ); ?>],
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
								$year 		= date( 'Y', $day_time );
								?>
								['<?php echo mktime( 0, 0, 0, $month, $day, $year ) * 1000; ?>', 
								<?php echo edd_get_sales_by_date( $day, $month, $year ); ?>,
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
								$year 		= date( 'Y', $day_time );
								?>
								['<?php echo mktime( 0, 0, 0, $month, $day, $year ) * 1000; ?>', 
								<?php echo edd_get_earnings_by_date( $day, $month, $year ); ?>,
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
		                    edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y );                    	
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

	$day 			= isset( $_GET['day'] ) 	? $_GET['day'] 		: null;
	$month_start 	= isset( $_GET['m_start'] ) ? $_GET['m_start'] 	: 1;
	$month_end 		= isset( $_GET['m_end'] ) 	? $_GET['m_end'] 	: 12;
	$year 			= isset( $_GET['year'] ) 	? $_GET['year'] 	: date( 'Y' );
	$years_end	 	= date( 'Y' );
	?>
	<form id="edd-garphs-filter" method="get">
		<div class="tablenav top">
			<div class="alignleft actions">
		       	<input type="hidden" name="post_type" value="download"/>
		       	<input type="hidden" name="page" value="edd-reports"/>
		       	<input type="hidden" name="view" value="earnings"/>

		       	<?php if( $years_end > 2012 ) : ?>
			       	<select id="edd-graphs-year" name="year">
			       		<?php for( $i = 2012; $i <= $years_end; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $year ); ?>><?php echo $i; ?></option>
				       	<?php endfor; ?>
			       	</select>
			    <?php endif; ?>
			    <span><?php _e( 'From', 'edd' ); ?>&nbsp;</span>
		       	<select id="edd-graphs-month-start" name="m_start">
		       		<?php for( $i = 1; $i <= 12; $i++ ) : ?>
		       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $month_start ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
			       	<?php endfor; ?>
		       	</select>
		       	<span><?php _e( 'To', 'edd' ); ?>&nbsp;</span>
		       	<select id="edd-graphs-month-start" name="m_end">
		       		<?php for( $i = 1; $i <= 12; $i++ ) : ?>
		       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $month_end ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
			       	<?php endfor; ?>
		       	</select>
		       	<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'edd' ); ?>"/>
			</div>
		</div>
	</form>
	<?php
}