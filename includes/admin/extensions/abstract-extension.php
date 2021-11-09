<?php

namespace EDD\Admin\Extensions;

use \EDD\Admin\Pass_Manager;

abstract class Extension {

	/**
	 * The product ID. This only needs to be set if the extending class is
	 * for a single product.
	 *
	 * @since 2.11.x
	 * @var int
	 */
	protected $item_id;

	/**
	 * The required AA pass level.
	 */
	const PASS_LEVEL = Pass_Manager::PERSONAL_PASS_ID;

	/**
	 * The Extension Manager
	 *
	 * @var \EDD\Admin\Extensions\Extension_Manager
	 */
	protected $manager;

	public function __construct() {
		$this->manager = new \EDD\Admin\Extensions\Extension_Manager( static::PASS_LEVEL );
	}

	/**
	 * Gets the configuration for the extension.
	 *
	 * @since 2.11.x
	 * @param bool|int $item_id
	 * @return array
	 */
	abstract protected function get_configuration( $item_id = false );

	/**
	 * Whether the extension is activated.
	 *
	 * @since 2.11.x
	 * @return bool
	 */
	abstract protected function is_activated();

	/**
	 * Output the settings field (installation helper).
	 *
	 * @param array $args
	 * @return void
	 */
	public function settings_field() {
		if ( $this->is_activated() ) {
			return;
		}
		$this->do_single_extension_card();
	}

	/**
	 * Outputs a single extension card.
	 *
	 * @since 2.11.x
	 * @param bool|int $item_id Optional: the individual extension product ID.
	 * @return void
	 */
	public function do_single_extension_card( $item_id = false ) {
		$config       = $this->get_configuration( $item_id );
		$product_data = $this->get_product_data( $item_id );
		if ( ! $product_data ) {
			return;
		}
		$this->manager->do_extension_card(
			$product_data,
			$config,
			$this->get_button_parameters( $config, $product_data ),
			$this->get_link_parameters( $config, $product_data->info->title )
		);
	}

	/**
	 * Gets the product data for a specific extension.
	 *
	 * @param boolean $item_id
	 * @return bool|object False if there is no data; API object if there is.
	 */
	public function get_product_data( $item_id = false ) {
		$body = $this->get_api_body();
		if ( empty( $body ) ) {
			return false;
		}
		require_once EDD_PLUGIN_DIR . 'includes/admin/extensions/class-extensions-api.php';
		$api          = new ExtensionsAPI();
		$product_data = $api->get_product_data( $body, $this->item_id );
		if ( ! $product_data ) {
			return false;
		}

		return $this->item_id ? $product_data : $product_data[ $item_id ];
	}

	/**
	 * Gets the array for the body of the API request.
	 * Classes may need to override this (for example, to query a specific tag).
	 *
	 * @return array
	 */
	protected function get_api_body() {
		return array(
			'product' => $this->item_id,
		);
	}

	/**
	 * Gets the type for the button data-type attribute.
	 * This is intended to sync with the Products API request.
	 * Default is product.
	 *
	 * Really a shim for array_key_first.
	 *
	 * @param array $array
	 * @return void
	 */
	private function get_type( array $array ) {
		if ( function_exists( 'array_key_first' ) ) {
			return array_key_first( $array );
		}
		foreach ( $array as $key => $unused ) {
			return $key;
		}

		return 'product';
	}

	/**
	 * Gets the button parameters.
	 * Classes should not need to replace this method.
	 *
	 * @param array  $config       The array of provided data about the extension.
	 * @param object $product_data The extension data returned from the Products API.
	 * @return array
	 */
	protected function get_button_parameters( $config, $product_data ) {
		$item_id = ! empty( $product_data->info->id ) ? $product_data->info->id : $this->item_id;
		$body    = $this->get_api_body();
		$type    = $this->get_type( $body );
		$id      = $body[ $type ];
		$button  = array(
			'type'    => $type,
			'id'      => $id,
			'product' => $item_id,
		);
		// If the extension is not installed, the button will prompt to install and activate it.
		if ( ! $this->manager->is_plugin_installed( $config['basename'] ) ) {
			if ( $this->manager->pass_can_download() ) {
				$button['action'] = 'install';
				/* translators: The extension name. */
				$button['button_text'] = sprintf( __( 'Install & Activate %s', 'easy-digital-downloads' ), $product_data->info->title );
			} else {
				$button = array(
					/* translators: The extension name. */
					'button_text' => sprintf( __( 'Upgrade Today to Access %s!', 'easy-digital-downloads' ), $product_data->info->title ),
					'href'        => ! empty( $config['upgrade_url'] ) ? $config['upgrade_url'] : 'https://easydigitaldownloads.com/pricing',
					'new_tab'     => true,
					'type'        => $type,
				);
			}
		} elseif ( ! $this->is_activated() ) {
			// If the extension is installed, but not activated, the button will prompt to activate it.
			$button['plugin'] = $config['basename'];
			$button['action'] = 'activate';
			/* translators: The extension name. */
			$button['button_text'] = sprintf( __( 'Activate %s', 'easy-digital-downloads' ), $product_data->info->title );
		}

		return $button;
	}

	/**
	 * Gets the array of parameters for the link to configure the extension.
	 *
	 * @since 2.11.x
	 * @param array  $config  The array of provided data about the extension.
	 * @param object $title   The extension name.
	 * @return array
	 */
	protected function get_link_parameters( $config, $title ) {
		return array(
			/* translators: The extension name. */
			'button_text' => sprintf( __( 'Configure %s', 'easy-digital-downloads' ), $title ),
			'href'        => add_query_arg(
				array(
					'post_type' => 'download',
					'page'      => 'edd-settings',
					'tab'       => $config['tab'],
					'section'   => $config['section'],
				),
				admin_url( 'edit.php' )
			),
		);
	}
}
