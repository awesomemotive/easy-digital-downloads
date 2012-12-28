<?php
// Load WordPress test environment
// https://github.com/nb/wordpress-tests
// The path to wordpress-tests

$path = './travis/vendor/wordpress-tests/bootstrap.php';
if( file_exists( $path ) ) {
    require_once $path;
} else {
    exit( "Couldn't find wordpress-tests please run\n git submodule init && git submodule update\n" );
}
$loader= './easy-digital-downloads.php';
if( file_exists( $loader ) ) {
    require_once $loader;
} else {
    exit( "Couldn't find EDD plugin mainfile" );
}
$_SESSION['travis']='true';