<?php
/**
 * EDD PDF Report Class
 *
 * Extends the TCPDF Class for the EDD Reports
 *
 * @package     Easy Digital Downloads
 * @subpackage  Reporting
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

/**
 * EDD_PDF Class
 */
class EDD_PDF extends TCPDF {
	/**
	 * Outputs the footer on each page
	 *
	 * @since 1.4.4
	 */
	public static function Footer() {
		parent::SetY( -15 );
		parent::SetFont( 'freesans', 'I', 8 );
		parent::Cell( 0, 10, __( 'Page', 'edd' ) . ' ' . parent::getAliasNumPage() . ' ' . __( 'of', 'edd' ) . ' ' . parent::getAliasNbPages(), 0, 0, 'C');
		do_action( 'edd_pdf_report_footer' );
	}
}