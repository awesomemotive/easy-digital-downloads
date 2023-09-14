<?php
/**
 * Email Summary Class.
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
 * EDD_Email_Summary Class.
 *
 * Takes care of preparing the necessary dataset, building the
 * email template and sending the Email Summary.
 *
 * @since 3.1
 */
class EDD_Email_Summary {

	/**
	 * Are we in a test mode.
	 *
	 * @since 3.1
	 *
	 * @var bool
	 */
	private $test_mode;

	/**
	 * Email options.
	 *
	 * @since 3.1
	 *
	 * @var string
	 */
	private $email_options;

	/**
	 * Image URI Path
	 *
	 * @since 3.1.1
	 *
	 * @var string
	 */
	private $image_path = 'https://plugin.easydigitaldownloads.com/cdn/summaries/';

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct( $test_mode = false ) {
		$this->test_mode     = $test_mode;
		$this->email_options = array(
			'email_summary_frequency' => edd_get_option( 'email_summary_frequency', 'weekly' ),
		);
	}

	/**
	 * Get site URL.
	 *
	 * @since 3.1
	 *
	 * @return string Host of the site url.
	 */
	public function get_site_url() {
		$site_url        = get_site_url();
		$site_url_parsed = wp_parse_url( $site_url );
		$site_url        = isset( $site_url_parsed['host'] ) ? $site_url_parsed['host'] : $site_url;

		return $site_url;
	}

	/**
	 * Get email subject.
	 *
	 * @since 3.1
	 *
	 * @return string Email subject.
	 */
	public function get_email_subject() {
		/* Translators: Site domain name */
		$email_subject = sprintf( __( 'Easy Digital Downloads Summary - %s', 'easy-digital-downloads' ), $this->get_site_url() );

		if ( $this->test_mode ) {
			$email_subject = '[TEST] ' . $email_subject;
		}

		return $email_subject;
	}

	/**
	 * Get email recipients.
	 *
	 * @since 3.1
	 *
	 * @return array Recipients to receive the email.
	 */
	public function get_email_recipients() {
		$recipients = array();

		if ( 'admin' === edd_get_option( 'email_summary_recipient', 'admin' ) ) {
			$recipients[] = get_option( 'admin_email' );
		} else {
			$emails = edd_get_option( 'email_summary_custom_recipients', '' );
			$emails = array_map( 'trim', explode( "\n", $emails ) );
			foreach ( $emails as $email ) {
				if ( is_email( $email ) ) {
					$recipients[] = $email;
				}
			}
		}

		if ( empty( $recipients ) ) {
			edd_debug_log( __( 'Missing email recipients for Email Summary', 'easy-digital-downloads' ), true );
		}

		return apply_filters( 'edd_email_summary_recipients', $recipients );
	}

	/**
	 * Get report start date.
	 *
	 * @since 3.1
	 *
	 * @return EDD\Utils\Date An array of start date and its relative counterpart as the EDD date object set at the UTC equivalent time.
	 */
	public function get_report_start_date() {
		$date = EDD()->utils->date( 'now', edd_get_timezone_id(), false );

		if ( 'monthly' === $this->email_options['email_summary_frequency'] ) {
			$start_date          = $date->copy()->subMonth( 1 )->startOfMonth();
			$relative_start_date = $date->copy()->subMonth( 2 )->startOfMonth();
		} else {
			$start_date          = $date->copy()->subDay( 7 )->startOfDay();
			$relative_start_date = $date->copy()->subDay( 14 )->startOfDay();
		}

		return array(
			'start_date'          => $start_date,
			'relative_start_date' => $relative_start_date,
		);
	}

	/**
	 * Get report end date.
	 *
	 * @since 3.1
	 *
	 * @return EDD\Utils\Date An array of end date and its relative counterpart as the EDD date object set at the UTC equivalent time.
	 */
	public function get_report_end_date() {
		$date = EDD()->utils->date( 'now', edd_get_timezone_id(), false );

		if ( 'monthly' === $this->email_options['email_summary_frequency'] ) {
			$end_date          = $date->copy()->subMonth( 1 )->endOfMonth();
			$relative_end_date = $date->copy()->subMonth( 2 )->endOfMonth();
		} else {
			$end_date          = $date->copy()->endOfDay();
			$relative_end_date = $date->copy()->subDay( 7 )->endOfDay();
		}

		return array(
			'end_date'          => $end_date,
			'relative_end_date' => $relative_end_date,
		);
	}

	/**
	 * Get report date range.
	 *
	 * @since 3.1
	 *
	 * @return array Array of start and end date objects in \EDD\Utils\Date[] format.
	 */
	public function get_report_date_range() {
		// @todo - Check if we have to convert this to UTC because of DB?
		return array_merge(
			$this->get_report_start_date(),
			$this->get_report_end_date()
		);
	}
	/**
	 * Retrieve ! TEST ! dataset for email content.
	 *
	 * @since 3.1
	 *
	 * @return array Data and statistics for the period.
	 */
	public function get_test_report_dataset() {
		$stats = new EDD\Stats();
		$args  = array(
			'post_type'      => 'download',
			'posts_per_page' => 5,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);

		$downloads = new WP_Query( $args );
		$top_selling_products = array();

		foreach ( $downloads->posts as $post ) {
			$download = new EDD_Download( $post );

			$product = new stdClass();
			$product->object = $download;
			$product->total  = 100;

			$top_selling_products[] = $product;
		}

		$data = array(
			'earnings_gross'       => array(
				'value'         => 5000,
				'relative_data' => $stats->generate_relative_data( 5000, 4000  ),
			),
			'earnings_net'         => array(
				'value'         => 4500,
				'relative_data' => $stats->generate_relative_data( 4500, 3500 ),
			),
			'average_order_value'  => array(
				'value'         => 29,
				'relative_data' => $stats->generate_relative_data( 20, 35 ),
			),
			'new_customers'        => array(
				'value'         => 25,
				'relative_data' => $stats->generate_relative_data( 25, 20 ),
			),
			'top_selling_products' => $top_selling_products,
			'order_count'          => array( 'value' => 172 ),
		);

		return $data;
	}

	/**
	 * Retrieve dataset for email content.
	 *
	 * @since 3.1
	 *
	 * @return array Data and statistics for the period.
	 */
	public function get_report_dataset() {
		if ( $this->test_mode ) {
			return $this->get_test_report_dataset();
		}

		$date_range          = $this->get_report_date_range();
		$start_date          = $date_range['start_date']->format( 'Y-m-d H:i:s' );
		$end_date            = $date_range['end_date']->format( 'Y-m-d H:i:s' );
		$relative_start_date = $date_range['relative_start_date']->format( 'Y-m-d H:i:s' );
		$relative_end_date   = $date_range['relative_end_date']->format( 'Y-m-d H:i:s' );
		$stats               = new EDD\Stats(
			array(
				'output'         => 'array',
				'start'          => $start_date,
				'end'            => $end_date,
				'relative'       => true,
				'relative_start' => $relative_start_date,
				'relative_end'   => $relative_end_date,
			)
		);

		$earnings_gross = $stats->get_order_earnings(
			array(
				'function'      => 'SUM',
				'exclude_taxes' => false,
				'revenue_type'  => 'gross',
			)
		);

		$earnings_net = $stats->get_order_earnings(
			array(
				'function'      => 'SUM',
				'exclude_taxes' => true,
				'revenue_type'  => 'net',
			)
		);

		$average_order_value = $stats->get_order_earnings(
			array(
				'function'      => 'AVG',
				'exclude_taxes' => false,
			)
		);

		$new_customers = $stats->get_customer_count(
			array(
				'purchase_count' => true,
			)
		);

		$top_selling_products = $stats->get_most_valuable_order_items(
			array(
				'number' => 5,
			)
		);

		$order_count = $stats->get_order_count();

		return compact(
			'earnings_gross',
			'earnings_net',
			'average_order_value',
			'new_customers',
			'top_selling_products',
			'order_count'
		);
	}

	/**
	 * Generate HTML for relative markup.
	 *
	 * @since 3.1
	 *
	 * @param array $relative_data Calculated relative data.
	 *
	 * @return string HTML for relative markup.
	 */
	private function build_relative_markup( $relative_data ) {
		$arrow  = $relative_data['positive_change'] ? 'icon-arrow-up.png' : 'icon-arrow-down.png';
		$output = __( 'No data to compare', 'easy-digital-downloads' );
		if ( $relative_data['no_change'] ) {
			$output = __( 'No Change', 'easy-digital-downloads' );
		} elseif ( $relative_data['comparable'] ) {
			$output = $relative_data['formatted_percentage_change'] . '%';
		}
		ob_start();
		?>
		<?php if ( $relative_data['comparable'] ) : ?>
			<img src="<?php echo esc_url( $this->image_path . $arrow ); ?>" width="12" height="10" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; width: 12px; height: 10px; max-width: 100%; clear: both; vertical-align: text-top;">
		<?php endif; ?>
		<span style="padding-left: 1px;">
			<?php echo esc_html( $output ); ?>
		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * Build email template.
	 *
	 * @since 3.1
	 *
	 * @param array|bool $blurb Structured blurb data.
	 *
	 * @return string|bool The string of the email template or false if the email template couldn't be built.
	 */
	public function build_email_template( $blurb = false ) {
		$dataset = $this->get_report_dataset();
		// If there were no sales, do not build an email template.
		if ( empty( $dataset['order_count'] ) || 0 === $dataset['order_count'] ) {
			return false;
		}

		$date_range     = $this->get_report_date_range();
		$site_url       = get_site_url();
		$view_more_url  = edd_get_admin_url(
			array(
				'page'           => 'edd-reports',
				'range'          => ( 'monthly' === $this->email_options['email_summary_frequency'] ) ? 'last_month' : 'last_week',
				'relative_range' => 'previous_period',
			)
		);
		$wp_date_format = get_option( 'date_format' );
		$period_name    = ( 'monthly' === $this->email_options['email_summary_frequency'] ) ? __( 'month', 'easy-digital-downloads' ) : __( 'week', 'easy-digital-downloads' );
		/* Translators: period name (e.g. week) */
		$relative_text  = sprintf( __( 'vs previous %s', 'easy-digital-downloads' ), $period_name );

		ob_start();
		include EDD_PLUGIN_DIR . 'includes/emails/email-summary/edd-email-summary-template.php';

		return ob_get_clean();
	}

	/**
	 * Prepare and send email.
	 *
	 * @since 3.1
	 *
	 * @return bool True if email was sent, false if there was an error.
	 */
	public function send_email() {
		// Get next blurb.
		$email_blurbs = new EDD_Email_Summary_Blurb();
		$next_blurb   = false;

		if ( ! $this->test_mode ) {
			$next_blurb = $email_blurbs->get_next();
		}

		// Prepare email.
		$email_body = $this->build_email_template( $next_blurb );
		// If there is no email body, we cannot continue.
		if ( ! $email_body ) {
			edd_debug_log( __( 'Email body for Email Summary was empty.', 'easy-digital-downloads' ), true );
			return false;
		}

		$email_subject    = $this->get_email_subject();
		$email_recipients = $this->get_email_recipients();
		$email_headers    = array( 'Content-Type: text/html; charset=UTF-8' );

		// Everything is ok, send email.
		$email_sent = wp_mail( $email_recipients, $email_subject, $email_body, $email_headers );
		if ( $email_sent ) {
			$email_blurbs->mark_blurb_sent( $next_blurb );
		}

		return $email_sent;
	}
}
