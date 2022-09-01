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
	 * Email options.
	 *
	 * @since 3.1
	 *
	 * @var string
	 */
	private $email_options;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {
		$options                                = array();
		$options['email_summary_frequency']     = edd_get_option( 'email_summary_frequency', 'weekly' );
		$options['email_summary_start_of_week'] = jddayofweek( (int) get_option( 'start_of_week' ) - 1, 1);
		$this->email_options                    = $options;
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
		// Translators: The domain of the site is appended to the subject.
		$email_subject = sprintf( __( 'Easy Digital Downloads Summary - %s', 'easy-digital-downloads' ), $this->get_site_url() );

		return apply_filters( 'edd_email_summary_subject', $email_subject );
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
	 * @return EDD\Utils\Date An array of start date and it's relative counterpart as the EDD date object set at the UTC equivalent time.
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
	 * @return EDD\Utils\Date An array of end date and it's relative counterpart as the EDD date object set at the UTC equivalent time.
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
	 * Retrieve dataset for email content.
	 *
	 * @since 3.1
	 *
	 * @return array Data and statistics for the period.
	 */
	public function get_report_dataset() {
		$date_range          = $this->get_report_date_range();
		$stats               = new EDD\Stats();
		$start_date          = $date_range['start_date']->format('Y-m-d H:i:s');
		$end_date            = $date_range['end_date']->format('Y-m-d H:i:s');
		$relative_start_date = $date_range['relative_start_date']->format('Y-m-d H:i:s');
		$relative_end_date   = $date_range['relative_end_date']->format('Y-m-d H:i:s');

		$earnings_gross = $stats->get_order_earnings( array(
			'output'        => 'array',
			'function'      => 'SUM',
			'exclude_taxes' => false,
			'revenue_type'  => 'gross',
			'start'         => $start_date,
			'end'           => $end_date,
			'relative'      => true,
			'relative_start'=> $relative_start_date,
			'relative_end'  => $relative_end_date,
		) );

		$earnings_net = $stats->get_order_earnings( array(
			'output'        => 'array',
			'function'      => 'SUM',
			'exclude_taxes' => true,
			'revenue_type'  => 'net',
			'start'         => $start_date,
			'end'           => $end_date,
			'relative'      => true,
			'relative_start'=> $relative_start_date,
			'relative_end'  => $relative_end_date,
		) );

		$average_order_value =  $stats->get_order_earnings( array(
			'output'        => 'array',
			'function'      => 'AVG',
			'exclude_taxes' => false,
			'start'         => $start_date,
			'end'           => $end_date,
			'relative'      => true,
			'relative_start'=> $relative_start_date,
			'relative_end'  => $relative_end_date,
		) );

		$new_customers =  $stats->get_customer_count( array(
			'output'        => 'array',
			'start'         => $start_date,
			'end'           => $end_date,
			'relative'      => true,
			'relative_start'=> $relative_start_date,
			'relative_end'  => $relative_end_date,
		) );

		$top_selling_products = $stats->get_most_valuable_order_items( array(
			'number'   => 5,
			'start'         => $start_date,
			'end'           => $end_date,
			'relative'      => true,
			'relative_start'=> $relative_start_date,
			'relative_end'  => $relative_end_date,
		) );

		$order_count =  $stats->get_order_count( array(
			'start'         => $start_date,
			'end'           => $end_date,
			'relative'      => true,
			'relative_start'=> $relative_start_date,
			'relative_end'  => $relative_end_date,
		) );

		return compact(
			'earnings_gross',
			'earnings_net',
			'average_order_value',
			'new_customers',
			'top_selling_products',
			'order_count',
		) ;
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
		$arrow  = $relative_data['positive_change'] ? 'icon-arrow-up.png': 'icon-arrow-down.png';
		$output = __( 'No data to compare', 'easy-digital-downloads' );
		if ( $relative_data['no_change'] ) {
			$output = __( 'No Change', 'easy-digital-downloads' );
		} else if ( $relative_data['comparable'] ) {
			$output = $relative_data['formatted_percentage_change'] . '%';
		}
		ob_start();
		?>
		<?php if( $relative_data['comparable'] ): ?>
			<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/' . $arrow ); ?>" width="12" height="10" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; width: 12px; height: 10px; max-width: 100%; clear: both; vertical-align: text-top;">
		<?php endif; ?>
		<span style="padding-left: 1px;">
			<?php echo esc_html( $output );?>
		</span>
		<?php
		$relative_markup = ob_get_clean();
		return $relative_markup;
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
		$dataset         = $this->get_report_dataset();
		$date_range      = $this->get_report_date_range();

		$wp_date_format  = get_option('date_format');
		$site_url        = $this->get_site_url();

		$period_name     = ( 'monthly' === $this->email_options['email_summary_frequency'] ) ? __( 'month', 'easy-digital-downloads' ) :  __( 'week', 'easy-digital-downloads' );
		/* Translators: period name (e.g. week) */
		$relative_text   = sprintf( __( 'vs previous %s', 'easy-digital-downloads' ), $period_name );

		// If there were no sales, do not build an email template.
		if ( $dataset['order_count'] === 0 ) {
			return false;
		}

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title><?php echo esc_html( $this->get_email_subject() );?></title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<!--[if !mso]><!-->
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<!--<![endif]-->

				<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
				<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
			</head>
			<style>
				@media only screen and (max-width: 599px) {
					.push-down-25 {
						margin-bottom: 20px;
					}
					.pull-down-8 {
						margin-top: 4px !important;
					}
					.pull-down-5 {
						margin-top: 2.5px !important;
					}
					h1 {
						font-size: 18px !important;
						line-height: 24px !important;
					}
					h2 {
						font-size: 12px !important;
						line-height: 16px !important;
					}
					p {
						font-size: 10px !important;
						line-height: 13px !important;
					}
					p.bigger {
						font-size: 10px !important;
						line-height: 16px !important;
					}
					.email-header-holder {
						max-height: 50px !important;
						height: 50px !important;
					}
					.logo-holder {
						padding: 10px 22px 5px 22px !important;
					}
					.logo-holder .edd-logo {
						max-width: 175px !important;
						max-height: 28px !important;
					}
					.content-holder {
						padding: 22px 22px 0px 22px !important;
					}
					.content-holder.pro-tip-section {
						padding: 0px 22px 27px 22px !important;
					}
					.period-date {
						font-size: 10px !important;
						line-height: 13px !important;
					}
					.link-style {
						font-size: 10px !important;
						line-height: 13px !important;
					}
					.link-style.bigger {
						font-size: 10px !important;
						line-height: 13px !important;
					}
					.stats-totals-wrapper {
						margin-top: 38px !important;
					}
					.stats-totals-wrapper td {
						font-size: 12px !important;
						border-collapse: collapse !important;
					}
					.stats-total-item {
						width: 145px;
						display: inline-table;
						min-width: 145px;
					}
					.stats-total-item-inner {
						font-size: 12px !important;
						border-collapse: collapse !important;
					}
					.stats-total-item-title {
						font-size: 12px !important;
						line-height: 15px !important;
						margin-bottom: 2.5px !important;
					}
					.stats-total-item-value {
						line-height: 18px !important;
						font-size: 16px !important;
					}
					.stats-total-item-percent {
						line-height: 8px !important;
						font-size: 8px !important;
						white-space: nowrap;
					}
					.stats-total-item-percent span.comparison {
						font-style: normal;
						font-weight: lighter;
						font-size: 8px !important;
						line-height: 10px !important;
						margin-top: 3px !important;
					}
					.stats-total-item-percent img {
						width: 8px !important;
						height: 6px !important;
						vertical-align: baseline !important;
					}
					.table-top-title {
						font-size: 12px !important;
						line-height: 15px !important;
					}
					table.top-products tr th {
						padding: 6px 0px !important;
						font-size: 10px !important;
						line-height: 13px !important;
					}
					table.top-products tr td {
						font-size: 10px !important;
						padding: 6px 0px !important;
						border-bottom: 1px solid #F0F1F4;
					}
					table.top-products tr td:nth-child(2) {
						font-size: 12px !important;
					}
					.pro-tip-holder {
						background: #F3F8FE;
						border-radius: 8px !important;
						padding: 24px 24px !important;
					}
					.pro-tip-section-title {
						font-size: 14px !important;
						line-height: 15px !important;
					}
					.pro-tip-section-title img {
						margin-right: 3px !important;
					}
					.pro-tip-title {
						font-size: 12px !important;
						line-height: 16px !important;
					}
					.cta-btn {
						padding: 6px 15px;
						font-weight: 600;
						font-size: 14px;
						line-height: 20px;
						background: #2794DA;
						display: inline-block;
						color: #FFFFFF;
						text-decoration: none;
					}
				}
			</style>

			<body style="margin: 0px;">
				<!-- PREVIEW TEXT -->
				<div style="display: none; max-height: 0px; overflow: hidden;">
					<?php echo esc_html( __( 'Store performance summary', 'easy-digital-downloads' ) ); ?> <?php echo esc_html( $date_range['start_date']->format( $wp_date_format ) );?> - <?php echo esc_html( $date_range['end_date']->format( $wp_date_format ) );?>
				</div>

				<!-- HEADER HOLDER -->
				<div class="email-header-holder" style="background: #343A40; max-height: 60px; height: 60px;">

					<div class="email-container" style="max-width: 450px; margin: 0 auto; font-family: 'Source Sans Pro', sans-serif; color: #1F2937;">

						<div class="logo-holder" style="padding: 12px 31px 7px 31px; display: inline-block;">
							<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/edd-logo-white-2x.png' ); ?>" class="edd-logo" width="216" height="35" style="width: 100%; max-width: 100%; height: auto; max-width: 216px; max-height: 35px;">
						</div>

					</div>
				</div>

				<div class="email-container" style="max-width: 450px; margin: 0 auto; font-family: 'Source Sans Pro', sans-serif; color: #1F2937;">


					<!-- MAIN EMAIL CONTENT HOLDER -->

					<div class="content-box" style="background: #FFF;">

						<div class="content-holder" style="padding: 22px 31px 0px 31px;">

							<h1 style="margin: 0px; color: #1F2937;font-weight: 700;font-size: 24px;line-height: 24px;"><?php echo esc_html( __( 'Your eCommerce Summary', 'easy-digital-downloads' ) ); ?></h1>

							<div class="period-date pull-down-8" style="margin-top: 8px; font-weight: 400; font-size: 14px; line-height: 18px; color: #4B5563;">
								<?php echo esc_html( $date_range['start_date']->format( $wp_date_format ) );?> - <?php echo esc_html( $date_range['end_date']->format( $wp_date_format ) );?>
							</div>

							<a href="<?php echo esc_attr( $site_url );?>" class="link-style pull-down-8" style="margin-top: 8px; font-weight: 400; font-size: 14px; text-decoration-line: underline; display: inline-block; color: inherit; text-decoration: none;">
								<?php echo esc_url( $site_url );?>
							</a>


							<div class="pull-down-20" style="margin-top: 20px;">
								<h2 style="margin: 0px; font-weight: 700; font-size: 18px; line-height: 23px; letter-spacing: -0.02em; color: #1F2937;"><?php echo esc_html( __( 'Hey there!', 'easy-digital-downloads' ) ); ?></h2>
							</div>


							<p class="pull-down-5" style="margin: 0px; font-weight: 400; font-size: 14px; line-height: 18px; color: #4B5563; margin-top: 5px;">
								<?php
									/* Translators: period name (e.g. week) */
									echo esc_html( sprintf( __( 'Below is a look at how your store performed in the last %s.', 'easy-digital-downloads' ), $period_name ) );
								?>
							</p>


							<!-- DATA LISTING -->
							<table class="stats-totals-wrapper" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 100%; margin-top: 48px;" width="100%" valign="top" align="left">
							<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
								<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
									<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
										<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
										<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
											<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
												<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-gross.png' ); ?>" alt="#" title="#" width="28" height="28">
											</p>
											<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
												<?php echo esc_html( __( 'Gross Revenue', 'easy-digital-downloads' ) ); ?>
											</p>
											<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
												<?php echo edd_currency_filter( edd_format_amount( $dataset['earnings_gross']['value'] ) );?>
											</p>
											<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
												<?php echo $this->build_relative_markup( $dataset['earnings_gross']['relative_data'] );?>
												<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
													<?php echo esc_html( $relative_text ); ?>
												</span>
											</p>
										</td>
										</tr>
									</table>
								</td>


								<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
									<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
										<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
										<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
											<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
												<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-net.png' ); ?>" alt="#" title="#" width="28" height="28">
											</p>
											<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
												<?php echo esc_html( __( 'Net Revenue', 'easy-digital-downloads' ) ); ?>
											</p>
											<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
												<?php echo edd_currency_filter( edd_format_amount( $dataset['earnings_net']['value'] ) );?>
											</p>
											<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
												<?php echo $this->build_relative_markup( $dataset['earnings_net']['relative_data'] );?>
												<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
													<?php echo esc_html( $relative_text ); ?>
												</span>
											</p>
										</td>
										</tr>
									</table>
								</td>


							</tr>
							</table>

							<table class="stats-totals-wrapper pull-down-40" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 100%; margin-top: 48px; margin-bottom: 48px !important;" width="100%" valign="top" align="left">
							<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
								<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
									<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
										<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
										<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
											<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
												<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-new-customers.png' ); ?>" alt="#" title="#" width="28" height="28">
											</p>
											<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
												<?php echo esc_html( __( 'New Customers', 'easy-digital-downloads' ) ); ?>
											</p>
											<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
												<?php echo $dataset['new_customers']['value'];?>
											</p>
											<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
												<?php echo $this->build_relative_markup( $dataset['new_customers']['relative_data'] );?>
												<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
													<?php echo esc_html( $relative_text ); ?>
												</span>
											</p>
										</td>
										</tr>
									</table>
								</td>


								<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
									<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
										<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
										<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
											<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
												<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-average.png' ); ?>" alt="#" title="#" width="28" height="28">
											</p>
											<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
												<?php echo esc_html( __( 'Average Order', 'easy-digital-downloads' ) ); ?>
											</p>
											<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
												<?php echo edd_currency_filter( edd_format_amount( $dataset['average_order_value']['value'] ) );?>
											</p>
											<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
												<?php echo $this->build_relative_markup( $dataset['average_order_value']['relative_data'] );?>
												<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
													<?php echo esc_html( $relative_text ); ?>
												</span>
											</p>
										</td>
										</tr>
									</table>
								</td>


							</tr>
							</table>


							<hr style="border: 0.2px solid #E5E7EB; display: block;">


							<!-- TABLE DATA -->
							<div class="table-data-holder pull-down-25 push-down-25" style="margin-top: 25px; margin-bottom: 25px;">
								<div class="table-top-icon align-c" style="text-align: center;">
									<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-top-products.png' ); ?>" alt="#" title="#" width="28" height="28">
								</div>

								<div class="table-top-title align-c" style="text-align: center; font-size: 14px; line-height: 18px; font-weight: 600; color: #1F2937; display: block; margin-top: 0px; margin-bottom: 12px;">
									<?php echo esc_html( __( 'Top 5 Products by Revenue', 'easy-digital-downloads' ) ); ?>
								</div>

								<table class="top-products" style="border-collapse: collapse; width: 100%; font-size: 12px; line-height: 15px; color: #4B5563;" width="100%">
									<tr>
										<th style="font-weight: 600; border-bottom: 1px solid #E5E7EB; text-align: left; border-right: none; padding: 10px 0px; font-size: 12px; line-height: 15px;" align="left"><?php echo esc_html( __( 'Product', 'easy-digital-downloads' ) ); ?></th>
										<th style="font-weight: 600; border-bottom: 1px solid #E5E7EB; border-right: none; padding: 10px 0px; font-size: 12px; line-height: 15px; text-align: right;" align="right"><?php echo esc_html( __( 'Gross Revenue', 'easy-digital-downloads' ) ); ?>e</th>
									</tr>
									<?php
									$counter = 1;
									foreach( $dataset['top_selling_products'] as $product ):
										if ( ! $product->object instanceof \EDD_Download ) {
											continue;
										}

										$title   = $product->object->post_title;
										$revenue = edd_currency_filter( edd_format_amount( $product->total ) );
									?>
									<tr>
										<td style="font-size: 12px; color: #4B5563; font-weight: 400; text-align: left; padding: 9px 0px; border-bottom: 1px solid #F0F1F4;" align="left"><?php echo esc_html( $counter );?>. <?php echo esc_html( $title );?></td>
										<td style="font-size: 12px; color: #4B5563; font-weight: 400; padding: 9px 0px; border-bottom: 1px solid #F0F1F4; text-align: right;" align="right"><?php echo esc_html( $revenue );?></td>
									</tr>
									<?php
										$counter++;
									endforeach;
									?>
								</table>

							</div>

						</div>

					</div>
					<!-- /.content-box -->


				</div>
				<!-- /.email-container -->


				<?php if( ! empty( $blurb ) ): ?>
				<!-- PRO-TIP SECTION -->
				<div class="email-container pro-tip-blurb" style="max-width: 450px; margin: 0 auto; font-family: 'Source Sans Pro', sans-serif; color: #1F2937;">
					<div class="content-box pro-tip-section" style="background: #FFF;">

						<div class="content-holder pro-tip-section" style="padding: 0px 31px 27px 31px;">

							<div class="pro-tip-holder" style="background: #F3F8FE; border-radius: 10px; padding: 32px 40px;">

								<div class="pro-tip-section-title" style="font-weight: 600; font-size: 18px; line-height: 23px; color: #2794DA;">
									<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-megaphone.png' ); ?>" alt="#" title="#" width="24" height="24" style="vertical-align: bottom; display: inline-block; margin-right: 4px;">
									<?php echo esc_html( __( 'Pro-tip from our expert', 'easy-digital-downloads' ) ); ?>
								</div>

								<div class="pro-tip-title pull-down-12" style="margin-top: 12px; font-weight: 600; font-size: 20px; line-height: 26px; color: #1F2937;">
									<?php echo esc_html( $blurb['headline'] );?>
								</div>

								<p class="bigger pull-down-8" style="margin: 0px; font-weight: 400; color: #4B5563; margin-top: 8px; font-size: 16px; line-height: 22px;">
									<?php echo esc_html( $blurb['content'] );?>
								</p>

								<div class="pull-down-15" style="margin-top: 15px;">
									<a href="<?php echo esc_attr( $this->add_blurb_tracking_parameters( $blurb['button_link'], $blurb ) );?>" class="cta-btn" style="padding: 6px 15px; font-weight: 600; font-size: 14px; line-height: 20px; background: #2794DA; display: inline-block; text-decoration: none; color: white;">
										<?php echo esc_html( $blurb['button_text'] );?>
									</a>
								</div>

							</div>

						</div>

					</div>

				</div>
				<!-- /.pro-tip-blurb -->
				<?php endif; ?>

			</body>
		</html>
		<?php
		$email_template = ob_get_clean();
		return $email_template;
	}

	/**
	 * Add tracking parameters to the blurb URL.
	 *
	 * @since 3.1
	 * @param  string $url
	 * @param  array $blurb
	 *
	 * @return string URL with tracking parameters.
	 */
	public function add_blurb_tracking_parameters( $url, $blurb ) {
		// @todo - Define tracking parameters!
		return add_query_arg( array(
			'utm_source'   => 'email-summary',
			'utm_medium'   => 'blurb-' . urlencode( $blurb[ 'headline' ] ), // @todo - We have to change this to something more appropriate.
			'utm_campaign' => 'email',
			'utm_content'  => 'pro-tip'
		), $url );
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
		$email_blurbs     = new EDD_Email_Summary_Blurb();
		$next_blurb       = $email_blurbs->get_next();

		// Prepare email.
		$email_subject    = $this->get_email_subject();
		$email_recipients = $this->get_email_recipients();
		$email_body       = $this->build_email_template( $next_blurb );
		$email_headers    = array('Content-Type: text/html; charset=UTF-8');

		// If there is no email body, do not send an email.
		if ( ! $email_body ) {
			edd_debug_log( __( 'Email body for Email Summary was empty.', 'easy-digital-downloads' ), true );
			return false;
		}

		// Everything is ok, send email.
		$email_sent = wp_mail( $email_recipients, $email_subject, $email_body, $email_headers );
		if ( $email_sent ) {
			$email_blurbs->mark_blurb_sent( $next_blurb );
		}

		return $email_sent;
	}

}
