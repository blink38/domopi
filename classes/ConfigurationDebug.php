<?php

/**
 * Classe de lecture de la configuration des debug
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationDebug  extends BaseClass {

	/**
	 * Read the configuration file - section Debug
	 *
	 * @param $config the DOMNode <configuration /> containing the <debug /> data
	 */
	public function read_debug(DOMNode $config){
	
		global $DEBUG;
		
		$this->getLogger()->debug("Initialisation des journaux d'événement");

		$classes = $config->getElementsByTagName("class");

		$a_class = array("" => "INFO" );
		
		foreach ($classes as $xml_class){

			$cfg = new StdClass();
			
			$name = $xml_class->getAttribute("name");
			$cfg->level = $xml_class->getAttribute("level");
			$cfg->libelle = $xml_class->getAttribute("libelle");
			
			if (!empty($name) && !empty($cfg->level) && !empty($cfg->libelle)){
				$a_class[$name] = $cfg;
				$this->getLogger()->debug("init debug for class " .$name . " to " . $cfg->level);
			}
		}		
		
		$DEBUG = $a_class;
		// print_r($DEBUG);
	}
	
	
	
}
?>
