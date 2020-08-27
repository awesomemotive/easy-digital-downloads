module.exports = function(grunt) {

	// Load multiple grunt tasks using globbing patterns
	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		cssmin: {
			options: {
				mergeIntoShorthands: false,
			},
			target: {
				files: [
					{
						expand: true,
						cwd: 'assets/css',
						src: ['edd-admin.css'],
						dest: 'assets/css',
						ext: '.min.css'
					},
					{
						expand: true,
						cwd: 'templates',
						src: ['edd.css'],
						dest: 'templates',
						ext: '.min.css'
					}
				],
			}
		},

		uglify: {
			options: {
				mangle: false,
			},
			target: {
				files: [{
					expand: true,
					cwd: 'assets/js',
					src: [ '*.js', '!*.min.js', '!*jquery*.js' ],
					dest: 'assets/js',
					ext: '.min.js',
					extDot: 'last',
				}]
			}
		},

		checktextdomain: {
			options:{
				text_domain: 'easy-digital-downloads',
				correct_domain: true,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
				]
			},
			files: {
				src: [
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!build/**'// Exclude build/
				],
				expand: true
			}
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages/',    // Where to save the POT file.
					exclude: ['build/.*'],
					mainFile: 'easy-digital-downloads.php',    // Main project file.
					potFilename: 'easy-digital-downloads.pot',    // Name of the POT file.
					potHeaders: {
						poedit: true,                 // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
								},
					type: 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true,    // Whether the POT-Creation-Date should be updated without other changes.
					processPot: function( pot, options ) {
						pot.headers['report-msgid-bugs-to'] = 'https://easydigitaldownloads.com/';
						pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)';
						pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>';
						pot.headers['language'] = 'en_US';
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
								];
									for ( translation in pot.translations[''] ) {
										if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
											if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
												console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
													delete pot.translations[''][ translation ];
												}
											}
										}
						return pot;
					}
				}
			}
		},

		// Clean up build directory
		clean: {
			main: ['build/<%= pkg.name %>']
		},

		// Copy the plugin into the build directory
		copy: {
			main: {
				src:  [
					'assets/**',
					'includes/**',
					'languages/**',
					'templates/**',
					'*.php',
					'*.txt'
				],
				dest: 'build/<%= pkg.name %>/'
			}
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip'
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
			}
		},

	});

	// Build task(s).
	grunt.registerTask( 'build', [ 'cssmin', 'uglify', 'force:checktextdomain', 'makepot', 'clean', 'copy', 'compress' ] );

};
