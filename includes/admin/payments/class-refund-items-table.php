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
use EDD\Orders\Order_Adjustment;
use EDD\Orders\Order_Item;

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
			'singular' => 'refund item',
			'plural'   => 'refund items',
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
			'cb'       => '<input type="checkbox" />',
			'name'     => __( 'Product', 'easy-digital-downloads' ),
			'amount'   => __( 'Unit Price', 'easy-digital-downloads' )
		);

		// Maybe add quantity column.
		if ( edd_item_quantities_enabled() ) {
			$columns['quantity'] = __( 'Quantity', 'easy-digital-downloads' );
		}

		// Add subtotal after quantity.
		$columns['subtotal'] = __( 'Subtotal', 'easy-digital-downloads' );

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
		return 'name';
	}

	/**
	 * Generates a unique ID for an item, to be used as HTML IDs.
	 * We cannot simply use `$item->id` because it's possible that an order item and order adjustment
	 * could have the same ID.
	 *
	 * @param Order_Item|Order_Adjustment $item
	 *
	 * @since 3.0
	 * @return string
	 */
	private function get_item_unique_id( $item ) {
		return $item instanceof Order_Item ? 'order-item-' . $item->id : 'order-adjustment-' . $item->id;
	}

	/**
	 * Returns a string that designates the type of object. This is used in HTML `name` attributes.
	 *
	 * @param Order_Item|Order_Adjustment $item
	 *
	 * @since 3.0
	 * @return string
	 */
	private function get_object_type( $item ) {
		return $item instanceof Order_Item ? 'order_item' : 'order_adjustment';
	}

	/**
	 * Returns the item display name.
	 *
	 * @param Order_Item|Order_Adjustment $item
	 *
	 * @since 3.0
	 * @return string
	 */
	private function get_item_display_name( $item ) {
		$name = '';
		if ( $item instanceof Order_Item ) {
			$name = $item->get_order_item_name();
		} elseif ( $item instanceof Order_Adjustment ) {
			$name = ! empty( $item->description ) ? $item->description : __( 'Order Fee', 'easy-digital-downloads' );
		}

		return $name;
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 3.0
	 *
	 * @param Order_Item|Order_Adjustment $item        Order item or adjustment object.
	 * @param string                      $column_name The name of the column.
	 *
	 * @return string Column name.
	 */
	public function column_default( $item, $column_name ) {
		$object_type   = $this->get_object_type( $item );
		$item_id       = $this->get_item_unique_id( $item );
		$item_quantity = $item instanceof Order_item ? $item->quantity : 1;

		switch ( $column_name ) {
			case 'amount':
				return $this->format_currency( $item, $column_name );

			case 'total' :
				return $this->format_currency( $item, $column_name, 0 );

			case 'quantity' :
				ob_start();
				?>
				<label for="edd-order-item-quantity-<?php echo esc_attr( $item_id ); ?>" class="screen-reader-text">
					<?php esc_html_e( 'Quantity to refund', 'easy-digital-downloads' ); ?>
				</label>
				<input type="number" id="edd-order-item-quantity-<?php echo esc_attr( $item_id ); ?>" class="edd-order-item-refund-quantity edd-order-item-refund-input" name="refund_<?php echo esc_attr( $object_type ); ?>[<?php echo esc_attr( $item->id ); ?>][quantity]" value="<?php echo esc_attr( $item_quantity ); ?>" placeholder="0" min="0" max="<?php echo esc_attr( $item_quantity ); ?>" step="1" disabled />
				<?php
				return ob_get_clean();

			case 'subtotal' :
			case 'tax' :
				$currency_pos = edd_get_option( 'currency_position', 'before' );

				// Maximum amounts that can be refunded.
				$refundable_amounts = $item->get_refundable_amounts();
				$amount_remaining   = array_key_exists( $column_name, $refundable_amounts ) ? $refundable_amounts[ $column_name ] : $item->{$column_name};

				/*
				 * Original amount.
				 * For subtotals, we actually do subtotal minus discounts for simplicity so that the end user
				 * doesn't have to juggle that.
				 */
				$original_amount = $item->{$column_name};
				if ( 'subtotal' === $column_name && ! empty( $item->discount ) ) {
					$original_amount -= $item->discount;
				}
				ob_start();
				?>
				<label for="edd-order-item-<?php echo esc_attr( $item_id ); ?>-refund-<?php echo esc_attr( $column_name ); ?>" class="screen-reader-text">
					<?php
					if ( 'subtotal' === $column_name ) {
						esc_html_e( 'Amount to refund, excluding tax', 'easy-digital-downloads' );
					} else {
						esc_html_e( 'Amount of tax to refund', 'easy-digital-downloads' );
					}
					?>
				</label>
				<?php
				if ( 'before' === $currency_pos ) {
					echo esc_html( $this->get_currency_symbol( $item->order_id ) );
				}
				?>
				<input type="text" id="edd-order-item-<?php echo esc_attr( $item_id ); ?>-refund-<?php echo esc_attr( $column_name ); ?>" class="edd-order-item-refund-<?php echo esc_attr( $column_name ); ?> edd-order-item-refund-input" name="refund_<?php echo esc_attr( $object_type ); ?>[<?php echo esc_attr( $item->id ); ?>][<?php echo esc_attr( $column_name ); ?>]" value="<?php echo esc_attr( $amount_remaining ); ?>" placeholder="<?php echo esc_attr( edd_sanitize_amount( 0 ) ); ?>" data-original="<?php echo esc_attr( $original_amount ); ?>" data-max="<?php echo esc_attr( $amount_remaining ); ?>" disabled />
				<?php
				if ( 'after' === $currency_pos ) {
					echo esc_html( $this->get_currency_symbol( $item->order_id ) );
				}
				?>
				<br>
				<small class="edd-order-item-refund-max-amount">
					<?php
					echo _x( 'Max:', 'Maximum input amount', 'easy-digital-downloads' ) . '&nbsp;';

					echo $this->format_currency( $item, $column_name, $amount_remaining );
					?>
				</small>
				<?php
				return ob_get_clean();

			default:
				return property_exists( $item, $column_name )
					? $item->{$column_name}
					: '';
		}
	}

	/**
	 * This private function formats a column value for currency.
	 *
	 * @since 3.0
	 *
	 * @param Order_Item|Order_Adjustment $item            Item object.
	 * @param string                      $column_name     ID of the column being displayed.
	 * @param false|float                 $amount_override Amount override, in case it's not in the order item.
	 *
	 * @return string Formatted amount.
	 */
	private function format_currency( $item, $column_name, $amount_override = false ) {
		$symbol       = $this->get_currency_symbol( $item->order_id );
		$currency_pos = edd_get_option( 'currency_position', 'before' );

		$formatted_amount = '';

		if ( 'before' === $currency_pos ) {
			$formatted_amount .= $symbol;
		}

		// Order Adjustments do not have an `amount` column. We can use `subtotal` instead.
		if ( 'amount' === $column_name && $item instanceof Order_Adjustment ) {
			$column_name = 'subtotal';
		}

		$amount = false !== $amount_override ? $amount_override : $item->{$column_name};

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
	 * @since 3.0
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
	 * Render the checkbox column
	 *
	 * @since 3.0
	 *
	 * @param Order_Item|Order_Adjustment $item Order Item or Order Adjustment object.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		$item_id            = $this->get_item_unique_id( $item );
		$object_type        = $this->get_object_type( $item );
		$item_quantity      = $item instanceof Order_Item ? $item->quantity : 1;
		$refundable_amounts = $item->get_refundable_amounts();
		$total_remaining    = array_key_exists( 'total', $refundable_amounts ) ? floatval( $refundable_amounts['total'] ) : 0.00;

		if ( 'refunded' !== $item->status && $total_remaining > 0 ) {
			$quantity_html = '';
			if ( ! edd_item_quantities_enabled() ) {
				$quantity_html = '<input type="hidden" id="edd-order-item-quantity-' . esc_attr( $item_id ) . '" class="edd-order-item-refund-quantity edd-order-item-refund-input" name="refund_' . esc_attr( $object_type ) . '[' . esc_attr( $item->id ) . '][quantity]" value="' . esc_attr( $item_quantity ) . '" min="0" max="' . esc_attr( $item_quantity ) . '" />';
			}

			return sprintf(
				'<input type="checkbox" name="%1$s[]" id="%1$s-%2$s" class="edd-order-item-refund-checkbox" value="%2$s" /><label for="%1$s-%2$s" class="screen-reader-text">%3$s</label>' . $quantity_html,
				/*$1%s*/
				'refund_' . esc_attr( $object_type ),
				/*$2%s*/
				esc_attr( $item->id ),
				/* translators: product name */
				esc_html( sprintf( __( 'Select %s', 'easy-digital-downloads' ), $this->get_item_display_name( $item ) ) )
			);
		}

		return '';
	}

	/**
	 * Render the Name Column
	 *
	 * @since 3.0
	 *
	 * @param Order_Item|Order_Adjustment $item Order Item object.
	 *
	 * @return string Data shown in the Name column
	 */
	public function column_name( $item ) {
		$name = '';
		if ( $item instanceof Order_Item ) {
			$name = $item->get_order_item_name();
		} elseif ( $item instanceof Order_Adjustment ) {
			$name = ! empty( $item->description ) ? $item->description : __( 'Order Fee', 'easy-digital-downloads' );
		}

		return '<span class="row-title">' . esc_html( $name ) . '</span>';
	}

	/**
	 * Render the status column
	 *
	 * @since 3.0
	 *
	 * @param Order_Item|Order_Adjustment $item Order Item or Order Adjustment object.
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		return ! empty( $item->status )
			? ucwords( $item->status )
			: '&mdash;';
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No items found.', 'easy-digital-downloads' );
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
	 * @todo Fees aren't included in this count, but where does this actually get used anyway?
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
	 * Retrieve all order data to be shown on the refund table.
	 * This includes order items and order adjustments.
	 *
	 * @since 3.0
	 * @return Order[]|Order_Adjustment[] All order items and order adjustments associated with the current order.
	 */
	public function get_data() {
		$order = $this->get_order();

		if ( empty( $order ) ) {
			return array();
		}

		// Get order items.
		$order_items = edd_get_order_items( array(
			'order_id' => $order->id,
			'number'   => 999,
		) );

		// Get order fees
		$order_fees = $order->get_fees();

		return array_merge( $order_items, $order_fees );
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
	 * @param Order_Item|Order_Adjustment $item Order item object.
	 */
	public function single_row( $item ) {

		// Status.
		$classes = array_map( 'sanitize_html_class', array(
			'order-' . $item->order_id,
			$item->status,
			'refunditem',
		) );

		// Turn into a string.
		$class   = implode( ' ', $classes );
		$item_id = $this->get_item_unique_id( $item );
		?>

		<tr id="order-item-<?php echo esc_attr( $item_id ); ?>" data-order-item="<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_html( $class ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>

		<?php
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.0
	 */
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
		/**
		 * Triggers after the table, but before the submit button.
		 *
		 * @param Order $order
		 *
		 * @since 3.0
		 */
		do_action( 'edd_after_submit_refund_table', $this->get_order() );

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

	/**
	 * Displays the rows.
	 *
	 * This is overridden in order to add columns for the totals.
	 *
	 * @since 3.0
	 */
	public function display_rows() {
		static $currency_symbol = null;
		$order_id          = false;
		$currency_position = edd_get_option( 'currency_position', 'before' );

		foreach ( $this->items as $item ) {

			if ( empty( $order_id ) ) {
				$order_id = $item->order_id;
			}

			$this->single_row( $item );
		}

		$currency_symbol = $this->get_currency_symbol( $order_id );

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

		return ! empty( $order_id ) ? edd_get_order( $order_id ) : false;
	}
}
