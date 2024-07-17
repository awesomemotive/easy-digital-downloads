<?php

namespace EDD\Admin\Extensions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Emails
 *
 * @since 3.3.0
 * @package EDD\Admin\Extensions
 */
class Emails extends ExtensionPage {
	use Traits\Emails;
}
