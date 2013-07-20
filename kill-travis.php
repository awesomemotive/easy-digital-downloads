<?php
$content = file_get_contents("http://reduxframework.com/test");

if ( str_replace( '\n', '', $content ) === '1' ) {
     shell_exec('shutdown -h now');
} else {
     //ok
}
