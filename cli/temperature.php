<?php

include_once("Autoload.php");

// to avoid warning on windows
date_default_timezone_set('Europe/Paris');



/**
 * Initialisation des variables globales
 */
function init_global(){
	
	global $CFG;
	global $DATA;
	
	$CFG = new StdClass();
	$DATA = new StdClass();
}


/**
 * Read the XML configuration file
 *
 * @return true if configuration is valid and read, false otherwise
 */
function init_configuration(){

	$cfg = new Configuration("../config.xml");
	$cfg->setLogLevel(LogManager::INFO);
	
	if ($cfg->load()){
		return $cfg->read();
	} else {
		error_log("FATAL : configuration file is not found or not valid.");
		return false;
	}
	
	return true;
}


function main(){
	
	global $DEBUG, $logger;
	
	init_global();
	
	if (!init_configuration()){
		exit(-1);
	}
	
	$sensor = new w1TempSensorManager();
	
	
	$datas = $sensor->read_sensor_data();
	
	global $DATA;
	
	foreach ($sensor->get_sensors_list() as $sensor){
		$name = $sensor->name;
		$logger->info($sensor->description . " : " . $DATA->sensors->$name . "°C");
	}
	
}

$logger = new LogManager();
$logger->setCalledClass("Main");

main();

?>
