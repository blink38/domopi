<?php

include_once("Autoload.php");

// to avoid warning on windows
date_default_timezone_set('Europe/Paris');

setlocale(LC_ALL, 'fr_FR');


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
 * Parse les options de ligne de commande
 *
 * Ajoute les options dans la variable globale $OPTS
 */
function init_options_from_commandline(){

	global $OPTS;
	$OPTS = new StdClass();

	$longopts = array(
		"reportxml::",
		"report::",
		"verbose",
		"stats",
		"quiet"
	);
	$shortopts = "sr::x::vq";
	
	$options = getopt($shortopts, $longopts);

	if (array_key_exists("quiet",$options) ||array_key_exists("q",$options)){
		$OPTS->quiet = true;
	}

	if (array_key_exists("stats",$options) ||array_key_exists("s",$options)){
		$OPTS->stats = true;
	}
	
	if (array_key_exists("verbose",$options) || array_key_exists("v",$options)){
		$OPTS->verbose = true;
	}

	if (array_key_exists("x",$options)){
		$options['reportxml'] = $options['x'];
	}

	if (array_key_exists("reportxml",$options)){

		if ($options['reportxml'] === false ||$options['reportxml'] === 'stdout'){
			$OPTS->reportxml = 'stdout';
		} else {
			$OPTS->reportxml = $options['reportxml'];
		}
	}

	if (array_key_exists("r",$options)){
		$options['report'] = $options['r'];
	}

	if (array_key_exists("report",$options)){
		if ($options['report'] === false || $options['report'] === 'stdout' ){
			$OPTS->report = 'stdout';
		} else { 
			$OPTS->report = $options['report'];
		}
	}

	/*print_r($options);
	var_dump($options);
	print_r($OPTS);
	*/
	
}

/**
 * Read the XML configuration file
 *
 * @return true if configuration is valid and read, false otherwise
 */
function init_configuration(){

	$cfg = new Configuration("../config.xml");

	global $OPTS;
	if (isset($OPTS->verbose)){
		$cfg->setLogLevel(LogManager::INFO);
	}	

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
	
	$progMgr = new ProgramManager();
	$actionMgr = new ActionManager();
	$modeMgr = new ModeManager();
	$sensor = new w1TempSensorManager();
	$gpioMgr = new GPIOManager();
	$dataMgr = new DataManager();
	
	$dataMgr->record_date();
	
	$sensor->read_sensor_data();
	// $sensor->read_sensor_data_test_2();
	

	// recherche du programme à exécuter.
	$array_id = $progMgr->find_programs();
	$dataMgr->record_programs($array_id);
	
	// retourne une liste de programme. Donc s'il existe plusieurs programmes, un warning est affiché.
	if (count($array_id) > 1){
		$logger->warning("plusieurs programmes ont été trouvés. Vous ne pouvez utiliser qu'un programme à la fois. Le programme utilisé sera le programme avec id=".$array_id[0]);
	}

	$program = $progMgr->get_program_by_id($array_id[0]);
	$logger->info("utilisation du programme ".$program->name);
	
	// recherche des événéments du programme à exécuter.
	$events = $progMgr->get_events_to_apply($program);
	$dataMgr->record_events($events);
	
	// global $CFG;
	// print_r($CFG);
	
	// print_r($events);
	// $events = $progMgr->get_event_from_date($program);
	
	// recherche des conditions qui s'appliquent
	$conditions = $modeMgr->get_conditions_to_apply($events);
	$dataMgr->record_conditions($conditions);
	
	$action = $actionMgr->get_action_to_apply($conditions);
	$dataMgr->record_action($action);

	$logger->info("nouvel état : " . $action);
	
	$gpios = $actionMgr->get_gpio_from_action($action);
	
	$ret = $gpioMgr->write_gpio_from_array($gpios);
	
	// print_r($mode);
	// $p1 = $progMgr->get_program_by_id(0);
	// print_r($p1);

	global $OPTS;
		
	if (isset($OPTS->report)){
		$reportMgr = new ReportTxtManager();
		$report = $reportMgr->get_report();
		if ($OPTS->report === 'stdout'){
			echo $report;
		}
	}
	//error_log($report);

	if (isset($OPTS->reportxml)){
	
		$reportMgr = new ReportXmlManager();
		if ($OPTS->reportxml === 'stdout'){
			$report = $reportMgr->get_report();
			echo($report);
		} else {
			$reportMgr->save_report($OPTS->reportxml);
		}
	}
	//error_log($report);

	if (isset($OPTS->stats)){
		$statsMgr = new StatsManager();
		$statsMgr->record_stats();
	}
}

$logger = new LogManager();
$logger->setCalledClass("Main");

init_options_from_commandline();

main();
?>
