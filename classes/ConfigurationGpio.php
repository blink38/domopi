<?php

/**
 * Classe de lecture de la configuration des différents modes de fonctionnement.
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationGpio extends BaseClass {

	private $gpio_id = 0;
	
	/**
	 * Lit la section <modes> du fichier de configuration
	 *
	 */
	public function read_gpios(DOMNode $config){

		global $CFG;
		$CFG->gpios = new StdClass();
		
		$this->gpio_id = 1;
		
		$this->getLogger()->debug("reading gpios section configuration");
		
		// lecture des paramètres généraux des modes
		$this->read_gpio_configuration($config);
		
		// lecture de chacun des modes
		$gpios = $config->getElementsByTagName("gpio");
		$a_gpio = array();

		foreach ($gpios as $xml_gpio){

			$gpio = new StdClass();
			foreach ($xml_gpio->childNodes as $node){
				
				switch(strtolower($node->nodeName)){
					case "id" :
						$gpio->id = $node->textContent;
						break;
					case "default" :
						$gpio->default = $node->textContent;
						break;
					case "description" :
						$gpio->description = $node->textContent;
						break;
				}
			}
			$gpio->id = $this->gpio_id++;
			array_push($a_gpio, $gpio);
		}		
		
		$CFG->gpios->list = $a_gpio;
		 // print_r($CFG->gpios);
	}

	/** 
	 * Lecture des paramètres généraux des modes
	 *
	 * @param DOMNode $config
	 */
	private function read_gpio_configuration(DOMNode $config){
		
		global $CFG;
		
		$path= $config->getElementsByTagName("path");
		
		if ($path->length > 0){
			// $this->log_debug("AJOUT ".trim($action->item(0)->textContent));
			$CFG->gpios->path = trim($path->item(0)->textContent);
		}
		
		
		
	}
	
}

?>
