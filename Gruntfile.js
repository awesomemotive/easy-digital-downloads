module.exports = function( grunt ) {
	// Load multiple grunt tasks using globbing patterns
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		// Clean up build directory
		clean: {
			main: [ 'build/<%= pkg.name %>' ],
		},

		// Copy the plugin into the build directory
		copy: {
			main: {
				src: [
					'assets/sample-products-import.xml',
					'assets/css/*.min.css',
					'assets/css/admin/style.css',
					'assets/js/*.js',
					'assets/js/*.min.js',
					'assets/js/vendor/**',
					'assets/images/**',
					'includes/**',
					'languages/**',
					'templates/**',
					'*.php',
					'*.txt',
				],
				dest: 'build/<%= pkg.name %>/',
			},
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip',
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>/',
			},
		},

		replace: {
			stripe: {
				options: {
					patterns: [
						{
							match: /edd_stripe_bootstrap/g,
							replacement: 'edd_stripe_core_bootstrap',
							expression: true,
						},
						{
							match: /remove_action(.*);/g,
							replacement: '',
							expression: true,
						}
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: [ 'includes/gateways/stripe/edd-stripe.php' ],
						dest: 'includes/gateways/stripe'
					}
				]
			},
			blocks: {
				options: {
					patterns: [
						{
							match: /init_blocks/g,
							replacement: 'init_core_blocks',
							expression: true,
						},
						{
							match: /remove_action(.*);/g,
							replacement: '',
							expression: true,
						}
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: [ 'includes/blocks/edd-blocks.php' ],
						dest: 'includes/blocks'
					}
				]
			}
		}
	} );

	// Build task(s).
	grunt.registerTask( 'prep', [ 'clean', 'replace' ] );
	grunt.registerTask( 'build', [ 'copy', 'compress' ] );
};
