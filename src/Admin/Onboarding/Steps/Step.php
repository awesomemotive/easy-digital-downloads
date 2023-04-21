<?php
/**
 * Onboarding Wizard Step absctract class.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */

namespace EDD\Admin\Onboarding\Steps;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

abstract class Step {
	use \EDD\Admin\Onboarding\Helpers;

	/**
	 * Get step view.
	 *
	 * @since 3.1.1
	 */
	abstract public function step_html();
}
