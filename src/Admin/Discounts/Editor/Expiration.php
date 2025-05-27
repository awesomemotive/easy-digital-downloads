<?php
/**
 * Discount editor expiration field.
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
 * Discount editor expiration field.
 *
 * @since 3.3.9
 */
class Expiration extends Field {

	/**
	 * Get the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-expiration';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Expiration date', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Pick the date and time this discount will expire on. Leave blank to never expire.', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$date   = '';
		$hour   = '23';
		$minute = '59';
		if ( ! empty( $this->data->end_date ) ) {
			$expiration_date = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $this->data->end_date, 'utc' ) );
			$date            = $expiration_date->format( 'Y-m-d' );
			$hour            = $expiration_date->format( 'H' );
			$minute          = $expiration_date->format( 'i' );
		}

		?>
		<input name="end_date" id="edd-expiration" type="text" value="<?php echo esc_attr( $date ); ?>" class="edd_datepicker" data-format="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" placeholder="<?php echo esc_attr( edd_get_date_picker_format() ); ?>" />

		<label class="screen-reader-text" for="end-date-hour">
			<?php esc_html_e( 'Expiration Date Hour', 'easy-digital-downloads' ); ?>
		</label>
		<input type="number" min="0" max="24" step="1" name="end_date_hour" id="end-date-hour" value="<?php echo esc_attr( $hour ); ?>" placeholder="00" />
		:
		<label class="screen-reader-text" for="end-date-minute">
			<?php esc_html_e( 'Expiration Date Minute', 'easy-digital-downloads' ); ?>
		</label>
		<input type="number" min="0" max="59" step="1" name="end_date_minute" id="end-date-minute" value="<?php echo esc_attr( $minute ); ?>" placeholder="00" />

		<?php echo esc_html( ' (' . edd_get_timezone_abbr() . ')' ); ?>
		<?php
	}

	/**
	 * Render the expiration field.
	 *
	 * @since 3.3.9
	 */
	public function render(): void {
		?>
		<div class="edd-form-group">
			<?php
			do_action( 'edd_edit_discount_form_before_expiration', $this->data->id, $this->data );
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
