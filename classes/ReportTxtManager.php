<?php

/** 
 * Classe de gestion des rapports d'exécution au format texte
 */
class ReportTxtManager extends BaseClass implements iReportManager {


	/**
	 * Retourne le rapport d'exécution complet
	 *
	 * @return string contenant le rapport complet
	 */
	public function get_report(){
	
		global $DATA;
		
		$report = "Date d'éxécution : " . $d = date("d/m/Y H:i:s",$DATA->date) ."\n";
		
		$report .= $this->get_report_temperatures();
		$report .= $this->get_report_programs();
		$report .= $this->get_report_events();
		$report .= $this->get_report_modes();
		$report .= $this->get_report_conditions();
		$report .= $this->get_report_action();
		
		return $report;
		
	}

	/** ---------------------------------------------------------- */
	
	/**
 	 * Retourne le rapport concernant les programmes
	 *
	 * @return string contenant le rapport
	 */
	private function get_report_programs(){
	
		global $CFG, $DATA;
		
		$report = "Programmes :\n";
		
		foreach ($CFG->programs as $program){
			$report .= " - " .$program->name;
			
			if ($DATA->programs[0] == $program->id){
				$report .= " : OK\n";
			} else {
				$report .= " : NO\n";
			}
		}
		return $report;
	}
	

	/**
         * Retourne le rapport concernant les températures relevées
         *
         * @return string contenant le rapport
         */
	private function get_report_temperatures(){
	
		
		$sensorMgr = new w1TempSensorManager();
		
		global $CFG, $DATA;
		
		$report = "Sondes de témpérature :\n";
		
		foreach($DATA->sensors as $key => $value){
			
			$sensor = $sensorMgr->get_sensor_by_name($key);
			
			if ($sensor != null){
				$report .= " - sonde " .$key . " - " .$sensor->description . " = " . $value . "°C\n";
			}
		}
		return $report;
	}
	
	/**
         * Retourne le rapport concernant les événements traités
         *
         * @return string contenant le rapport
         */
	private function get_report_events(){
	
		
		$progMgr = new ProgramManager();
		global $CFG, $DATA;
		
		$report = "Evénements du programme sélectionné :\n";
		
		$program = $progMgr->get_program_by_id($DATA->programs[0]);
		
		foreach ($program->events as $event){
		
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
			
			
			$report .= " - " .$debut. "->" . $fin. " (priorité=".$event->priority.",mode=".$event->mode.")";
			
			if (in_array($event->id,$DATA->events)){
				$report .= " : OK\n";
			} else {
				$report .= " : NO\n";
			}
		}
		
		return $report;
	}

	/**
	 * Retourne le rapport concernant les modes sélectionnés
	 *
	 * @return string contenant le rapport
	 */
	private function get_report_modes(){

		global $CFG, $DATA;

		$report = "Modes sélectionnées :\n";
	
		foreach ($DATA->modes as $mode){
			$report .= " - ".$mode."\n";
		}
		return $report;
	}

	/**
         * Retourne le rapport concernant l'action sélectionnée
         *
         * @return string contenant le rapport
         */
        private function get_report_action(){

		$actionMgr = new ActionManager();

                global $CFG, $DATA;

		$act = $actionMgr->get_action_by_name($DATA->action);

                $report = "Action sélectionnée :\n";

		$report .= " - " .$DATA->action . ", ". $act->description;

		foreach ($act->gpio as $gpio){
			$report .= ", GPIO(" . $gpio->name . ")=" . $gpio->value;
		};
		$report .= "\n";
		return $report;
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
