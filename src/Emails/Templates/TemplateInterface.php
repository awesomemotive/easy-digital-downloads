<?php
/**
 * TemplateInterface.php
 *
 * @package   edd
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.0
 */

namespace EDD\Emails\Templates;

interface TemplateInterface {

	/**
	 * Name of the template.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_name();

	/**
	 * Description of the email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_description();

	/**
	 * The default email properties.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function defaults(): array;
}
