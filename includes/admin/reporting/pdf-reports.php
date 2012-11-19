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

		include_once( EDD_PLUGIN_DIR . '/includes/libraries/html2pdf/html2pdf.class.php' );

		$daterange = date_i18n( get_option( 'date_format' ), mktime( 0, 0, 0, 1, 1, date( 'Y' ) ) ) . ' ' . utf8_decode( __( 'to', 'edd' ) ) . ' ' . date_i18n( get_option( 'date_format' ) );

		$pdf = new HTML2PDF( 'L', 'A4' );
		$pdf->pdf->SetDisplayMode( 'real' );
		$pdf->pdf->SetTitle( utf8_decode( __( 'Sales and earnings reports for the current year for all products', 'edd') ) );
		$pdf->pdf->SetAuthor( utf8_decode( __( 'Easy Digital Downloads', 'edd' ) ) );

		$content = '<page style="font-family: freesans" backtop="10px" backbottom="10px" backleft="4mm" backright="4mm">';

		ob_start(); ?>
			<style type="text/css">
			<!--
			body { font-family: freesans; }
			th { background-color: #eeeeee; padding: 8px 0; border-bottom: 1px solid #666; }
			td { padding: 5px 0; }
			-->
			</style>
			<img src="<?php echo EDD_PLUGIN_URL ?>/includes/images/edd-logo.png" />
			<h1 style="color: #323232f; margin-top: 8px; margin-bottom: 0;"><?php echo utf8_decode( __( 'Sales and earnings reports for the current year for all products', 'edd' ) ); ?></h1>
			<p style="color: #969696; font-size: 12pt; margin-bottom: 0;"><?php echo utf8_decode( __( 'Date Range: ', 'edd' ) ) . $daterange; ?></p>
			<h2 style="font-size: 14pt; color: #323232"><?php echo utf8_decode( __( 'Table View', 'edd' ) ); ?></h2>
			<table cellpadding="2" cellspacing="2;" style="border: 1px solid #666;">
				<thead>
					<tr>
						<th style="width: 70mm; padding-left: 2px"><?php echo utf8_decode( __( 'Product Name', 'edd' ) ); ?></th>
						<th style="width: 30mm"><?php echo utf8_decode( __( 'Price', 'edd' ) ) ?></th>
						<th style="width: 50mm;"><?php echo utf8_decode( __( 'Categories', 'edd' ) ) ?></th>
						<th style="width: 50mm;"><?php echo utf8_decode( __( 'Tags', 'edd' ) ) ?></th>
						<th style="width: 45mm;"><?php echo utf8_decode( __( 'Number of Sales', 'edd' ) ) ?></th>
						<th style="width: 35mm;"><?php echo utf8_decode( __( 'Earnings to Date', 'edd' ) ) ?></th>
					</tr>
				</thead>
		<?php

		$year = date('Y');

		$downloads = get_posts(
			array(
				'post_type' => 'download',
				'year' => $year,
				'posts_per_page' => -1,
				'cache_results' => false,
				'update_post_term_cache' => false,
				'no_found_rows' => true
			)
		);

		if ( $downloads ):
			foreach ( $downloads as $download ):
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
				$link = get_permalink( $download->ID );
				$earnings = html_entity_decode ( edd_currency_filter( edd_get_download_earnings_stats( $download->ID ) ) );

				?>
				<tr>
					<td style="padding-left: 2px;"><?php echo $title; ?></td>
					<td><?php echo $price; ?></td>
					<td><?php echo $categories; ?></td>
					<td><?php echo $tags; ?></td>
					<td><?php echo $sales; ?></td>
					<td><?php echo $earnings; ?></td>
				</tr>
				<?php
			endforeach;
		else: ?>
			<tr><td><?php echo utf8_decode( sprintf( __( 'No %s found.', 'edd' ), edd_get_label_plural() ) ); ?></td></tr>
			<?php
		endif;

		$image = html_entity_decode( urldecode( edd_draw_chart_image() ) );
		$image = str_replace( ' ', '%20', $image );

		?>
			</table>
			<h2 style="font-size: 14pt; color: #323232"><?php echo utf8_decode( __( 'Graph View', 'edd' ) ); ?></h2>
			<p align="center"><img src="<?php echo $image ?>&amp;file=.png" /></p>
			<page_footer>
				<p align="center; font-size: 9pt"><i><?php echo utf8_decode( __( 'Page', 'edd' ) ); ?> [[page_cu]]/[[page_nb]]</i></p>
			</page_footer>
		</page>
		<?php

		$content .= ob_get_clean();
		$pdf->WriteHTML( $content );
		$pdf->Output( 'edd-report-' . date_i18n('Y-m-d') . '.pdf', apply_filters( 'edd_pdf_report_download_type', 'I' ) );
	}
}
add_action( 'edd_generate_pdf', 'edd_generate_pdf' );


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
	include_once( EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/GoogleChart.php' );
	include_once( EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartShapeMarker.php' );
	include_once( EDD_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartTextMarker.php' );

	$chart = new GoogleChart( 'lc', 900, 330 );

	$i = 1;
	$earnings = "";
	$sales = "";
	while( $i <= 12 ) :
		$earnings .= edd_get_earnings_by_date( null, $i, date('Y') ) . ",";
		$sales .= edd_get_sales_by_date( null, $i, date('Y') ) . ",";
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

	$data->setLegend( __( 'Earnings', 'edd' ) );
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
	$data->setLegend( __( 'Sales', 'edd' ) );
	$data->setColor( 'ff6c1c' );
	$chart->addData( $data );

	$chart->setTitle( __( 'Sales and Earnings by Month for all Products', 'edd' ), '336699', 18 );

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