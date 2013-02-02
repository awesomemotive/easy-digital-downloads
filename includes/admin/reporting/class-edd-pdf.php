<?php
/**
 * PDF MultiCell Table Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  FPDF
 * @since       1.1.4.0
*/

class EDD_PDF extends TCPDF {
	function Footer() {

		$this->SetY( -15 );
		$this->SetFont( 'Helvetica', 'I', 8 );
		$this->Cell( 0, 10, 'Page '. $this->PageNo(), 0, 0, 'C');

	}
}