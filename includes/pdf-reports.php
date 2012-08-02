<?php
/**
 * PDF Report Generation Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  PDF Report Generation
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @author      Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.4.0
*/


/**
 * Generate PDF Reports
 *
 * Generates PDF report on sales and earnings for all downloads for the current year.
 *
 * @access      public
 * @since       1.1.4.0
 * @param 		string $data
 * @author 		Sunny Ratilal
*/

function edd_generate_pdf( $data ) {

	$edd_pdf_reports_nonce = $_GET['_wpnonce'];

	if ( wp_verify_nonce( $edd_pdf_reports_nonce, 'edd_generate_pdf' ) ) {

		include_once(EDD_PLUGIN_DIR . '/includes/libraries/fpdf/fpdf.php');
		include_once(EDD_PLUGIN_DIR . '/includes/libraries/fpdf/edd_pdf.php');

		ob_end_clean(); // Fixes a glitch in Internet Explorer

		$daterange = date('F jS', mktime(0,0,0,1,1, date('Y') ) ) . ' ' . __('to', 'edd') . ' ' . date('F jS Y');

		$pdf = new edd_pdf();
		$pdf->AddPage('L', 'A4');

		$pdf->SetTitle( __('Sales and earnings reports for the current year for all products', 'edd') );
		$pdf->SetAuthor( __('Easy Digital Downloads', 'edd') );
		$pdf->SetCreator( __('Easy Digital Downloads', 'edd') );

		$pdf->Image(EDD_PLUGIN_URL . 'includes/images/edd-logo.png', 205, 10);

		$pdf->SetMargins( 8, 8, 8 );
		$pdf->SetX( 8 );

		$pdf->SetFont( 'Helvetica', '', 16 );
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->Cell( 0, 3, __('Sales and earnings reports for the current year for all products', 'edd'), 0, 2, 'L', false );

		$pdf->SetFont( 'Helvetica', '', 13 );
		$pdf->Ln();
		$pdf->SetTextColor( 150, 150, 150 );
		$pdf->Cell( 0, 6, __('Date Range: ', 'edd') . $daterange, 0, 2, 'L', false );
		$pdf->Ln();
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->SetFont( 'Helvetica', '', 14 ); 
		$pdf->Cell( 0, 10, __('Table View', 'edd'), 0, 2, 'L', false );
		$pdf->SetFont( 'Helvetica', '', 12 );

		$pdf->SetFillColor( 238, 238, 238 );
		$pdf->Cell( 70, 6, __('Product Name', 'edd'), 1, 0, 'L', true );
		$pdf->Cell( 30, 6, __('Price', 'edd'), 1, 0, 'L', true );
		$pdf->Cell( 50, 6, __('Categories', 'edd'), 1, 0, 'L', true );
		$pdf->Cell( 50, 6, __('Tags', 'edd'), 1, 0, 'L', true );
		$pdf->Cell( 45, 6, __('Number of Sales', 'edd'), 1, 0, 'L', true );
		$pdf->Cell( 35, 6, __('Earnings to Date', 'edd'), 1, 1, 'L', true );

		$year = date('Y');
		$downloads = get_posts( array( 'post_type' => 'download', 'year' => $year ) );
		if ( $downloads ) :
			$pdf->SetWidths( array( 70, 30, 50, 50, 45, 35 ) );

			foreach ( $downloads as $download ) :

				$pdf->SetFillColor( 255, 255, 255 );
				
				$title = utf8_decode( get_the_title( $download->ID ) );
				
				if ( edd_has_variable_prices( $download->ID ) ) {
					$prices = get_post_meta( $download->ID, 'edd_variable_prices', true );
					$total = count( $prices ) - 1;
					if ( $prices[0]['amount'] < $prices[$total]['amount'] ) {
						$min = $prices[0]['amount'];
						$max = $prices[$total]['amount'];
					} else {
						$min = $prices[$total]['amount'];
						$max = $prices[0]['amount'];
					}
					$price = html_entity_decode( sprintf( '%s - %s', edd_currency_filter( $min ), edd_currency_filter( $max ) ) );
				} else {
					$price = html_entity_decode( edd_currency_filter( edd_get_download_price( $download->ID ) ) );
				}
				
				$categories = strip_tags( get_the_term_list( $download->ID, 'download_category', '', ', ', '' ) );
				$tags = strip_tags( get_the_term_list( $download->ID, 'download_tag', '', ', ', '' ) );
				$sales = edd_get_download_sales_stats( $download->ID );
				$link = get_permalink( $download->ID );
				$earnings = html_entity_decode ( edd_currency_filter( edd_get_download_earnings_stats( $download->ID ) ) );
				
				$pdf->Row( array( $title, $price, $categories, $tags, $sales, $earnings ) );
			endforeach;
		else:
			$pdf->SetWidths( array( 280 ) );
			$title = __('No Downloads found.', 'edd');
			$pdf->Row( array($title) );
		endif;
		
		$pdf->Ln();
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->SetFont( 'Helvetica', '', 14 );
		$pdf->Cell( 0, 10, __('Graph View', 'edd'), 0, 2, 'L', false );
		$pdf->SetFont( 'Helvetica', '', 12 );

		$image = html_entity_decode( urldecode( edd_draw_chart_image() ) );
		$image = str_replace( ' ', '%20', $image );

		$pdf->SetX( 25 );
		$pdf->Image( $image .'&file=.png' );
		$pdf->Ln( 7 );
		$pdf->Output( 'edd-report' . date('Y-m-d') . '.pdf', D );

	}
}
add_action('edd_generate_pdf', 'edd_generate_pdf');


/**
 * Draws Chart for PDF Report
 *
 * Draws the sales and earnings chart for the PDF report.
 *
 * @access      public
 * @since       1.1.4.0
 * @author      Sunny Ratilal
 * @return      string
*/

function edd_draw_chart_image() {
	include_once(EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/GoogleChart.php');
	include_once(EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartShapeMarker.php');
	include_once(EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartTextMarker.php');

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

	$data->setLegend( __('Earnings', 'edd') );
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
	$data->setLegend( __('Sales', 'edd') );
	$data->setColor( 'ff6c1c' );
	$chart->addData( $data );

	$chart->setTitle( __('Sales and Earnings by Month for all Products', 'edd'), '336699', 18 );

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
		__('Jan', 'edd'),
		__('Feb', 'edd'),
		__('Mar', 'edd'),
		__('Apr', 'edd'),
		__('May', 'edd'),
		__('June', 'edd'),
		__('July', 'edd'),
		__('Aug', 'edd'),
		__('Sept', 'edd'),
		__('Oct', 'edd'),
		__('Nov', 'edd'),
		__('Dec', 'edd')
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

	return $chart->getUrl();
}