<?php

/**
 * Classe de lecture de la configuration des différents programmes de fonctionnement.
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationPrograms extends BaseClass {

	private $event_id = 0;
	
	/**
	 * Lit la section <programs> du fichier de configuration
	 *
	 */
	public function read_programs(DOMNode $config){

		global $CFG;
		
		$this->event_id = 1;
		
		$this->getLogger()->debug("reading programs section configuration");
		$programs = $config->getElementsByTagName("program");
		$cprogs = array();
		foreach ($programs as $xml_prog){

			$program = new StdClass();
			foreach ($xml_prog->childNodes as $node){
				
				switch(strtolower($node->nodeName)){
					case "name" :
						$program->name = $node->textContent;
						break;
					case "id" :
						$program->id = $node->textContent;
						break;
					case "description" :
						$program->description = $node->textContent;
						break;
					case "events" :
						$program->events  = $this->read_programs_events($node);
						break;
					case "active" :
						$program->active = Configuration::get_boolean_from_string($node->textContent);
						
						// type boolean (true ou false (default))
						// if (strcasecmp($node->textContent,"true") == 0){
							// $program->active = true;
						// } else {
							// $program->active = false;
						// }
						break;
					case "daysofweek" :
						$program->daysofweek = $this->read_programs_daysofweek($node);
						break;
				}
			}
			array_push($cprogs, $program);
		}		
		
		$CFG->programs = $cprogs;
	}
	
	/**
	 * Lecture des évenements pour un programme donné
	 *
  	 * @param $node noeud xml à parser
	 *
	 */
	private function read_programs_events(DOMNode $config){

		$this->getLogger()->debug("reading events");
		$events = $config->getElementsByTagName("event");
		
		$cevents = array();
		
		foreach ($events as $xml_event){
		
			$event = new StdClass();
			foreach ($xml_event->childNodes as $node){
			
				switch(strtolower($node->nodeName)){
					case "debut" :
						$event->debut = Configuration::get_hour_from_string($node->textContent);
						break;
					case "fin" :
						$event->fin = Configuration::get_hour_from_string($node->textContent);
						break;
					case "priority":
						$event->priority = Configuration::get_numeric_from_string($node->textContent);
						break;
					case "mode" :
						$event->mode = $node->textContent;
						break;
				}
			}	
			$event->id = $this->event_id;
			$this->event_id++;
			array_push($cevents, $event);
		}
		return $cevents;
	}
	
	
	/**
	 * Lecture des jours pour un programme donné
	 *
  	 * @param $node noeud xml à parser
	 *
	 */
	private function read_programs_daysofweek(DOMNode $config){

		$this->getLogger()->debug("reading daysofweek");
		$days = $config->getElementsByTagName("dayofweek");
		
		$cdays = array();
		
		foreach ($days as $xml_day){
		
			array_push($cdays,$xml_day->textContent);
		}
		return $cdays;
	}

}
?>
