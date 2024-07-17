<?php
/**
 * EmailTemplateListTable.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.0
 */

namespace EDD\Admin\Emails;

use EDD\HTML\Tooltip;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class EmailTemplateListTable
 *
 * @since 3.3.0
 * @package EDD\Admin\Emails
 */
class ListTable extends \WP_List_Table {

	/**
	 * Number of results to show per page.
	 * Pagination is not currently supported.
	 *
	 * @var int
	 */
	public $per_page = 9999;

	/**
	 * @var EDD\Emails\Templates\Registry
	 */
	protected $registry;

	/**
	 * Constructor
	 *
	 * @since 3.3.0
	 * @param array $args The arguments.
	 */
	public function __construct( $args = array() ) {
		$this->registry = edd_get_email_registry();

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen ) {
				$screen->action = 'list';
			}
		}

		parent::__construct( $args );
	}

	/**
	 * Gets a list of columns.
	 *
	 * The format is:
	 * - `'internal-name' => 'Title'`
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'name'          => __( 'Email', 'easy-digital-downloads' ),
			'sender'        => __( 'Sender', 'easy-digital-downloads' ),
			'context'       => __( 'Context', 'easy-digital-downloads' ),
			'recipient'     => __( 'Recipient', 'easy-digital-downloads' ),
			'subject'       => __( 'Subject', 'easy-digital-downloads' ),
			'date_modified' => __( 'Updated', 'easy-digital-downloads' ),
			'status'        => __( 'Status', 'easy-digital-downloads' ),
		);
	}

	/**
	 * ID of the primary column.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * Renders most columns.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $item        The current item.
	 * @param string                              $column_name The column name.
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		$value = '';

		switch ( $column_name ) {
			case 'recipient':
				$value = $this->get_value_from_array( $item->recipient, $this->registry->get_recipients() );
				break;
			case 'context':
				$value = $this->get_value_from_array( $item->get_context_label(), $this->registry->get_contexts() );
				break;
			case 'subject':
				$value = $item->subject;
				break;
			case 'sender':
				$value = $this->get_value_from_array( $item->sender, $this->registry->get_senders() );
				break;
			case 'date_modified':
				$value  = edd_date_i18n( strtotime( $item->date_modified ), get_option( 'date_format' ) );
				$value .= '<br>';
				$value .= edd_date_i18n( strtotime( $item->date_modified ), get_option( 'time_format' ) );
				break;
			default:
				break;
		}

		return $value;
	}

	/**
	 * Renders the "Status" column.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $item The current item.
	 * @return string
	 */
	protected function column_status( $item ) {
		$status = 'active';
		$label  = __( 'Disable Email', 'easy-digital-downloads' );
		$action = 'disable';
		if ( ! $item->status ) {
			$status = 'inactive';
			$label  = __( 'Enable Email', 'easy-digital-downloads' );
			$action = 'enable';
		}
		ob_start();
		$status_tooltip = $item->get_status_tooltip();
		if ( ! empty( $status_tooltip ) ) {
			$tooltip = new Tooltip( $status_tooltip );
			$tooltip->output();
		}
		?>
		<button
			class="edd-button__toggle edd-email-manager__action edd-button-toggle--<?php echo esc_attr( $status ); ?>"
			data-status="<?php echo esc_attr( $status ); ?>"
			data-id="<?php echo esc_attr( $item->email_id ); ?>"
			<?php if ( $item->can_edit( 'status' ) ) : ?>
				data-action="<?php echo esc_attr( $action ); ?>"
			<?php else : ?>
				disabled
			<?php endif; ?>
		>
			<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
		</button>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the "Email" column.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $item The current item.
	 * @return string
	 */
	protected function column_name( $item ) {
		$name = sprintf(
			'<div class="edd-list-table__name"><a href="%s" class="row-title">%s</a>%s</div>',
			esc_url( $item->get_edit_url() ),
			$item->get_name(),
			$this->maybe_add_extra_email_data( $item )
		);

		return $name . $this->row_actions( $this->get_row_actions( $item ) );
	}

	/**
	 * Generates the tbody element for the list table.
	 *
	 * We're modifying the core method here, as we shouldn't ever have no-emails showing, however someone could apply filters to the list
	 * in a way that would result in no matching emails, so we need a hidden 'no items' row to display when no items are left in the list.
	 *
	 * @since 3.3.0
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();

			// Add our hidden row for when no items are found.
			echo '<tr id="no-items" class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Renders the "No items found" message.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No emails found matching filters.', 'easy-digital-downloads' );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $item The current item.
	 */
	public function single_row( $item ) {

		// Add custom data attributes based on filter options.
		$attributes = array(
			'data-type="item"',
			'data-status="' . absint( $item->status ? 1 : 0 ) . '"',
			'data-recipient="' . esc_attr( $item->recipient ) . '"',
			'data-sender="' . esc_attr( $item->sender ) . '"',
			'data-context="' . esc_attr( $item->context ) . '"',
		);

		echo '<tr class="' . esc_attr( $this->get_row_class() ) . '" ' . implode( ' ', $attributes ) . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Retrieves the table classes for the Emails ListTable.
	 * We need to ensure our table does not have the "striped" class.
	 *
	 * @since 3.3.0
	 * @return array An array of table classes.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'table-view-list', $this->_args['plural'] );
	}

	/**
	 * Displays available filters.
	 *
	 * @since 3.3.0
	 * @param string $which The position.
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			do_action( 'edd_email_manager_bottom' );
			return;
		}

		?>
		<div class="alignleft actions">
			<?php
			$this->do_status_filter();
			$this->do_sender_filter();
			$this->do_context_filter();
			$this->do_recipient_filter();
			?>
			<button id="edd-email-clear-filters" class="button button-secondary" style="display:none;">
				<?php esc_html_e( 'Clear', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<?php
		$add_new_actions = $this->registry->get_add_new_actions();
		if ( empty( $add_new_actions ) ) {
			return;
		}
		?>
		<div class="alignright actions">
			<?php $this->do_new_actions_overlay( $add_new_actions ); ?>
		</div>
		<?php
	}

	/**
	 * Renders the "Add New" button and overlay.
	 *
	 * @since 3.3.0
	 * @param array $add_new_actions The add new actions.
	 * @return void
	 */
	private function do_new_actions_overlay( $add_new_actions ) {
		?>
		<button id="edd-emails__add" class="button button-primary">
			<?php esc_html_e( 'Add New Email', 'easy-digital-downloads' ); ?>
		</button>
		<div class="edd-emails__add-new__overlay" style="display:none;">
			<?php
			foreach ( $add_new_actions as $key => $label ) {
				$is_promo = false;
				$product  = 0;
				if ( is_array( $label ) ) {
					$is_promo = ! empty( $label['promo'] );
					$product  = $label['promo'];
					$label    = $label['label'];
				}
				$classes = array(
					'button',
					'edd-emails__add-new',
				);
				if ( $is_promo ) {
					$classes[] = 'edd-promo-notice__trigger';
					$classes[] = 'edd-promo-notice__trigger--ajax';
				}
				printf(
					'<button data-value="%s" data-id="emails" %sclass="%s">%s</option>',
					esc_attr( $key ),
					$product ? 'data-product="' . absint( $product ) . '"' : '',
					esc_attr( implode( ' ', $classes ) ),
					esc_html( $label )
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Prepares the items for display in the table.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = array();

		$emails = $this->registry->get_emails();

		// The key is important as it is used to manage dynamic emails.
		foreach ( $emails as $key => $email_class_name ) {
			try {
				$email = $this->registry->get_email( $email_class_name, array( $key ) );
				if ( ! $email->can_view ) {
					continue;
				}
				$this->items[] = $email;
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}
	}

	/**
	 * Gets the row actions for an item.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $item The email template.
	 * @return array
	 */
	private function get_row_actions( $item ) {
		$row_actions = array();
		$actions     = $item->get_row_actions();
		foreach ( $actions as $action => $data ) {
			$row_actions[ $action ] = sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( $data['url'] ),
				isset( $data['target'] ) ? ' target="' . esc_attr( $data['target'] ) . '"' : '',
				esc_html( $data['text'] )
			);
		}

		return $row_actions;
	}

	/**
	 * Renders the status filter.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function do_status_filter() {
		?>
		<label for="edd-email-status-filter" class="screen-reader-text">
			<?php esc_html_e( 'Filter by status', 'easy-digital-downloads' ); ?>
		</label>
		<select id="edd-email-status-filter" name="status">
			<option value="">
				<?php esc_html_e( 'All Emails', 'easy-digital-downloads' ); ?>
			</option>
			<option value="1">
				<?php esc_html_e( 'Enabled Emails', 'easy-digital-downloads' ); ?>
			</option>
			<option value="0">
				<?php esc_html_e( 'Disabled Emails', 'easy-digital-downloads' ); ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Renders the sender filter.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function do_sender_filter() {
		?>
		<label for="edd-email-sender-filter" class="screen-reader-text">
			<?php esc_html_e( 'Filter by sender', 'easy-digital-downloads' ); ?>
		</label>
		<select id="edd-email-sender-filter" name="sender">
			<option value="">
				<?php esc_html_e( 'All Senders', 'easy-digital-downloads' ); ?>
			</option>
			<?php foreach ( $this->registry->get_senders() as $sender_key => $sender_label ) : ?>
				<option value="<?php echo esc_attr( $sender_key ); ?>">
					<?php echo esc_html( $sender_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Renders the recipient filter.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function do_recipient_filter() {
		?>
		<label for="edd-email-recipient-filter" class="screen-reader-text">
			<?php esc_html_e( 'Filter by recipient', 'easy-digital-downloads' ); ?>
		</label>
		<select id="edd-email-recipient-filter" name="recipient">
			<option value="">
				<?php esc_html_e( 'All Recipients', 'easy-digital-downloads' ); ?>
			</option>
			<?php foreach ( $this->registry->get_recipients() as $recipient_key => $recipient_label ) : ?>
				<option value="<?php echo esc_attr( $recipient_key ); ?>">
					<?php echo esc_html( $recipient_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Renders the context filter.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function do_context_filter() {
		?>
		<label for="edd-email-context-filter" class="screen-reader-text">
			<?php esc_html_e( 'Filter by context', 'easy-digital-downloads' ); ?>
		</label>
		<select id="edd-email-context-filter" name="context">
			<option value="">
				<?php esc_html_e( 'All Contexts', 'easy-digital-downloads' ); ?>
			</option>
			<?php foreach ( $this->registry->get_contexts() as $context_key => $context_label ) : ?>
				<option value="<?php echo esc_attr( $context_key ); ?>">
					<?php echo esc_html( $context_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Adds extra email data to the given item.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $item         The item to add extra email data to.
	 * @return string
	 */
	private function maybe_add_extra_email_data( $item ) {
		$extra_content = apply_filters( 'edd_email_list_table_extra_content', array(), $item );
		if ( empty( $extra_content ) ) {
			return;
		}

		$tooltip = new \EDD\HTML\Tooltip(
			array(
				'dashicon' => 'dashicons-info-outline',
				'content'  => implode( '<br>', $extra_content ),
			)
		);
		return $tooltip->get();
	}

	/**
	 * Retrieves the CSS class for a table row. This allows the table to mimic the WordPress
	 * Core "striped" table output, but we have to do it manually.
	 *
	 * @since 3.3.0
	 * @return string The CSS class for the table row.
	 */
	private function get_row_class() {
		static $row_class = '';
		$row_class        = empty( $row_class ) ? 'alternate' : '';
		$class            = 'edd-list-table__item';

		return empty( $row_class ) ? $class : $class . ' ' . $row_class;
	}

	/**
	 * Gets the value from an array.
	 *
	 * @since 3.3.0
	 * @param string $value   The selected value.
	 * @param array  $options The array of options.
	 * @return string
	 */
	private function get_value_from_array( $value, $options ) {
		return array_key_exists( $value, $options ) ? $options[ $value ] : $value;
	}
}
