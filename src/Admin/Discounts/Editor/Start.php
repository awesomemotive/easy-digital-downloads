<?php
/**
 * Discount editor start field.
 *
 * @package     EDD\Admin\Discounts\Editor
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Admin\Discounts\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Forms\Field;

/**
 * Discount editor start field.
 *
 * @since 3.3.9
 */
class Start extends Field {

	/**
	 * Get the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-start';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Start date', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Pick the date and time this discount will start on. Leave blank for no start date.', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$date   = '';
		$hour   = '';
		$minute = '';
		if ( ! empty( $this->data->start_date ) ) {
			$discount_start_date = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $this->data->start_date, 'utc' ) );
			$date                = $discount_start_date->format( 'Y-m-d' );
			$hour                = $discount_start_date->format( 'H' );
			$minute              = $discount_start_date->format( 'i' );
		}

		?>
		<input name="start_date" id="edd-start" type="text" value="<?php echo esc_attr( $date ); ?>" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />

		<label class="screen-reader-text" for="start-date-hour">
			<?php esc_html_e( 'Start Date Hour', 'easy-digital-downloads' ); ?>
		</label>
		<input type="number" min="0" max="24" step="1" name="start_date_hour" id="start-date-hour" value="<?php echo esc_attr( $hour ); ?>" placeholder="00" />
		:
		<label class="screen-reader-text" for="start-date-minute">
			<?php esc_html_e( 'Start Date Minute', 'easy-digital-downloads' ); ?>
		</label>
		<input type="number" min="0" max="59" step="1" name="start_date_minute" id="start-date-minute" value="<?php echo esc_attr( $minute ); ?>" placeholder="00" />

		<?php echo esc_html( ' (' . edd_get_timezone_abbr() . ')' ); ?>
		<?php
	}

	/**
	 * Render the start field.
	 *
	 * @since 3.3.9
	 */
	public function render(): void {
		?>
		<div class="edd-form-group">
			<?php
			do_action( 'edd_edit_discount_form_before_start_date', $this->data->id, $this->data );
			$this->do_label();
			?>
			<div class="edd-form-group__control edd-discount-datetime">
				<?php $this->do_input(); ?>
			</div>
			<?php $this->do_description(); ?>
		</div>
		<?php
	}
}
