<?php
/**
 * Deprecated classes.
 *
 * This holds any classes that we've deprecated. Some will be aliased to their new classes, others will be removed entirely
 * via a 'stub' class.
 *
 * @package EDD
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Aliased Classes
 *
 * These classes are aliased to their new classes. This is to ensure that any extensions or custom code that uses these classes don't
 * result in fatal errors.
 */

/**
 * Legacy `EDD_Emails` class was refactored and moved to the new `EDD\Emails\Base` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Emails,
 * which we deleted.
 *
 * @deprecated 3.2.0
 */
class_alias( \EDD\Emails\Base::class, 'EDD_Emails' );

/**
 * Legacy `EDD_Tracking` class was refactored and moved to the new `EDD\Telemetry\Tracking` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Tracking,
 * which we deleted.
 *
 * @deprecated 3.2.7
 */
class_alias( \EDD\Telemetry\Tracking::class, 'EDD_Tracking' );

/**
 * Legacy `EDD_HTML_Elements` class was refactored and moved to the new `EDD\HTML\Elements` class.
 * This alias is a safeguard to those developers who use our EDD_HTML_Elements class directly
 * instead of using EDD()->html.
 *
 * @deprecated 3.2.8
 */
class_alias( \EDD\HTML\Elements::class, 'EDD_HTML_Elements' );

/**
 * Legacy `EDD_Logging` class was refactored and moved to the new `EDD\Logging` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Logging,
 * which we deleted.
 *
 * @deprecated 3.2.10
 */
class_alias( \EDD\Logging::class, 'EDD_Logging' );

/**
 * Some legacy classes have been completely deprecated and emptied .
 * This alias is a safeguard to those developers who use our internal classes,
 * which we deleted .
 *
 * @deprecated 3.2.10
 */
class_alias( \EDD\Deprecated\EmptyClass::class, 'EDD_DB_Customers' );
class_alias( \EDD\Deprecated\EmptyClass::class, 'EDD_DB_Customer_Meta' );

/**
 * Legacy 'EDD_Email_Summaries' class that was moved to `EDD\Cron\Events\EmailSummaries` class.
 * This alias is a safeguard to those developers and extensions that use our internal class EDD_Email_Summaries.
 *
 * @deprecated 3.3.0
 */
class_alias( \EDD\Cron\Components\EmailSummaries::class, 'EDD_Email_Summaries' );

/**
 * Legacy EDD\Admin\PassHandler\Cron class that was moved to `EDD\Cron\Components\Passes` class.
 *
 * @deprecated 3.3.0
 */
class_alias( \EDD\Cron\Components\Passes::class, 'EDD\Admin\PassHandler\Cron' );

/**
 * Legacy `EDD_Email_Template_Tags` class was refactored and moved to the new `EDD\Emails\Tags\Handler` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Email_Template_Tags,
 * which we deleted.
 *
 * @since 3.3.0
 */
class_alias( \EDD\Emails\Tags\Handler::class, 'EDD_Email_Template_Tags' );

/**
 * Legacy `EDD_Session` class was refactored and moved to the new `EDD\Sessions\Handler` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Session,
 * which we deleted.
 *
 * @deprecated 3.3.0
 */
class_alias( \EDD\Sessions\Handler::class, 'EDD_Session' );

/**
 * Class alias for the `EDD\Emails\Templates\Previews\Data` class.
 */
class_alias( \EDD\Emails\Templates\Previews\Data::class, '\\EDD\\Emails\\Templates\\PreviewData' );

/**
 * Class Alias for the `EDD\Orders\Refund_Validator` class.
 *
 * @deprecated 3.3.0
 */
class_alias( \EDD\Orders\Refunds\Validator::class, 'EDD\Orders\Refund_Validator' );

/**
 * Class Alias for the `EDD\RequirementsCheck` class.
 *
 * @deprecated 3.3.3
 */
class_alias( \EDD\RequirementsCheck::class, 'EDD_Requirements_Check' );

/**
 * Fully Deprecated Classes
 *
 * These classes are fully deprecated and are no longer used internally. There are no aliases for them, and their original class
 * files have been removed. This is a safeguard to those developers who use our internal classes directly.
 *
 * When fully deprecating a class, you must wrap it in a `class_exists` check to ensure that we do not throw a fatal error if the
 * original class file was not deleted by the update/installation process. While this is an edge case it is possible for it to happen.
 *
 * Your stubbed version of the class should contain a constructor that throws a deprecated notice,
 * and any methods that are publicly available.
 */

if ( ! class_exists( 'EDD_Cron' ) ) :
	/**
	 * Legacy 'EDD_Cron' class that is deprecated in favor of new Cron Loading system.
	 *
	 * Since the EDD_Cron class was used internally only with no real function outside of our own events, replacing it entirely should
	 * not pose an issue.
	 *
	 * @deprecated 3.3.0 This class was converted to the new namespaced Cron Loader.
	 */
	class EDD_Cron {
		public function __construct() { // phpcs:ignore.
			_edd_deprecated_class(
				__CLASS__,
				'3.3.0',
				'Cron management has been refactored and is located in src/Cron/Loader.php'
			);
		}
		public function add_schedules( $schedules = array() ) { return $schedules; } // phpcs:ignore.
		public function schedule_events() {} // phpcs:ignore.
	}
endif;
