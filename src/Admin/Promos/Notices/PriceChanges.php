<?php
/**
 * Show a notice in the download editor when removing an active price, or changing
 * from a variable price to a single price (or vice versa).
 *
 * @since 3.3.6
 * @package EDD\Admin\Promos\Notices
 * @subpackage PriceChanges
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class PriceChanges
 *
 * @since 3.3.6
 * @package EDD\Admin\Promos\Notices
 */
class PriceChanges extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-post.php';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Gets the notice content for AJAX requests.
	 *
	 * @return string
	 */
	public function get_ajax_content() {
		if ( ! $this->_should_display() ) {
			return '';
		}

		ob_start();
		?>
		<p>
			<?php esc_html_e( 'Deleting a price option with existing sales can cause issues with your store reporting and customer experience. Are you sure you want to delete this price ID?', 'easy-digital-downloads' ); ?>
		</p>
		<?php
		$price_id       = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_NUMBER_INT );
		$button_classes = array(
			'edd-section-content__remove',
			'button',
			'button-secondary',
			'edd-delete',
			'edd-promo-notice-dismiss',
		);
		?>
		<div class="edd-promo-notice__actions">
			<button type="button" class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" data-id="<?php echo esc_attr( $price_id ); ?>">
				<?php esc_html_e( 'Yes, delete it', 'easy-digital-downloads' ); ?>
			</button>
			<button class="button button-secondary edd-promo-notice-dismiss">
				<?php esc_html_e( 'No, keep it', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @inheritDoc
	 * @since 3.3.6
	 * @return bool
	 */
	protected function _should_display() {
		$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $product_id && ! edd_doing_ajax() ) {
			$product_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		}
		if ( ! $product_id || 'download' !== get_post_type( $product_id ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_product', $product_id ) ) {
			return false;
		}

		return current_user_can( 'edit_products' );
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the AJAX content.
	 *
	 * @since 3.3.6
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Displays the notice content.
	 * This is intentionally left blank as the content is rendered in the AJAX content.
	 *
	 * @return void
	 */
	protected function _display() {}
}
