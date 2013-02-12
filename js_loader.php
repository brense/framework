<?php

header("Content-type: application/x-javascript");
$script = str_replace('/', DIRECTORY_SEPARATOR, substr($_SERVER['DOCUMENT_ROOT'], 0, -1) . $_SERVER['REQUEST_URI']);
if(file_exists($script)){
	$js = file_get_contents($script);
	echo $js;
}

?>