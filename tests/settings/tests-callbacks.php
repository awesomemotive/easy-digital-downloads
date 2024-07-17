<?php

namespace EDD\Tests\Settings;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Callbacks extends EDD_UnitTestCase {

	public function test_select() {
		$args = array(
			'id'      => 'email_template',
			'name'    => __( 'Template', 'easy-digital-downloads' ),
			'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads' ),
			'options' => edd_get_email_templates(),
		);

		ob_start();
		edd_select_callback( $this->parse_args( $args ) );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'multiple', $output );
		$this->assertStringContainsString( 'name="edd_settings[email_template]"', $output );
	}

	public function test_multiple_select() {
		$args = array(
			'id'          => 'edd_das_service_categories',
			'name'        => __( 'Downloads as Services', 'easy-digital-downloads' ),
			'desc'        => __( 'Select the categories that contain services, or products with no downloadable files.', 'easy-digital-downloads' ),
			'options'     => array(
				'1' => 'Category 1',
				'2' => 'Category 2',
				'3' => 'Category 3',
			),
			'multiple'    => true,
			'chosen'      => true,
			'placeholder' => __( 'Select categories', 'easy-digital-downloads' ),
			'std'         => array(),
		);

		ob_start();
		edd_select_callback( $this->parse_args( $args ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'multiple', $output );
		$this->assertStringContainsString( 'name="edd_settings[edd_das_service_categories][]"', $output );
	}

	public function test_checkbox_toggle() {
		$args = array(
			'id'    => 'enable_public_request_logs',
			'name'  => __( 'Request Logs', 'easy-digital-downloads' ),
			'check' => __( 'Log public API requests.', 'easy-digital-downloads' ),
			'desc'  => __( 'Authenticated requests to the EDD API are always logged.', 'easy-digital-downloads' ),
			'type'  => 'checkbox_toggle',
		);

		ob_start();
		edd_checkbox_toggle_callback( $this->parse_args( $args ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'name="edd_settings[enable_public_request_logs]"', $output );
		$this->assertStringContainsString( 'type="checkbox"', $output );
		$this->assertStringContainsString( 'class="edd-toggle', $output );
	}

	public function test_upload() {
		$args = array(
			'id'   => 'email_logo',
			'name' => __( 'Logo', 'easy-digital-downloads' ),
			'desc' => __( 'Upload or choose a logo to be displayed at the top of sales receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads' ),
			'type' => 'upload',
		);

		ob_start();
		edd_upload_callback( $this->parse_args( $args ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'name="edd_settings[email_logo]"', $output );
		$this->assertStringContainsString( 'type="text"', $output );
		$this->assertStringContainsString( 'class="regular-text"', $output );

	}

	private function parse_args( $args ) {
		return wp_parse_args(
			$args,
			array(
				'id'            => null,
				'desc'          => '',
				'name'          => '',
				'size'          => null,
				'options'       => '',
				'std'           => '',
				'min'           => null,
				'max'           => null,
				'step'          => null,
				'chosen'        => null,
				'multiple'      => null,
				'placeholder'   => null,
				'allow_blank'   => true,
				'readonly'      => false,
				'faux'          => false,
				'tooltip_title' => false,
				'tooltip_desc'  => false,
				'field_class'   => '',
				'label_for'     => false
			)
		);
	}
}
