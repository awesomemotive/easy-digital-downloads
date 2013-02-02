<?php
/**
 * PDF MultiCell Table Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  FPDF
 * @since       1.1.4.0
*/

class EDD_PDF extends TCPDF {
	public static function Footer() {
		parent::SetY( -15 );
		parent::SetFont( 'freesans', 'I', 8 );
		parent::Cell( 0, 10, __( 'Page', 'edd' ) . ' ' . parent::getAliasNumPage() . ' ' . __( 'of', 'edd' ) . ' ' . parent::getAliasNbPages(), 0, 0, 'C');
	}
}