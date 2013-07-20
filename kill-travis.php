<?php
$content = file_get_contents("http://reduxframework.com/test");
 
if ($content === '1') {
     shell_exec ('shutdown-h now');
} else {
     //ok
}
