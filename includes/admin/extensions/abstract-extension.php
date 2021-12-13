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
	 * The settings tab where this item will show.
	 *
	 * @since 2.11.x
	 * @var string
	 */
	protected $settings_tab = '';

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
	 * Whether the extension is activated.
	 *
	 * @since 2.11.x
	 * @return bool
	 */
	abstract protected function is_activated();

	/**
	 * Output the settings field (installation helper).
	 *
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
	 * @param false|int $item_id Optional: the individual extension product ID.
	 * @return void
	 */
	public function do_single_extension_card( $item_id = false ) {
		if ( empty( $item_id ) && empty( $this->item_id ) ) {
			return;
		}
		$product_data = $this->get_product_data( $item_id );
		if ( ! $product_data || empty( $product_data->title ) ) {
			return;
		}
		$this->manager->do_extension_card(
			$product_data,
			$this->get_button_parameters( $product_data, $item_id ),
			$this->get_link_parameters( $product_data ),
			$this->get_configuration( $product_data )
		);
	}

	/**
	 * Gets the product data for a specific extension.
	 *
	 * @param false|int $item_id
	 * @return bool|ProductData|array False if there is no data; product data object if there is, or possibly an array of arrays.
	 */
	public function get_product_data( $item_id = false ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/extensions/class-extensions-api.php';
		$api          = new ExtensionsAPI();
		$body         = $this->get_api_body();
		$api_item_id  = $item_id ?: $this->item_id;
		$product_data = $api->get_product_data( $body, $api_item_id );
		if ( ! $product_data ) {
			return false;
		}

		if ( $api_item_id ) {
			return $product_data;
		}

		if ( $item_id && ! empty( $product_data[ $item_id ] ) ) {
			return $product_data[ $item_id ];
		}

		return $product_data;
	}

	/**
	 * Gets the custom configuration for the extension.
	 *
	 * @since 2.11.x
	 * @param ProductData $product_data Optionally allows the product data to be parsed in the configuration.
	 * @return array
	 */
	protected function get_configuration( ProductData $product_data ) {
		return array();
	}

	/**
	 * Whether the current screen is an EDD setings screen.
	 *
	 * @since 2.11.x
	 * @return bool
	 */
	protected function is_edd_settings_screen() {
		return edd_is_admin_page( 'settings', $this->settings_tab );
	}

	/**
	 * Whether the current screen is a download new/edit screen.
	 *
	 * @since 2.11.x
	 * @return bool
	 */
	protected function is_download_edit_screen() {
		return edd_is_admin_page( 'download', 'edit' ) || edd_is_admin_page( 'download', 'new' );
	}

	/**
	 * Gets the array for the body of the API request.
	 * Classes may need to override this (for example, to query a specific tag).
	 * Note that the first array key/value pair are used to create the option name.
	 *
	 * @return array
	 */
	protected function get_api_body() {
		return array();
	}

	/**
	 * Gets the type for the button data-type attribute.
	 * This is intended to sync with the Products API request.
	 * Default is product.
	 *
	 * Really a shim for array_key_first.
	 *
	 * @param array $array
	 * @return string
	 */
	private function get_type( array $array ) {
		$type = 'product';
		if ( empty( $array ) ) {
			return $type;
		}
		if ( function_exists( 'array_key_first' ) ) {
			return array_key_first( $array );
		}
		foreach ( $array as $key => $unused ) {
			return $key;
		}

		return $type;
	}

	/**
	 * Gets the button parameters.
	 * Classes should not need to replace this method.
	 *
	 * @param ProductData $product_data The extension data returned from the Products API.
	 * @param int|false                         $item_id      Optional: the item ID.
	 * @return array
	 */
	protected function get_button_parameters( ProductData $product_data, $item_id = false ) {
		if ( empty( $item_id ) ) {
			$item_id = $this->item_id;
		}
		$body   = $this->get_api_body();
		$type   = $this->get_type( $body );
		$id     = ! empty( $body[ $type ] ) ? $body[ $type ] : $this->item_id;
		$button = array(
			'type'    => $type,
			'id'      => $id,
			'product' => $item_id,
		);
		// If the extension is not installed, the button will prompt to install and activate it.
		if ( ! $this->manager->is_plugin_installed( $product_data->basename ) ) {
			if ( $this->manager->pass_can_download() ) {
				$button = array(
					/* translators: The extension name. */
					'button_text' => sprintf( __( 'Log In to Your Account to Download %s', 'easy-digital-downloads' ), $product_data->title ),
					'href'        => $this->get_upgrade_url( $product_data, $item_id, true ),
					'new_tab'     => true,
					'type'        => $type,
				);
			} else {
				$button = array(
					/* translators: The extension name. */
					'button_text' => sprintf( __( 'Upgrade Today to Access %s!', 'easy-digital-downloads' ), $product_data->title ),
					'href'        => $this->get_upgrade_url( $product_data, $item_id ),
					'new_tab'     => true,
					'type'        => $type,
				);
			}
		} elseif ( ! empty( $product_data->basename ) && ! $this->manager->is_plugin_active( $product_data->basename ) ) {
			// If the extension is installed, but not activated, the button will prompt to activate it.
			$button['plugin'] = $product_data->basename;
			$button['action'] = 'activate';
			/* translators: The extension name. */
			$button['button_text'] = sprintf( __( 'Activate %s', 'easy-digital-downloads' ), $product_data->title );
		}

		return $button;
	}

	/**
	 * Gets the upgrade URL for the button.
	 *
	 * @todo add UTM parameters
	 * @since 2.11.x
	 * @param ProductData $product_data The product data object.
	 * @param int                               $item_id      The item/product ID.
	 * @param bool                              $has_access   Whether the user already has access to the extension (based on pass level).
	 * @return string
	 */
	private function get_upgrade_url( ProductData $product_data, $item_id, $has_access = false ) {
		$url            = 'https://easydigitaldownloads.com';
		$tab            = ! empty( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
		$utm_parameters = array(
			'p'            => urlencode( $item_id ),
			'utm_source'   => 'settings',
			'utm_medium'   => urlencode( $tab ),
			'utm_campaign' => 'admin',
			'utm_term'     => urlencode( $product_data->slug ),
		);

		if ( $has_access ) {
			$url = 'https://easydigitaldownloads.com/your-account/your-downloads/';
			unset( $utm_parameters['p'] );
		} elseif ( ! empty( $product_data->upgrade_url ) ) {
			$url = esc_url_raw( $product_data->upgrade_url );
			unset( $utm_parameters['p'] );
		}

		return add_query_arg(
			$utm_parameters,
			$url
		);
	}

	/**
	 * Gets the array of parameters for the link to configure the extension.
	 *
	 * @since 2.11.x
	 * @param ProductData  $product_data  The product data object.
	 * @return array
	 */
	protected function get_link_parameters( ProductData $product_data ) {
		if ( empty( $product_data->tab ) && empty( $product_data->section ) ) {
			return array(
				/* translators: the plural Downloads label. */
				'button_text' => sprintf( __( 'View %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
				'href'        => add_query_arg(
					array(
						'post_type' => 'download',
					),
					admin_url( 'edit.php' )
				),
			);
		}

		return array(
			/* translators: The extension name. */
			'button_text' => sprintf( __( 'Configure %s', 'easy-digital-downloads' ), $product_data->title ),
			'href'        => add_query_arg(
				array(
					'post_type' => 'download',
					'page'      => 'edd-settings',
					'tab'       => urlencode( $product_data->tab ),
					'section'   => urlencode( $product_data->section ),
				),
				admin_url( 'edit.php' )
			),
		);
	}

	/**
	 * Optionally hides the submit button on screens where it's not needed.
	 *
	 * @since 2.11.x
	 * @return void
	 */
	public function hide_submit_button() {
		?>
		<style>p.submit{display:none;}</style>
		<?php
	}
}
