<?php
$content = file_get_contents("http://reduxframework.com/test");

if ( strstr ( $content, '1' ) ) {
     killtravis();
}
