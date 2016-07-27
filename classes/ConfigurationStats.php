<?php

/**
 * Classe de lecture de la configuration des statistiques
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationStats  extends BaseClass {

	/**
	 * Read the configuration file - section Statistics
	 *
	 * @param $config the DOMNode <configuration /> containing the <debug /> data
	 */
	public function read_stats(DOMNode $config){

		global $CFG;
		$CFG->stats = new StdClass();
		
		$this->getLogger()->debug("reading stats section configuration");
		
		// lecture des paramètres généraux des modes
		$this->read_stats_configuration($config);

	}

	/** 
	 * Lecture des paramètres généraux des modes
	 *
	 * @param DOMNode $config
	 */
	private function read_stats_configuration(DOMNode $config){
		
		global $CFG;
		
		$path= $config->getElementsByTagName("path");
		
		if ($path->length > 0){
			// $this->log_debug("AJOUT ".trim($action->item(0)->textContent));
			$CFG->stats->path = trim($path->item(0)->textContent);
		}
		
	}
	
	
	
}
?>
