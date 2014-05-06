<?php
/**
 * Email Templates
 *
 * This class handles all email templating
 *
 * @package     EDD
 * @subpackage  Classes/Email_Templates
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Email_Templates Class
 *
 * @since 2.0
 */
class EDD_Email_Templates {
 
	public function __construct() {
		add_action( 'edd_email_header', array( $this, 'email_header' ) );
		add_action( 'edd_email_footer', array( $this, 'email_footer' ) );
	}
 
	public static function templates() {
		$templates = array(
			'default' => __( 'Default Template', 'edd' ),
			'none'    => __( 'No template, plain text only', 'edd' )
		);
 
		return apply_filters( 'edd_email_templates', $templates );
	}
 
	public function email_header() {
		edd_get_template_part( sprintf( 'emails/email-%s-header.php', edd_get_option( 'email_template', 'default' ) ) );
	}
 
	public function email_footer() {
		edd_get_template_part( sprintf( 'emails/email-%s-footer.php', edd_get_option( 'email_template', 'default' ) ) );
	}

}
new EDD_Email_Templates;