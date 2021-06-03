<?php
/**
 * Notice
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Admin\Promos\Notices;

use EDD\Admin\Promos\PromoHandler;

abstract class Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_notices';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'top-of-page';

	/**
	 * Whether or not the notice can be dismissed.
	 */
	const DISMISSIBLE = true;

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 */
	const DISMISS_DURATION = 0;

	/**
	 * Displays the notice content.
	 *
	 * @return void
	 */
	abstract protected function _display();

	/**
	 * Generates a unique ID for this notice.
	 * It's the class name (without the namespace) and with underscores converted to hyphens.
	 *
	 * @since 2.10.5
	 *
	 * @return string
	 */
	protected function get_id() {
		return strtolower( str_replace( '_', '-', basename( str_replace( '\\', '/', get_class( $this ) ) ) ) );
	}

	/**
	 * Determines whether or not the notice should be displayed.
	 * Typically individual notices should not override this method, as it combines
	 * a dismissal check and custom display logic (`_should_display()`). Custom logic
	 * should go in `_should_display()`.
	 *
	 * @since 2.10.5
	 *
	 * @return bool
	 */
	public function should_display() {
		return ! PromoHandler::is_dismissed( $this->get_id() ) && $this->_should_display();
	}

	/**
	 * Individual notices can override this method to control display logic.
	 *
	 * @since 2.10.5
	 *
	 * @return bool
	 */
	protected function _should_display() {
		return true;
	}

	/**
	 * Displays the notice.
	 * Individual notices typically should not override this method, as it contains
	 * all the notice wrapper logic. Instead, notices should override `_display()`
	 *
	 * @since 2.10.5
	 * @return void
	 */
	public function display() {
		?>
		<div
			id="edd-admin-notice-<?php echo esc_attr( $this->get_id() ); ?>"
			class="edd-admin-notice-<?php echo esc_attr( sanitize_html_class( static::TYPE ) ); ?> edd-promo-notice"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-dismiss-notice-' . $this->get_id() ) ); ?>"
			data-id="<?php echo esc_attr( $this->get_id() ); ?>"
			data-lifespan="<?php echo esc_attr( static::DISMISS_DURATION ); ?>"
		>
			<?php $this->_display(); ?>

			<?php if ( static::DISMISSIBLE ) : ?>
				<button class="button-link edd-promo-notice-dismiss">
					&times;
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

}
