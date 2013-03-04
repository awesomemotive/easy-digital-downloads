<?php
// Load WordPress test environment
// https://github.com/nb/wordpress-tests
echo "\n\nWelcome to the EDD PHPUnit Test Suite \n";
echo "Version: 0.9.5 \n";
echo "Author: Chris Christoff (@chriscct7) \n\n\n";
echo "Preparing to load WordPress TU Bootstrap File \n";

$path = './travis/vendor/wordpress-tests/bootstrap.php';
if( file_exists( $path ) ) {
    require_once $path;
} else {
    exit( "Copy of bootstrap file failed in travis.yml \n" );
}
echo "WordPress TU Boostrap File Loaded \n\n";
echo "Preparing to load EDD Plugin from Mainfile \n";
$loader= './easy-digital-downloads.php';
if( file_exists( $loader ) ) {
    require_once $loader;
} else {
    exit( "Couldn't load EDD \n" );
}
echo "EDD Loaded \n";
echo "EDD Plugin Loaded from Mainfile \n\n";
echo "Preparing to execute PHPUnit Tests \n";
$_SESSION['travis']='true';