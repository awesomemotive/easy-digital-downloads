<?php
/**
 * Tax Rates List Table.
 *
 * @package     EDD
 * @subpackage  Admin\Settings
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Settings;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * Tax_Rates_List_Table Class.
 *
 * @since 3.0
 */
class Tax_Rates_List_Table extends List_Table {

	/**
	 * Tooltips.
	 *
	 * @since 3.0
	 * @var array
	 */
	protected $tooltips = array();

	protected $tax_rate_key = 1;

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Tax Rate', 'easy-digital-downloads' ),
			'plural'   => __( 'Tax Rates', 'easy-digital-downloads' ),
			'ajax'     => false,
		) );

		// Setup counts.
		$this->counts = edd_get_tax_rate_counts();

		// Tooltips for column headers.
		$this->tooltips = array(
			'rate' => '<strong>' . __( 'Regional tax rates', 'easy-digital-downloads' ) . ':</strong> ' . __( 'When a customer enters an address on checkout that matches the specified region for this tax rate, the cart tax will adjust automatically.', 'easy-digital-downloads' ),
			'from' => '<strong>' . __( 'Start date', 'easy-digital-downloads' ) . ':</strong>' . __( 'The date a tax rate is active from.', 'easy-digital-downloads' ),
			'to'   => '<strong>' . __( 'End date', 'easy-digital-downloads' ) . ':</strong>' . __( 'The date a tax rate stops applying.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Retrieve the bulk actions.
	 *
	 * @since 3.0
	 *
	 * @return array $actions Bulk actions.
	 */
	public function get_bulk_actions() {
		return array(
			'activate'   => __( 'Activate', 'easy-digital-downloads' ),
			'deactivate' => __( 'Deactivate', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'country' => __( 'Country', 'easy-digital-downloads' ),
			'region'  => __( 'Region', 'easy-digital-downloads' ),
			'rate'    => __( 'Rate', 'easy-digital-downloads' ),
			'actions' => __( 'Actions', 'easy-digital-downloads' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the sortable columns.
	 *
	 * @since 3.0
	 *
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Render the Country column.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Adjustments\Adjustment $adjustment Adjustment object.
	 * @return string Data shown in the Country column.
	 */
	public function column_country( $adjustment ) {
		return EDD()->html->select( array(
			'options'          => edd_get_country_list(),
			'name'             => 'tax_rates[' . $this->tax_rate_key . '][country]',
			'selected'         => $adjustment->name,
			'show_option_all'  => false,
			'show_option_none' => false,
			'class'            => 'edd-tax-country',
			'chosen'           => true,
			'placeholder'      => __( 'Choose a country', 'easy-digital-downloads' ),
			'data'             => array(
				'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
			),
		) );
	}

	/**
	 * Render the Region column.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Adjustments\Adjustment $adjustment Adjustment object.
	 * @return string Data shown in the Region column.
	 */
	public function column_region( $adjustment ) {
		$states = edd_get_shop_states( $adjustment->name );

		if ( ! empty( $states ) ) {
			$select = EDD()->html->select( array(
				'options'          => $states,
				'name'             => 'tax_rates[' . $this->tax_rate_key . '][state]',
				'selected'         => $adjustment->description,
				'disabled'         => 'country' === $adjustment->scope,
				'show_option_all'  => false,
				'show_option_none' => false,
				'chosen'           => true,
				'placeholder'      => __( 'Choose a state', 'easy-digital-downloads' ),
			) );
		} else {
			$select = EDD()->html->text( array(
				'name'  => 'tax_rates[' . $this->tax_rate_key . '][state]',
				'value' => $adjustment->description,
			) );
		}

		$checkbox = '<span class="edd-tax-whole-country">' . EDD()->html->checkbox( array(
			'name'    => 'tax_rates[' . $this->tax_rate_key . '][global]',
			'current' => (bool) 'country' === $adjustment->scope,
			'label'   => __( 'Apply to whole country', 'easy-digital-downloads' ),
		) ) . '</span>';

		return $select . $checkbox;
	}

	/**
	 * Render the Rate column.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Adjustments\Adjustment $adjustment Adjustment object.
	 * @return string Data shown in the Rate column.
	 */
	public function column_rate( $adjustment ) {
		return '<input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[' . $this->tax_rate_key . '][rate]" value="' . esc_attr( floatval( $adjustment->amount ) ) . '" autocomplete="off" />';
	}

	public function column_actions( $adjustment ) {
		?>
		<span class="edd_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'easy-digital-downloads' ); ?></span>
		<?php

		$this->tax_rate_key++;
	}

	/**
	 * Message to be displayed when there are no tax rates.
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No tax rates found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve all the data for all the tax rates table.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @return array Array of all the data for the tax rates table.
	 */
	private function tax_rates_data() {
		// Get tax rates
		return edd_get_tax_rates( array( 'number' => 1000, 'status' => 'active' ), OBJECT );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 3.0
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = $this->tax_rates_data();

		$type = isset( $_GET['type'] ) // WPCS: CSRF ok.
			? sanitize_key( $_GET['type'] )
			: 'active';

		$total = ! empty( $this->counts[ $type ] )
			? $this->counts[ $type ]
			: 0;
	}

	/**
	 * Print column headers.
	 *
	 * @internal This method is overridden because tooltips are displayed on the
	 *           tax rates list table.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_id Whether to set the ID attribute or not.
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb']     = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All', 'easy-digital-downloads' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'";
			}

			$tooltip = isset( $this->tooltips[ $column_key ] )
				? '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="' . wp_kses_post( $this->tooltips[ $column_key ] ) . '"></span>'
				: '';

			echo "<{$tag} {$scope} {$id} {$class}>{$column_display_name}{$tooltip}</{$tag}>"; // WPCS: XSS ok.
		}
	}

	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 3.1.0
	 */
	public function display_rows_or_placeholder() {
		$has_items = $this->has_items();
		
		$show_no_items = $has_items ? ' style="display:none"' : '';
		echo '<tr ' . $show_no_items . ' class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
		$this->no_items();
		echo '</td></tr>';
		$item = new \stdClass();
		$item->id = 0;
		$item->parent = 0;
		$item->name = '';
		$item->code = '';
		$item->status = 'active';
		$item->type = 'tax_rate';
		$item->scope = 'region';
		$item->amount_type = 'percent';
		$item->amount = 0;
		$item->description = '';
		echo '<tr class="edd-tax-rate-initial" style="display:none" >';
		$this->single_row_columns( $item );
		echo '</tr>';

		if ( $has_items ) {
			$this->display_rows();
		}
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.0
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {}
	
}
