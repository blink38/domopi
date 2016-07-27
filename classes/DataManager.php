<?php

/** 
 * Classe de gestion des données d'exécution
 */
class DataManager extends BaseClass{


	public function record_date(){
	
		global $DATA;
		$DATA->date = time();
	}
	
	/**
	 * Enregistre les programmes trouvés en mémoire
	 * Seul le premier programme sera utilisé
	 *
	 * @param $array_p tableaux des programmes
	 */
	public function record_programs($array_p){
		
		global $DATA;
		$DATA->programs = $array_p;
	}

	/**
	 * Enregistre les événements du programme qui s'exécute
	 * 
	 * @param $events événements trouvés
	 */
	public function record_events($events){
	
		global $DATA;
		
		$array_id = array();
		$array_mode = array();
		
		foreach($events as $event){
			array_push($array_id,$event->id);
			array_push($array_mode, $event->mode);
		}
		$DATA->events = $array_id;
		$DATA->modes = $array_mode;
	}
	
	/**
	 * Enregistre les conditions qui s'appliquent
	 * 
	 * @param $conditions les conditions qui s'appliquent
	 */
	public function record_conditions($conditions){
	
		global $DATA;

		$array_id = array();
		foreach($conditions as $condition){
			array_push($array_id, $condition->id);	
		}

		//$DATA->conditions = $conditions;
		$DATA->conditions = $array_id;
	}
	
	/**
	 * Enregistre l'action qui s'applique
	 *
	 * @param $action l'action qui s'applique
	 */
	public function record_action($action){
	
		global $DATA;
		$DATA->action = $action;
	}
	
}

?>
