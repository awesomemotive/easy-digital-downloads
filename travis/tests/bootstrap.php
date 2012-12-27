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
//require_once './easy-digital-downloads.php';
$_SESSION['travis']='true';