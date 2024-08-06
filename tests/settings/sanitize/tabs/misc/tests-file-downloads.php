<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Misc\FileDownloads;
use EDD\Settings\Setting;

class FileDownloadsSection extends EDD_UnitTestCase {
	public function test_file_downloads_runs_additional_processing() {
		// Set the download method to redirect so we can test the conditional.
		Setting::update( 'download_method', 'redirect' );

		$this->assertSame(
			array(
				'download_method' => 'direct',
			),
			FileDownloads::sanitize(
				array(
					'download_method' => 'direct',
				)
			)
		);
	}
}
