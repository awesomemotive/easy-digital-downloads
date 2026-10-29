/**
 * Escapes block attributes for the editor's featured-styling stylesheet.
 *
 * Mirrors the server-side policy in EDD\Utils\Colors.
 *
 * @since 3.7.3
 */

/**
 * Whether a `(` or `[` in the value is never closed.
 *
 * An open bracket in a custom property value takes in every token after it, so the declarations
 * that follow in the same rule are lost. A stray closer only spoils its own declaration.
 *
 * @since 3.7.3
 *
 * @param {string} value The value.
 * @return {boolean} True when a bracket is left open.
 */
function hasUnclosedBracket( value ) {
	const closers = { '(': ')', '[': ']' };
	const open = [];

	for ( const char of value ) {
		if ( closers[ char ] ) {
			open.push( closers[ char ] );
		} else if ( open[ open.length - 1 ] === char ) {
			open.pop();
		}
	}

	return open.length > 0;
}

/**
 * A value safe to interpolate into a declaration.
 *
 * Mirrors EDD\Utils\Colors::css_value(): returns an empty string for anything that could end the
 * declaration or the rule, for a quote or comment opener, for a bracket left open, and for any
 * function the server does not list as one a color or length is written with.
 *
 * @since 3.7.3
 *
 * @param {*} value The submitted value.
 * @return {string} The value, or an empty string.
 */
export function cssValue( value ) {
	const trimmed = String( value ?? '' ).trim();

	if ( /[;{}\\<>"']/.test( trimmed ) || trimmed.includes( '/*' ) || hasUnclosedBracket( trimmed ) ) {
		return '';
	}

	const allowedFunctions = globalThis.EDDBlocks?.css_value_functions ?? [];

	for ( const match of trimmed.matchAll( /(?<![\w-])([\w-]+)\s*\(/g ) ) {
		if ( !allowedFunctions.includes( match[ 1 ].toLowerCase() ) ) {
			return '';
		}
	}

	return trimmed;
}

/**
 * A value safe to interpolate inside a double-quoted CSS string.
 *
 * Mirrors EDD\Utils\Colors::css_string().
 *
 * @since 3.7.3
 *
 * @param {*} text The submitted text.
 * @return {string} The escaped text.
 */
export function cssString( text ) {
	return String( text ?? '' )
		.replace( /[\r\n\f]+/g, ' ' )
		.replace( /[\\"'<>{}]/g, ( char ) => ( {
			'\\': String.raw`\\`,
			'"': String.raw`\"`,
			'\'': String.raw`\27 `,
			'<': String.raw`\3c `,
			'>': String.raw`\3e `,
			'{': String.raw`\7b `,
			'}': String.raw`\7d `,
		}[ char ] ) );
}

/**
 * Reduces a palette slug to the characters sanitize_html_class() keeps.
 *
 * @since 3.7.3
 *
 * @param {*} slug The palette slug.
 * @return {string} The sanitized slug.
 */
export function cssSlug( slug ) {
	return String( slug ?? '' )
		.replace( /%[a-fA-F0-9]{2}/g, '' )
		.replace( /[^A-Za-z0-9_-]/g, '' );
}
