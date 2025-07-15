<?php
/**
 * ExportLoader.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.8
 */

namespace EDD\Admin\Exports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\FileSystem;

/**
 * Class Loader
 *
 * @since 3.3.8
 */
class Loader implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Gets the events that this class subscribes to.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_reports_tab_export_content_top' => 'export_forms',
			'edd_export_init'                    => 'register_exporters',
		);
	}

	/**
	 * Bootstraps the exporter.
	 *
	 * @since 3.3.8
	 */
	public static function bootstrap() {

		/**
		 * Initializes after the bootstrap has completed.
		 *
		 * @since 3.3.8
		 *
		 * @param ExportRegistry $registry
		 */
		do_action( 'edd_export_init', Registry::instance() );
	}

	/**
	 * Renders the export forms.
	 *
	 * @since 3.3.8
	 */
	public function export_forms() {
		foreach ( Registry::instance()->get_items_by_priority() as $exporter_id => $exporter ) : ?>
			<div class="postbox edd-export-<?php echo esc_attr( sanitize_html_class( $exporter_id ) ); ?>">
				<?php /* Translators: %s name of the exporter tool */ ?>
				<h2 class="hndle">
					<span>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: Name of the exporter tool */
								__( 'Export %s', 'easy-digital-downloads' ),
								esc_html( $exporter['label'] )
							)
						);
						?>
					</span>
				</h2>
				<div class="inside">
					<?php if ( ! empty( $exporter['description'] ) ) : ?>
						<p><?php echo esc_html( $exporter['description'] ); ?></p>
					<?php endif; ?>

					<form id="edd-export-<?php echo esc_attr( sanitize_html_class( $exporter_id ) ); ?>" class="edd-export-form edd-import-export-form" method="POST">
						<?php $this->render_form( $exporter_id, $exporter ); ?>
						<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
						<input type="hidden" name="exporter_id" value="<?php echo esc_attr( $exporter_id ); ?>" />
						<button type="submit" class="button button-secondary">
							<?php echo esc_html( $exporter['button'] ); ?>
						</button>
					</form>
				</div>
			</div>
			<?php
		endforeach;
	}

	/**
	 * Registers the exporters.
	 *
	 * @since 3.3.8
	 * @param Registry $registry Export registry.
	 */
	public function register_exporters( Registry $registry ) {
		static $is_registered;
		if ( $is_registered ) {
			return;
		}
		try {
			foreach ( $this->get_exporters() as $exporter_id => $args ) {
				$registry->register_exporter( $exporter_id, $args );
			}
			$is_registered = true;
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * Gets the core exporters.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private function get_exporters() {
		return array(
			'earnings_report'  => array(
				'label'       => __( 'Earnings Report', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV giving a detailed look into earnings over time.', 'easy-digital-downloads' ),
				'class'       => Exporters\Earnings::class,
				'view'        => 'export-earnings-report',
			),
			'sales_earnings'   => array(
				'label'       => __( 'Sales and Earnings', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of all sales or earnings on a day-by-day basis.', 'easy-digital-downloads' ),
				'class'       => Exporters\SalesEarnings::class,
				'view'        => 'export-sales-earnings',
			),
			'product_sales'    => array(
				'label'       => __( 'Product Sales', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV file containing a record of each sale of a product along with the customer information.', 'easy-digital-downloads' ),
				'class'       => Exporters\ProductSales::class,
				'view'        => 'export-sales',
			),
			'orders'           => array(
				'label'       => __( 'Orders', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of all orders.', 'easy-digital-downloads' ),
				'class'       => Exporters\Orders::class,
				'view'        => 'export-orders',
			),
			'taxed_orders'     => array(
				'label'       => __( 'Taxed Orders', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of all orders, taxed by Country and/or Region.', 'easy-digital-downloads' ),
				'class'       => Exporters\TaxedOrders::class,
				'view'        => 'export-taxed-orders',
			),
			'customers'        => array(
				'label'       => __( 'Customers', 'easy-digital-downloads' ),
				'description' => sprintf(
					/* Translators: %s: Downloads plural label */
					__( 'Download a CSV of customers. Select a taxonomy to see all the customers who purchased %s in that taxonomy.', 'easy-digital-downloads' ),
					edd_get_label_singular()
				),
				'class'       => Exporters\Customers::class,
				'view'        => 'export-customers',
			),
			'taxed_customers'  => array(
				'label'       => __( 'Taxed Customers', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of all customers that were taxed.', 'easy-digital-downloads' ),
				'class'       => Exporters\TaxedCustomers::class,
				'view'        => 'export-taxed-customers',
			),
			'downloads'        => array(
				'label'       => __( 'Downloads', 'easy-digital-downloads' ),
				'description' => sprintf(
					/* translators: %s: Download plural label */
					__( 'Download a CSV of product %1$s.', 'easy-digital-downloads' ),
					edd_get_label_plural( true )
				),
				'class'       => Exporters\Downloads::class,
				'view'        => 'export-downloads',
			),
			'api_requests'     => array(
				'label'       => __( 'API Requests', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of API request logs.', 'easy-digital-downloads' ),
				'class'       => Exporters\ApiRequests::class,
				'view'        => 'export-api-requests',
			),
			'download_history' => array(
				'label'       => __( 'Download History', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of file download logs.', 'easy-digital-downloads' ),
				'class'       => Exporters\DownloadHistory::class,
				'view'        => 'export-download-history',
			),
		);
	}

	/**
	 * Renders the form.
	 *
	 * @since 3.3.8
	 * @param string $exporter_id ID of the exporter.
	 * @param array  $exporter    Exporter data.
	 */
	private function render_form( $exporter_id, $exporter ) {
		$view = $this->get_view( $exporter );
		if ( $view ) {
			include_once $view;
		}

		/**
		 * Used to add custom form fields to an exporter.
		 *
		 * @since 3.3.8
		 * @param string $exporter_id ID of the exporter being rendered.
		 * @param array  $exporter    Exporter data.
		 */
		do_action( 'edd_export_form', $exporter_id, $exporter );
	}

	/**
	 * Gets the view.
	 *
	 * @since 3.3.8
	 * @param array $exporter Exporter data.
	 * @return string|bool
	 */
	private function get_view( $exporter ) {
		if ( empty( $exporter['view'] ) ) {
			return false;
		}
		if ( FileSystem::file_exists( $exporter['view'] ) ) {
			return $exporter['view'];
		}

		$view = EDD_PLUGIN_DIR . "includes/admin/reporting/views/{$exporter['view']}.php";

		return FileSystem::file_exists( $view ) ? $view : false;
	}
}
