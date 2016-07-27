<?php

/**
 * Classe de lecture de la configuration des différentes actions
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationActions extends BaseClass {

	private $action_id = 0;
	
	/**
	 * Lit la section <modes> du fichier de configuration
	 *
	 */
	public function read_actions(DOMNode $config){

		global $CFG;
		$CFG->actions = new StdClass();
		
		$this->action_id = 1;
		
		$this->getLogger()->debug("reading actions section configuration");
		
		// lecture de chacun des modes
		$actions = $config->getElementsByTagName("action");
		$a_actions = array();

		foreach ($actions as $xml_action){

			$action = new StdClass();
			$a_gpio = array();
			
			foreach ($xml_action->childNodes as $node){
				
				switch(strtolower($node->nodeName)){
					case "name" :
						$action->name = $node->textContent;
						break;
					case "gpio" :
						$gpio = $this->read_gpio($node);
						array_push($a_gpio,$gpio);
						break;
					case "description" :
						$action->description = $node->textContent;
						break;
				}
			}
			$action->id = $this->action_id++;
			$action->gpio = $a_gpio;
			array_push($a_actions, $action);
		}		
		
		// print_r($a_actions);
		$CFG->actions->list = $a_actions;
	}

	/** 
	 * Lecture d'un gpio
	 *
	 * @param DOMNode $config
	 */
	private function read_gpio(DOMNode $node){
		
		$gpio = new StdClass();
		
		$gpio->name = $node->getAttribute("name");
		$gpio->value = $node->getAttribute("value");
			
		return $gpio;
	}
	
}

?>
