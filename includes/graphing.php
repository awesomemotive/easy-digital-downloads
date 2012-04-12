<?php

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
		        data.addColumn('string', '<?php _e("Download", "edd"); ?>');
		        data.addColumn('number', '<?php _e("Sales", "edd"); ?>');
		        data.addRows([
					<?php foreach($downloads as $download) { ?>
		          		['<?php echo get_the_title($download->ID); ?>', 
							<?php echo edd_get_download_sales_stats($download->ID); ?>, 
						],
					<?php } ?>
		        ]);

		        var options = {
		          	title: "<?php _e('Downloads Performance in Sales', 'edd'); ?>",
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


function edd_show_download_eanings_graph($bgcolor = 'white') {
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
		          		['<?php echo get_the_title($download->ID); ?>', 
							<?php echo edd_get_download_earnings_stats($download->ID); ?>
						],
					<?php } ?>
		        ]);

		        var options = {
		          	title: "<?php _e('Downloads Performance in Earnings', 'edd'); ?>",
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
					<?php echo edd_get_earnings_by_date($i, date('Y') ); ?>,
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