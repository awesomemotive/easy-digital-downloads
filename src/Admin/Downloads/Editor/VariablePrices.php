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
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 501;

	/**
	 * The dynamic class.
	 *
	 * @var string
	 */
	protected $dynamic = true;

	/**
	 * The section requirement.
	 *
	 * @var string
	 */
	protected $requires = 'variable-pricing';

	/**
	 * Gets the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		$args = $this->get_price_args( $this->item );
		if ( ! empty( $args['name'] ) ) {
			return $args['name'];
		}

		/* translators: %s: Price ID */
		return sprintf( __( 'Price ID: %s', 'easy-digital-downloads' ), $this->id );
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
		$args = $this->get_price_args( $download );
		$this->do_row( $download, $args );
		do_action( 'edd_render_price_row', $this->id, $args, $download->ID, $this->id );
	}

	/**
	 * Get the repeatable button.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_repeatable_button(): string {
		ob_start();
		$classes   = array(
			'section-title--add-new',
			'button',
		);
		$timestamp = time();
		?>
		<li id="edd_download_editor___add--section" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="menuitem" data-edd-requires-variable-pricing="true">
			<button type="button" class="edd-add-new-section" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>">
				<span class="dashicons dashicons-insert"></span>
				<span class="label"><?php esc_html_e( 'Add Price', 'easy-digital-downloads' ); ?></span>
			</button>
		</li>
		<div class="edd-spacer"></div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get the ID of the section.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	protected function get_id() {
		return 'price_id_' . $this->id;
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
		$classes[] = 'edd_repeatable_row'; // For backwards compatibility.

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
		$args   = array(
			'name'   => '',
			'amount' => '',
		);
		if ( isset( $prices[ $this->id ] ) ) {
			$args = wp_parse_args( $prices[ $this->id ], $args );
		}

		return $args;
	}

	/**
	 * Render the row.
	 * This is essentially a replacement of `edd_render_price_row`.
	 *
	 * @since 3.3.6
	 *
	 * @param \EDD_Download $download The download object.
	 * @param array         $args     The arguments.
	 */
	private function do_row( $download, $args ) {
		?>
		<input type="hidden" class="edd-section__id" value="<?php echo esc_attr( $this->id ); ?>" />
		<div class="edd-section__id--badge">#<?php echo esc_html( $this->id ); ?></div>
		<div class="edd-section-content__fields--standard">

			<div class="edd-form-group">
				<label for="edd_variable_prices-<?php echo esc_attr( $this->id ); ?>-name" class="edd-form-group__label">
					<?php esc_html_e( 'Variation Name', 'easy-digital-downloads' ); ?>
				</label>
				<div class="edd-form-group__control">
					<?php
					$text = new \EDD\HTML\Text(
						array(
							'name'        => 'edd_variable_prices[' . $this->id . '][name]',
							'id'          => 'edd_variable_prices-' . $this->id . '-name',
							'value'       => esc_attr( $args['name'] ),
							'placeholder' => __( 'Option Name', 'easy-digital-downloads' ),
							'class'       => 'edd_variable_prices_name regular-text',
						)
					);
					$text->output();
					?>
				</div>
			</div>

			<div class="edd-form-group edd-option-price">
				<label for="edd_variable_prices-<?php echo esc_attr( $this->id ); ?>-amount" class="">
					<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
				</label>
				<?php
				$price_args = array(
					'name'        => 'edd_variable_prices[' . $this->id . '][amount]',
					'id'          => 'edd_variable_prices-' . $this->id . '-amount',
					'value'       => $args['amount'],
					'placeholder' => edd_format_amount( 9.99 ),
					'class'       => 'edd-form-group__input edd-price-field',
				);
				?>

				<div class="edd-form-group__control edd-price-input-group">
					<?php
					$currency_position = edd_get_option( 'currency_position', 'before' );
					if ( 'before' === $currency_position ) {
						?>
						<span class="edd-amount-control__currency is-before"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
						<?php
					}
					$price = new \EDD\HTML\Text( $price_args );
					$price->output();
					if ( 'after' === $currency_position ) {
						?>
						<span class="edd-amount-control__currency is-after"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
						<?php
					}
					?>
				</div>
			</div>
		</div>

		<?php
		if ( has_action( 'edd_download_price_option_row' ) ) {
			?>
			<div class="edd-section-content__fields--custom">
				<?php do_action( 'edd_download_price_option_row', $download->ID, $this->id, $args ); ?>
			</div>
			<?php
		}
		?>

		<div class="edd-section-content__actions">
			<?php
			$this->do_default_price_checkbox( $download );
			$this->do_add_to_cart_button( $download );
			$this->do_remove_button( $download );
			?>
		</div>
		<?php
	}

	/**
	 * Render the default price checkbox.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download The download object.
	 */
	private function do_default_price_checkbox( $download ) {
		$default_price_id = $download->get_default_price_id() ?: 1;
		?>
		<div class="edd-form-group edd-variable-prices__default">
			<div class="edd-form-group__control edd-toggle">
				<input type="checkbox" name="_edd_default_price_id" id="edd_default_price_id_<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->id ); ?>" <?php checked( $default_price_id, $this->id ); ?> />
				<label for="edd_default_price_id_<?php echo esc_attr( $this->id ); ?>">
					<?php esc_html_e( 'Default Price', 'easy-digital-downloads' ); ?>
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the add to cart button.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download The download object.
	 */
	private function do_add_to_cart_button( $download ) {
		$add_to_cart_link = add_query_arg(
			array(
				'edd_action'            => 'add_to_cart',
				'download_id'           => (int) $download->ID,
				'edd_options[price_id]' => (int) $this->id,
			),
			edd_get_checkout_uri()
		);
		?>
		<input type="text" id="edd-add-to-cart-link-<?php echo esc_attr( $this->id ); ?>" class="hidden" value="<?php echo esc_html( $add_to_cart_link ); ?>">
		<button type="button" class="button button-secondary edd-button__copy" data-clipboard-target="#edd-add-to-cart-link-<?php echo esc_attr( $this->id ); ?>"><?php esc_html_e( 'Copy Add to Cart Link', 'easy-digital-downloads' ); ?></button>
		<?php
	}

	/**
	 * Render the remove button.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download The download object.
	 */
	private function do_remove_button( $download ) {
		$button_classes = array(
			'edd-section-content__remove',
			'button',
			'button-secondary',
			'edd-delete',
		);
		$has_orders     = edd_get_order_items(
			array(
				'product_id' => $download->ID,
				'price_id'   => $this->id,
				'status__in' => edd_get_deliverable_order_item_statuses(),
				'number'     => 1,
			)
		);
		if ( ! empty( $has_orders ) ) {
			$button_classes[] = 'edd-promo-notice__trigger';
			$button_classes[] = 'edd-promo-notice__trigger--ajax';
		}
		?>
		<button type="button" class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" data-id="pricechanges" data-product="<?php echo absint( $download->id ); ?>" data-value="<?php echo absint( $this->id ); ?>">
			<?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?>
		</button>
		<?php
	}
}
