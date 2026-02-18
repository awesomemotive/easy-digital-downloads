/**
 * External dependencies
 */
const path = require( 'path' );
const webpack = require( 'webpack' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const RTLCSSWebpackPlugin = require( 'rtlcss-webpack-plugin' );
const glob = require( 'glob' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

/**
 * Auto-discover JavaScript entry points.
 *
 * Conventions:
 * - Files named *.entry.js are explicit entry points
 * - index.js files in assets/src/js/admin/* directories become js/admin/* entries
 * - index.js files in assets/src/js/frontend/* directories become js/frontend/* entries
 * - index.js files in assets/src/js/frontend/gateways/* become js/gateways/* entries
 */
const getJsEntryPoints = () => {
	const entries = {};

	// Auto-discover admin page entries (index.js files in admin subdirectories)
	// Exclude utility directories like components and gateways
	const adminIndexFiles = glob.sync( './assets/src/js/admin/*/index.js', {
		ignore: [
			'./assets/src/js/admin/components/**',
			'./assets/src/js/admin/gateways/**',
		]
	} );
	adminIndexFiles.forEach( ( file ) => {
		const dirName = path.basename( path.dirname( file ) );
		entries[ `js/admin/${ dirName }` ] = file;
	} );

	// Auto-discover admin nested entries (e.g., tools/export, emails/list-table, settings/email-tags)
	// Exclude utility directories like components and gateways, and modules imported by parent
	const adminNestedIndexFiles = glob.sync( './assets/src/js/admin/*/*/index.js', {
		ignore: [
			'./assets/src/js/admin/components/**',
			'./assets/src/js/admin/gateways/**',
			'./assets/src/js/admin/orders/order-details/**',
			'./assets/src/js/admin/orders/order-overview/**',
		]
	} );
	adminNestedIndexFiles.forEach( ( file ) => {
		const relativePath = path.relative( './assets/src/js/admin', path.dirname( file ) );
		// Convert path separators to dashes for the entry name
		// e.g., admin/emails/editor -> emails-editor, admin/settings/tax-rates -> settings-tax-rates
		const entryName = relativePath.replace( /[\/\\]/g, '-' );
		entries[ `js/admin/${ entryName }` ] = file;
	} );

	// Auto-discover frontend entries (e.g., checkout)
	const frontendIndexFiles = glob.sync( './assets/src/js/frontend/*/index.js', {
		ignore: [
			'./assets/src/js/frontend/gateways/**',
		]
	} );
	frontendIndexFiles.forEach( ( file ) => {
		const dirName = path.basename( path.dirname( file ) );
		entries[ `js/frontend/${ dirName }` ] = file;
	} );

	// Auto-discover gateway entries - flattened at gateways/ level for easier access
	const gatewayIndexFiles = glob.sync( './assets/src/js/frontend/gateways/*/index.js' );
	gatewayIndexFiles.forEach( ( file ) => {
		const gatewayName = path.basename( path.dirname( file ) );
		entries[ `js/gateways/${ gatewayName }` ] = file;
	} );

	// Auto-discover all .entry.js files for explicit entries
	const entryFiles = glob.sync( './assets/src/js/**/*.entry.js' );
	entryFiles.forEach( ( file ) => {
		const relativePath = path.relative( './assets/src/js', file );
		let entryName = relativePath
			.replace( /\.entry\.js$/, '' )
			.replace( /[\/\\]/g, '-' );

		// Handle naming conventions for different paths
		if ( entryName === 'admin' ) {
			// Root admin file: admin.entry.js -> js/admin/admin
			entryName = 'js/admin/admin';
		} else if ( entryName.startsWith( 'admin-' ) ) {
			// Admin files: admin/foo/bar.entry.js -> js/admin/foo-bar
			entryName = 'js/admin/' + entryName.replace( 'admin-', '' );
		} else if ( entryName.startsWith( 'frontend-gateways-' ) ) {
			// Frontend gateway files: frontend/gateways/stripe/foo.entry.js -> js/gateways/stripe-foo
			entryName = 'js/gateways/' + entryName.replace( 'frontend-gateways-', '' );
		} else if ( entryName.startsWith( 'frontend-' ) ) {
			// Frontend files: frontend/foo.entry.js -> js/frontend/foo
			entryName = 'js/frontend/' + entryName.replace( 'frontend-', '' );
		}

		entries[ entryName ] = file;
	} );

	// Auto-discover pro entries
	const proEntryFiles = glob.sync( './assets/src/pro/js/**/*.entry.js' );
	proEntryFiles.forEach( ( file ) => {
		const relativePath = path.relative( './assets/src/pro/js', file );
		const entryName = relativePath
			.replace( /\.entry\.js$/, '' )
			.replace( /[\/\\]/g, '/' );
		entries[ `pro/js/${ entryName }` ] = file;
	} );

	return entries;
};

/**
 * Auto-discover SCSS entry points.
 *
 * Conventions:
 * - Only top-level .scss files in assets/src/scss/admin/ and assets/src/scss/frontend/ become entries
 * - Subdirectories are for organization - their files are imported, not built separately
 * - Partials (starting with _) are excluded
 */
const getScssEntryPoints = () => {
	const entries = {};

	// Auto-discover top-level SCSS files in admin and frontend (excluding partials)
	const topLevelScss = glob.sync( './assets/src/scss/{admin,frontend}/*.scss', {
		ignore: [
			'./assets/src/scss/{admin,frontend}/_*.scss'
		]
	} );
	topLevelScss.forEach( ( file ) => {
		const fileName = path.basename( file, '.scss' );
		const dirName = path.basename( path.dirname( file ) );
		entries[ `css/${ dirName }/${ fileName }` ] = file;
	} );

	// Auto-discover gateway SCSS files
	const gatewayScss = glob.sync( './assets/src/scss/frontend/gateways/*.scss', {
		ignore: [
			'./assets/src/scss/frontend/gateways/_*.scss'
		]
	} );
	gatewayScss.forEach( ( file ) => {
		const fileName = path.basename( file, '.scss' );
		entries[ `css/gateways/${ fileName }` ] = file;
	} );

	// Auto-discover stripe-specific SCSS files in subdirectories
	const stripeScss = glob.sync( './assets/src/scss/frontend/stripe/*.scss', {
		ignore: [
			'./assets/src/scss/frontend/stripe/_*.scss'
		]
	} );
	stripeScss.forEach( ( file ) => {
		const fileName = path.basename( file, '.scss' );
		entries[ `css/gateways/stripe-${ fileName }` ] = file;
	} );

	// Auto-discover pro SCSS entries - only top-level style.scss files
	const proScss = glob.sync( './assets/src/pro/scss/*/style.scss' );
	proScss.forEach( ( file ) => {
		const dirName = path.basename( path.dirname( file ) );
		entries[ `pro/css/${ dirName }/style` ] = file;
	} );

	return entries;
};

// Webpack configuration.
const config = {
	...defaultConfig,
	resolve: {
		...defaultConfig.resolve,
		modules: [
			`${ __dirname }/assets/src/js`,
			'node_modules',
		],

		// Alias faked packages. One day these may be published...
		alias: {
			...defaultConfig.resolve.alias,
			'@easy-digital-downloads/currency': path.resolve( __dirname, 'assets/src/js/packages/currency/src/index.js' ),
			'@easy-digital-downloads/hooks': path.resolve( __dirname, 'assets/src/js/utilities/hooks.js' ),
			'@easy-digital-downloads/icons': path.resolve( __dirname, 'assets/src/js/utilities/icons.js' ),
			'@easy-digital-downloads/copy': path.resolve( __dirname, 'assets/src/js/utilities/copy.js' ),
			'@easy-digital-downloads/modal': path.resolve( __dirname, 'assets/src/js/utilities/modal.js' ),
		},
	},
	entry: {
		// Automatically discovered entry points
		...getJsEntryPoints(),
		...getScssEntryPoints(),
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets/build' ),
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
		// Note: @wordpress/interactivity is NOT externalized because it uses
		// WordPress script modules, which handle import mapping automatically.
		// The import will be resolved by WordPress Core at runtime.
	},
	plugins: [
		new MiniCSSExtractPlugin( {
			filename: ( pathData ) => {
				const name = pathData.chunk.name;
				if ( name.startsWith( 'pro/css/' ) ) {
					// Pro CSS: pro/css/invoice/style -> pro/css/invoice/style.min.css
					return `${ name }.min.css`;
				}
				return `${ name.replace( '-style', '' ) }.min.css`;
			}
		} ),
		new RemoveEmptyScriptsPlugin(),
		new RTLCSSWebpackPlugin( {
			filename: ( pathData ) => {
				const name = pathData.chunk.name;
				if ( name.startsWith( 'pro/css/' ) ) {
					// Pro RTL CSS: pro/css/invoice/style -> pro/css/invoice/style-rtl.min.css
					return `${ name.replace( '-style', '' ) }-rtl.min.css`;
				}
				return `${ name.replace( '-style', '' ) }-rtl.min.css`;
			}
		} ),
		new webpack.ProvidePlugin( {
			$: 'jquery',
			jQuery: 'jquery'
		} ),
	],
};

// Remove automatic split of style- imports.
// @link https://github.com/WordPress/gutenberg/blob/master/packages/scripts/config/webpack.config.js#L67-L77
delete config.optimization.splitChunks.cacheGroups;

module.exports = config;
