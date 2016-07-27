<?php

include_once('../utils.php');

/**
 * Manager des programmes
 *
 */
 
 class ProgramManager extends BaseClass {
 
	/**
	 * Retourne le numéro du jour courant
	 */
	public function get_today(){
		return date("N");
	}
 
	/**
	 * Retourne la liste des IDs des programmes à exécuter.
	 *
	 * Se base sur l'état "active" et sur le numéro du jour de la semaine
	 */
	public function find_programs(){
		
		global $CFG;
		
		$progs = array();
		
		foreach ($CFG->programs as $program){
		
			if ($program->active === true && in_array($this->get_today(), $program->daysofweek)){ 
				$this->getLogger()->debug("Programme éligible : ".$program->name);
				
				// $this->get_event_from_date($program);
				array_push($progs,$program->id);
			}
		}
		return $progs;
	}
 
	/**
	 * Retourne la liste des événements d'un programme pour une date donnée
	 * Plusieurs événéments sont possibles simultanéement. On se basera ensuite sur la priorité de la condition du mode lié à l'événément.
	 *
	 * @param $program programme à parcourir
	 * @param $date date (peut être null)
	 *
	 * @return un tableau contenant les événements trouvés
	 */
	public function get_event_from_date($program, $date = null){
	
		$events = array();
		
		if ($date == null){
			$date = getdate();
		}
	
		foreach($program->events as $event){
		
			if (strpos($event->debut,"*:") === 0){
				$debut = getdate();
				$debut['minutes'] = substr($event->debut,2);
			} else {
				$debut = getdate($event->debut);
			}
			
			if (strpos($event->fin,"*:") === 0){
				$fin = getdate();
				$fin['minutes'] = substr($event->fin,2);
			} else {
				$fin = getdate($event->fin);
			}
 			
			
			$text = "vérification de l'événement ".$event->id. " : " .sprintf("%1$02d",$debut['hours']) . ":" .sprintf("%1$02d",$debut['minutes']) . 
						" -> " . sprintf("%1$02d",$fin['hours']) . ":" .sprintf("%1$02d",$fin['minutes']) . 
						" vs " . sprintf("%1$02d",$date['hours']). ":" .sprintf("%1$02d",$date['minutes']);

			$bool = hour_in_interval($date,$debut,$fin);
			if ($bool === true){
				$this->getLogger()->debug($text . " = ok");
				array_push($events, $event);
			} else {
				$this->getLogger()->debug($text . " = échoué");
			}
			
		}
		return $events;
	}
	
	/**
	 * Retourne la liste des événements à appliquer
	 *
	 * @param $events événements qui s'appliquent
	 * @return la liste des conditios à appliquer
	 */
	public function get_events_to_apply($program, $date = null){
	
		$events = $this->get_event_from_date($program, $date);
		
		$events = $this->get_events_by_priority($events);
		
		$this->getLogger()->info("Nombre d'événement éligibles : " .count($events));
		return $events;
	}
	
	/**
	 * Retourne la liste des événements dont la priorité est la plus importante
	 *
	 * @param $events liste des événements à parcourir
	 * @return la liste des événements de plus forte priorité
	 */
	public function get_events_by_priority($events){
	
		$priority = 1000;
		
		// 1. on recherche la priorité la plus forte
		foreach ($events as $event){
		
			if ($event->priority < $priority){
				$priority = $event->priority;
			}
		}
		$this->getLogger()->debug("Priorité d'événement la plus forte = " .$priority);
		
		$ret = array();
		$ids = array();	// pour éviter de saisir des doublons
		
		// 2. on parcourt les conditions et on ne garde que celle avec la priorité la plus forte
		foreach ($events as $event){
			if ($event->priority == $priority){
				if (!in_array($event->id,$ids)){
					array_push($ret,$event);
					array_push($ids,$event->id);
				}
			}
		}
		return $ret;
		
	}
	
	/**
	 * Retourne une programme son ID
	 *
	 * @param $id id du programme à rechercher
	 * @return le programme trouvé ou null
	 */
	public function get_program_by_id($id){
	
		global $CFG;
		foreach ($CFG->programs as $program){
		
			if ($program->id == $id){
				return $program;
			}
		}
		return null;
	}
 
 
 
 
 
 }
 ?>
