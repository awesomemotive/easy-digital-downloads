/**
 * WordPress dependencies
 */
const wordpressConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...wordpressConfig,
	rootDir: '../../',
	modulePathIgnorePatterns: [
		'build',
		'js/vendor',
	],
};
