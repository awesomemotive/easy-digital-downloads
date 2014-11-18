module.exports = function(grunt) {

// Load multiple grunt tasks using globbing patterns
require('load-grunt-tasks')(grunt);

// Project configuration.
grunt.initConfig({
  pkg: grunt.file.readJSON('package.json'),

    makepot: {
      target: {
        options: {
          domainPath: '/languages/',    // Where to save the POT file.
          exclude: ['build/.*'],
          mainFile: 'easy-digital-downloads.php',    // Main project file.
          potFilename: 'edd.pot',    // Name of the POT file.
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
            return pot;
          }
        }
      }
    },

    exec: {
      txpull: { // Pull Transifex translation - grunt exec:txpull
        cmd: 'tx pull -a' // Change the percentage with --minimum-perc=yourvalue
      },
      txpush_s: { // Push pot to Transifex - grunt exec:txpush_s
        cmd: 'tx push -s'
      },
    },

        dirs: {
    lang: 'languages',
  },

    potomo: {
      dist: {
        options: {
         poDel: true
        },
        files: [{
         expand: true,
         cwd: '<%= dirs.lang %>',
          src: ['*.po'],
          dest: '<%= dirs.lang %>',
         ext: '.mo',
          nonull: true
      }]
    }
  },

    // Clean up build directory
    clean: {
      main: ['build/<%= pkg.name %>']
    },

    // Copy the theme into the build directory
    copy: {
      main: {
        src:  [
          '**',
          '!node_modules/**',
          '!build/**',
          '!.git/**',
          '!Gruntfile.js',
          '!package.json',
          '!.gitignore',
          '!.gitmodules',
          '!.tx/**',
          '!tests/**',
          '!**/Gruntfile.js',
          '!**/package.json',
          '!**/README.md',
          '!**/*~'
        ],
        dest: 'build/<%= pkg.name %>/'
      }
    },

    //Compress build directory into <name>.zip and <name>-<version>.zip
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

// Default task. - grunt makepot
grunt.registerTask( 'default', 'makepot' );

// Makepot and push it on Transifex task(s).
grunt.registerTask( 'makandpush', [ 'makepot', 'exec:txpush_s' ] );

// Pull from Transifex and create .mo task(s).
grunt.registerTask( 'tx', [ 'exec:txpull', 'potomo' ] );

// Build task(s).
grunt.registerTask( 'build', [ 'clean', 'copy', 'compress' ] );

};
