<?php
/**
 * Easy Digital Downloads Email Settings
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */
namespace EDD\Admin\Settings\Tabs;

use EDD\Emails\Registry;

defined( 'ABSPATH' ) || exit;

class Emails extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	protected $id = 'emails';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {

		return array(
			'main'               => array(
				'email_header'   => array(
					'id'   => 'email_header',
					'name' => '<strong>' . __( 'Email Configuration', 'easy-digital-downloads' ) . '</strong>',
					'type' => 'header',
				),
				'email_template' => array(
					'id'      => 'email_template',
					'name'    => __( 'Template', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => edd_get_email_templates(),
				),
				'email_logo'     => array(
					'id'   => 'email_logo',
					'name' => __( 'Logo', 'easy-digital-downloads' ),
					'desc' => __( 'Upload or choose a logo to be displayed at the top of sales receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads' ),
					'type' => 'upload',
				),
				'from_name'      => array(
					'id'          => 'from_name',
					'name'        => __( 'From Name', 'easy-digital-downloads' ),
					'desc'        => __( 'This should be your site or shop name. Defaults to Site Title if empty.', 'easy-digital-downloads' ),
					'type'        => 'text',
					'std'         => $this->get_site_name(),
					'placeholder' => $this->get_site_name(),
				),
				'from_email'     => array(
					'id'          => 'from_email',
					'name'        => __( 'From Email', 'easy-digital-downloads' ),
					'desc'        => __( 'This will act as the "from" and "reply-to" addresses.', 'easy-digital-downloads' ),
					'type'        => 'email',
					'std'         => $this->get_admin_email(),
					'placeholder' => $this->get_admin_email(),
				),
				'email_settings' => array(
					'id'   => 'email_settings',
					'name' => '',
					'desc' => '',
					'type' => 'hook',
				),
			),
			'purchase_receipts'  => array(
				'purchase_receipt_email_settings' => array(
					'id'   => 'purchase_receipt_email_settings',
					'name' => '',
					'desc' => '',
					'type' => 'hook',
				),
				'purchase_subject'                => array(
					'id'   => 'purchase_subject',
					'name' => __( 'Purchase Email Subject', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the subject line for the purchase receipt email.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
				),
				'purchase_heading'                => array(
					'id'   => 'purchase_heading',
					'name' => __( 'Purchase Email Heading', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the heading for the purchase receipt email.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
				),
				'purchase_receipt'                => array(
					'id'   => 'purchase_receipt',
					'name' => __( 'Purchase Receipt', 'easy-digital-downloads' ),
					'desc' => __( 'Text to email customers after completing a purchase. Personalize with HTML and <code>{tag}</code> markers.', 'easy-digital-downloads' ) . '<br/><br/>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => $this->get_default_email_content( 'order_receipt' ),
				),
			),
			'sale_notifications' => array(
				'sale_notification_subject' => array(
					'id'   => 'sale_notification_subject',
					'name' => __( 'Sale Notification Subject', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the subject line for the sale notification email.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => 'New download purchase - Order #{payment_id}',
				),
				'sale_notification_heading' => array(
					'id'   => 'sale_notification_heading',
					'name' => __( 'Sale Notification Heading', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the heading for the sale notification email.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'New Sale!', 'easy-digital-downloads' ),
				),
				'sale_notification'         => array(
					'id'   => 'sale_notification',
					'name' => __( 'Sale Notification', 'easy-digital-downloads' ),
					'desc' => __( 'Text to email as a notification for every completed purchase. Personalize with HTML and <code>{tag}</code> markers.', 'easy-digital-downloads' ) . '<br/><br/>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => $this->get_default_email_content( 'admin_order_notice' ),
				),
				'admin_notice_emails'       => array(
					'id'   => 'admin_notice_emails',
					'name' => __( 'Sale Notification Emails', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made. One per line.', 'easy-digital-downloads' ),
					'type' => 'textarea',
					'std'  => $this->get_admin_email(),
				),
				'disable_admin_notices'     => array(
					'id'   => 'disable_admin_notices',
					'name' => __( 'Disable Admin Notifications', 'easy-digital-downloads' ),
					'desc' => __( 'Check this box if you do not want to receive sales notification emails.', 'easy-digital-downloads' ),
					'type' => 'checkbox',
				),
			),
			'email_summaries'    => $this->get_email_summaries(),
		);
	}

	/**
	 * Gets the default email content for a registered email.
	 *
	 * @since 3.2.0
	 * @param string $email The registered email to get the default content for.
	 * @return string
	 */
	private function get_default_email_content( $email ) {
		if ( ! Registry::is_registered( $email ) ) {
			return '';
		}
		$registered_email = Registry::get( $email, array( false ) );

		return $registered_email->get_default_body_content();
	}

	/**
	 * Gets the email summaries settings.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_email_summaries() {
		$email_summary_recipient     = edd_get_option( 'email_summary_recipient', 'admin' );
		$email_summary_trigger_url   = wp_nonce_url(
			edd_get_admin_url(
				array(
					'page'       => 'edd-settings',
					'tab'        => 'emails',
					'section'    => 'email_summaries',
					'edd_action' => 'trigger_email_summary',
				)
			),
			'edd_trigger_email_summary'
		);
		$email_summary_schedule      = wp_next_scheduled( \EDD_Email_Summary_Cron::CRON_EVENT_NAME );
		$email_summary_schedule_text = '<span><span class="dashicons dashicons-warning"></span> ' . esc_html( __( 'The summary email is not yet scheduled. Save the settings to manually schedule it.', 'easy-digital-downloads' ) ) . '</span>';
		if ( $email_summary_schedule ) {
			$email_summary_schedule_date = \EDD\Utils\Date::createFromTimestamp( $email_summary_schedule )->setTimezone( edd_get_timezone_id() );
			/* Translators: formatted date */
			$email_summary_schedule_text = sprintf( __( 'The next summary email is scheduled to send on %s.', 'easy-digital-downloads' ), $email_summary_schedule_date->format( get_option( 'date_format' ) ) );
		}

		return array(
			'email_summary_frequency'         => array(
				'id'      => 'email_summary_frequency',
				'name'    => __( 'Email Frequency', 'easy-digital-downloads' ),
				'type'    => 'select',
				'std'     => 'weekly',
				'desc'    => $email_summary_schedule_text,
				'options' => array(
					'weekly'  => __( 'Weekly', 'easy-digital-downloads' ),
					'monthly' => __( 'Monthly', 'easy-digital-downloads' ),
				),
			),
			'email_summary_recipient'         => array(
				'id'      => 'email_summary_recipient',
				'name'    => __( 'Email Recipient', 'easy-digital-downloads' ),
				'type'    => 'select',
				'std'     => 'admin',
				'options' => array(
					/* Translators: email */
					'admin'  => sprintf( __( 'Administrator: %s', 'easy-digital-downloads' ), $this->get_admin_email() ),
					'custom' => __( 'Custom Recipients', 'easy-digital-downloads' ),
				),
			),
			'email_summary_custom_recipients' => array(
				'id'    => 'email_summary_custom_recipients',
				'class' => ( 'admin' === $email_summary_recipient ) ? 'hidden' : '',
				'name'  => __( 'Custom Recipients', 'easy-digital-downloads' ),
				'desc'  => __( 'Enter the email address(es) that should receive Email Summaries. One per line.', 'easy-digital-downloads' ),
				'type'  => 'textarea',
			),
			'email_summary_buttons'           => array(
				'id'   => 'email_summary_buttons',
				'name' => '',
				'desc' => '
							<a href="' . esc_url( $email_summary_trigger_url ) . '" class="button" id="edd-send-test-summary">' . esc_html( __( 'Send Test Email', 'easy-digital-downloads' ) ) . '</a>
							<div id="edd-send-test-summary-save-changes-notice"></div>
							<div id="edd-send-test-summary-notice"></div>
						',
				'type' => 'descriptive_text',
			),
			'disable_email_summary'           => array(
				'id'    => 'disable_email_summary',
				'name'  => __( 'Disable Email Summary', 'easy-digital-downloads' ),
				'desc'  => '<a target="_blank" href="https://easydigitaldownloads.com/docs/email-settings/#summaries">' . __( 'Learn more about Email Summaries.', 'easy-digital-downloads' ) . '</a>',
				'check' => __( 'Check this box to disable Email Summaries.', 'easy-digital-downloads' ),
				'type'  => 'checkbox_description',
			),
		);
	}
}
