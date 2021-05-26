<?php
/**
 * Currency
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.0
 */

namespace EDD\Currency;

class Currency {

	/**
	 * @var string
	 */
	public $code;

	public $prefix;

	public $suffix;

	public $symbol;

	public $decimal_separator = '.';

	public $thousands_separator = ',';

	public $is_zero_decimal = false;

	public $number_decimals = 2;

	/**
	 * Currency constructor.
	 *
	 * @param string $currency_code
	 */
	public function __construct( $currency_code ) {
		$this->code = strtoupper( $currency_code );

		$this->setup();
	}

	/**
	 * Returns a new currency object.
	 *
	 * @param string $currency_code
	 *
	 * @since 3.0
	 *
	 * @return Currency
	 */
	public static function from_code( $currency_code ) {
		return new self( $currency_code );
	}

	/**
	 * Sets up properties.
	 */
	private function setup() {
		$this->symbol              = $this->get_symbol();
		$this->decimal_separator   = edd_get_option( 'decimal_separator', '.' );
		$this->thousands_separator = edd_get_option( 'thousands_separator', ',' );

		/**
		 * Filters the decimal separator.
		 *
		 * @param string $decimal_separator
		 * @param string $code
		 *
		 * @since 3.0
		 */
		$this->decimal_separator = apply_filters( 'edd_currency_decimal_separator', $this->decimal_separator, $this->code );

		/**
		 * Filters the thousands separator.
		 *
		 * @param string $thousands_separator
		 * @param string $code
		 *
		 * @since 3.0
		 */
		$this->thousands_separator = apply_filters( 'edd_currency_thousands_separator', $this->thousands_separator, $this->code );

		$separator = $this->_has_space_around_symbol() ? ' ' : '';
		if ( 'before' === edd_get_option( 'currency_position', 'before' ) ) {
			$this->prefix = $this->symbol . $separator;
		} else {
			$this->suffix = $separator . $this->symbol;
		}

		$this->is_zero_decimal  = $this->_is_zero_decimal();
		$this->number_decimals  = $this->is_zero_decimal ? 0 : 2;
	}

	private function _has_space_around_symbol() {
		return ! in_array( $this->code, array(
			'GBP',
			'BRL',
			'EUR',
			'USD',
			'AUD',
			'CAD',
			'HKD',
			'MXN',
			'SGD',
			'JPY'
		) );
	}

	/**
	 * Returns the symbol for this currency.
	 * Depending on settings, this will be used as either the prefix or the suffix.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_symbol() {
		switch ( $this->code ) :
			case "GBP" :
				$symbol = '&pound;';
				break;
			case "BRL" :
				$symbol = 'R&#36;';
				break;
			case "EUR" :
				$symbol = '&euro;';
				break;
			case "USD" :
			case "AUD" :
			case "NZD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
				$symbol = '&#36;';
				break;
			case "JPY" :
				$symbol = '&yen;';
				break;
			case "AOA" :
				$symbol = 'Kz';
				break;
			default :
				$symbol = $this->code;
				break;
		endswitch;

		/**
		 * Filters the currency symbol.
		 *
		 * @since unknown
		 *
		 * @param string $symbol Currency symbol.
		 * @param string $code   Currency code.
		 */
		return apply_filters( 'edd_currency_symbol', $symbol, $this->code );
	}

	/**
	 * Determines whether or not the currency is zero-decimal.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	private function _is_zero_decimal() {
		$currencies = array(
			'bif',
			'clp',
			'djf',
			'gnf',
			'jpy',
			'kmf',
			'krw',
			'mga',
			'pyg',
			'rwf',
			'ugx',
			'vnd',
			'vuv',
			'xaf',
			'xof',
			'xpf',
		);

		return in_array( strtolower( $this->code ), $currencies, true );
	}

}
