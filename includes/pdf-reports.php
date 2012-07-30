<?php
/**
 * PDF Report Generation Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  PDF Report Generation
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @author      Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.3.2
*/

function edd_generate_pdf( $data ) {

	$nonce = $_GET['_wpnonce'];

	if( wp_verify_nonce( $nonce, 'edd_generate_pdf' ) ) {

		include_once(EDD_PLUGIN_DIR . 'includes/libraries/fpdf/fpdf.php');
		include_once(EDD_PLUGIN_DIR . 'includes/libraries/fpdf/mc_table.php');

		ob_end_clean();
						
		$year = date('Y');		
		$daterange = '1st January ' . date('Y') . ' to ' . date('jS F Y');
		
		$pdf = new PDF_HTML();
		$pdf->AddPage('L', 'A4');
		
		$pdf->SetTitle('Sales and earnings reports for the current year for all products');
		$pdf->SetAuthor('Easy Digital Downloads');
		$pdf->SetCreator('Easy Digital Downloads');
		
		$pdf->Image(EDD_PLUGIN_URL . 'includes/images/edd-logo.png', 205, 10);
		
		$pdf->SetMargins( 8, 8, 8 );
		$pdf->SetX( 8 );
		
		$pdf->SetFont( 'Helvetica', '', 16 );
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->Cell( 0, 3,'Sales and earnings reports for the current year for all products', 0, 2, 'L', false );
		
		$pdf->SetFont( 'Helvetica', '', 13 );
		$pdf->Ln();
		$pdf->SetTextColor( 150, 150, 150 );
		$pdf->Cell( 0, 6, 'Date Range: ' . $daterange, 0, 2, 'L', false );
		$pdf->Ln();
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->SetFont( 'Helvetica', '', 14 ); 
		$pdf->Cell( 0, 10, 'Table View', 0, 2, 'L', false );
		$pdf->SetFont( 'Helvetica', '', 12 );
		
		$pdf->SetFillColor( 238, 238, 238 );
		$pdf->Cell( 75, 6, 'Product Name', 1, 0, 'L', true );
		$pdf->Cell( 30, 6, 'Price', 1, 0, 'L', true );
		$pdf->Cell( 50, 6, 'Categories', 1, 0, 'L', true );
		$pdf->Cell( 50, 6, 'Tags', 1, 0, 'L', true );
		$pdf->Cell( 45, 6, 'Number of Sales', 1, 0, 'L', true );
		$pdf->Cell( 30, 6, 'Earnings', 1, 1, 'L', true );
		
		$pdf->SetWidths( array( 75, 30, 50, 50, 45, 30 ) );
		
		$downloads = get_posts( array( 'post_type' => 'download', 'year' => $year ) );
		if( $downloads ) :
			foreach( $downloads as $download ) :

				$pdf->SetFillColor( 255, 255, 255 );
				
				$title = get_the_title( $download->ID );
				
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
		endif;
		$pdf->Ln();
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->SetFont( 'Helvetica', '', 14 );
		$pdf->Cell( 0, 10, 'Graph View', 0, 2, 'L', false );
		$pdf->SetFont( 'Helvetica', '', 12 );

		//$image = chart_image();

		//$img = html_entity_decode('http://chart.apis.google.com/chart?cht=lc&amp;chs=900x330&amp;chtt=Sales+and+Earnings+by+Month+for+all+Products&amp;chd=t%3A0.00%2C0.00%2C0.00%2C0.00%2C0.00%2C235.00%2C205.00%2C0.00%2C0.00%2C0.00%2C0.00%2C0.00%7C0.00%2C0.00%2C0.00%2C0.00%2C0.00%2C62.00%2C28.00%2C0.00%2C0.00%2C0.00%2C0.00%2C0.00&amp;chco=1b58a3%2Cff6c1c&amp;chds=0%2C235&amp;chdl=Earnings%7CSales&amp;chdlp=s&amp;chm=o%2Cffffff%2C0%2C-1%2C9%7Co%2C000000%2C0%2C-1%2C7%7CN%2C000000%2C0%2C-1%2C10%7Co%2Cffffff%2C1%2C-1%2C8%7Co%2C4D89F9%2C1%2C-1%2C6%7CN%2C4D89F9%2C1%2C-1%2C10&amp;chxt=y%2Cx&amp;chxl=0%3A%7C0%7C235%7C1%3A%7CJan%7CFeb%7CMar%7CApr%7CMay%7CJune%7CJuly%7CAug%7CSept%7COct%7CNov%7CDec&amp;chxtc=1%2C5&amp;chxs=0%2C666666%2C11%2C1%2Clt');

		$pdf->SetX( 25 );
		//$pdf->WriteHTML( '<img src="'.$img.'" width="900">' );
		$pdf->Ln( 7 );

		$pdf->Output();


	}
}
add_action('edd_generate_pdf', 'edd_generate_pdf');


function chart_image() {
	include_once('libraries/googlechartlib/GoogleChart.php');
	include_once('libraries/googlechartlib/markers/GoogleChartShapeMarker.php');
	include_once('libraries/googlechartlib/markers/GoogleChartTextMarker.php');

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

$chart->setTitle( 'Sales and Earnings by Month for all Products', '336699', 18 );

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

return $chart->toHTML();

}