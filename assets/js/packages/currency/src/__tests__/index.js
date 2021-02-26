/**
 * Internal dependencies
 */
import { Currency, NumberFormat } from './../';

global.edd_vars = {
	currency: 'EUR',
	currency_sign: '€',
	currency_pos: 'after',
	currency_decimals: 2,
	decimal_separator: ',',
	thousands_separator: '.',
};

describe( 'Currency', () => {
	it( 'should be able to set config on instantiation', () => {
		const currency = new Currency( {
			currency: 'USD',
			currencySymbol: '$',
			currencySymbolPosition: 'before',
			precision: 2,
			decimalSeparator: '.',
			thousandSeparator: ',',
		} );

		expect( currency.config.currency ).toEqual( 'USD' );

		expect( currency.config.currencySymbol ).toEqual( '$' );

		expect( currency.config.currencySymbolPosition ).toEqual( 'before' );

		expect( currency.config.precision ).toEqual( 2 );

		expect( currency.config.decimalSeparator ).toEqual( '.' );

		expect( currency.config.thousandSeparator ).toEqual( ',' );
	} );

	it( 'should be inherit config from edd_vars', () => {
		const currency = new Currency();

		expect( currency.config.currency ).toEqual( 'EUR' );

		expect( currency.config.currencySymbol ).toEqual( '€' );

		expect( currency.config.currencySymbolPosition ).toEqual( 'after' );

		expect( currency.config.precision ).toEqual( 2 );

		expect( currency.config.decimalSeparator ).toEqual( ',' );

		expect( currency.config.thousandSeparator ).toEqual( '.' );
	} );

	describe( 'format', () => {
		let currency;

		beforeEach( () => {
			currency = new Currency( {
				currency: 'USD',
				currencySymbol: '$',
				currencySymbolPosition: 'before',
				precision: 2,
				decimalSeparator: ',',
				thousandSeparator: '.',
			} );
		} );

		it( 'should accept a Number', () => {
			expect( currency.format( Number( 5 ) ) ).toEqual( '$5,00' );
		} );

		it( 'should accept a numeric String', () => {
			expect( currency.format( '1285.50' ) ).toEqual( '$1.285,50' );
		} );

		it( 'should accept an Int', () => {
			expect( currency.format( parseInt( '5.0' ) ) ).toEqual( '$5,00' );
		} );

		it( 'should accept an Float', () => {
			expect( currency.format( parseFloat( '5.0abc' ) ) ).toEqual(
				'$5,00'
			);
		} );

		it( 'should return zero on invalid', () => {
			expect( currency.format( 'abc' ) ).toEqual( '$0,00' );
		} );
	} );

	describe( 'unformat', () => {
		let currency;

		beforeEach( () => {
			currency = new Currency( {
				currency: 'USD',
				currencySymbol: '$',
				currencySymbolPosition: 'before',
				precision: 2,
				decimalSeparator: '.',
				thousandSeparator: ',',
			} );
		} );

		it( 'should return a number', () => {
			expect( currency.unformat( '$73.97' ) ).toEqual( 73.97 );
		} );

		it( 'should return zero on invalid', () => {
			expect( currency.unformat( 'abc' ) ).toEqual( 0 );
		} );
	} );
} );

describe( 'Number', () => {
	it( 'should be able to set config on instantiation', () => {
		const number = new NumberFormat( {
			precision: 2,
			decimalSeparator: '.',
			thousandSeparator: ',',
		} );

		expect( number.config.precision ).toEqual( 2 );

		expect( number.config.decimalSeparator ).toEqual( '.' );

		expect( number.config.thousandSeparator ).toEqual( ',' );
	} );

	it( 'should be inherit config from edd_vars', () => {
		const number = new Currency();

		expect( number.config.precision ).toEqual( 2 );

		expect( number.config.decimalSeparator ).toEqual( ',' );

		expect( number.config.thousandSeparator ).toEqual( '.' );
	} );

	describe( 'format', () => {
		let number;

		beforeEach( () => {
			number = new NumberFormat( {
				precision: 2,
				decimalSeparator: ',',
				thousandSeparator: '.',
			} );
		} );

		it( 'should accept a Number', () => {
			expect( number.format( Number( 5 ) ) ).toEqual( '5,00' );
		} );

		it( 'should accept a numeric String', () => {
			expect( number.format( '5.838,58' ) ).toEqual( '5,84' );
		} );

		it( 'should accept an Int', () => {
			expect( number.format( parseInt( 5 ) ) ).toEqual( '5,00' );
		} );

		it( 'should accept an Float', () => {
			expect( number.format( parseFloat( '5.0abc' ) ) ).toEqual( '5,00' );
		} );

		it( 'should return zero on invalid', () => {
			expect( number.format( 'abc' ) ).toEqual( '0,00' );
		} );
	} );

	describe( 'unformat', () => {
		let number;

		beforeEach( () => {
			number = new NumberFormat( {
				precision: 2,
				decimalSeparator: ',',
				thousandSeparator: '.',
			} );
		} );

		it( 'should return a number', () => {
			expect( number.unformat( '1.283,83' ) ).toEqual( 1283.83 );
		} );

		it( 'should return zero on invalid', () => {
			expect( number.unformat( 'abc' ) ).toEqual( 0 );
		} );
	} );

	describe( 'absint', () => {
		let number;

		beforeEach( () => {
			number = new NumberFormat( {
				precision: 2,
				decimalSeparator: '.',
				thousandSeparator: ',',
			} );
		} );

		it( 'should return a positive number from a positive number', () => {
			expect( number.absint( 5 ) ).toEqual( 5 );
			expect( number.absint( 5.00 ) ).toEqual( 5.00 );
			expect( number.absint( '5.00' ) ).toEqual( 5 );
		} );

		it( 'should return a positive number from a negative number', () => {
			expect( number.absint( -5 ) ).toEqual( 5 );
			expect( number.absint( -5.00 ) ).toEqual( 5.00 );
			expect( number.absint( '-5.00' ) ).toEqual( 5 );
		} );
	} );
} );
