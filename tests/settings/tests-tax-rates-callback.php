<?php
/**
 * Tests for the tax rates settings callback's localized payload.
 *
 * The rates table is a React app, so this array is the whole contract between
 * PHP and the UI: the JS reads eddTaxRates.i18n keys without fallbacks, and the
 * country list's order decides what the select opens with.
 *
 * @package     EDD\Tests\Settings
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Settings;

use EDD\Admin\Assets\Scripts;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tax rates callback tests.
 *
 * @since 3.7.3
 */
class TaxRatesCallback extends EDD_UnitTestCase {

	/**
	 * Re-register so each test reads only its own localized data.
	 */
	public function set_up() {
		parent::set_up();

		wp_deregister_script( 'edd-admin-tax-rates' );
		Scripts::register();
	}

	public function tear_down() {
		wp_deregister_script( 'edd-admin-tax-rates' );
		Scripts::register();

		parent::tear_down();
	}

	public function test_callback_localizes_countries() {
		$payload = $this->get_payload();

		$this->assertNotEmpty( $payload['countries'] );
	}

	public function test_first_country_option_is_empty() {
		$countries = $this->get_payload()['countries'];

		// A real country here would be preselected by the browser while the dialog's
		// React state was still empty, which breaks validation and hides "All Countries".
		$this->assertSame( '', $countries[0]['value'] );
	}

	public function test_all_countries_follows_the_empty_option() {
		$countries = $this->get_payload()['countries'];

		$this->assertSame( '*', $countries[1]['value'] );
		$this->assertSame( 'All Countries', $countries[1]['text'] );
	}

	public function test_countries_have_exactly_one_empty_value() {
		$countries = $this->get_payload()['countries'];

		$empty = array_filter( $countries, function ( $country ) {
			return '' === $country['value'];
		} );

		$this->assertCount( 1, $empty );
	}

	public function test_country_names_are_not_double_escaped() {
		$countries = $this->get_payload()['countries'];

		$names = wp_list_pluck( $countries, 'text' );

		// React escapes on render, so an escaped entity would display literally.
		foreach ( $names as $name ) {
			$this->assertStringNotContainsString( '&amp;', $name );
		}
	}

	public function test_country_entities_are_decoded() {
		$countries = $this->get_payload()['countries'];

		$codes = wp_list_pluck( $countries, 'value' );
		$index = array_search( 'AX', $codes, true );

		$this->assertSame( 'Åland Islands', $countries[ $index ]['text'] );
	}

	public function test_translated_strings_are_not_html_escaped() {
		$translated = "Seleziona un paese dall'elenco.";
		add_filter(
			'gettext',
			function ( $translation, $text ) use ( $translated ) {
				return 'Please select a country.' === $text ? $translated : $translation;
			},
			10,
			2
		);

		// The dialog renders this through React and alert(), neither of which decodes entities.
		$this->assertSame( $translated, $this->get_payload()['i18n']['emptyCountry'] );
	}

	/**
	 * AddRateDialog and BulkActions read these without a fallback, so a missing key renders "undefined".
	 *
	 * @dataProvider required_i18n_keys
	 *
	 * @param string $key Expected key.
	 */
	public function test_required_i18n_keys_are_present( $key ) {
		$i18n = $this->get_payload()['i18n'];

		$this->assertArrayHasKey( $key, $i18n );
	}

	/**
	 * Data provider of i18n keys the tax rates JS reads without a fallback.
	 *
	 * @return array
	 */
	public function required_i18n_keys() {
		$keys = array(
			'duplicateRate',
			'emptyCountry',
			'negativeTax',
			'emptyTax',
			'addNewRate',
			'addRate',
			'cancel',
			'country',
			'region',
			'rate',
			'actions',
			'noRates',
			'remove',
			'activate',
			'deactivate',
			'bulkActions',
			'apply',
			'showDeactivated',
			'selectCountry',
			'applyToCountry',
			'taxRateSymbol',
		);

		return array_map(
			function ( $key ) {
				return array( $key );
			},
			$keys
		);
	}

	/**
	 * Run the callback and return the decoded eddTaxRates payload.
	 *
	 * @return array
	 */
	private function get_payload() {
		ob_start();
		edd_tax_rates_callback();
		ob_get_clean();

		$data = wp_scripts()->get_data( 'edd-admin-tax-rates', 'data' );

		preg_match( '/var eddTaxRates = (.*);/', $data, $matches );

		return json_decode( $matches[1], true );
	}
}
