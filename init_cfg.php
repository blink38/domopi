<?php
	// to avoid warning on windows
	date_default_timezone_set('Europe/Paris');
	setlocale(LC_ALL, 'fr_FR');

	/**
	 * Initialisation des variables globales
	 */
	global $CFG;
	global $DATA;

	$CFG = new StdClass();
	$DATA = new StdClass();

	/**
	 * Read the XML configuration file
	 *
	 * @return true if configuration is valid and read, false otherwise
	 */
	function init_configuration($filename){

		$cfg = new Configuration($filename);

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

?>


