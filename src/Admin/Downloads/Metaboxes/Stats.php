<?php
/**
 * Stats metabox.
 *
 * @package   EDD\Admin\Downloads\Metaboxes
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Metaboxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Stats metabox class.
 */
class Stats extends Metabox {

	/**
	 * Metabox ID.
	 *
	 * @var string
	 */
	protected $id = 'edd_product_stats';

	/**
	 * Context.
	 *
	 * @var string
	 */
	protected $context = 'side';

	/**
	 * Priority.
	 *
	 * @var string
	 */
	protected $priority = 'high';

	/**
	 * Gets the metabox title.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_title(): string {
		return sprintf(
			/* translators: %s: Download singular label */
			__( '%s Stats', 'easy-digital-downloads' ),
			edd_get_label_singular(),
		);
	}

	/**
	 * Renders the metabox.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render( \WP_Post $post ) {
		if ( ! current_user_can( 'view_product_stats', $post->ID ) ) {
			return;
		}

		$earnings = edd_get_download_earnings_stats( $post->ID );
		$sales    = edd_get_download_sales_stats( $post->ID );

		$sales_url = add_query_arg(
			array(
				'page'       => 'edd-payment-history',
				'product-id' => urlencode( $post->ID ),
			),
			edd_get_admin_base_url()
		);

		$earnings_report_url = edd_get_admin_url(
			array(
				'page'     => 'edd-reports',
				'view'     => 'downloads',
				'products' => absint( $post->ID ),
			)
		);
		?>

		<p class="product-sales-stats">
			<span class="label"><?php esc_html_e( 'Net Sales:', 'easy-digital-downloads' ); ?></span>
			<span><a href="<?php echo esc_url( $sales_url ); ?>"><?php echo esc_html( $sales ); ?></a></span>
		</p>

		<p class="product-earnings-stats">
			<span class="label"><?php esc_html_e( 'Net Revenue:', 'easy-digital-downloads' ); ?></span>
			<span>
				<a href="<?php echo esc_url( $earnings_report_url ); ?>">
					<?php echo edd_currency_filter( edd_format_amount( $earnings ) ); ?>
				</a>
			</span>
		</p>

		<hr />

		<p class="file-download-log">
			<?php
			$url = edd_get_admin_url(
				array(
					'page'     => 'edd-tools',
					'view'     => 'file_downloads',
					'tab'      => 'logs',
					'download' => absint( $post->ID ),
				)
			);
			?>
			<span>
				<a href="<?php echo esc_url( $url ); ?>">
					<?php esc_html_e( 'View File Download Log', 'easy-digital-downloads' ); ?>
				</a>
			</span>
			<br/>
		</p>
		<?php

		/**
		 * Fires after the stats metabox content.
		 *
		 * @param \EDD_Download $this->download The download object.
		 */
		do_action( 'edd_stats_meta_box', $this->download );
	}
}
