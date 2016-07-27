<?php

/** 
 * Classe de gestion des rapports d'exécution au format texte
 */
class ReportXmlManager extends BaseClass implements iReportManager {


	/**
	 * Retourne le rapport d'exécution complet
	 *
	 * @return string contenant le rapport complet
	 */
	public function get_xml_document(){
	
		global $DATA;
		
		$domtree = new DOMDocument('1.0', 'UTF-8');

		$xmlRoot = $domtree->createElement("report");
		$xmlRoot = $domtree->appendChild($xmlRoot);

		$dateXml = $domtree->createElement("date",date("d/m/Y H:i:s",$DATA->date));
		$dateXml = $xmlRoot->appendChild($dateXml);


		$xmlRoot->appendChild($this->get_report_temperatures($domtree));
		$xmlRoot->appendChild($this->get_report_programs($domtree));
		$xmlRoot->appendChild($this->get_report_modes($domtree));
		$xmlRoot->appendChild($this->get_report_action($domtree));
	
	//	$report .= $this->get_report_temperatures();
	//	$report .= $this->get_report_programs();
	//	$report .= $this->get_report_events();
	//	$report .= $this->get_report_modes();
	//	$report .= $this->get_report_conditions();
	//	$report .= $this->get_report_action();
		
		return $domtree;
		
	}

	public function get_report(){

		$domtree = $this->get_xml_document();
		$domtree->formatOutput = TRUE;
		return $domtree->saveXML();

	}
	public function save_report($filename){

		$domtree = $this->get_xml_document();
		$domtree->formatOutput = TRUE;
		$res = $domtree->save($filename);

		if ($res === FALSE){
			$this->getLogger()->error("can't write xml report to $filename");
		}
		
	}

	/** ---------------------------------------------------------- */
	
	/**
 	 * Retourne le rapport concernant les programmes
	 *
	 * @return string contenant le rapport
	 */
	private function get_report_programs(DOMDocument $domtree){
	
		global $CFG, $DATA;

		$node = $domtree->createElement("programs");
			
			
		foreach ($CFG->programs as $program){
			$progXml = $domtree->createElement("program");

			$progXml->appendChild($domtree->createElement('name',$program->name));
			$progXml->appendChild($domtree->createElement('description',$program->description));
			$progXml->appendChild($domtree->createElement('id',$program->id));
			$progXml->appendChild($domtree->createElement('active',$program->active));
			
			if ($DATA->programs[0] == $program->id){
				$progXml->appendChild($domtree->createElement('used',1));
			} else {
				$progXml->appendChild($domtree->createElement('used',0));
			}

			$dXml = $domtree->createElement("daysofweek");
			foreach($program->daysofweek as $day){
				$dXml->appendChild($domtree->createElement('day',$day));
			}
			$progXml->appendChild($dXml);


			$eXml = $domtree->createElement("events");
			foreach($program->events as $event){
				$xml = $eXml->appendChild($domtree->createElement('event'));
				 if (substr($event->debut,0,2) === "*:"){
	                                $debut = $event->debut;
	                        } else {
	                                $debut = date("H:i",$event->debut);
	                        }
	                        if (substr($event->fin,0,2) === "*:"){
	                                $fin = $event->fin;
	                        } else {
	                                $fin = date("H:i",$event->fin);
				}

				$xml->appendChild($domtree->createElement('debut',$debut));
				$xml->appendChild($domtree->createElement('fin',$fin));
				$xml->appendChild($domtree->createElement('mode',$event->mode));
				if (isset($event->priority)){
					$xml->appendChild($domtree->createElement('priority',$event->priority));		
				}
				$xml->appendChild($domtree->createElement('id',$event->id));

				
				if (in_array($event->id,$DATA->events)){
					$xml->appendChild($domtree->createElement('used',1));
	                        } else {
					$xml->appendChild($domtree->createElement('used',0));
                	        }
			}
			$progXml->appendChild($eXml);

			$node->appendChild($progXml);
		}


		return $node;
	}
	

	/**
         * Retourne le rapport concernant les températures relevées
         *
         * @return string contenant le rapport
         */
	private function get_report_temperatures(DOMDocument $domtree){
	
		$node = $domtree->createElement("sensors");
		$sensorMgr = new w1TempSensorManager();
		
		global $CFG, $DATA;
		
		
		foreach($DATA->sensors as $key => $value){
			
			$sensor = $sensorMgr->get_sensor_by_name($key);
			
			if ($sensor != null){
				$sensorXml = $domtree->createElement("sensor");
				$sensorXml->appendChild($domtree->createElement('name',$sensor->name));
				$sensorXml->appendChild($domtree->createElement('value',$value));
				$sensorXml->appendChild($domtree->createElement('id',$sensor->id));
				$sensorXml->appendChild($domtree->createElement('type',$sensor->type));
				$sensorXml->appendChild($domtree->createElement('description',$sensor->description));
				
				$node->appendChild($sensorXml);
				//$report .= " - sonde " .$key . " - " .$sensor->description . " = " . $value . "°C\n";
			}
		}
		return $node;
	}
	
	/**
	 * Retourne le rapport concernant les modes sélectionnés
	 *
	 * @return string contenant le rapport
	 */
	private function get_report_modes(DOMDocument $domtree){

                global $CFG, $DATA;

                $node = $domtree->createElement("modes");
		$node->appendChild($domtree->createElement('temperature_approx',$CFG->modes->temperature_approx));

		$priorXml = $domtree->createElement("actionpriority");	
		foreach ($CFG->modes->actionpriority as $key => $value){
			//error_log($key . " = " . $value);
			//$priorXml->appendChild($domtree->createElement("prio".$key,$value));
			$elt = $domtree->createElement("priority");
			$elt->setAttribute("value",$key);
			$elt->setAttribute("action",$value);
			$priorXml->appendChild($elt);
		}
		$node->appendChild($priorXml);


                foreach ($CFG->modes->list as $mode){
                        $modeXml = $domtree->createElement("mode");

                        $modeXml->appendChild($domtree->createElement('id',$mode->id));
                        $modeXml->appendChild($domtree->createElement('description',$mode->description));

                        if (in_array($mode->id,$DATA->modes)){
                                $modeXml->appendChild($domtree->createElement('used',1));
                        } else {
                                $modeXml->appendChild($domtree->createElement('used',0));
                        }

			$condsXml = $domtree->createElement("conditions");
			foreach($mode->conditions as $condition){
				if ($condition->type == ConfigurationModes::CONDITION_SUP){
					$condsXml->appendChild($this->get_report_conditions_sup($domtree,$condition));
				}
				if ($condition->type ==  ConfigurationModes::CONDITION_FORCE){
					 $condsXml->appendChild($this->get_report_conditions_force($domtree,$condition));
				}
			}

                        $modeXml->appendChild($condsXml);
			$node->appendChild($modeXml);
                }

                return $node;
	}

	/**
	 * Retourne le code XML pour une condition de type cond_sup
	 *
   	 * @param $domtree le document XMl
	 * @param $condition la condition
	 * 
 	 * @return le code XML de la condition
 	 */
	private function get_report_conditions_sup($domtree,$condition){
	
		global $DATA;	
		$node = $domtree->createElement("condition");

		$node->appendChild($domtree->createElement("type", ConfigurationModes::CONDITION_SUP));
		$node->appendChild($domtree->createElement("action",$condition->action));
		$node->appendChild($domtree->createElement("priority",$condition->priority));
		$node->appendChild($domtree->createElement("description",$condition->description));
		$node->appendChild($domtree->createElement("id",$condition->id));
	


                $modeMgr = new ModeManager();
		$node->appendChild($domtree->createElement("t1",$modeMgr->get_temperature_from_condition_t($condition->t1)));
		$node->appendChild($domtree->createElement("t2",$modeMgr->get_temperature_from_condition_t($condition->t2)));

 		if (in_array($condition->id,$DATA->conditions)){
			$node->appendChild($domtree->createElement('used',1));
		} else {
			$node->appendChild($domtree->createElement('used',0));
		}

		return $node;
	}

	/**
	 * Retourne le code XML pour une condition de type cond_force
	 *
   	 * @param $domtree le document XMl
	 * @param $condition la condition
	 * 
 	 * @return le code XML de la condition
 	 */
	private function get_report_conditions_force(DOMDocument $domtree,$condition){
		
		global $DATA;	
		$node = $domtree->createElement("condition");

		$node->appendChild($domtree->createElement("type", ConfigurationModes::CONDITION_FORCE));
		$node->appendChild($domtree->createElement("action",$condition->action));
		$node->appendChild($domtree->createElement("priority",$condition->priority));
		$node->appendChild($domtree->createElement("description",$condition->description));
		$node->appendChild($domtree->createElement("id",$condition->id));
	

 		if (in_array($condition->id,$DATA->conditions)){
			$node->appendChild($domtree->createElement('used',1));
		} else {
			$node->appendChild($domtree->createElement('used',0));
		}

		return $node;
	}


	/**
         * Retourne le rapport concernant l'action sélectionnée
         *
         * @return string contenant le rapport
         */
        private function get_report_action(DOMDocument $domtree){

		
		$actionMgr = new ActionManager();

                global $CFG, $DATA;

		$node = $domtree->createElement("actions");
		
		foreach($CFG->actions->list as $action){
		
			$actxml =  $domtree->createElement("action");
			$actxml->appendChild($domtree->createElement("name",$action->name));
			$actxml->appendChild($domtree->createElement("description",$action->description));
			$actxml->appendChild($domtree->createElement("id",$action->id));
	
			if ($action->name == $DATA->action){
                        	$actxml->appendChild($domtree->createElement('used',1));
	                } else {
        	                $actxml->appendChild($domtree->createElement('used',0));
                	}
			$gpios = $domtree->createElement("gpios");

			foreach ($action->gpio as $gpio){
				$gpioxml = $domtree->createElement("gpio");
				$gpioxml->appendChild($domtree->createElement("name",$gpio->name));
				$gpioxml->appendChild($domtree->createElement("value",$gpio->value));

				$gpios->appendChild($gpioxml);
                	};
			$actxml->appendChild($gpios);
			$node->appendChild($actxml);

		}

		return $node;
	}


	/**
         * Retourne le rapport concernant les conditions vérifiées
         *
         * @return string contenant le rapport
         */
        private function get_report_conditions(){

                $modeMgr = new ModeManager();
                global $CFG, $DATA;

                $report = "Conditions vérifiées :\n";

		// parcours des modes sélectionés pour avoir la liste complète des conditions parcourues
		$array_conditions = array();
		foreach ($DATA->modes as $mode_id){
			$mode = $modeMgr->get_mode_by_id($mode_id);
			if ($mode != null){
				foreach ($mode->conditions as $condition){
					$array_conditions[$condition->id] = $condition;
				}
			}
		}
		
		foreach ($array_conditions as $condition){
			
			if ($condition->type == ConfigurationModes::CONDITION_FORCE){
				$report .= " - " .$condition->description.", ".$condition->type. ", action=".$condition->action .", priority=".$condition->priority;
			}
			if ($condition->type == ConfigurationModes::CONDITION_SUP){
				$t1 = $modeMgr->get_temperature_from_condition_t($condition->t1);
                		$t2 = $modeMgr->get_temperature_from_condition_t($condition->t2);

				 $report .= " - " .$condition->description.", ".$condition->type. ", t1=".$t1."+/-".$CFG->modes->temperature_approx." °C, t2=".$t2. "°C, action=".$condition->action .", priority=".$condition->priority;
                        }


                        if (in_array($condition->id,$DATA->conditions)){
                                $report .= " : OK\n";
                        } else {
                                $report .= " : NO\n";
                        }

		}
	


                return $report;
        }

}

?>
