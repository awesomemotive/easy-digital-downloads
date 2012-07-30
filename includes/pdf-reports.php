<?php
/**
 * PDF Report Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  PDF Report Generation
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @author      Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.3.2
*/


include_once('../../../../wp-load.php');
include_once(EDD_PLUGIN_DIR . 'includes/googlechartlib/GoogleChart.php');
include_once(EDD_PLUGIN_DIR . 'includes/googlechartlib/markers/GoogleChartShapeMarker.php');
include_once(EDD_PLUGIN_DIR . 'includes/googlechartlib/markers/GoogleChartTextMarker.php');

if ( isset ( $_GET['title'] ) && isset ( $_GET['report'] ) ) {
	
	if( $_GET['report'] == 'sales_and_earnings' && $_GET['title'] == 'all Products' ) {

		$chart = new GoogleChart( 'lc', 900, 330 );
		
		$i = 1;
		$earnings = "";
		$sales = "";
		while( $i <= 12 ) :
			$earnings .= edd_get_earnings_by_date( $i, date('Y') ) . ",";
			$sales .= edd_get_sales_by_date( $i, date('Y') ) . ",";
			$i++;
		endwhile;
		
		$earnings_array = explode( ",", $earnings );
		$sales_array = explode( ",", $sales );
		
		$i = 0;
		while( $i <= 11 ) {
			if( empty( $sales_array[$i] ) )
				$sales_array[$i] = 0;
			$i++;
		}
		
		$min_earnings = 0;
		$max_earnings = max( $earnings_array );
		$earnings_scale = round( $max_earnings, -1 );
		
		$data = new GoogleChartData( array(
			$earnings_array[0],
			$earnings_array[1],
			$earnings_array[2],
			$earnings_array[3],
			$earnings_array[4],
			$earnings_array[5],
			$earnings_array[6],
			$earnings_array[7],
			$earnings_array[8],
			$earnings_array[9],
			$earnings_array[10],
			$earnings_array[11]
		) );
		
		$data->setLegend( 'Earnings' );
		$data->setColor( '1b58a3' );
		$chart->addData( $data );
		
		$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
		$shape_marker->setColor( '000000' );
		$shape_marker->setSize( 7 );
		$shape_marker->setBorder( 2 );
		$shape_marker->setData( $data );
		$chart->addMarker( $shape_marker );
		
		$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
		$value_marker->setColor( '000000' );
		$value_marker->setData( $data );
		$chart->addMarker( $value_marker );
		
		$data = new GoogleChartData( array( $sales_array[0],$sales_array[1], $sales_array[2],$sales_array[3], $sales_array[4],$sales_array[5],$sales_array[6],$sales_array[7],$sales_array[8],$sales_array[9],$sales_array[10],$sales_array[11] ) );
		$data->setLegend( 'Sales' );
		$data->setColor( 'ff6c1c' );
		$chart->addData( $data );
		
		$chart->setTitle( "Sales and Earnings by Month for ". $_GET['title'] ."", '336699', 18 );
		
		$chart->setScale( 0, $max_earnings );
		
		$y_axis = new GoogleChartAxis( 'y' );
		$y_axis->setDrawTickMarks( true )->setLabels( array(
			0, 
			$max_earnings 
		) );
		$chart->addAxis( $y_axis );
		
		$x_axis = new GoogleChartAxis( 'x' );
		$x_axis->setTickMarks( 5 );
		$x_axis->setLabels( array(
			'Jan', 
			'Feb', 
			'Mar', 
			'Apr', 
			'May', 
			'June', 
			'July', 
			'Aug', 
			'Sept', 
			'Oct', 
			'Nov', 
			'Dec' 
		) );
		$chart->addAxis( $x_axis );
		
		$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
		$shape_marker->setSize( 6 );
		$shape_marker->setBorder( 2 );
		$shape_marker->setData( $data );
		$chart->addMarker( $shape_marker );
		
		$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
		$value_marker->setData( $data );
		$chart->addMarker( $value_marker );

		header('Content-Type: image/png');
		echo $chart;

	} elseif ( $_GET['report'] == 'sales_and_earnings' && $_GET['title'] !== 'all Products' ) {
		$chart = new GoogleChart( 'lc', 900, 330 );

		function edd_get_earnings_by_date_for_download( $download_id, $i, $year ) {
			$sales = get_posts(
				array(
					'post_type' => 'edd_payment', 
					'posts_per_page' => -1, 
					'year' => $year, 
					'monthnum' => $i
				)
			);


/*echo "<pre> SALES CPT QUERY ";
			print_r($sales);
			echo "</pre>";*/

			//echo "---------------------------------------------------------------------";

			foreach($sales as $sale) {
				$sale_meta = get_post_meta($sale->ID, '_edd_payment_meta', true);

				$price = $sale_meta['amount'] . "  ";
				echo $price;
				//print_r($sale_meta);

				/*echo "<pre> SALE META QUERY ";
			print_r($sale_meta);
			echo "</pre>";*/
			}

		}

		function edd_get_sales_by_date_for_download( $download_id, $i, $year ) {  } 

		$i = 1;
		$earnings = "";
		$sales = "";

		$download_id = ( isset( $_GET['id'] ) ) ? (int) $_GET['id'] : NULL;

		while( $i <= 12 ) :
			$earnings .= edd_get_earnings_by_date_for_download( $download_id, 07, date('Y') ) . ",";
			$sales .= edd_get_sales_by_date_for_download( $download_id, $i, date('Y') ) . ",";
			$i++;
		endwhile;
		
		$earnings_array = explode( ",", $earnings );
		$sales_array = explode( ",", $sales );
		
		$i = 0;
		while( $i <= 11 ) {
			if( empty( $sales_array[$i] ) )
				$sales_array[$i] = 0;
			$i++;
		}
		
		$min_earnings = 0;
		$max_earnings = max( $earnings_array );
		$earnings_scale = round( $max_earnings, -1 );
		
		$data = new GoogleChartData( array(
			$earnings_array[0],
			$earnings_array[1],
			$earnings_array[2],
			$earnings_array[3],
			$earnings_array[4],
			$earnings_array[5],
			$earnings_array[6],
			$earnings_array[7],
			$earnings_array[8],
			$earnings_array[9],
			$earnings_array[10],
			$earnings_array[11]
		) );
		
		$data->setLegend( 'Earnings' );
		$data->setColor( '1b58a3' );
		$chart->addData( $data );
		
		$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
		$shape_marker->setColor( '000000' );
		$shape_marker->setSize( 7 );
		$shape_marker->setBorder( 2 );
		$shape_marker->setData( $data );
		$chart->addMarker( $shape_marker );
		
		$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
		$value_marker->setColor( '000000' );
		$value_marker->setData( $data );
		$chart->addMarker( $value_marker );
		
		$data = new GoogleChartData( array( $sales_array[0],$sales_array[1], $sales_array[2],$sales_array[3], $sales_array[4],$sales_array[5],$sales_array[6],$sales_array[7],$sales_array[8],$sales_array[9],$sales_array[10],$sales_array[11] ) );
		$data->setLegend( 'Sales' );
		$data->setColor( 'ff6c1c' );
		$chart->addData( $data );
		
		$chart->setTitle( "Sales and Earnings by Month for ". $_GET['title'] ."", '336699', 18 );
		
		$chart->setScale( 0, $max_earnings );
		
		$y_axis = new GoogleChartAxis( 'y' );
		$y_axis->setDrawTickMarks( true )->setLabels( array(
			0, 
			$max_earnings 
		) );
		$chart->addAxis( $y_axis );
		
		$x_axis = new GoogleChartAxis( 'x' );
		$x_axis->setTickMarks( 5 );
		$x_axis->setLabels( array(
			'Jan', 
			'Feb', 
			'Mar', 
			'Apr', 
			'May', 
			'June', 
			'July', 
			'Aug', 
			'Sept', 
			'Oct', 
			'Nov', 
			'Dec' 
		) );
		$chart->addAxis( $x_axis );
		
		$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
		$shape_marker->setSize( 6 );
		$shape_marker->setBorder( 2 );
		$shape_marker->setData( $data );
		$chart->addMarker( $shape_marker );
		
		$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
		$value_marker->setData( $data );
		$chart->addMarker( $value_marker );

		//header('Content-Type: image/png');
		//echo $chart;
	}

}