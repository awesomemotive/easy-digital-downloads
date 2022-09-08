<?php
/**
 * Email Summary Admin Class.
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Email_Summary_Admin Class.
 *
 * Takes care of Admin actions for Email Summaries.
 *
 * @since 3.1
 */
class EDD_Email_Summary_Admin {

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {
		add_action( 'edd_trigger_email_summary', array( $this, 'trigger_email_summary' ) );
		add_action( 'edd_preview_email_summary', array( $this, 'preview_email_summaryy' ) );
	}

	/**
	 * Send Email Summary preview.
	 *
	 * @since 3.1
	 *
	 * @param array $data GET Request array.
	 */
	public function trigger_email_summary( $data ) {
		if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_trigger_email_summary' ) ) {
			wp_die( __( 'Nonce verification failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$email        = new EDD_Email_Summary();
		$email_status = $email->send_email();
		$this->check_email_status( $email_status );

		$url = edd_get_admin_url(
			array(
				'page'        => 'edd-settings',
				'tab'         => 'emails',
				'section'     => 'email_summaries',
				'edd-message' => 'test-summary-email-sent',
			)
		);

		edd_redirect( $url );
	}

	/**
	 * Build and output Email Summary template to the screen.
	 *
	 * @since 3.1
	 */
	public function preview_email_summaryy() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_preview_email_summary' ) ) {
			wp_die( __( 'Nonce verification failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		// Get next blurb.
		$email_blurbs = new EDD_Email_Summary_Blurb();
		$next_blurb   = $email_blurbs->get_next();

		// Get email body.
		$email      = new EDD_Email_Summary();
		$email_body = $email->build_email_template( $next_blurb );
		$this->check_email_status( $email_body );

		echo $email_body;
		exit;
	}

	/**
	 * Check if there was an error while preparing
	 * or sending email summary and abort.
	 *
	 * @since 3.1
	 *
	 * @param bool $email_status True if email was built and sent succesfully, false if not.
	 */
	public function check_email_status( $email_status ) {
		if ( ! $email_status ) {
			wp_die( __( 'There was an error while sending an email. Does your store have any sales in the requested period?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 500 ) );
		}
	}

}
