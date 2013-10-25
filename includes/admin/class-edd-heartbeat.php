<?php
/**
 * Admin / Heartbeat
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/


/**
 * EDD_Heartbeart Class
 *
 * Hooks into the WP heartbeat API to update various parts of the dashboard as new sales are made
 *
 * Dashboard components that are effect:
 *	- Dashboard Summary Widget
 *
 * @since 1.8
 */
class EDD_Heartbeat {

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public static function init() {

		add_filter( 'heartbeat_received', array( 'EDD_Heartbeat', 'heartbeat_received' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( 'EDD_Heartbeat', 'enqueue_scripts' ) );
	}

	/**
	 * Tie into the heartbeat and append our stats
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public static function heartbeat_received( $response, $data ) {

		  // Make sure we only run our query if the edd_heartbeat key is present
		 if( ( isset( $data['edd_heartbeat'] ) ) && ( $data['edd_heartbeat'] == 'dashboard_summary' ) ) {

			// Instantiate the stats class
			$stats = new EDD_Payment_Stats;

			$earnings = edd_get_total_earnings();

			// Send back the number of complete payments
			$response['edd-total-payments'] = number_format_i18n( edd_get_total_sales() );
			$response['edd-total-earnings'] = html_entity_decode( edd_currency_filter( $earnings ), ENT_COMPAT, 'UTF-8' );
			$response['edd-payments-month'] = number_format_i18n( $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ) );
			$response['edd-earnings-month'] = html_entity_decode( edd_currency_filter( $stats->get_earnings( 0, 'this_month' ) ), ENT_COMPAT, 'UTF-8' );

		}

		return $response;

	}

	/**
	 * Load the heartbeat scripts
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public static function enqueue_scripts() {
		// Make sure the JS part of the Heartbeat API is loaded.
		wp_enqueue_script( 'heartbeat' );
		add_action( 'admin_print_footer_scripts', array( 'EDD_Heartbeat', 'footer_js' ), 20 );
	}

	/**
	 * Inject our JS into the admin footer
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public static function footer_js() {
		global $pagenow;

		// Only proceed if on the dashboard
		if( 'index.php' != $pagenow )
			return;
		?>
		<script>
			(function($){

			// Hook into the heartbeat-send
			$(document).on('heartbeat-send', function(e, data) {
				data['edd_heartbeat'] = 'dashboard_summary';
			});

			// Listen for the custom event "heartbeat-tick" on $(document).
			$(document).on( 'heartbeat-tick', function(e, data) {

				// Only proceed if our EDD data is present
				if ( ! data['edd-total-payments'] )
					return;

				// Update sale count and bold it to provide a highlight
				$('.edd_dashboard_widget .b.b-earnings').text( data['edd-total-earnings'] ).css( 'font-weight', 'bold' );
				$('.edd_dashboard_widget .b.b-sales').text( data['edd-total-payments'] ).css( 'font-weight', 'bold' );
				$('.edd_dashboard_widget .table_current_month .b.b-earnings').text( data['edd-earnings-month'] ).css( 'font-weight', 'bold' );
				$('.edd_dashboard_widget .table_current_month .b.b-sales').text( data['edd-payments-month'] ).css( 'font-weight', 'bold' );

				// Return font-weight to normal after 2 seconds
				setTimeout(function(){
					$('.edd_dashboard_widget .b.b-sales,.edd_dashboard_widget .b.b-earnings').css( 'font-weight', 'normal' );
					$('.edd_dashboard_widget .table_current_month .b.b-earnings,.edd_dashboard_widget .table_current_month .b.b-sales').css( 'font-weight', 'normal' );
				}, 2000);

			});
			}(jQuery));
		</script>
		<?php
	}
}
add_action( 'plugins_loaded', array( 'EDD_Heartbeat', 'init' ) );
