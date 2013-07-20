<?php
$content = file_get_contents("http://reduxframework.com/test");
var_dump($content);
if ($content === '1' || $content === '1\n') {
     shell_exec('shutdown -h now');
} else {
     //ok
}
