<?php
/**
 * Integration Interface
 *
 * @package     EDD\Integrations
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Integrations;

/**
 * Interface that all integrations must implement.
 *
 * @since 3.6.0
 */
interface Integration {
	/**
	 * Check if this integration can be loaded.
	 *
	 * @since 3.6.0
	 * @return bool
	 */
	public function can_load(): bool;

	/**
	 * Subscribe to WordPress hooks and actions.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function subscribe();
}
