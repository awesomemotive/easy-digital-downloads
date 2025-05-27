<?php
/**
 * Handles the Discounts admin screen.
 *
 * @package     EDD\Admin\Discounts
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Admin\Discounts;

/**
 * Handles the Discounts admin screen.
 *
 * @since 3.3.9
 */
class Screen {

	/**
	 * Renders the Discounts admin screen.
	 *
	 * @since 3.3.9
	 */
	public static function render() {
		// Enqueue scripts.
		wp_enqueue_script( 'edd-admin-discounts' );

		$view = self::get_view();
		if ( in_array( $view, array( 'edit_discount', 'add_discount' ), true ) ) {
			self::render_editor( $view );
			return;
		}

		edd_adjustments_page();
	}

	/**
	 * Renders the list table.
	 *
	 * @since 3.3.9
	 */
	public static function render_list_table() {
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			wp_die( __( 'You do not have permission to manage discounts.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$discount_codes_table = new ListTable();
		$discount_codes_table->prepare_items();

		do_action( 'edd_discounts_page_top' ); ?>

		<form id="edd-discounts-filter" method="get" action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-discounts' ) ) ); ?>">
			<?php $discount_codes_table->search_box( __( 'Search Discounts', 'easy-digital-downloads' ), 'edd-discounts' ); ?>

			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-discounts" />

			<?php
			$discount_codes_table->views();
			$discount_codes_table->display();
			?>
		</form>

		<?php
		do_action( 'edd_discounts_page_bottom' );
	}

	/**
	 * Renders the editor.
	 *
	 * @since 3.3.9
	 */
	private static function render_editor( $action ) {
		$discount = null;

		if ( 'edit_discount' === $action ) {
			if ( ! current_user_can( 'edit_shop_discounts' ) ) {
				wp_die( __( 'You do not have permission to edit discounts.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
			}

			$discount_id = filter_input( INPUT_GET, 'discount', FILTER_SANITIZE_NUMBER_INT );
			if ( ! $discount_id ) {
				wp_die( __( 'Something went wrong.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
			}

			$discount = edd_get_discount( $discount_id );
			if ( empty( $discount ) ) {
				wp_die( __( 'Something went wrong.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
			}

			wp_enqueue_script( 'edd-admin-notes' );
		}

		// Add.
		if ( 'add_discount' === $action && ! current_user_can( 'manage_shop_discounts' ) ) {
			wp_die( __( 'You do not have permission to manage discounts.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$editor = new Editor\Form( $discount );
		$editor->render();
	}

	/**
	 * Gets the view from the request.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	private static function get_view() {
		$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! empty( $view ) ) {
			return $view;
		}

		return filter_input( INPUT_GET, 'edd-action', FILTER_SANITIZE_SPECIAL_CHARS );
	}
}
