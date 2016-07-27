<?php

/** 
 * Classe de gestion des modes de fonctionnement
 */
class ModeManager extends BaseClass{


	/**
	 * Retourne un mode selon ID
	 *
	 * @param $id id du mode à rechercher
	 * @return le mode trouvé ou null
	 */
	public function get_mode_by_id($id){
	
		global $CFG;
		
		foreach ($CFG->modes->list as $mode){
		
			if ($mode->id == $id){
				return $mode;
			}
		}
		return null;
	}
	
	
	/**
	 * Retourne la liste des conditions remplies pour un mode donné (selon son id)
	 *
	 * @param $mode_id id du mode à parcourir
	 * @return array des conditions remplies
	 */
	public function get_conditions_ok_from_mode($mode_id){
	
		$conditions = array();
		
		$mode = $this->get_mode_by_id($mode_id);
		if ($mode === null){
			$this->getLogger()->debug("ERROR : mode with id=".$mode_id." not found.");
			return $conditions;
		}
	
		// parcours de toutes les conditions du mode
		foreach ($mode->conditions as $condition){
		
			// si la condition est vérifiée, on l'ajoute à la liste des conditions vérifiées
			if ($this->check_condition($condition)){
				array_push($conditions,$condition);
			}
		}
		
		// on retourne la liste des conditions vérifiées.
		// print_r($conditions);
		return $conditions;
	}
	
	/**
	 * Retourne la liste des conditions dont la priorité est la plus importante
	 *
	 * @param $conditions liste des conditions à parcourir
	 * @return la liste des conditions de plus forte priorité
	 */
	public function get_conditions_by_priority($conditions){
	
		$priority = 1000;
		
		// 1. on recherche la priorité la plus forte
		foreach ($conditions as $condition){
		
			if ($condition->priority < $priority){
				$priority = $condition->priority;
			}
		}
		$this->getLogger()->debug("Priorité de condition la plus forte = " .$priority);
		
		$ret = array();
		$ids = array();	// pour éviter de saisir des doublons
		
		// 2. on parcourt les conditions et on ne garde que celle avec la priorité la plus forte
		foreach ($conditions as $condition){
			if ($condition->priority == $priority){
				if (!in_array($condition->id,$ids)){
					array_push($ret,$condition);
					array_push($ids,$condition->id);
				}
			}
		}
		return $ret;
		
	}
	
	/**
	 * Retourne la liste des conditions selon l'action prioritaire (si existe)
	 *
	 * @param $conditions liste des conditions à parcourir
	 * @return la liste des conditions
	 */
	public function get_conditions_by_action_priority($conditions){
	
		global $CFG;
		
		// print_r("---------------------\n");
		// print_r($CFG->modes);
		// print_r("---------------------\n");
		if (empty($CFG->modes->actionpriority) || ! is_array($CFG->modes->actionpriority)){
			return $conditions;
		}
		
		$action = null;
		
		// print_r("actionpriority: ");
		// print_r($CFG->modes->actionpriority);
		
		$ret = array();
		
		// on parcourt la liste des conditions pour trouver l'action la plus prioritaire
		foreach ($conditions as $condition){
		
			if ($action === null){
				$action = array_search($condition->action, $CFG->modes->actionpriority);
				if ($action === false){
					$action = null;
				}
				
			} else {
			
				$key = array_search($condition->action, $CFG->modes->actionpriority);
				if ($key !== false){
					if ($action == null || $key < $action){
						$action = $key;
					}
				}
			}
		}

		
		if ($action != null){
			
			$this->getLogger()->debug("Action prioritaire = " .$CFG->modes->actionpriority[$action]);
		
			// on reparcout la liste des conditions pour ne garder que celles avec l'action la plus prioritaire
			foreach ($conditions as $condition){
		
				if ($condition->action == $CFG->modes->actionpriority[$action]){
					array_push($ret,$condition);
				}
			}
		}
		
		// si auncune condition ne satisfait l'action prioritaire, alors on retourne toutes les conditions
		if (count($ret) == 0 && count($conditions) > 0){
			$ret = $conditions;
		}
		
		return $ret;
		
	}
	
	
	/**
	 * Vérifie que les données actuelles ($DATA) vérifie la condition
	 *
	 * @param $condition la condition à vérifier
	 * @return true ou false
	 */
	public function check_condition($condition){
	
		if ($condition->type == ConfigurationModes::CONDITION_SUP){
			return $this->check_condition_sup($condition);
		}
		
		
		if ($condition->type == ConfigurationModes::CONDITION_FORCE){
			return $this->check_condition_force($condition);
		}
		
		return false;
	}
	
	
	/**
	 * Vérifie une condition de type SUP (ConfigurationModes::CONDITION_SUP)
	 *
	 * @param $condition la condition à vérifier
	 * @return true ou false
	 */
	public function check_condition_sup($condition){

		global $CFG;
	
		$t1 = $this->get_temperature_from_condition_t($condition->t1);
		$t2 = $this->get_temperature_from_condition_t($condition->t2);
		
	
		if ($t1 != null && $t2 != null && is_numeric($t1) && is_numeric($t2)){
		
			$f_t1 = floatval($t1);
			$f_approx = floatval($CFG->modes->temperature_approx);

			$f_t1 = $f_t1 + $f_approx;

			$res = $f_t1 > $t2;
	
			if ($res){
				$this->getLogger()->info("condition t1=".$t1."°C +(/-)".$f_approx."°C > t2=".$t2."°C vérifiée");
				$this->getLogger()->info("condition priorite=" . $condition->priority . ", action=" .$condition->action . ", description=" . $condition->description);
			}
			return $res;
		}
		
		$this->getLogger()->warning("check_condition_sup - temperatures are not valid. return false");
		return false;
	}
	
	/**
	 * Vérifie une condition de type FORCE (ConfigurationModes::CONDITION_FORCE)
	 *
	 * @param $condition la condition à vérifier
	 * @return true ou false
	 */
	public function check_condition_force($condition){

		$this->getLogger()->info("condition force priorite=" . $condition->priority . ", action=" .$condition->action . ", description=" . $condition->description . " -> vérifié");
		return true;
	}

	/**
	 * Retourne la température d'une condition
	 *
	 * @param $temp classe contenant les informations de température
	 * 				Soit de type [sensor], soit de type [temp]
	 * @return la température trouvée ou null
	 */
	public function get_temperature_from_condition_t($temp){
	
		global $DATA;
		
		$t = null;
		
		if (!empty($temp->sensor)){
			$name = $temp->sensor;
			$t = $DATA->sensors->$name;
		} else {
			if (!empty($temp->temp) && is_numeric($temp->temp)){
				$t = $temp->temp;
			}
		}
		
		return $t;
	}
	
	
	/**
	 * Retourne la liste des conditions à appliquer
	 *
	 * @param $events événements qui s'appliquent
	 * @return la liste des conditios à appliquer
	 */
	public function get_conditions_to_apply($events){
	
		$conditions = array();
	
		// pour chaque événements, on va chercher les conditions qui s'appliquent
		foreach ($events as $event){
			$mode = $this->get_mode_by_id($event->mode);
			
			$tmp_c = $this->get_conditions_ok_from_mode($mode->id);
			$conditions = array_merge($conditions,$tmp_c);
		
		}
	
		
		$conditions = $this->get_conditions_by_priority($conditions);
		// print_r($conditions);
		
		$conditions = $this->get_conditions_by_action_priority($conditions);
		
		$this->getLogger()->info("Nombre de conditions éligibles : " .count($conditions));
		return $conditions;
	}
	
}

?>
