// Run from the plugin root: npm run test:unit

import { cssValue, cssString, cssSlug } from '../../includes/blocks/src/downloads/css-sanitizers';

// The server localizes this list into EDDBlocks; the fixture is a sample of it.
beforeAll( () => {
	globalThis.EDDBlocks = {
		css_value_functions: [ 'rgb', 'rgba', 'hsl', 'var', 'calc' ],
	};
} );

afterAll( () => {
	delete globalThis.EDDBlocks;
} );

const ruleClosingValue = 'solid} .some-selector{outline:9px solid red} body{background-image:url(https://example.com/x.png)';

describe( 'cssValue', () => {
	it.each( [ '#007cba', 'solid', '3px', 'rgb( 0, 124, 186 )', 'var( --wp--preset--color--primary )', 'calc( 1px + 2px )' ] )(
		'keeps %s',
		( value ) => expect( cssValue( value ) ).toBe( value )
	);

	it( 'rejects a value that closes the declaration and rule', () => {
		expect( cssValue( ruleClosingValue ) ).toBe( '' );
	} );

	it( 'rejects a function outside the allow-list', () => {
		expect( cssValue( 'url(https://example.com/x.png)' ) ).toBe( '' );
	} );

	it( 'rejects every function when the server list is absent', () => {
		delete globalThis.EDDBlocks;
		expect( cssValue( 'rgb( 0, 0, 0 )' ) ).toBe( '' );
		globalThis.EDDBlocks = { css_value_functions: [ 'rgb', 'rgba', 'hsl', 'var', 'calc' ] };
	} );

	it.each( [ 'rgb(', '[', '(', 'rgb(1,2,3]', 'calc( 1px + ( 2px )' ] )( 'rejects the unclosed bracket in %s', ( value ) => {
		expect( cssValue( value ) ).toBe( '' );
	} );

	it( 'scans a long run of word characters in linear time', () => {
		const started = Date.now();

		expect( cssValue( 'a'.repeat( 100000 ) ) ).toBe( 'a'.repeat( 100000 ) );
		expect( Date.now() - started ).toBeLessThan( 1000 );
	} );

	it.each( [ '"', "'", '/*', '#fff /* x', 'a"b' ] )( 'rejects %s', ( value ) => {
		expect( cssValue( value ) ).toBe( '' );
	} );
} );

describe( 'cssString', () => {
	it( 'leaves a plain label alone', () => {
		expect( cssString( 'Best Seller!' ) ).toBe( 'Best Seller!' );
	} );

	it( 'escapes a double quote and a backslash', () => {
		expect( cssString( String.raw`Say "hi" \ there` ) ).toBe( String.raw`Say \"hi\" \\ there` );
	} );

	it( 'hex-escapes braces and a single quote', () => {
		expect( cssString( "a{b}c'd" ) ).toBe( String.raw`a\7b b\7d c\27 d` );
	} );

	it( 'hex-escapes angle brackets and flattens newlines', () => {
		expect( cssString( '</style>\nx' ) ).toBe( String.raw`\3c /style\3e  x` );
	} );
} );

describe( 'cssSlug', () => {
	it( 'keeps a palette slug and strips anything else', () => {
		expect( cssSlug( 'vivid-red_2' ) ).toBe( 'vivid-red_2' );
		expect( cssSlug( 'x ); } body{' ) ).toBe( 'xbody' );
		expect( cssSlug( 'foo%2Fbar' ) ).toBe( 'foobar' );
	} );
} );
