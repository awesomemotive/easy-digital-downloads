<?php

defined( 'ABSPATH' ) || exit;

/**
 * Legacy `EDD_Tracking` class was refactored and moved to the new `EDD\Telemetry\Tracking` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Tracking,
 * which we deleted.
 *
 * @since 3.2.7
 */
class_alias( \EDD\Telemetry\Tracking::class, 'EDD_Tracking' );

/**
 * Legacy `EDD_HTML_Elements` class was refactored and moved to the new `EDD\HTML\Elements` class.
 * This alias is a safeguard to those developers who use our EDD_HTML_Elements class directly
 * instead of using EDD()->html.
 *
 * @since 3.2.8
 */
class_alias( \EDD\HTML\Elements::class, 'EDD_HTML_Elements' );

/**
 * Legacy `EDD_Logging` class was refactored and moved to the new `EDD\Logging` class.
 * This alias is a safeguard to those developers who use our internal class EDD_Logging,
 * which we deleted.
 *
 * @since 3.2.10
 */
class_alias( \EDD\Logging::class, 'EDD_Logging' );

/**
 * Some legacy classes have been completely deprecated and emptied .
 * This alias is a safeguard to those developers who use our internal classes,
 * which we deleted .
 *
 * @since 3.2.10
 */
class_alias( \EDD\Deprecated\EmptyClass::class, 'EDD_DB_Customers' );
class_alias( \EDD\Deprecated\EmptyClass::class, 'EDD_DB_Customer_Meta' );
