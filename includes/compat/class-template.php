<?php
/**
 * Backwards Compatibility Handler for Templates.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
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

		add_action( 'edd_loaded', array( $this, 'update_receipt_template' ) );
	}

	/**
	 * Update the receipt template to use `edd_get_payment()` instead of `get_post()`.
	 *
	 * @since 3.0
	 */
	public function update_receipt_template() {
		$template = edd_locate_template( 'shortcode-receipt.php' );

		// Bail if the template being used is in EDD.
		if ( false === strpos( $template, 'edd_templates' ) ) {
			return;
		}

		$contents = file_get_contents( $template );

		$contents = str_replace( 'get_post( $edd_receipt_args[\'id\'] )', 'edd_get_payment( $edd_receipt_args[\'id\'] )', $contents );

		file_put_contents( $template, $contents );
	}
}