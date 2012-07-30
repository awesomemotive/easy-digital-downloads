<?php
/**
 * PDF Report Generation
 *
 * @package     Easy Digital Downloads
 * @subpackage  PDF Report Generation
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @author      Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.3.2
*/

// Load WordPress and FPDF
include_once('../../../../wp-load.php');
include_once(EDD_PLUGIN_DIR . 'includes/fpdf/fpdf.php');
include_once(EDD_PLUGIN_DIR . 'includes/fpdf/mc_table.php');

ob_end_clean();

if (current_user_can("manage_options")) {
	if ( isset( $_GET['report'] ) ) {

		if ( $_GET['report'] == 'sales_and_earnings' ) {
			
			$year = date('Y');
			$query = new WP_Query( $query_string . 'post_type=download&year=' . $year );
			
			$daterange = '1st January ' . date('Y') . ' to ' . date('jS F Y');
			
			$pdf = new PDF_MC_Table();
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
			
			while ( $query->have_posts() ) : $query->the_post();
				$pdf->SetFillColor( 255, 255, 255 );
				
				$title = get_the_title();
				
				if ( edd_has_variable_prices( $post->ID ) ) {
					$prices = get_post_meta( $post->ID, 'edd_variable_prices', true );
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
						$price = html_entity_decode( edd_currency_filter( edd_get_download_price( $post->ID ) ) );
				}
				
				$categories = strip_tags (get_the_term_list( $post->ID, 'download_category', '', ', ', '' ) );
				
				$tags = strip_tags( get_the_term_list( $post->ID, 'download_tag', '', ', ', '' ) );
				
				$sales = edd_get_download_sales_stats( $post->ID );
				
				$link = get_permalink( $post->ID );
				
				$earnings = html_entity_decode ( edd_currency_filter( edd_get_download_earnings_stats( $post->ID ) ) );
				
				$pdf->Row( array( $title, $price, $categories, $tags, $sales, $earnings ) );
			endwhile;

			$pdf->Ln();
			$pdf->SetTextColor( 50, 50, 50 );
			$pdf->SetFont( 'Helvetica', '', 14 );
			$pdf->Cell( 0, 10, 'Graph View', 0, 2, 'L', false );
			$pdf->SetFont( 'Helvetica', '', 12 );
			
			$pdf->SetX( 25 );
			$pdf->Image( EDD_PLUGIN_URL . 'includes/pdf-reports.php?title=all+Products&report=sales_and_earnings&file.png' );
			$pdf->Ln( 7 ); 
			
			while ( $query->have_posts() ) : $query->the_post();
				$earnings = edd_get_download_earnings_stats( $post->ID ) . ",";
				$download_name = str_replace( ' ', '+', get_the_title() );
				$pdf->SetX( 25 );
				$pdf->Image( EDD_PLUGIN_URL . 'includes/pdf-reports.php?id='. $post->ID .'&title='. $download_name .'&report=sales_and_earnings&file=.png' );
				$pdf->Ln( 7 );
			endwhile;

			$pdf->Output();
		}
	
	} else {
		
	}
} else {
	header( 'Location: ' . wp_login_url( EDD_PLUGIN_URL . 'pdf/generate-report.php?report=' . $_GET['report'] ) );
}

?>