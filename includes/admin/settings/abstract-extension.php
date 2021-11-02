<?php

namespace EDD\Admin\Settings;

use \EDD\Admin\Pass_Manager;

abstract class Extension {

	/**
	 * The product ID.
	 *
	 * @var [type]
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
		$this->config  = $this->get_configuration();
		$this->manager = new \EDD\Admin\Extension_Manager( static::PASS_LEVEL );
	}

	/**
	 * Gets the configuration for the extension.
	 *
	 * @return array
	 */
	abstract protected function get_configuration();

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
		$this->manager->do_extension_field(
			$this->item_id,
			$this->get_button_parameters(),
			$this->get_link_parameters(),
			$this->is_activated()
		);
		?>
		<style>.submit{display:none;}</style>
		<?php
	}

	/**
	 * Gets the button parameters.
	 * Classes should not need to replace this method.
	 *
	 * @return array
	 */
	protected function get_button_parameters() {
		$button = array();
		// If the extension is not installed, the button will prompt to install and activate it.
		if ( ! $this->manager->is_plugin_installed( $this->config['pro_plugin'] ) ) {
			$download_url = $this->manager->get_download_url( $this->item_id, 'extension' );
			if ( $this->manager->pass_can_download() && $download_url ) {
				$button['data-plugin'] = $download_url;
				$button['data-action'] = 'install';
				$button['type']        = 'extension';
				/* translators: The extension name. */
				$button['button_text'] = sprintf( __( 'Install & Activate %s', 'easy-digital-downloads' ), $this->config['name'] );
			} else {
				$button = array(
					/* translators: The extension name. */
					'button_text' => sprintf( __( 'Get %s Today!', 'easy-digital-downloads' ), $this->config['name'] ),
					'href'        => $this->config['upgrade_url'],
					'new_tab'     => true,
				);
			}
		} elseif ( ! $this->is_activated() ) {
			// If the extension is installed, but not activated, the button will prompt to activate it.
			$button['data-plugin'] = $this->config['pro_plugin'];
			$button['data-action'] = 'activate';
			/* translators: The extension name. */
			$button['button_text'] = sprintf( __( 'Activate %s', 'easy-digital-downloads' ), $this->config['name'] );
		}

		return $button;
	}

	/**
	 * Gets the array of parameters for the link to configure the extension.
	 *
	 * @since 2.11.x
	 * @return array
	 */
	protected function get_link_parameters() {
		return array(
			/* translators: The extension name. */
			'button_text' => sprintf( __( 'Configure %s', 'easy-digital-downloads' ), $this->config['name'] ),
			'href'        => admin_url( $this->config['settings_url'] ),
		);
	}
}
