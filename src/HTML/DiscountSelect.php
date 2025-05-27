<?php
/**
 * DiscountSelect HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.3.9
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class DiscountSelect
 *
 * @since 3.3.9
 * @package EDD\HTML
 */
class DiscountSelect extends Select {

	/**
	 * Gets the HTML for the element.
	 *
	 * @since 3.3.9
	 * @return string Element HTML.
	 */
	public function get(): string {

		$this->args['options']          = $this->get_options();
		$this->args['show_option_none'] = false;

		// The product select must always show an empty option.
		if ( empty( $this->args['show_option_empty'] ) ) {
			$this->args['show_option_empty'] = sprintf(
				__( 'All Discounts', 'easy-digital-downloads' ),
			);
		}

		return parent::get();
	}

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function defaults(): array {
		return array(
			'name'              => 'edd_discounts',
			'id'                => 'discounts',
			'class'             => array( 'edd-discount-select' ),
			'multiple'          => false,
			'selected'          => 0,
			'chosen'            => true,
			'number'            => 30,
			'placeholder'       => sprintf(
				__( 'Choose a Discount', 'easy-digital-downloads' ),
			),
			'data'              => array(
				'search-type'        => 'discount',
				'search-placeholder' => sprintf(
					__( 'Search Discounts', 'easy-digital-downloads' ),
				),
			),
			'required'          => false,
			'show_option_empty' => sprintf(
				__( 'All Discounts', 'easy-digital-downloads' ),
			),
			'status'            => array( 'active', 'expired', 'inactive', 'archived' ),
		);
	}

	/**
	 * Gets the options for the select element.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	private function get_options(): array {
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			return array();
		}

		$discounts = $this->get_discounts();
		$options   = array();

		foreach ( $discounts as $discount ) {
			$options[ $discount->id ] = esc_html( $discount->name );
		}

		$missing_item = $this->get_missing_selected_discount( $this->args['selected'] );
		if ( ! empty( $missing_item ) ) {
			$options = $options + $missing_item;
		}

		return $options;
	}

	/**
	 * Gets the discounts.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	private function get_discounts(): array {
		$discount_args = array(
			'number'     => $this->args['number'],
			'status__in' => $this->args['status'],
		);

		return edd_get_discounts( $discount_args );
	}

	/**
	 * Gets the missing selected product.
	 *
	 * @since 3.3.9
	 * @param string $discount_id Discount ID to check.
	 * @return array
	 */
	private function get_missing_selected_discount( $discount_id ): array {
		$discount = edd_get_discount( $discount_id );
		if ( ! $discount ) {
			return array();
		}

		return array( $discount_id => esc_html( $discount->name ) );
	}
}
