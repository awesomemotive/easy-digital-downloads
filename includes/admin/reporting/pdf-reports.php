<?php
/**
 * PDF Report Generation Functions
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @author      Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generate PDF Reports
 *
 * Generates PDF report on sales and earnings for all downloads for the current year.
 *
 * @since 1.1.4.0
 * @param string $data
 * @uses edd_pdf
 * @author Sunny Ratilal
 */
function edd_generate_pdf( $data ) {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to generate PDF sales reports', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_generate_pdf' ) ) {
		wp_die( __( 'Nonce verification failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	require_once EDD_PLUGIN_DIR . '/includes/libraries/fpdf/fpdf.php';
	require_once EDD_PLUGIN_DIR . '/includes/libraries/fpdf/edd_pdf.php';

	$daterange = date_i18n( get_option( 'date_format' ), mktime( 0, 0, 0, 1, 1, date( 'Y' ) ) ) . ' ' . utf8_decode( __( 'to', 'easy-digital-downloads' ) ) . ' ' . date_i18n( get_option( 'date_format' ) );

	$pdf = new edd_pdf();
	$pdf->AddPage( 'L', 'A4' );

	$pdf->SetTitle( utf8_decode( __( 'Sales and earnings reports for the current year for all products','easy-digital-downloads' ) ) );
	$pdf->SetAuthor( utf8_decode( __( 'Easy Digital Downloads', 'easy-digital-downloads' ) ) );
	$pdf->SetCreator( utf8_decode( __( 'Easy Digital Downloads', 'easy-digital-downloads' ) ) );

	$pdf->Image( EDD_PLUGIN_URL . 'assets/images/edd-logo-pdf.png', 205, 10 );

	$pdf->SetMargins( 8, 8, 8 );
	$pdf->SetX( 8 );

	$pdf->SetFont( 'Helvetica', '', 16 );
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->Cell( 0, 3, utf8_decode( __( 'Sales and earnings reports for the current year for all products', 'easy-digital-downloads' ) ), 0, 2, 'L', false );

	$pdf->SetFont( 'Helvetica', '', 13 );
	$pdf->Ln();
	$pdf->SetTextColor( 150, 150, 150 );
	$pdf->Cell( 0, 6, utf8_decode( __( 'Date Range: ', 'easy-digital-downloads' ) ) . $daterange, 0, 2, 'L', false );
	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( 'Helvetica', '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __( 'Table View', 'easy-digital-downloads' ) ), 0, 2, 'L', false );
	$pdf->SetFont( 'Helvetica', '', 12 );

	$pdf->SetFillColor( 238, 238, 238 );
	$pdf->Cell( 70, 6, utf8_decode( __( 'Product Name', 'easy-digital-downloads' ) ), 1, 0, 'L', true );
	$pdf->Cell( 30, 6, utf8_decode( __( 'Price', 'easy-digital-downloads' ) ), 1, 0, 'L', true );
	$category_labels = edd_get_taxonomy_labels( 'download_category' );
	$pdf->Cell( 50, 6, utf8_decode( $category_labels['name'] ), 1, 0, 'L', true );
	$pdf->Cell( 50, 6, utf8_decode( __( 'Tags', 'easy-digital-downloads' ) ), 1, 0, 'L', true );
	$pdf->Cell( 45, 6, utf8_decode( __( 'Number of Sales', 'easy-digital-downloads' ) ), 1, 0, 'L', true );
	$pdf->Cell( 35, 6, utf8_decode( __( 'Earnings to Date', 'easy-digital-downloads' ) ), 1, 1, 'L', true );

	$year = date('Y');
	$downloads = get_posts( array( 'post_type' => 'download', 'year' => $year, 'posts_per_page' => -1 ) );

	if ( $downloads ):
		$pdf->SetWidths( array( 70, 30, 50, 50, 45, 35 ) );

		foreach ( $downloads as $download ):
			$pdf->SetFillColor( 255, 255, 255 );

			$title = utf8_decode( get_the_title( $download->ID ) );

			if ( edd_has_variable_prices( $download->ID ) ) {

				$prices = edd_get_variable_prices( $download->ID );

				$first = $prices[0]['amount'];
				$last = array_pop( $prices );
				$last = $last['amount'];

				if ( $first < $last ) {
					$min = $first;
					$max = $last;
				} else {
					$min = $last;
					$max = $first;
				}

				$price = html_entity_decode( edd_currency_filter( edd_format_amount( $min ) ) . ' - ' . edd_currency_filter( edd_format_amount( $max ) ) );
			} else {
				$price = html_entity_decode( edd_currency_filter( edd_get_download_price( $download->ID ) ) );
			}

			$categories = get_the_term_list( $download->ID, 'download_category', '', ', ', '' );
			$categories = $categories ? strip_tags( $categories ) : '';

			$tags = get_the_term_list( $download->ID, 'download_tag', '', ', ', '' );
			$tags = $tags ? strip_tags( $tags ) : '';

			$sales = edd_get_download_sales_stats( $download->ID );
			$earnings = html_entity_decode ( edd_currency_filter( edd_get_download_earnings_stats( $download->ID ) ) );

			if( function_exists( 'iconv' ) ) {
				// Ensure characters like euro; are properly converted. See GithuB issue #472 and #1570
				$price    = iconv('UTF-8', 'windows-1252', utf8_encode( $price ) );
				$earnings = iconv('UTF-8', 'windows-1252', utf8_encode( $earnings ) );
			}

			$pdf->Row( array( $title, $price, $categories, $tags, $sales, $earnings ) );
		endforeach;
	else:
		$pdf->SetWidths( array( 280 ) );
		$title = utf8_decode( sprintf( __( 'No %s found.', 'easy-digital-downloads' ), edd_get_label_plural() ) );
		$pdf->Row( array( $title ) );
	endif;

	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( 'Helvetica', '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __('Graph View','easy-digital-downloads' ) ), 0, 2, 'L', false );
	$pdf->SetFont( 'Helvetica', '', 12 );

	$image = html_entity_decode( urldecode( edd_draw_chart_image() ) );
	$image = str_replace( ' ', '%20', $image );

	$pdf->SetX( 25 );
	$pdf->Image( $image .'&file=.png' );
	$pdf->Ln( 7 );
	$pdf->Output( apply_filters( 'edd_sales_earnings_pdf_export_filename', 'edd-report-' . date_i18n('Y-m-d') ) . '.pdf', 'D' );
}
add_action( 'edd_generate_pdf', 'edd_generate_pdf' );

/**
 * Draws Chart for PDF Report
 *
 * Draws the sales and earnings chart for the PDF report and then retrieves the
 * URL of that chart to display on the PDF Report
 *
 * @since 1.1.4.0
 * @uses GoogleChart
 * @uses GoogleChartData
 * @uses GoogleChartShapeMarker
 * @uses GoogleChartTextMarker
 * @uses GoogleChartAxis
 * @author Sunny Ratilal
 * @return string $chart->getUrl() URL for the Google Chart
 */
function edd_draw_chart_image() {
	require_once EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/GoogleChart.php';
	require_once EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartShapeMarker.php';
	require_once EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartTextMarker.php';

	$chart = new GoogleChart( 'lc', 900, 330 );

	$i = 1;
	$earnings = "";
	$sales = "";

	while ( $i <= 12 ) :
		$earnings .= edd_get_earnings_by_date( null, $i, date('Y') ) . ",";
		$sales .= edd_get_sales_by_date( null, $i, date('Y') ) . ",";
		$i++;
	endwhile;

	$earnings_array = explode( ",", $earnings );
	$sales_array = explode( ",", $sales );

	$i = 0;
	while ( $i <= 11 ) {
		if ( empty( $sales_array[ $i ] ) )
			$sales_array[$i] = 0;
		$i++;
	}

	$max_earnings = max( $earnings_array );

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

	$data->setLegend( __( 'Earnings', 'easy-digital-downloads' ) );
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

	$data = new GoogleChartData( array( $sales_array[0], $sales_array[1], $sales_array[2], $sales_array[3], $sales_array[4], $sales_array[5], $sales_array[6], $sales_array[7], $sales_array[8], $sales_array[9], $sales_array[10], $sales_array[11] ) );
	$data->setLegend( __( 'Sales', 'easy-digital-downloads' ) );
	$data->setColor( 'ff6c1c' );
	$chart->addData( $data );

	$chart->setTitle( __( 'Sales and Earnings by Month for all Products', 'easy-digital-downloads' ), '336699', 18 );

	$chart->setScale( 0, $max_earnings );

	$y_axis = new GoogleChartAxis( 'y' );
	$y_axis->setDrawTickMarks( true )->setLabels( array( 0, $max_earnings ) );
	$chart->addAxis( $y_axis );

	$x_axis = new GoogleChartAxis( 'x' );
	$x_axis->setTickMarks( 5 );
	$x_axis->setLabels( array(
		__('Jan','easy-digital-downloads' ),
		__('Feb','easy-digital-downloads' ),
		__('Mar','easy-digital-downloads' ),
		__('Apr','easy-digital-downloads' ),
		__('May','easy-digital-downloads' ),
		__('June','easy-digital-downloads' ),
		__('July','easy-digital-downloads' ),
		__('Aug','easy-digital-downloads' ),
		__('Sept','easy-digital-downloads' ),
		__('Oct','easy-digital-downloads' ),
		__('Nov','easy-digital-downloads' ),
		__('Dec','easy-digital-downloads' )
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
