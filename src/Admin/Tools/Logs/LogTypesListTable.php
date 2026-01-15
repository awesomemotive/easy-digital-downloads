<?php
/**
 * Log Types List Table Class.
 *
 * @package     EDD\Admin\Tools\Logs
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Admin\Tools\Logs;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * Log Types List Table Class.
 *
 * Renders the log types table for the log pruning settings.
 *
 * @since 3.6.4
 */
class LogTypesListTable extends List_Table {

	/**
	 * The log types data.
	 *
	 * @since 3.6.4
	 * @var array
	 */
	private $log_types = array();

	/**
	 * The pruning settings.
	 *
	 * @since 3.6.4
	 * @var array
	 */
	private $settings = array();

	/**
	 * Whether pruning is enabled globally.
	 *
	 * @since 3.6.4
	 * @var bool
	 */
	private $pruning_enabled = false;

	/**
	 * Whether this is the additional (unregistered) types table.
	 *
	 * @since 3.6.4
	 * @var bool
	 */
	private $is_additional = false;

	/**
	 * Constructor.
	 *
	 * @since 3.6.4
	 *
	 * @param array $args {
	 *     Optional. Arguments for configuring the list table.
	 *
	 *     @type array $log_types       Array of log type configurations.
	 *     @type array $settings        The pruning settings array.
	 *     @type bool  $pruning_enabled Whether pruning is enabled globally.
	 *     @type bool  $is_additional   Whether this is the additional types table.
	 * }
	 */
	public function __construct( $args = array() ) {
		parent::__construct(
			array(
				'singular' => 'log_type',
				'plural'   => 'log_types',
				'ajax'     => false,
			)
		);

		$this->log_types       = isset( $args['log_types'] ) ? $args['log_types'] : array();
		$this->settings        = isset( $args['settings'] ) ? $args['settings'] : array();
		$this->pruning_enabled = isset( $args['pruning_enabled'] ) ? (bool) $args['pruning_enabled'] : false;
		$this->is_additional   = isset( $args['is_additional'] ) ? (bool) $args['is_additional'] : false;
	}

	/**
	 * Get the columns for the table.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public function get_columns() {
		$storage_tooltip = new \EDD\HTML\Tooltip(
			array(
				'content' => __( 'Storage values are estimates based on row data. Actual database storage may vary slightly due to indexes and overhead.', 'easy-digital-downloads' ),
			)
		);

		return array(
			'log_type'       => __( 'Log Type', 'easy-digital-downloads' ),
			'records'        => __( 'Records', 'easy-digital-downloads' ),
			'storage'        => __( 'Database Storage', 'easy-digital-downloads' ) . $storage_tooltip->get(),
			'enable_pruning' => __( 'Enable Pruning', 'easy-digital-downloads' ),
			'days_to_keep'   => __( 'Days to Keep', 'easy-digital-downloads' ),
			'next_pruning'   => __( 'Next Pruning', 'easy-digital-downloads' ),
			'manual_pruning' => __( 'Manual Pruning', 'easy-digital-downloads' ),
			'status'         => __( 'Status', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.6.4
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'log_type';
	}

	/**
	 * Retrieves the data for the table.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public function get_data() {
		return $this->log_types;
	}

	/**
	 * Prepare items for display.
	 *
	 * @since 3.6.4
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	/**
	 * Default column output.
	 *
	 * @since 3.6.4
	 *
	 * @param array  $item        The current item.
	 * @param string $column_name The column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}

	/**
	 * Render the Log Type column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_log_type( $item ) {
		$output = '<strong>' . esc_html( $item['label'] ) . '</strong>';

		if ( ! empty( $item['description'] ) ) {
			$output .= '<br><span class="description">' . esc_html( $item['description'] ) . '</span>';
		}

		return $output;
	}

	/**
	 * Render the Records column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_records( $item ) {
		return number_format_i18n( $item['record_count'] );
	}

	/**
	 * Render the Database Storage column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_storage( $item ) {
		return esc_html( $item['storage'] );
	}

	/**
	 * Render the Enable Pruning column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_enable_pruning( $item ) {
		$type_id      = $item['type_id'];
		$type_enabled = $item['type_enabled'];
		$is_prunable  = $item['is_prunable'];
		$setting_name = 'log_pruning_' . $type_id . '_enabled';

		// Disable if not prunable OR if global pruning is disabled.
		$is_disabled = ! $is_prunable || ! $this->pruning_enabled;

		$toggle_args = array(
			'name'    => $setting_name,
			'id'      => 'edd_log_pruning_' . esc_attr( $type_id ),
			'current' => $type_enabled,
			'class'   => 'edd-log-type-toggle',
			'options' => array(
				'disabled' => $is_disabled,
			),
		);

		// Add AJAX data attributes for prunable types.
		if ( $is_prunable ) {
			$toggle_args['data'] = array(
				'setting'  => $setting_name,
				'nonce'    => wp_create_nonce( 'edd-toggle-nonce' ),
				'prunable' => '1',
			);
		}

		$toggle = new \EDD\HTML\CheckboxToggle( $toggle_args );
		return $toggle->get();
	}

	/**
	 * Render the Days to Keep column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_days_to_keep( $item ) {
		$type_id     = $item['type_id'];
		$type_days   = $item['type_days'];
		$is_prunable = $item['is_prunable'];

		// Disable if not prunable OR if global pruning is disabled.
		$is_disabled = ! $is_prunable || ! $this->pruning_enabled;

		$days_args = array(
			'name'     => 'edd_log_pruning[log_types][' . esc_attr( $type_id ) . '][days]',
			'id'       => 'edd_log_pruning_days_' . esc_attr( $type_id ),
			'value'    => $type_days,
			'min'      => 1,
			'max'      => 3650,
			'class'    => 'edd-log-days-input edd-log-pruning-number edd-log-type-days',
			'disabled' => $is_disabled,
		);

		// Add AJAX data attributes for prunable types.
		if ( $is_prunable ) {
			$days_args['data'] = array(
				'setting'  => $type_id . '_days',
				'nonce'    => wp_create_nonce( 'edd-log-pruning-number-nonce' ),
				'prunable' => '1',
			);
		}

		$days_input = new \EDD\HTML\Number( $days_args );
		return $days_input->get();
	}

	/**
	 * Render the Next Pruning column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_next_pruning( $item ) {
		return esc_html( $item['next_prune_text'] );
	}

	/**
	 * Render the Manual Pruning column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_manual_pruning( $item ) {
		if ( ! $item['is_prunable'] ) {
			return '<span class="description">' . esc_html__( 'Not available', 'easy-digital-downloads' ) . '</span>';
		}

		$output  = '<button type="button" ';
		$output .= 'class="button button-secondary edd-promo-notice__trigger edd-promo-notice__trigger--ajax edd-prune-now" ';
		$output .= 'data-id="logpruningconfirmmodal" ';
		$output .= 'data-product="' . esc_attr( $item['type_id'] ) . '" ';
		$output .= 'data-value="' . esc_attr( $item['type_days'] ) . '">';
		$output .= esc_html__( 'Prune Now', 'easy-digital-downloads' );
		$output .= '</button>';
		$output .= '<span class="edd-prune-result"></span>';

		return $output;
	}

	/**
	 * Render the Status column.
	 *
	 * @since 3.6.4
	 *
	 * @param array $item The current item.
	 * @return string
	 */
	public function column_status( $item ) {
		$type_id       = $item['type_id'];
		$is_prunable   = $item['is_prunable'];
		$has_warning   = $item['has_warning'];
		$is_unsupported = ! empty( $item['unsupported'] );

		// Check for unsupported legacy types first.
		if ( $is_unsupported ) {
			$status_badge = new \EDD\Utils\StatusBadge(
				array(
					'status'  => 'error',
					'label'   => __( 'Unsupported', 'easy-digital-downloads' ),
					'icon'    => 'no',
					'tooltip' => __( 'This log type uses custom storage and cannot be managed through the standard retention system. Contact the extension developer for an update.', 'easy-digital-downloads' ),
				)
			);
			return $status_badge->get();
		}

		if ( ! $is_prunable ) {
			$reason       = \EDD\Logs\Registry::get_pruning_warning( $type_id );
			$status_badge = new \EDD\Utils\StatusBadge(
				array(
					'status'  => 'default',
					'label'   => __( 'Non-prunable', 'easy-digital-downloads' ),
					'icon'    => 'lock',
					'tooltip' => $reason,
				)
			);
			return $status_badge->get();
		}

		if ( $has_warning ) {
			$warning      = \EDD\Logs\Registry::get_pruning_warning( $type_id );
			$status_badge = new \EDD\Utils\StatusBadge(
				array(
					'status'  => 'warning',
					'label'   => __( 'Warning', 'easy-digital-downloads' ),
					'icon'    => 'warning',
					'tooltip' => $warning,
				)
			);
			return $status_badge->get();
		}

		if ( $this->is_additional || ! empty( $item['legacy'] ) ) {
			$status_badge = new \EDD\Utils\StatusBadge(
				array(
					'status'  => 'info',
					'label'   => __( 'Unknown', 'easy-digital-downloads' ),
					'icon'    => 'editor-help',
					'tooltip' => __( 'This log type has not defined retention rules. You may enable pruning based on your own preferences.', 'easy-digital-downloads' ),
				)
			);
			return $status_badge->get();
		}

		$status_badge = new \EDD\Utils\StatusBadge(
			array(
				'status' => 'success',
				'label'  => __( 'Prunable', 'easy-digital-downloads' ),
				'icon'   => 'yes-alt',
			)
		);
		return $status_badge->get();
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 3.6.4
	 */
	public function no_items() {
		esc_html_e( 'No log types found.', 'easy-digital-downloads' );
	}

	/**
	 * Generate the table navigation.
	 *
	 * Override to hide bulk actions and pagination since this is a settings table.
	 *
	 * @since 3.6.4
	 *
	 * @param string $which The location of the tablenav: 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {
		// No tablenav needed for this settings table.
	}

	/**
	 * Display the table.
	 *
	 * Override to remove the table footer since this is a compact settings table.
	 *
	 * @since 3.6.4
	 */
	public function display() {
		$this->display_tablenav( 'top' );
		?>
		<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>
			<tbody id="the-list" data-wp-lists="list:<?php echo esc_attr( $this->_args['singular'] ); ?>">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Get bulk actions.
	 *
	 * Override to return empty array since this is a settings table.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}
}
