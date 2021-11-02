<?php

namespace EDD\Admin\Settings;

use \EDD\Admin\Pass_Manager;

abstract class Extension {

	/**
	 * The product ID.
	 *
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
	 * @var \EDD\Admin\Extension_Manager
	 */
	protected $manager;

	public function __construct() {
		$this->manager = new \EDD\Admin\Extension_Manager( static::PASS_LEVEL );
	}

	/**
	 * Gets the configuration for the extension.
	 *
	 * @param bool|int $item_id
	 * @return array
	 */
	abstract protected function get_configuration( $item_id = false );

	/**
	 * Whether the extension is activated.
	 *
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
		?>
		<style>.submit{display:none;}</style>
		<?php
	}

	/**
	 * Outputs a single extension card.
	 *
	 * @return void
	 */
	public function do_single_extension_card( $item_id = false ) {
		$config = $this->get_configuration( $item_id );
		$this->manager->do_extension_card(
			$config['item_id'],
			$this->get_button_parameters( $config ),
			$this->get_link_parameters( $config ),
			$this->is_activated()
		);
	}

	/**
	 * Gets the button parameters.
	 * Classes should not need to replace this method.
	 *
	 * @return array
	 */
	protected function get_button_parameters( $config ) {
		$button = array();
		// If the extension is not installed, the button will prompt to install and activate it.
		if ( ! $this->manager->is_plugin_installed( $config['pro_plugin'] ) ) {
			$download_url = $this->manager->get_download_url( $config['item_id'], 'extension' );
			if ( $this->manager->pass_can_download() && $download_url ) {
				$button['data-plugin'] = $download_url;
				$button['data-action'] = 'install';
				$button['type']        = 'extension';
				/* translators: The extension name. */
				$button['button_text'] = sprintf( __( 'Install & Activate %s', 'easy-digital-downloads' ), $config['name'] );
			} else {
				$button = array(
					/* translators: The extension name. */
					'button_text' => sprintf( __( 'Get %s Today!', 'easy-digital-downloads' ), $config['name'] ),
					'href'        => ! empty( $config['upgrade_url'] ) ? $config['upgrade_url'] : 'https://easydigitaldownloads.com/pricing',
					'new_tab'     => true,
				);
			}
		} elseif ( ! $this->is_activated() ) {
			// If the extension is installed, but not activated, the button will prompt to activate it.
			$button['data-plugin'] = $config['pro_plugin'];
			$button['data-action'] = 'activate';
			/* translators: The extension name. */
			$button['button_text'] = sprintf( __( 'Activate %s', 'easy-digital-downloads' ), $config['name'] );
		}

		return $button;
	}

	/**
	 * Gets the array of parameters for the link to configure the extension.
	 *
	 * @since 2.11.x
	 * @param array $config
	 * @return array
	 */
	protected function get_link_parameters( $config ) {
		return array(
			/* translators: The extension name. */
			'button_text' => sprintf( __( 'Configure %s', 'easy-digital-downloads' ), $config['name'] ),
			'href'        => admin_url( $config['settings_url'] ),
		);
	}
}
