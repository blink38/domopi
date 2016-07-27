<?php


/**
 * Manager des actions
 *
 */
 
 class ActionManager extends BaseClass{
 

	/**
	 * Recherche l'action à exécuter  à partir d'une liste de conditions
	 * 
	 * @param conditions liste des conditions
	 * @return l'action à exécuter
	 */
	public function get_action_to_apply($conditions){
	
		global $CFG;
		
		$action = null;
		
		foreach ($conditions as $condition){
		
			// si l'action correspond à l'action prioritaire, alors on retourne l'action prioritaire
			if ($condition->action == $CFG->modes->actionpriority[0]){
				$this->getLogger()->debug("Action par défaut à appliquer : " . $condition->action);
				return $CFG->modes->actionpriority[0];
			}
			
			$action = $condition->action;
		}
		
		// si on n'a trouvé auncun action, alors on retourne l'action prioritaire
		if ($action == null){
			$this->getLogger()->debug("Aucune action trouvé. Application de l'action par défaut : " . $CFG->modes->actionpriority[0]);
			return $CFG->modes->actionpriority[0];
		}
		
		$this->getLogger()->debug("Action à appliquer :  " . $condition->action);
		return $action;
	}
	
	/**
	 * A partir d'une action à exécuter, retourne les états des différents GPIO
	 *
	 * @param $state état à exécuter
	 * @return un tableau contenant l'état des différents gpio
	 */
	public function get_gpio_from_action($state){
	
	
		$action = $this->get_action_by_name($state);
		
		if ($action != null){
			return $action->gpio;
		}
		
		return null;
	}
	
	
	/**
	 * Retourne l'action  à partir de son nom
	 *
	 * @param $name nom de l'action à rechercher
	 * @return l'action trouvée ou null
	 */
	public function get_action_by_name($name){
	
		global $CFG;
		
		foreach ($CFG->actions->list as $action){
			if ($action->name == $name){
				return $action;
			}
		}
		
		return null;
	}
	
}
?>
