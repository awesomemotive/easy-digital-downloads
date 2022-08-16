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
	 * Email options
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
	 * Get email subject.
	 *
	 * @since 3.1
	 */
	public function get_email_subject() {
		$site_url        = get_site_url();
		$site_url_parsed = wp_parse_url( $site_url );
		$site_url        = isset( $site_url_parsed['host'] ) ? $site_url_parsed['host'] : $site_url;
		// Translators: The domain of the site is appended to the subject.
		$email_subject = sprintf( __( 'EDD Summary - %s', 'easy-digital-downloads' ), $site_url );
		return apply_filters( 'edd_email_summary_subject', $email_subject );
	}

	/**
	 * Get email recipients.
	 *
	 * @since 3.1
	 */
	public function get_email_recipients() {
		$recipients = array();

		if ( 'admin' == edd_get_option( 'email_summary_recipient' ) ) {
			$recipients[] = get_option( 'admin_email' );
		} else {
			$emails = edd_get_option( 'email_summary_custom_recipients', array() );
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

		return $recipients;
	}

	/**
	 * Get report start date.
	 *
	 * @since 3.1
 	 * @return EDD\Utils\Date The EDD date object set at the UTC equivalent time.
	 */
	public function get_report_start_date() {
		$date = EDD()->utils->date( 'now', edd_get_timezone_id(), false );

		if ( 'monthly' === $this->email_options['email_summary_frequency'] ) {
			$start_date = $date->copy()->subMonth( 1 )->startOfMonth();
		} else {
			$start_date = $date->copy()->subDay( 7 )->startOfDay();
		}

		return $start_date;
	}

	/**
	 * Get report end date.
	 *
	 * @since 3.1
	 * @return EDD\Utils\Date The EDD date object set at the UTC equivalent time.
	 */
	public function get_report_end_date() {
		$date = EDD()->utils->date( 'now', edd_get_timezone_id(), false );

		if ( 'monthly' === $this->email_options['email_summary_frequency'] ) {
			$end_date = $date->copy()->subMonth( 1 )->endOfMonth();
		} else {
			$end_date = $date->copy()->endOfDay();
		}

		return $end_date;
	}

	/**
	 * Get report date range.
	 *
	 * @since 3.1
	 * @return \EDD\Utils\Date[] Array of start and end date objects.
	 */
	public function get_report_date_range() {
		// @TODO - Check if we have to convert this to UTC because of DB?
		return array(
			'start_date' => $this->get_report_start_date(),
			'end_date' => $this->get_report_end_date(),
		);
	}

	/**
	 * Retrieve dataset for email content.
	 *
	 * @since 3.1
	 */
	public function get_report_dataset() {
		$date_range = $this->get_report_date_range();

		$stats       = new EDD\Stats();

		$earnings_gross = $stats->get_order_earnings( array(
			'start'         => $date_range['start_date']->format('Y-m-d H:i:s'),
			'end'           => $date_range['end_date']->format('Y-m-d H:i:s'),
			'function'      => 'SUM',
			'exclude_taxes' => false,
			'relative'      => false,
			'revenue_type'  => 'gross',
		) );

		$earnings_net = $stats->get_order_earnings( array(
			'start'         => $date_range['start_date']->format('Y-m-d H:i:s'),
			'end'           => $date_range['end_date']->format('Y-m-d H:i:s'),
			'function'      => 'SUM',
			'exclude_taxes' => true,
			'relative'      => false,
			'revenue_type'  => 'net',
		) );

		$average_order_value =  $stats->get_order_earnings( array(
			'start'         => $date_range['start_date']->format('Y-m-d H:i:s'),
			'end'           => $date_range['end_date']->format('Y-m-d H:i:s'),
			'function'      => 'AVG',
			'exclude_taxes' => false,
			// 'output'     => 'formatted',
			'relative'      => false,
		) );

		$new_customers =  $stats->get_customer_count( array(
			'start'         => $date_range['start_date']->format('Y-m-d H:i:s'),
			'end'           => $date_range['end_date']->format('Y-m-d H:i:s'),
			'relative' => false,
		) );

		$top_selling_products = $stats->get_most_valuable_order_items( array(
			'start'         => $date_range['start_date']->format('Y-m-d H:i:s'),
			'end'           => $date_range['end_date']->format('Y-m-d H:i:s'),
			'number'   => 5,
			'currency' => '',
		) );

		return compact(
			'earnings_gross',
			'earnings_net',
			'average_order_value',
			'new_customers',
			'top_selling_products'
		) ;
	}

	/**
	 * Build email template
	 *
	 * @since 3.1
	 */
	public function build_email_template() {
		$dataset         = $this->get_report_dataset();
		$date_range      = $this->get_report_date_range();

		$wp_date_format  = get_option('date_format');
		$site_url        = get_site_url();
		$site_url_parsed = wp_parse_url( $site_url );
		$site_url        = isset( $site_url_parsed['host'] ) ? $site_url_parsed['host'] : $site_url;

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title><?php echo esc_html( $this->get_email_subject() );?></title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			</head>

			<body style="margin: 0px;">

				<!-- HEADER HOLDER -->
				<div class="email-header-holder" style="background: #343A40;max-height: 60px;height: 60px;">

					<div class="email-container" style="max-width: 650px;margin: 0 auto;font-family: 'Source Sans Pro', sans-serif;color: #1F2937;">

						<div class="logo-holder" style="padding: 12px 3.6vw 8.5px 3.6vw;display: inline-block;">
							<img src="img/edd-logo.png" class="edd-logo" style="width: 100%;max-width: 100%;height: auto;">
						</div>

					</div>
				</div>

				<div class="email-container" style="max-width: 650px;margin: 0 auto;font-family: 'Source Sans Pro', sans-serif;color: #1F2937;">


					<!-- MAIN EMAIL CONTENT HOLDER -->

					<div class="content-box" style="background: #FFF;">

						<div class="content-holder" style="padding: 24px 3.6vw 0px 3.6vw;">

							<h1 style="margin: 0px;color: #1F2937;font-weight: 700;font-size: clamp(22px, 5vw, 24px);line-height: 24px;">Headline will go here</h1>

							<div class="period-date pull-down-8" style="margin-top: 8px;font-weight: 400;font-size: clamp(12px, 5vw, 14px);line-height: 18px;color: #4B5563;">
								<?php echo esc_html( $date_range['start_date']->format( $wp_date_format ) );?> - <?php echo esc_html( $date_range['end_date']->format( $wp_date_format ) );?>
							</div>

							<a href="<?php echo esc_attr( $site_url );?>" class="link-style pull-down-8" style="margin-top: 8px;font-weight: 400;font-size: clamp(12px, 5vw, 14px);color: #1F2937;text-decoration-line: underline;display: inline-block;">
								<?php echo esc_url( $site_url );?>
							</a>


							<div class="pull-down-20" style="margin-top: 20px;">
								<h2 style="margin: 0px;font-weight: 700;font-size: clamp(16px, 5vw, 18px);line-height: 23px;letter-spacing: -0.02em;color: #1F2937;">Hey there!</h2>
							</div>


							<p class="pull-down-5" style="margin: 0px;font-weight: 400;font-size: clamp(12px, 5vw, 14px);line-height: clamp(16px, 5vw, 18px);color: #4B5563;margin-top: 5px;">
								Below is a look at how your store performed in the past
							</p>


							<!-- DATA LISTING -->

							<div class="data-listing-holder pull-down-25" style="margin-top: 25px;display: block;">

								<div class="single-data align-c" style="text-align: center;padding: 4% 4%;width: 39.5%;position: relative;display: inline-block;border-radius: 10px;">

									<div class="data-icon">
										<img src="img/icon-growth.png">
									</div>

									<div class="data-title" style="font-weight: 600;font-size: clamp(12px, 5vw, 14px);line-height: 18px;color: #1F2937;">
										Gross Revenue
									</div>

									<div class="data-value" style="font-weight: 700;font-size: clamp(18px, 5vw, 32px);color: #111827;margin-top: 6px;">
										<?php echo $dataset['earnings_gross'];?>
									</div>

									<div class="data-statistic growth" style="font-weight: 600;font-size: clamp(8px, 5vw, 16px);color: #059669;margin-top: 5px;">
										<span>⬆️</span>
										<span>8.0%</span>
									</div>

									<div class="data-period" style="font-weight: 400;font-size: clamp(8px, 5vw, 12px);color: #6B7280;margin-top: 7px;">
										vs previous 30 days
									</div>

								</div>


								<div class="single-data align-c" style="text-align: center;padding: 4% 4%;width: 39.5%;position: relative;display: inline-block;border-radius: 10px;float: right;">

									<div class="data-icon">
										<img src="img/icon-money.png">
									</div>

									<div class="data-title" style="font-weight: 600;font-size: clamp(12px, 5vw, 14px);line-height: 18px;color: #1F2937;">
										Net Revenue
									</div>

									<div class="data-value" style="font-weight: 700;font-size: clamp(18px, 5vw, 32px);color: #111827;margin-top: 6px;">
									<?php echo $dataset['earnings_net'];?>
									</div>

									<div class="data-statistic growth" style="font-weight: 600;font-size: clamp(8px, 5vw, 16px);color: #059669;margin-top: 5px;">
										<span>⬆️</span>
										<span>8.0%</span>
									</div>

									<div class="data-period" style="font-weight: 400;font-size: clamp(8px, 5vw, 12px);color: #6B7280;margin-top: 7px;">
										vs previous 30 days
									</div>

								</div>



							</div>

							<div class="data-listing-holder pull-down-20" style="margin-top: 20px;display: block;">

								<div class="single-data align-c" style="text-align: center;padding: 4% 4%;width: 39.5%;position: relative;display: inline-block;border-radius: 10px;">

									<div class="data-icon">
										<img src="img/icon-growth.png">
									</div>

									<div class="data-title" style="font-weight: 600;font-size: clamp(12px, 5vw, 14px);line-height: 18px;color: #1F2937;">
										New Customers
									</div>

									<div class="data-value" style="font-weight: 700;font-size: clamp(18px, 5vw, 32px);color: #111827;margin-top: 6px;">
									<?php echo $dataset['new_customers'];?>
									</div>

									<div class="data-statistic growth" style="font-weight: 600;font-size: clamp(8px, 5vw, 16px);color: #059669;margin-top: 5px;">
										<span>⬆️</span>
										<span>8.0%</span>
									</div>

									<div class="data-period" style="font-weight: 400;font-size: clamp(8px, 5vw, 12px);color: #6B7280;margin-top: 7px;">
										vs previous 30 days
									</div>

								</div>


								<div class="single-data align-c" style="text-align: center;padding: 4% 4%;width: 39.5%;position: relative;display: inline-block;border-radius: 10px;float: right;">

									<div class="data-icon">
										<img src="img/icon-money.png">
									</div>

									<div class="data-title" style="font-weight: 600;font-size: clamp(12px, 5vw, 14px);line-height: 18px;color: #1F2937;">
										Average Order Value
									</div>

									<div class="data-value" style="font-weight: 700;font-size: clamp(18px, 5vw, 32px);color: #111827;margin-top: 6px;">
									<?php echo $dataset['average_order_value'];?>
									</div>

									<div class="data-statistic growth" style="font-weight: 600;font-size: clamp(8px, 5vw, 16px);color: #059669;margin-top: 5px;">
										<span>⬆️</span>
										<span>8.0%</span>
									</div>

									<div class="data-period" style="font-weight: 400;font-size: clamp(8px, 5vw, 12px);color: #6B7280;margin-top: 7px;">
										vs previous 30 days
									</div>

								</div>

							</div>


							<div class="data-listing-holder pull-down-20" style="margin-top: 20px;display: block;">

								<div class="single-data exposed-box with-pink-bg" style="padding: 4% 4%;width: 39.5%;position: relative;display: inline-block;border-radius: 10px;min-height: 193px;background: #FDF2F8;">

									<div class="data-icon">
										<img src="img/icon-new-customer.png">
									</div>

									<div class="data-title" style="font-weight: 600;font-size: clamp(12px, 5vw, 24px);line-height: clamp(15px, 5vw, 30px);color: #1F2937;margin-top: 4px;">
										New Customers
									</div>

									<div class="data-value" style="font-weight: 700;font-size: clamp(18px, 5vw, 40px);color: #111827;margin-top: 6px;">
										345
									</div>

									<div class="data-statistic growth" style="font-weight: 600;font-size: clamp(8px, 5vw, 16px);color: #059669;margin-top: 5px;">
										<span>⬆️</span>
										<span>8.0%</span>
									</div>

									<div class="data-period" style="font-weight: 400;font-size: clamp(8px, 5vw, 12px);color: #6B7280;margin-top: 7px;">
										vs previous 30 days
									</div>

								</div>


								<div class="single-data exposed-box with-orange-bg" style="padding: 4% 4%;width: 39.5%;position: relative;display: inline-block;border-radius: 10px;min-height: 193px;background: #FFF5EB;float: right;">

									<div class="data-icon">
										<img src="img/icon-average-order.png">
									</div>

									<div class="data-title" style="font-weight: 600;font-size: clamp(12px, 5vw, 24px);line-height: clamp(15px, 5vw, 30px);color: #1F2937;margin-top: 4px;">
										Average Order <br> Value
									</div>

									<div class="data-value" style="font-weight: 700;font-size: clamp(18px, 5vw, 40px);color: #111827;margin-top: 6px;">
										$ 1000
									</div>

								</div>



							</div>



							<!-- TABLE DATA -->
							<div class="table-data-holder pull-down-30 push-down-40" style="margin-top: 30px;margin-bottom: 40px;border: 2px solid #FBBF24;border-radius: 10px;padding: 4% 4%;">
								<div class="table-top-icon align-c" style="text-align: center;">
									<img src="img/icon-gift-box.png" alt="#" title="#">
								</div>

								<div class="table-top-title align-c" style="text-align: center;font-size: clamp(12px, 5vw, 14px);font-weight: 600;color: #1F2937;display: block;margin-top: 12px;margin-bottom: 12px;">
									Top 5 Products by Revenue
								</div>

								<table style="border-collapse: collapse;width: 100%;">
								<tr>
									<th style="color: #4B5563;font-weight: 600;border-bottom: 1px solid #D1D5DB;text-align: left;border-right: none;padding: 7px 0px;font-size: clamp(10px, 5vw, 20px);">Product</th>
									<th style="color: #4B5563;font-weight: 600;border-bottom: 1px solid #D1D5DB;text-align: right;border-right: none;padding: 7px 0px;font-size: clamp(10px, 5vw, 20px);">Gros Revenue</th>
								</tr>
								<tr>
								<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: left;padding: 7px 0px;">1. Product 1</td>
								<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: right;padding: 7px 0px;">XX</td>
								</tr>
								<tr>
									<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: left;padding: 7px 0px;">2. Product 2</td>
									<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: right;padding: 7px 0px;">XX</td>
								</tr>
								<tr>
								<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: left;padding: 7px 0px;">3. Product 3</td>
								<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: right;padding: 7px 0px;">XX</td>
								</tr>
								<tr>
									<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: left;padding: 7px 0px;">4. Product 4</td>
									<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: right;padding: 7px 0px;">XX</td>
								</tr>
								<tr>
									<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: left;padding: 7px 0px;">5. Product 5</td>
									<td style="font-size: clamp(10px, 5vw, 16px);color: #4B5563;font-weight: 400;border-bottom: 1px solid #E5E7EB;text-align: right;padding: 7px 0px;">XX</td>
								</tr>
								</table>


								<a href="#" class="link-style bigger pull-down-8" style="margin-top: 8px;font-weight: 400;font-size: clamp(10px, 5vw, 16px);color: #1F2937;text-decoration-line: underline;display: inline-block;">
									View more
								</a>

							</div>

						</div>

					</div>
					<!-- /.content-box -->


				</div>
				<!-- /.email-container -->


				<!-- PRO-TIP SECTION -->
				<div class="pro-tip-section-bg" style="background: #343A40;">
					<div class="email-container" style="max-width: 650px;margin: 0 auto;font-family: 'Source Sans Pro', sans-serif;color: #1F2937;">

						<div class="content-box pro-tip-section" style="background: #FFF;background-color: #343A40;">

							<div class="content-holder pro-tip-section" style="padding: 44px 3.6vw 44px 3.6vw;">

								<div class="pro-tip-holder" style="background: #FFF;border: 2px solid #2794DA;border-radius: 10px;padding: 5% 4%;">

									<div class="pro-tip-section-title" style="font-weight: 600;font-size: clamp(10px, 5vw, 18px);line-height: clamp(12px, 5vw, 23px);color: #2794DA;">
										📢 Pro-tip from our expert
									</div>

									<div class="pro-tip-title pull-down-20" style="margin-top: 20px;font-weight: 600;font-size: clamp(12px, 5vw, 20px);line-height: clamp(16px, 5vw, 26px);color: #1F2937;">
										Did you know that adding your customers to your email marketing can boost revenue?
									</div>

									<p class="bigger pull-down-15" style="margin: 0px;font-weight: 400;font-size: clamp(12px, 5vw, 16px);line-height: clamp(16px, 5vw, 22px);color: #4B5563;margin-top: 15px;">
										Inform about personal pass. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tristique ut massa eget euismod nisl in. Rhoncus, aliquet mattis proin nisl laoreet elementum, et pretium.
									</p>

									<div class="pull-down-20" style="margin-top: 20px;">
										<a href="#" class="cta-btn" style="padding: 8px 26px;font-size: clamp(14px, 5vw, 16px);line-height: 20px;background: #1C3D94;display: inline-block;color: #FFFFFF;text-decoration: none;">
											Know More
										</a>
									</div>

								</div>

							</div>

						</div>

					</div>
				</div>
				<!-- /.email-container -->
			</body>
		</html>
		<?php
		$email_template = ob_get_clean();
		return $email_template;
	}

	/**
	 * Prepare and send email
	 *
	 * @since 3.1
	 */
	public function send_email() {
		// Prepare email.
		$email_subject    = $this->get_email_subject();
		$email_recipients = $this->get_email_recipients();
		$email_body       = $this->build_email_template();
		$email_headers    = array('Content-Type: text/html; charset=UTF-8');

		// From Name: Store Name.
		// Subject: Easy Digital Downloads Summary - <domain name>
		// Preview Text: Store performance summary <start date> - <end date>

		// Send email.
		wp_mail( $email_recipients, $email_subject, $email_body, $email_headers );
	}

}
