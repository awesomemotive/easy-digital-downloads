/**
 * Internal dependencies
 */
export { setup as setupDownload } from './download.js';
export { setup as setupCheckout } from './checkout.js';

/**
 * Parses an HTML dataset and decodes JSON values.
 *
 * @param {Object} dataset HTML data attributes.
 * @return {Object}
 */
export function parseDataset( dataset ) {
	let data = {};

	for ( const [ key, value ] of Object.entries( dataset ) ) {
		let parsedValue = value;

		try {
			parsedValue = JSON.parse( value );
		} catch ( e ) {}

		data[ key ] = parsedValue;
	}

	return data;
}
