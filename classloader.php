<?php
	spl_autoload_register(function($className) {
		$CLASS_ROOT = dirname(__FILE__) . '/utilities/';
		$class_path = $CLASS_ROOT . "$className.php";
		if(file_exists($class_path)) require_once($class_path);
	});
?>
