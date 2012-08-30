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
 * Show Download Sales Graph
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_show_download_sales_graph($bgcolor = 'white') {
	$downloads = get_posts(array('post_type' => 'download', 'posts_per_page' => -1));
	if($downloads) {
		ob_start(); ?>
	    <script type="text/javascript">
		    google.load("visualization", "1", {packages:["corechart"]});
			// sales chart
		    google.setOnLoadCallback(drawSalesChart);
		    function drawSalesChart() {
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', '<?php echo edd_get_label_plural(); ?>');
		        data.addColumn('number', '<?php _e("Sales", "edd"); ?>');
		        data.addRows([
					<?php foreach($downloads as $download) { ?>
		          		['<?php echo html_entity_decode( get_the_title($download->ID), ENT_COMPAT, 'UTF-8'); ?>', 
							<?php echo edd_get_download_sales_stats($download->ID); ?>, 
						],
					<?php } ?>
		        ]);

		        var options = {
		          	title: "<?php echo sprintf(__('%s Performance in Sales', 'edd'), edd_get_label_singular() ); ?>",
					colors:['#a3bcd3'],
					fontSize: 12,
					backgroundColor: '<?php echo $bgcolor; ?>'
		        };

		        var chart = new google.visualization.ColumnChart(document.getElementById('sales_chart_div'));
		        chart.draw(data, options);
		    }
	    </script>
		<div id="sales_chart_div"></div>
		<?php
		echo ob_get_clean();
	}
}


/**
 * Show Download Earnings Graph
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_show_download_earnings_graph($bgcolor = 'white') {
	$downloads = get_posts(array('post_type' => 'download', 'posts_per_page' => -1));
	if($downloads) {
		ob_start(); ?>
	    <script type="text/javascript">
	   	 google.load("visualization", "1", {packages:["corechart"]});
			// earnings chart	
		    google.setOnLoadCallback(drawEarningsChart);
		    function drawEarningsChart() {
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', '<?php _e("Download", "edd"); ?>');
		        data.addColumn('number', '<?php _e("Earnings", "edd"); ?>');
		        data.addRows([
					<?php foreach($downloads as $download) { ?>
		          		['<?php echo html_entity_decode(get_the_title($download->ID), ENT_COMPAT, 'UTF-8'); ?>', 
							<?php echo edd_get_download_earnings_stats($download->ID); ?>
						],
					<?php } ?>
		        ]);

		        var options = {
		          	title: "<?php echo sprintf(__('%s Performance in Earnings', 'edd'), edd_get_label_singular() ); ?>",
					colors:['#a3bcd3'],
					fontSize: 12,
					backgroundColor: '<?php echo $bgcolor; ?>'
		        };

		        var chart = new google.visualization.ColumnChart(document.getElementById('earnings_chart_div'));
		        chart.draw(data, options);
		    }
	    </script>
		<div id="earnings_chart_div"></div>
		<?php
		echo ob_get_clean();
	}
}


/**
 * Show Monthly Earnings Graph
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_show_monthly_eanings_graph($bgcolor = 'white') {
	ob_start(); ?>
    <script type="text/javascript">
	    google.load("visualization", "1", {packages:["corechart"]});
		// sales chart
	    google.setOnLoadCallback(drawSalesChart);
	    function drawSalesChart() {
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', '<?php _e("Month", "edd"); ?>');
	        data.addColumn('number', '<?php _e("Earnings", "edd"); ?>');
	        data.addRows([
				<?php
				$i = 1;
				while($i <= 12) : ?>
					['<?php echo edd_month_num_to_name($i) . ' ' . date("Y"); ?>', 
					<?php echo edd_get_earnings_by_date(null, $i, date('Y') ); ?>,
					],
					<?php $i++;
				endwhile;
				?>
	        ]);

	        var options = {
	          	title: "<?php _e('Earnings per month', 'edd'); ?>",
				colors:['#a3bcd3'],
				fontSize: 12,
				backgroundColor: '#ffffff'
	        };

	        var chart = new google.visualization.ColumnChart(document.getElementById('monthly_earnings_chart_div'));
	        chart.draw(data, options);
	    }
    </script>	    
	<div id="monthly_earnings_chart_div"></div>
	<?php
	echo ob_get_clean();
}


/**
 * Show Daily Earnings Graph
 *
 * @access      public
 * @since       1.1.8
 * @return      void
*/

function edd_show_daily_eanings_graph($bgcolor = 'white') {
	ob_start(); ?>
    <script type="text/javascript">
	    google.load("visualization", "1", {packages:["corechart"]});
		// sales chart
	    google.setOnLoadCallback(drawSalesChart);
	    function drawSalesChart() {
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', '<?php _e("Day", "edd"); ?>');
	        data.addColumn('number', '<?php _e("Earnings", "edd"); ?>');
	        data.addRows([
				<?php
				$day = date('d');
				$month = date('n');
				$year = date('Y');
				$num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year );
				$i = 1;
				while($i <= $num_of_days) :?>
					['<?php echo date("d", mktime(0, 0, 0, $month, $i, $year)); ?>', 
					<?php echo edd_get_earnings_by_date($i, $month, $year ); ?>,
					],
					<?php $i++;
				endwhile;
				?>
	        ]);

	        var options = {
	          	title: "<?php _e('Earnings per day', 'edd'); ?>",
				colors:['#a3bcd3'],
				fontSize: 12,
				backgroundColor: '#ffffff'
	        };

	        var chart = new google.visualization.ColumnChart(document.getElementById('daily_earnings_chart_div'));
	        chart.draw(data, options);
	    }
    </script>	    
	<div id="daily_earnings_chart_div"></div>
	<?php
	echo ob_get_clean();
}


/**
 * Show Sales Per Month Graph
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_show_monthly_sales_graph($bgcolor = 'white') {
	ob_start(); ?>
    <script type="text/javascript">
	    google.load("visualization", "1", {packages:["corechart"]});
		// sales chart
	    google.setOnLoadCallback(drawSalesChart);
	    function drawSalesChart() {
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', '<?php _e("Month", "edd"); ?>');
	        data.addColumn('number', '<?php _e("Sales", "edd"); ?>');
	        data.addRows([
				<?php
				$i = 1;
				while($i <= 12) : ?>
					['<?php echo edd_month_num_to_name($i) . ' ' . date("Y"); ?>', 
					<?php echo edd_get_sales_by_date($i, date('Y') ); ?>,
					],
					<?php $i++;
				endwhile;
				?>
	        ]);

	        var options = {
	          	title: "<?php _e('Sales per month', 'edd'); ?>",
				colors:['#a3bcd3'],
				fontSize: 12,
				backgroundColor: '#ffffff'
	        };

	        var chart = new google.visualization.ColumnChart(document.getElementById('monthly_sales_chart_div'));
	        chart.draw(data, options);
	    }
    </script>	    
	<div id="monthly_sales_chart_div"></div>
	<?php
	echo ob_get_clean();
}