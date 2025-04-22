<?php
/**
 * Variable prices section.
 *
 * @package EDD\Admin\Downloads\Editor
 * @since 3.3.6
 */

namespace EDD\Admin\Downloads\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Variable prices section.
 *
 * @package   EDD\Admin\Downloads\Editor
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */
class VariablePrices extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'variable-pricing';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 2;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'money-alt';

	/**
	 * Section requirement.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $requires = 'variable-pricing';

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return __( 'Prices', 'easy-digital-downloads' );
	}

	/**
	 * Render the section.
	 *
	 * @since 3.3.6
	 */
	public function render( $item = null ) {
		$download = $this->item;
		if ( ! $download ) {
			$download = $item;
		}

		$prices = $this->get_price_args( $download );

		?>
		<div class="edd-variable-prices__wrapper">
			<div class="edd__header-footer">
				<button type="button" class="button-link edd-button__toggle-expand-custom">
					<?php esc_html_e( 'Expand All', 'easy-digital-downloads' ); ?>
				</button>
				<span class="edd-header-separator">/</span>
				<button type="button" class="button-link edd-button__toggle-collapse-custom">
					<?php esc_html_e( 'Collapse All', 'easy-digital-downloads' ); ?>
				</button>
			</div>
			<div id="edd_price_fields" class="edd-variable-prices__rows">
				<?php
				foreach ( $prices as $key => $price ) {
					$this->do_row( $download, $price, $key );
				}
				?>
			</div>
			<div class="edd__header-footer">
				<?php $timestamp = time(); ?>
				<button type="button" class="button button-secondary edd-button__add--variation" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>">
					<?php esc_html_e( 'Add Variation', 'easy-digital-downloads' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the classes for the section.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	protected function get_classes(): array {
		$classes   = parent::get_classes();
		$classes[] = 'edd_variable_prices_wrapper'; // For backwards compatibility.

		return $classes;
	}

	/**
	 * Get the price arguments.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	private function get_price_args( $download ) {
		$prices = $download ? $download->get_prices() : array();
		if ( ! empty( $prices ) ) {
			return $prices;
		}

		return array(
			1 => array(
				'index'  => 1,
				'name'   => '',
				'amount' => '',
			),
		);
	}

	/**
	 * Render the row.
	 * This is essentially a replacement of `edd_render_price_row`.
	 *
	 * @since 3.3.6
	 *
	 * @param \EDD_Download $download The download object.
	 * @param array         $args     The price args.
	 * @param int           $price_id The key.
	 */
	public function do_row( $download, $args, $price_id ) {
		?>
		<div class="edd-section-content__row edd-variable-price__row edd_repeatable_row closed edd-handle-actions__group" id="edd-variable-price__row-<?php echo esc_attr( $price_id ); ?>">
			<input type="hidden" class="edd-section__id" value="<?php echo esc_attr( $price_id ); ?>" />
			<input type="hidden" name="edd_variable_prices[<?php echo esc_attr( $price_id ); ?>][index]" value="<?php echo esc_attr( $args['index'] ); ?>"/>
			<div class="edd-form-row edd-section-content__fields--standard" data-key="<?php echo esc_attr( $price_id ); ?>">
				<div class="edd-variable-price__id">#<?php echo esc_html( $price_id ); ?></div>
				<div class="edd-form-group__control edd-variable-price__name">
					<label for="edd_variable_prices-<?php echo esc_attr( $price_id ); ?>-name" class="screen-reader-text" aria-hidden="true">
						<?php esc_html_e( 'Variation Name', 'easy-digital-downloads' ); ?>
					</label>
					<?php
					$text = new \EDD\HTML\Text(
						array(
							'name'        => 'edd_variable_prices[' . $price_id . '][name]',
							'id'          => 'edd_variable_prices-' . $price_id . '-name',
							'value'       => esc_attr( $args['name'] ),
							'placeholder' => __( 'Variation Name', 'easy-digital-downloads' ),
							'class'       => array( 'edd_variable_prices_name', 'regular-text' ),
						)
					);
					$text->output();
					?>
				</div>

				<div class="edd-form-group__control edd-price-input-group edd-option-price">
					<label for="edd_variable_prices-<?php echo esc_attr( $price_id ); ?>-amount" class="screen-reader-text" aria-hidden="true">
						<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
					</label>
					<span class="edd-amount-type-wrapper">
						<?php
						$currency_position = edd_get_option( 'currency_position', 'before' );
						$currency_symbol   = edd_currency_symbol();

						// If the currency symbol is before the price, output the prefix.
						if ( 'before' === $currency_position ) {
							?>
							<span class="edd-input__symbol edd-input__symbol--prefix"><?php echo esc_html( $currency_symbol ); ?></span>
							<?php
						}
						?>
						<?php
						$price_input = new \EDD\HTML\Text(
							array(
								'name'         => 'edd_variable_prices[' . $price_id . '][amount]',
								'id'           => 'edd_variable_prices-' . $price_id . '-amount',
								'value'        => esc_attr( $args['amount'] ),
								'class'        => array( 'edd-amount-input', 'edd-price-field', 'no-controls', 'symbol-' . $currency_position ),
								'placeholder'  => edd_format_amount( 9.99 ),
								'include_span' => false,
							)
						);

						$price_input->output();
						if ( 'after' === $currency_position ) {
							?>
							<span class="edd-input__symbol edd-input__symbol--suffix"><?php echo esc_html( $currency_symbol ); ?></span>
							<?php
						}
						?>
					</span>
				</div>

			</div>

			<div class="edd-section__actions">
				<div class="edd__handle-actions-order hide-if-no-js">
					<button type="button" class="edd__handle-actions edd__handle-actions-order--higher" aria-disabled="false" aria-describedby="edd-variation-<?php echo esc_attr( $price_id ); ?>-edd__handle-actions-order--higher-description">
						<span class="screen-reader-text"><?php esc_html_e( 'Move up', 'easy-digital-downloads' ); ?></span>
						<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
					</button>
					<span class="hidden" id="edd-variation-<?php echo esc_attr( $price_id ); ?>-edd__handle-actions-order--higher-description">
							<?php
							/* translators: %d: price ID */
							printf( esc_html__( 'Move price %d up', 'easy-digital-downloads' ), $price_id );
							?>
					</span>
					<button type="button" class="edd__handle-actions edd__handle-actions-order--lower" aria-disabled="false" aria-describedby="edd-variation-<?php echo esc_attr( $price_id ); ?>-edd__handle-actions-order--lower-description">
						<span class="screen-reader-text"><?php esc_html_e( 'Move down', 'easy-digital-downloads' ); ?></span>
						<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
					</button>
					<span class="hidden" id="edd-variation-<?php echo esc_attr( $price_id ); ?>-edd__handle-actions-order--lower-description">
							<?php
							/* translators: %d: price ID */
							printf( esc_html__( 'Move price %d down', 'easy-digital-downloads' ), $price_id );
							?>
					</span>
				</div>
				<button type="button" class="button button-secondary edd-button__edit">
					<?php esc_html_e( 'Edit', 'easy-digital-downloads' ); ?>
				</button>
				<?php $this->do_remove_button( $download, $price_id ); ?>
			</div>

			<div class="edd-section-content__fields--custom">
				<?php
				if ( has_action( 'edd_download_price_option_row' ) ) {
					/**
					 * Fires inside the variable price row.
					 *
					 * @param \EDD_Download $download The download object.
					 * @param int           $price_id The price ID.
					 * @param array         $args     The price arguments.
					 */
					do_action( 'edd_download_price_option_row', $download->ID, $price_id, $args );
				}
				?>
				<div class="edd-section-content__actions">
					<?php
					$this->do_default_price_checkbox( $download, $price_id );
					$this->do_add_to_cart_button( $download, $price_id );
					?>
				</div>
			</div>

			<?php
			/**
			 * Fires after the variable price row.
			 *
			 * @param int   $price_id The price ID.
			 * @param array $args     The price arguments.
			 * @param int   $download_id The download ID.
			 * @param int   $price_id The price ID (duplicate, left for backwards compatibility).
			 */
			do_action( 'edd_render_price_row', $price_id, $args, $download->ID, $price_id );
			?>
		</div>
		<?php
	}

	/**
	 * Render the default price checkbox.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download The download object.
	 * @param int           $price_id The price ID.
	 */
	private function do_default_price_checkbox( $download, $price_id ) {
		$default_price_id = $download->get_default_price_id() ?: 1;
		?>
		<div class="edd-form-group__control edd-variable-prices__default edd-toggle">
			<input type="checkbox" name="_edd_default_price_id" id="edd_default_price_id_<?php echo esc_attr( $price_id ); ?>" value="<?php echo esc_attr( $price_id ); ?>" <?php checked( $default_price_id, $price_id ); ?> />
			<label for="edd_default_price_id_<?php echo esc_attr( $price_id ); ?>"><?php esc_html_e( 'Set as Default', 'easy-digital-downloads' ); ?></label>
		</div>
		<?php
	}

	/**
	 * Render the add to cart button.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download The download object.
	 * @param int           $price_id The price ID.
	 */
	private function do_add_to_cart_button( $download, $price_id ) {
		if ( empty( $download->ID ) ) {
			return;
		}
		$add_to_cart_link = add_query_arg(
			array(
				'edd_action'            => 'add_to_cart',
				'download_id'           => (int) $download->ID,
				'edd_options[price_id]' => (int) $price_id,
			),
			edd_get_checkout_uri()
		);
		?>
		<input type="text" id="edd-add-to-cart-link-<?php echo esc_attr( $price_id ); ?>" class="hidden" value="<?php echo esc_html( $add_to_cart_link ); ?>">
		<button type="button" class="button button-secondary edd-button__copy" data-clipboard-target="#edd-add-to-cart-link-<?php echo esc_attr( $price_id ); ?>"><?php esc_html_e( 'Copy Add to Cart Link', 'easy-digital-downloads' ); ?></button>
		<?php
	}

	/**
	 * Render the remove button.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download The download object.
	 */
	private function do_remove_button( $download, $price_id ) {
		$button_classes = array(
			'edd-section-content__remove',
			'button',
			'button-secondary',
			'edd-delete',
		);
		$has_orders     = edd_get_order_items(
			array(
				'product_id' => $download->ID,
				'price_id'   => $price_id,
				'status__in' => edd_get_deliverable_order_item_statuses(),
				'number'     => 1,
			)
		);
		if ( ! empty( $has_orders ) ) {
			$button_classes[] = 'edd-promo-notice__trigger';
			$button_classes[] = 'edd-promo-notice__trigger--ajax';
		}
		?>
		<button type="button" class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" data-id="pricechanges" data-product="<?php echo absint( $download->id ); ?>" data-price-id="<?php echo absint( $price_id ); ?>" data-value="<?php echo absint( $price_id ); ?>">
			<?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?>
		</button>
		<?php
	}
}
