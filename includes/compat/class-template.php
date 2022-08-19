<?php
/**
 * Backwards Compatibility Handler for Templates.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Template Class.
 *
 * EDD 3.0 stores data in custom tables making get_post() backwards incompatible. This class handles template changes
 * required for template to carry on working as expected.
 *
 * @since 3.0
 */
class Template extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'template';

	/**
	 * Backwards compatibility hooks for payments.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {

		/* Actions ***********************************************************/

		add_action( 'admin_init', array( $this, 'update_receipt_template' ) );
	}

	/**
	 * Update the receipt template to use `edd_get_payment()` instead of `get_post()`.
	 *
	 * @since 3.0
	 */
	public function update_receipt_template() {
		$access_type = get_filesystem_method();

		$last_checked = get_transient( 'edd-sc-receipt-check' );
		if ( false !== $last_checked ) {
			return false;
		}

		// Only run this once a day.
		set_transient( 'edd-sc-receipt-check', DAY_IN_SECONDS );

		// Retrieve the path to the template being used.
		$template = edd_locate_template( 'shortcode-receipt.php' );

		// Bail if the template has not been overridden.
		if ( false === strpos( $template, 'edd_templates' ) ) {
			return false;
		}

		if ( 'direct' === $access_type ) {

			// Request credentials from the user, if necessary.
			$credentials = request_filesystem_credentials( admin_url(), '', false, false, array() );

			// Authenticate & instantiate the WordPress Filesystem classes.
			if ( ! WP_Filesystem( $credentials ) ) {

				// Request credentials again in case they were wrong the first time.
				request_filesystem_credentials( admin_url(), '', true, false, array() );

				return false;
			}

			global $wp_filesystem;

			/** @var \WP_Filesystem_Base $wp_filesystem */

			if ( $wp_filesystem->exists( $template ) && $wp_filesystem->is_writable( $template ) ) {
				$contents = $wp_filesystem->get_contents( $template );

				$get_post_call_exists = strstr( $contents, 'get_post( $edd_receipt_args[\'id\'] )' );

				if ( false === $get_post_call_exists ) {
					return;
				}

				$contents = str_replace( 'get_post( $edd_receipt_args[\'id\'] )', 'edd_get_payment( $edd_receipt_args[\'id\'] )', $contents );
				$updated  = $wp_filesystem->put_contents( $template, $contents );

				// Only display a notice if we could not update the file.
				if ( ! $updated ) {
					add_action( 'admin_notices', function() use ( $template ) {
						?>
						<div class="notice notice-error">
							<p><?php esc_html_e( 'Easy Digital Downloads failed to automatically update your purchase receipt template. This update is necessary for the purchase receipt to display correctly.', 'easy-digital-downloads' ); ?></p>
							<p><?php printf( __( 'This update must be completed manually. Please click %shere%s for more information.', 'easy-digital-downloads' ), '<a href="https://easydigitaldownloads.com/development/2018/06/21/breaking-changes-to-orders-in-easy-digital-downloads-3-0/">', '</a>' ); ?></p>
							<p><?php esc_html_e( 'The file that needs to be updated is located at:', 'easy-digital-downloads' ); ?> <code><?php echo esc_html( $template ); ?></code></p>
						</div>
						<?php
					} );
				}
			}
		}
	}
}