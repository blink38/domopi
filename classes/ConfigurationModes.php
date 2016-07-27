<?php

/**
 * Classe de lecture de la configuration des différents modes de fonctionnement.
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationModes extends BaseClass {

	const CONDITION_SUP = "cond_sup";
	const CONDITION_FORCE = "cond_force";
	
	
	private $cond_id = 0;
	
	/**
	 * Lit la section <modes> du fichier de configuration
	 *
	 */
	public function read_modes(DOMNode $config){

		global $CFG;
		$CFG->modes = new StdClass();
		
		$this->cond_id = 0;
		
		$this->getLogger()->debug("reading modes section configuration");
		
		// lecture des paramètres généraux des modes
		$this->read_modes_configuration($config);
		
		// lecture de chacun des modes
		$modes = $config->getElementsByTagName("mode");
		$cmodes = array();

		foreach ($modes as $xml_mode){

			$mode = new StdClass();
			foreach ($xml_mode->childNodes as $node){
				
				switch(strtolower($node->nodeName)){
					case "name" :
						$mode->name = $node->textContent;
						break;
					case "id" :
						$mode->id = $node->textContent;
						break;
					case "description" :
						$mode->description = $node->textContent;
						break;
					case "conditions" :
						$mode->conditions  = $this->read_modes_conditions($node);
						break;
				}
			}
			array_push($cmodes, $mode);
		}		
		
		$CFG->modes->list = $cmodes;
		// print_r($CFG->modes);
	}

	/** 
	 * Lecture des paramètres généraux des modes
	 *
	 * @param DOMNode $config
	 */
	private function read_modes_configuration(DOMNode $config){
		
		global $CFG;
		
		$action= $config->getElementsByTagName("actionpriority");
		
		if ($action->length > 0){
			// $this->log_debug("AJOUT ".trim($action->item(0)->textContent));
			$CFG->modes->actionpriority = explode(",",trim($action->item(0)->textContent));
		}
	
		$approx = $config->getElementsByTagName("temperature_approx");
		$val = 0;

		if ($approx->length > 0){
			if (is_numeric($approx->item(0)->textContent)){
				$val = trim($approx->item(0)->textContent);
			}
		}

		$CFG->modes->temperature_approx  = $val;	
		$this->getLogger()->info("Approximation de température = " . $CFG->modes->temperature_approx);
		
		
	}
	
	/**
	 * Lecture des conditions pour un mode donné
	 *
  	 * @param $node noeud xml à parser
	 *
	 */
	private function read_modes_conditions(DOMNode $config){

		$this->getLogger()->debug("reading conditions");

		$conds = array();
		
		foreach ($config->childNodes as $node){
			if ($node->nodeType == XML_ELEMENT_NODE){
				
				$this->cond_id++;
				
				switch (strtolower($node->tagName)){
					case "sup" :
						$sup = $this->read_modes_conditions_sup($node);
						$sup->id = $this->cond_id;
						array_push($conds,$sup);
						break;
					case "force" :
						$force = $this->read_modes_conditions_force($node);
						$force->id = $this->cond_id;
						array_push($conds,$force);
						break;
				}
			}	
		}
		
		// print_r($conds);
		return $conds;
	}

	/**
	 * Lectude d'une condition de type <SUP>
	 * 
	 * @param $node noeud xml à parser de type DOMElement
	 *
      	 */
	private function read_modes_conditions_sup(DOMElement $element){
		
		$condition = new StdClass;
		$condition->type = ConfigurationModes::CONDITION_SUP;
		
		$this->getLogger()->debug("reading domelement sup");
		foreach($element->childNodes as $child){
			switch (strtolower($child->nodeName)){

				case "description" :
					$condition->description = $child->textContent;
					break;
				case "action" :
					$condition->action = $child->textContent;
					break;
				case "priority" :
					$condition->priority = Configuration::get_numeric_from_string($child->textContent);
					break;
				case "t1" :
					$condition->t1 = $this->get_condition_temperature($child);
					break;
				case "t2" :
					$condition->t2 = $this->get_condition_temperature($child);
					break;
			}
		}		
		// print_r($condition);
		return $condition;
	}
	
	/**
	 * Lectude d'une condition de type <FORCE>
	 * 
	 * @param $node noeud xml à parser de type DOMElement
	 *
      	 */
	private function read_modes_conditions_force(DOMElement $element){
		
		$condition = new StdClass;
		$condition->type = ConfigurationModes::CONDITION_FORCE;
		
		$this->getLogger()->debug("reading domelement force");
		foreach($element->childNodes as $child){
			switch (strtolower($child->nodeName)){

				case "description" :
					$condition->description = $child->textContent;
					break;
				case "action" :
					$condition->action = $child->textContent;
					break;
				case "priority" :
					$condition->priority = Configuration::get_numeric_from_string($child->textContent);
					break;
			}
		}		
		return $condition;
	}

	/**
	 * Retourne la valeur d'une température dans une condition
	 * Peut être soit le nom d'un sensor (sonde de température)
	 * Peut être une valeur (18°C par exemple)
	 */
	private function get_condition_temperature(DOMElement $child){
		$t = new StdClass;

		$t->sensor = $child->getAttribute("sensor");
		
		// l'attribut est une température. A vérifier
		if ( is_numeric($child->getAttribute("temp"))){
			$t->temp = $child->getAttribute("temp");
		}
		
		return $t;
	}
}

?>
