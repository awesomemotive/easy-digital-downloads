<?php
/**
 * Refund Items Table Class.
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin;

// Exit if accessed directly
use EDD\Orders\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Order_Items_Table Class.
 *
 * Renders the Refund Items table on the Refund modal.
 *
 * @since 3.0
 */
class Refund_Items_Table extends List_Table {

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		global $hook_suffix;

		parent::__construct( array(
			'singular' => __( 'Refund Item',  'easy-digital-downloads' ),
			'plural'   => __( 'Refund Items', 'easy-digital-downloads' ),
			'ajax'     => false,
		) );

		$this->get_counts();
	}

	/**
	 * Get the base URL for the order item list table.
	 *
	 * @since 3.0
	 *
	 * @return string Base URL.
	 */
	public function get_base_url() {}

	/**
	 * Retrieve the view types.
	 *
	 * @since 3.0
	 *
	 * @return array $views All the views available.
	 */
	public function get_views() {
		return array();
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
			'name'     => __( 'Product', 'easy-digital-downloads' ),
			'amount'   => __( 'Unit Price', 'easy-digital-downloads' ),
			'quantity' => __( 'Quantity', 'easy-digital-downloads' ),
			'subtotal' => __( 'Subtotal', 'easy-digital-downloads' )
		);

		// Maybe add tax column.
		$order = $this->get_order();
		if ( $order && $order->get_tax_rate() ) {
			$columns['tax'] = __( 'Tax', 'easy-digital-downloads' );
		}

		// Total at the end.
		$columns['total'] = __( 'Total', 'easy-digital-downloads' );

		// Return columns.
		return $columns;
	}

	/**
	 * Retrieve the sortable columns.
	 *
	 * @since 3.0
	 *
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() { return array(); }

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return '';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item  Order item object.
	 * @param string                 $column_name The name of the column.
	 *
	 * @return string Column name.
	 */
	public function column_default( $order_item, $column_name ) {
		switch ( $column_name ) {
			case 'amount':
				return $this->format_currency( $order_item, $column_name );

			case 'total' :
				return $this->format_currency( $order_item, $column_name, 0 );

			case 'quantity' :
				ob_start();
				?>
				<label for="edd-order-item-quantity-<?php echo esc_attr( $order_item->id ); ?>" class="screen-reader-text">
					<?php esc_html_e( 'Quantity to refund', 'easy-digital-downloads' ); ?>
				</label>
				<input type="number" id="edd-order-item-quantity-<?php echo esc_attr( $order_item->id ); ?>" class="edd-order-item-refund-quantity edd-order-item-refund-input" name="refund_order_item[<?php echo esc_attr( $order_item->id ); ?>][quantity]" value="" placeholder="0" min="0" max="<?php echo esc_attr( $order_item->quantity ); ?>" step="1"<?php echo 'refunded' === $order_item->status ? ' disabled' : ''; ?> />
				<?php
				return ob_get_clean();

			case 'subtotal' :
			case 'tax' :
				$currency_pos = edd_get_option( 'currency_position', 'before' );

				// Maximum amounts that can be refunded.
				$refundable_amounts = $order_item->get_refundable_amounts();
				$amount_remaining   = array_key_exists( $column_name, $refundable_amounts ) ? $refundable_amounts[ $column_name ] : $order_item->{$column_name};

				/*
				 * Original amount.
				 * For subtotals, we actually do subtotal minus discounts for simplicity so that the end user
				 * doesn't have to juggle that.
				 */
				$original_amount = $order_item->{$column_name};
				if ( 'subtotal' === $column_name ) {
					$original_amount -= $order_item->discount;
				}
				ob_start();
				?>
				<label for="edd-order-item-<?php echo esc_attr( $order_item->id ); ?>-refund-<?php echo esc_attr( $column_name ); ?>" class="screen-reader-text">
					<?php
					if ( 'subtotal' === $column_name ) {
						_e( 'Amount to refund, excluding tax', 'easy-digital-downloads' );
					} else {
						_e( 'Amount of tax to refund', 'easy-digital-downloads' );
					}
					?>
				</label>
				<?php
				if ( 'before' === $currency_pos ) {
					echo esc_html( $this->get_currency_symbol( $order_item->order_id ) );
				}
				?>
				<input type="text" id="edd-order-item-<?php echo esc_attr( $order_item->id ); ?>-refund-<?php echo esc_attr( $column_name ); ?>" class="edd-order-item-refund-<?php echo esc_attr( $column_name ); ?> edd-order-item-refund-input" name="refund_order_item[<?php echo esc_attr( $order_item->id ); ?>][<?php echo esc_attr( $column_name ); ?>]" value="" placeholder="<?php echo esc_attr( edd_sanitize_amount( 0 ) ); ?>" data-original="<?php echo esc_attr( $original_amount ); ?>" data-max="<?php echo esc_attr( $amount_remaining ); ?>" data-disabled="<?php echo 'refunded' === $order_item->status ? '1' : ''; ?>" disabled />
				<?php
				if ( 'after' === $currency_pos ) {
					echo esc_html( $this->get_currency_symbol( $order_item->order_id ) );
				}
				?>
				<br>
				<small class="edd-order-item-refund-max-amount">
					<?php
					echo _x( 'Max:', 'Maximum input amount', 'easy-digital-downloads' ) . '&nbsp;';

					echo $this->format_currency( $order_item, $column_name, $amount_remaining );
					?>
				</small>
				<?php
				return ob_get_clean();

			default:
				return property_exists( $order_item, $column_name )
					? $order_item->{$column_name}
					: '';
		}
	}

	/**
	 * This private function formats a column value for currency.
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item      Data for the order_item code.
	 * @param string                 $column_name     String to
	 * @param false|float            $amount_override Amount override, in case it's not in the order item.
	 *
	 * @return string Formatted amount.
	 */
	private function format_currency( $order_item, $column_name, $amount_override = false ) {
		$symbol       = $this->get_currency_symbol( $order_item->order_id );
		$currency_pos = edd_get_option( 'currency_position', 'before' );

		$formatted_amount = '';

		if ( 'before' === $currency_pos ) {
			$formatted_amount .= $symbol;
		}

		$amount = false !== $amount_override ? $amount_override : $order_item->{$column_name};

		$formatted_amount .= '<span data-' . $column_name . '="' . edd_sanitize_amount( $amount ) . '">' .
		                     edd_format_amount( $amount ) .
		                     '</span>';

		if ( 'after' === $currency_pos ) {
			$formatted_amount .= $symbol;
		}

		return $formatted_amount;
	}

	/**
	 * Retrieves the currency symbol for a given order item.
	 *
	 * @param int $order_id
	 *
	 * @return string|null
	 */
	private function get_currency_symbol( $order_id ) {
		static $symbol = null;

		if ( is_null( $symbol ) ) {
			$order = edd_get_order( $order_id );

			if ( $order ) {
				$symbol = edd_currency_symbol( $order->currency );
			}
		}

		return $symbol;
	}

	/**
	 * Render the Name Column
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item Order Item object.
	 *
	 * @return string Data shown in the Name column
	 */
	public function column_name( $order_item ) {
		// Wrap order_item title in strong anchor
		return '<strong>' . $order_item->get_order_item_name() . '</strong>';
	}

	/**
	 * Render the status column
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $order_item Order Item object.
	 *
	 * @return string Displays the order_item status
	 */
	public function column_status( $order_item ) {
		return ! empty( $order_item->status )
			? ucwords( $order_item->status )
			: '&mdash;';
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No order items found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 3.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() { return array(); }

	/**
	 * Process the bulk actions
	 *
	 * @since 3.0
	 */
	public function process_bulk_action() {}

	/**
	 * Retrieve the order_item code counts
	 *
	 * @since 3.0
	 */
	public function get_counts() {

		// Maybe retrieve counts.
		if ( ! edd_is_add_order_page() ) {

			// Check for an order ID
			$order_id = ! empty( $_POST['order_id'] )
				? absint( $_POST['order_id'] ) // WPCS: CSRF ok.
				: 0;

			// Get counts
			$this->counts = edd_get_order_item_counts( array(
				'order_id' => $order_id
			) );
		}
	}

	/**
	 * Retrieve all the data for all the order_item codes
	 *
	 * @since 3.0
	 * @return array $order_items_data Array of all the data for the order_item codes
	 */
	public function get_data() {

		// Query args.
		$id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

		// Get order items.
		return edd_get_order_items( array(
			'order_id' => $id,
			'number'   => 999,
		) );
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

		$this->items = $this->get_data();
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.0
	 *
	 * @param \EDD\Orders\Order_Item $item Order item object.
	 */
	public function single_row( $item ) {

		// Status.
		$classes = array_map( 'sanitize_html_class', array(
			'order-' . $item->order_id,
			$item->status,
			'refunditem',
		) );

		// Turn into a string.
		$class = implode( ' ', $classes );
		?>

		<tr id="order-item-<?php echo esc_attr( $item->id ); ?>" data-order-item="<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_html( $class ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>

		<?php
	}

	public function display() {
		$singular = $this->_args['singular'];

		wp_nonce_field( 'edd_process_refund', 'edd_process_refund' );
		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"<?php
			if ( $singular ) {
				echo " data-wp-lists='list:$singular'";
			} ?>>
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Adds custom submit button below the refund items table.
	 *
	 * @param string $which
	 * @since 3.0
	 */
	protected function display_tablenav( $which ) {
		if ( 'bottom' !== $which ) {
			return;
		}
		echo '<div class="tablenav bottom">';
		echo '<div id="edd-refund-submit-button-wrapper">';
		echo '<span class="spinner"></span>';
		printf( '<button id="edd-submit-refund-submit" class="button-primary" disabled>%s</button>', esc_html__( 'Submit Refund', 'easy-digital-downloads' ) );
		echo '</div>';
		echo '</div>';
	}

	public function display_rows() {
		static $currency_symbol = null;

		foreach ( $this->items as $item ) {

			if ( is_null( $currency_symbol ) ) {
				$currency          = edd_get_order( $item->order_id )->currency;
				$currency_symbol   = edd_currency_symbol( $currency );
				$currency_position = edd_get_option( 'currency_position', 'before' );
			}

			$this->single_row( $item );
		}

		// Now we need to add the columns for the totals.
		?>
		<tr id="edd-refund-submit-subtotal" class="edd-refund-submit-line-total">
			<td colspan="<?php echo $this->get_column_count() - 1; ?>">
				<?php _e( 'Refund Subtotal', 'easy-digital-downloads' ); ?>
			</td>

			<td>
				<?php
				$currency_symbol_output = sprintf( '<span>%s</span>', $currency_symbol );
				$before                 = 'before' === $currency_position ? $currency_symbol_output : '';
				$after                  = 'after' === $currency_position ? $currency_symbol_output : '';
				$amount                 = edd_format_amount( 0.00 );
				printf(
					'%1$s<span id="edd-refund-submit-subtotal-amount">%2$s</span>%3$s',
					$before,
					esc_attr( $amount ),
					$after
				);
				?>
			</td>
		</tr>

		<?php
		$order = $this->get_order();
		if ( $order && $order->get_tax_rate() ) :
		?>
		<tr id="edd-refund-submit-tax" class="edd-refund-submit-line-total">
			<td colspan="<?php echo $this->get_column_count() - 1; ?>">
				<?php _e( 'Refund Tax Total', 'easy-digital-downloads' ); ?>
			</td>

			<td>
				<?php
				printf(
					'%1$s<span id="edd-refund-submit-tax-amount">%2$s</span>%3$s',
					$before,
					esc_attr( $amount ),
					$after
				);
				?>
			</td>
		</tr>
		<?php endif; ?>

		<tr id="edd-refund-submit-total" class="edd-refund-submit-line-total">
			<td colspan="<?php echo $this->get_column_count() - 1; ?>">
				<?php _e( 'Refund Total', 'easy-digital-downloads' ); ?>
			</td>

			<td>
				<?php
				printf(
					'%1$s<span id="edd-refund-submit-total-amount">%2$s</span>%3$s',
					$before,
					esc_attr( $amount ),
					$after
				);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Gets the order object.
	 *
	 * @since 3.0
	 * @return Order|false
	 */
	private function get_order() {
		$order_id = ! empty( $_POST['order_id'] )
			? absint( $_POST['order_id'] ) // WPCS: CSRF ok.
			: 0;

		return edd_get_order( $order_id );
	}
}
