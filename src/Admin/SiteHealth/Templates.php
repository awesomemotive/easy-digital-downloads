<?php
/**
 * Gets the customized template data.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Loads customized template data into Site Health.
 *
 * @since 3.1.2
 */
class Templates {

	/**
	 * Gets the site health fields.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Customized Templates', 'easy-digital-downloads' ),
			'fields' => $this->get_templates(),
		);
	}

	/**
	 * Gets the customized templates.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_templates() {
		$customized_template_files = edd_get_theme_edd_templates();
		$templates                 = array();
		if ( empty( $customized_template_files ) ) {
			$templates['empty'] = array(
				'label' => '',
				'value' => 'No custom templates found.',
			);
		} else {
			foreach ( $customized_template_files as $customized_template_file ) {
				$templates[] = array(
					'label' => '',
					'value' => $customized_template_file,
				);
			}
		}

		return $templates;
	}
}
