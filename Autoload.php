<?php 

class Autoload {

	public static function autoloader($class){
	
		if (substr($class,0,1) === "i"){
			$classDir = './interfaces/';
		} else {
			$classDir = './classes/';
		}

		$path = $classDir . "$class.php";
		

		if (file_exists($path) && is_readable($path)){
			require $path;
		}	
	}
}

spl_autoload_register('Autoload::autoloader');
?>
