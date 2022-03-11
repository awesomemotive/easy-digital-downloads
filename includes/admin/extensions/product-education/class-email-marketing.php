<?php
/**
 * Email Marketing
 *
 * Manages automatic installation/activation for email marketing extensions.
 *
 * @package     EDD
 * @subpackage  EmailMarketing
 * @copyright   Copyright (c) 2021, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.11.4
 */
namespace EDD\Admin\Settings;

use \EDD\Admin\Extensions\Extension;

class EmailMarketing extends Extension {

	/**
	 * The EDD settings tab where this extension should show.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	protected $settings_tab = 'marketing';

	/**
	 * The settings section for this item.
	 *
	 * @since 2.11.5
	 * @var string
	 */
	protected $settings_section = 'email_marketing';

	public function __construct() {
		add_filter( 'edd_settings_sections_marketing', array( $this, 'add_section' ) );
		add_action( 'edd_settings_tab_top_marketing_email_marketing', array( $this, 'field' ) );

		parent::__construct();
	}

	/**
	 * Adds an email marketing section to the Marketing tab.
	 *
	 * @since 2.11.4
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections ) {
		if ( ! $this->is_edd_settings_screen() ) {
			return $sections;
		}
		$product_data = $this->get_product_data();
		if ( ! $product_data || ! is_array( $product_data ) ) {
			return $sections;
		}
		$sections[ $this->settings_section ] = __( 'Email Marketing', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * Gets the customized configuration for the extension card.
	 *
	 * @since 2.11.4
	 * @param \EDD\Admin\Extensions\ProductData $product_data The product data object.
	 * @return array
	 */
	protected function get_configuration( \EDD\Admin\Extensions\ProductData $product_data ) {
		$configuration = array();
		if ( ! empty( $product_data->title ) ) {
			/* translators: the product name */
			$configuration['title'] = sprintf( __( 'Get %s Today!', 'easy-digital-downloads' ), $product_data->title );
		}

		return $configuration;
	}

	/**
	 * Adds the email marketing extensions as cards.
	 *
	 * @since 2.11.4
	 * @return void
	 */
	public function field() {
		$this->hide_submit_button();
		if ( $this->is_activated() ) {
			printf( '<p>%s</p>', esc_html__( 'Looks like you have an email marketing extension installed, but we support more providers!', 'easy-digital-downloads' ) );
		}
		?>
		<div class="edd-extension-manager__card-group">
			<?php
			foreach ( $this->get_product_data() as $item_id => $extension ) {
				$this->do_single_extension_card( $item_id );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Overrides the body array sent to the Products API.
	 *
	 * @since 2.11.4
	 * @return array
	 */
	protected function get_api_body() {
		return array(
			'tag' => 1578,
		);
	}

	/**
	 * Whether any email marketing extension is active.
	 *
	 * @since 2.11.4
	 *
	 * @return bool True if any email marketing extension is active.
	 */
	protected function is_activated() {
		foreach ( $this->get_product_data() as $extension ) {
			// The data is stored in the database as an array--at this point it has not been converted to an object.
			if ( ! empty( $extension['basename'] ) && $this->manager->is_plugin_active( $extension['basename'] ) ) {
				return true;
			}
		}

		return false;
	}
}

new EmailMarketing();
