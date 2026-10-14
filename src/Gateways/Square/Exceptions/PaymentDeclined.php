<?php
/**
 * Square Payment Declined Exception
 *
 * @package     EDD\Gateways\Square\Exceptions
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Gateways\Square\Exceptions;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Thrown when Square answers a payment request with an error, so no payment was taken.
 *
 * @since 3.7.2
 */
class PaymentDeclined extends \Exception {}
