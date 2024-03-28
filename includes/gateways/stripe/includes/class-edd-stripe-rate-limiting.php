<?php
/**
 * EDD Stripe: Rate Limiting
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\FileSystem;

/**
 * Class EDD_Stripe_Rate_Limiting
 */
class EDD_Stripe_Rate_Limiting {

	/**
	 * Is the file writable.
	 *
	 * @var bool
	 */
	public $is_writable = true;

	/**
	 * The rate limiting log file.
	 *
	 * @var string
	 */
	private $filename = '';

	/**
	 * The file path to the log file.
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * Set up the EDD Logging Class
	 *
	 * @since 2.6.19
	 */
	public function __construct() {
		$this->actions();
		$this->filters();
	}

	/**
	 * Register any actions we need to use.
	 *
	 * @since 2.6.19
	 */
	private function actions() {

		// Setup the log file.
		add_action( 'plugins_loaded', array( $this, 'setup_log_file' ), 11 );

		// Maybe schedule the cron to clean up the log file.
		add_action( 'init', array( $this, 'schedule_cleanup' ) );

		// Hook into the scheduled cleanup.
		add_action( 'edds_cleanup_rate_limiting_log', array( $this, 'cleanup_log' ) );

		// Catch any recurring errors as they don't run through the main Stripe extension.
		add_action( 'edd_before_purchase_form', array( $this, 'listen_for_recurring_card_errors' ), 0 );
	}

	/**
	 * Register any filters we need to use.
	 *
	 * @since 2.6.19
	 */
	private function filters() {

		// Hide the purchase button if the visitor has hit the limit of errors.
		add_filter( 'edd_checkout_button_purchase', array( $this, 'maybe_hide_purchase_button' ) );
	}

	/**
	 * Schedule a cleanup of the card testing log entries.
	 *
	 * Runs every hour, and clears any card testing logs that are past expiration.
	 *
	 * @since 2.8.13
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'edds_cleanup_rate_limiting_log' ) ) {
			wp_schedule_event( time(), 'hourly', 'edds_cleanup_rate_limiting_log' );
		}
	}

	/**
	 * Process the card testing logs.
	 *
	 * Loops over the card testing logs, and if an entry is past it's expiration, remove it from the list.
	 *
	 * @since 2.8.13
	 */
	public function cleanup_log() {
		$current_logs = $this->get_decoded_file();
		if ( empty( $current_logs ) ) {
			return;
		}

		foreach ( $current_logs as $blocking_id => $entry ) {
			$expiration = ! empty( $entry['timeout'] ) ? $entry['timeout'] : 0;

			if ( $expiration < current_time( 'timestamp' ) ) { // @codingStandardsIgnoreLine
				unset( $current_logs[ $blocking_id ] );
			}
		}

		$this->write_to_log( $current_logs );
	}

	/**
	 * Sets up the log file if it is writable
	 *
	 * @since 2.6.19
	 * @return void
	 */
	public function setup_log_file() {
		$upload_dir     = edd_get_upload_dir();
		$this->filename = wp_hash( home_url( '/' ) ) . '-edd-stripe-rate-limiting.log';
		$this->file     = trailingslashit( $upload_dir ) . $this->filename;
		FileSystem::maybe_move_file( $this->filename, $this->file );

		if ( ! FileSystem::get_fs()->is_writable( $upload_dir ) ) {
			$this->is_writable = false;
		}
	}

	/**
	 * Checks if the current session has hit the card error limit.
	 *
	 * @since 2.6.19
	 *
	 * @return bool
	 */
	public function has_hit_card_error_limit() {
		if ( ! $this->card_error_checks_enabled() ) {
			return false;
		}

		$blocking_id = $this->get_card_error_id();
		$entry       = $this->get_rate_limiting_entry( $blocking_id );
		$expiration  = ! empty( $entry['timeout'] ) ? $entry['timeout'] : 0;
		$card_errors = ! empty( $entry['count'] ) ? $entry['count'] : 0;

		if ( $expiration < current_time( 'timestamp' ) ) { // @codingStandardsIgnoreLine
			$this->remove_log_entry( $this->get_card_error_id() );
			return false;
		}

		$max_error_count = 5;

		/**
		 * Filters the number of times checkout errors can occur before blocking future attempts.
		 *
		 * @since 2.6.19
		 *
		 * @param bool $max_error_count The maximum failed checkouts before blocking future attempts. Default 5.
		 */
		$max_error_count = apply_filters( 'edds_max_card_error_count', $max_error_count );

		return $max_error_count <= $card_errors;
	}

	/**
	 * Remove an entry from the rate limiting log.
	 *
	 * @since 2.6.19
	 *
	 * @param string $blocking_id The blocking ID for the rate limiting.
	 */
	public function remove_log_entry( $blocking_id = '' ) {
		$current_logs = $this->get_decoded_file();
		unset( $current_logs[ $blocking_id ] );

		$this->write_to_log( $current_logs );
	}

	/**
	 * Get a specific entry from the rate limiting log.
	 *
	 * @since 2.6.19
	 *
	 * @param string $blocking_id The blocking ID to get the entry for.
	 *
	 * @return array
	 */
	public function get_rate_limiting_entry( $blocking_id = '' ) {
		$current_logs = $this->get_decoded_file();
		$entry        = array();

		if ( array_key_exists( $blocking_id, $current_logs ) ) {
			$entry = $current_logs[ $blocking_id ];
		}

		return $entry;
	}


	/**
	 * Retrieves the number of times an IP address has generated card errors.
	 *
	 * @since 2.6.19
	 *
	 * @return int
	 */
	public function get_card_error_count() {
		$blocking_id = $this->get_card_error_id();
		$count       = 0;

		$current_blocks = $this->get_decoded_file();
		if ( array_key_exists( $blocking_id, $current_blocks ) ) {
			$count = $current_blocks[ $blocking_id ]['count'];
		}

		return $count;
	}

	/**
	 * Increments the Stripe card error counter.
	 *
	 * @since 2.6.19
	 *
	 * @return int
	 */
	public function increment_card_error_count() {
		$current_count = $this->get_card_error_count();
		$blocking_id   = $this->get_card_error_id();

		if ( empty( $current_count ) ) {
			$current_count = 1;
		} else {
			++$current_count;
		}

		$this->update_rate_limiting_count( $blocking_id, $current_count );

		return absint( $current_count );
	}

	/**
	 * Update an entry in the rate limiting array.
	 *
	 * @since 2.6.19
	 *
	 * @param string $blocking_id   The blocking ID.
	 * @param int    $current_count The count to update to.
	 */
	protected function update_rate_limiting_count( $blocking_id = '', $current_count = 0 ) {

		$expiration_in_seconds = HOUR_IN_SECONDS;

		/**
		 * Filters the length of time before checkout card error counts are reset.
		 *
		 * @since 2.6.19
		 *
		 * @param int $expiration_in_seconds The length in seconds before card error counts are reset. Default 60.
		 */
		$expiration_in_seconds = apply_filters( 'edds_card_error_timeout', $expiration_in_seconds );

		$current_log = $this->get_decoded_file();

		$current_log[ $blocking_id ]['count']   = $current_count;
		$current_log[ $blocking_id ]['timeout'] = current_time( 'timestamp' ) + $expiration_in_seconds; // @codingStandardsIgnoreLine

		$this->write_to_log( $current_log );
	}

	/**
	 * Determines if we should check for Stripe card errors and track them.
	 *
	 * @since 2.6.19
	 *
	 * @return bool
	 */
	public function card_error_checks_enabled() {
		$checks_enabled = true;

		/**
		 * Filters if card errors should be checked and tracked during checkout.
		 *
		 * @since 2.6.19
		 *
		 * @param bool $checks_enabled Enables or disables card error checking on checkout. Default true.
		 */
		$checks_enabled = apply_filters( 'edds_card_error_checking_enabled', true );

		return true === $checks_enabled;
	}

	/**
	 * Generates the card error tracking ID.
	 *
	 * ID is the IP address of the visitor. Prepends the value with `edds_card_errors_` for use with the transient system.
	 * Uses IP tracking in an attempt to mitigate the amount of bogus WordPress user accounts being created.
	 *
	 * @since 2.6.19
	 * @since 2.8.13 Try and use the __stripe_sid cookie before relying on IP.
	 *
	 * @return string
	 */
	public function get_card_error_id() {
		return isset( $_COOKIE['__stripe_sid'] ) ? $_COOKIE['__stripe_sid'] : edd_get_ip();
	}

	/**
	 * Determines if we should hide the purchase button.
	 *
	 * When someone has hit the card error limit, the purchase button is hidden.
	 *
	 * @since 2.6.19
	 *
	 * @param string $purchase_button_markup The markup for the purchase button.
	 *
	 * @return string
	 */
	public function maybe_hide_purchase_button( $purchase_button_markup = '' ) {
		if ( $this->has_hit_card_error_limit() ) {
			$purchase_button_markup = '';
		}

		return $purchase_button_markup;
	}

	/**
	 * When the purchase form errors are displayed, see if any were related to Stripe failures and increase the card error
	 * counter.
	 *
	 * @since 2.6.19
	 */
	public function listen_for_recurring_card_errors() {

		// Get all of our EDD errors.
		$errors = edd_get_errors();

		// If any of our errors are Stripe card errors from recurring, increment the card error counter.
		if ( isset( $errors['edd_recurring_stripe_error'] ) && ! empty( $errors['edd_recurring_stripe_error'] ) ) {
			$this->increment_card_error_count();
		}
	}

	/**
	 * Retrieve the log data
	 *
	 * @since 2.6.19
	 * @return string
	 */
	protected function get_file_contents() {
		return $this->get_file();
	}

	/**
	 * Get the decoded array of rate limiting from the log file.
	 *
	 * @since 2.6.19
	 *
	 * @return array
	 */
	protected function get_decoded_file() {
		$decoded_contents = json_decode( $this->get_file_contents(), true );
		if ( is_null( $decoded_contents ) ) {
			$decoded_contents = array();
		}

		return (array) $decoded_contents;
	}

	/**
	 * Retrieve the file data is written to
	 *
	 * @since 2.6.19
	 * @return string
	 */
	protected function get_file() {

		$file = json_encode( array() );

		if ( FileSystem::get_fs()->exists( $this->file ) ) {

			if ( ! FileSystem::get_fs()->is_writable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = FileSystem::get_fs()->get_contents( $this->file );
		} else {
			FileSystem::get_fs()->put_contents( $this->file, $file );
			FileSystem::get_fs()->chmod( $this->file, 0664 );
		}

		return $file;
	}

	/**
	 * Write the log message
	 *
	 * @since 2.6.19
	 *
	 * @param array $content The content of the rate limiting.
	 *
	 * @return void
	 */
	public function write_to_log( $content = array() ) {
		if ( count( $content ) > 200 ) {
			// Reduce the max number of identifiers to 200.
			$content = array_slice( $content, -200 );
		}

		$content = json_encode( $content );

		if ( $this->is_writable ) {
			FileSystem::get_fs()->put_contents( $this->file, $content );
		}
	}

	/**
	 * Get the error message to display when the card error limit has been hit.
	 *
	 * @since 2.9.2.2
	 * @return string The error message.
	 */
	public function get_rate_limit_error_message() {
		return esc_html__(
			'We are unable to process your payment at this time, please try again later or contact support.',
			'easy-digital-downloads'
		);
	}
}
