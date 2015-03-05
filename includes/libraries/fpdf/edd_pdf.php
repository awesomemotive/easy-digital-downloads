<?php
/**
 * PDF MultiCell Table Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  FPDF
 * @since       1.1.4.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class edd_pdf extends FPDF {

	var $widths;
	var $aligns;

	function Footer() {

		$this->SetY( -15 );
		$this->SetFont( 'Helvetica', 'I', 8 );
		$this->Cell( 0, 10, 'Page '. $this->PageNo(), 0, 0, 'C');

	}

	function SetWidths( $w ) {

		$this->widths = $w;

	}

	function SetAligns( $a ) {

		$this->aligns = $a;

	}

	function Row( $data ) {

		$nb = 0;

		for ( $i = 0; $i < count( $data ); $i++ )
			$nb = max( $nb, $this->NbLines( $this->widths[$i], $data[$i] ) );
			$h = 5 * $nb;

		$this->CheckPageBreak($h);

		for ( $i = 0; $i < count( $data ); $i++ ) {
			$w = $this->widths[ $i ];
			$a = isset( $this->aligns[ $i ] ) ? $this->aligns[ $i ] : 'L';
			$x = $this->GetX();
			$y = $this->GetY();
			$this->Rect( $x, $y, $w, $h );
			$this->MultiCell( $w, 5 , $data[ $i ], 0, $a);
			$this->SetXY( $x + $w , $y );
		}

		$this->Ln( $h );

	}

	function CheckPageBreak( $h ) {

		if ( $this->GetY() + $h > $this->PageBreakTrigger ) {
			$this->AddPage( $this->CurOrientation );
		}

	}

	function NbLines( $w, $txt ) {

		$cw = &$this->CurrentFont['cw'];

		if ( $w == 0 ) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = ( $w - 2 * $this->cMargin ) * 1000 / $this->FontSize;
		$s = str_replace( "\r", '', $txt );
		$nb = strlen( $s );

		if ( $nb > 0 and $s[ $nb - 1 ] == "\n" ) {
			$nb--;
		}

		$sep = -1;
		$i   = 0;
		$j   = 0;
		$l   = 0;
		$nl  = 1;

		while ( $i < $nb ) :
			$c = $s[$i];

			if ( $c == "\n" ) {
				$i++;
				$sep = -1;
				$j   = $i;
				$l   = 0;
				$nl++;
				continue;
			}

			if ( $c == ' ' ) {
				$sep = $i;
			}

			$l += $cw[ $c ];

			if ( $l > $wmax ) {
				if ( $sep == -1 ) {
					if ( $i == $j )
						$i++;
				}
				else
					$i = $sep +1;
				$sep = -1;
				$j   = $i;
				$l   = 0;
				$nl++;
			}
			else
				$i++;
		endwhile;

		return $nl;

	}

}