<?php


/**
 * Manager des actions
 *
 */
 
 class ActionManager extends BaseClass{
 

	/**
	 * Recherche l'action � ex�cuter  � partir d'une liste de conditions
	 * 
	 * @param conditions liste des conditions
	 * @return l'action � ex�cuter
	 */
	public function get_action_to_apply($conditions){
	
		global $CFG;
		
		$action = null;
		
		foreach ($conditions as $condition){
		
			// si l'action correspond � l'action prioritaire, alors on retourne l'action prioritaire
			if ($condition->action == $CFG->modes->actionpriority[0]){
				$this->getLogger()->debug("Action par d�faut � appliquer : " . $condition->action);
				return $CFG->modes->actionpriority[0];
			}
			
			$action = $condition->action;
		}
		
		// si on n'a trouv� auncun action, alors on retourne l'action prioritaire
		if ($action == null){
			$this->getLogger()->debug("Aucune action trouv�. Application de l'action par d�faut : " . $CFG->modes->actionpriority[0]);
			return $CFG->modes->actionpriority[0];
		}
		
		$this->getLogger()->debug("Action � appliquer :  " . $condition->action);
		return $action;
	}
	
	/**
	 * A partir d'une action � ex�cuter, retourne les �tats des diff�rents GPIO
	 *
	 * @param $state �tat � ex�cuter
	 * @return un tableau contenant l'�tat des diff�rents gpio
	 */
	public function get_gpio_from_action($state){
	
	
		$action = $this->get_action_by_name($state);
		
		if ($action != null){
			return $action->gpio;
		}
		
		return null;
	}
	
	
	/**
	 * Retourne l'action  � partir de son nom
	 *
	 * @param $name nom de l'action � rechercher
	 * @return l'action trouv�e ou null
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
