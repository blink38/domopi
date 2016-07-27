<?php

/**
 * Manager des actions
 *
 */
 
 class GPIOManager extends BaseClass{
 

	
	/**
	 * Ecrit une valeur dans une GPIO
	 *
	 * @param $gpio numéro de GPIO
	 * @param value valeur à écrire
	 *
	 * @return true si tout s'est bien passé, false sinon
	 */
	public function write_gpio($gpio, $value){
	
		$val = $this->get_numeric_value($value);
		
		if ($val !== null){
			//$this->getLogger()->debug("write gpio " .$gpio. " = " .$val);
			
			if ($this->execute_gpio_program("mode",$gpio,"out") == 0){
				$this->execute_gpio_program("write",$gpio,$val);

				// check if current value is value we want
				$current_val = $this->execute_gpio_program("read",$gpio);
				if ($current_val == $val){
					$this->getLogger()->debug("gpio " .$gpio. " write " . $value . " : OK");
					return true;
				}
			} else {
				$this->getLogger()->error("can't set mode out to GPIO " .$gpio);	
			}
			
		} else {
			$this->getLogger()->error("gpio ".$gpio. " write :  '" .$value. "' is not a valid value");
		}
		
		return false;
	}
	
	
	/**
	 * Ecrit les valeurs des différents GPIO selon les données d'un tableau
	 *
	 * @param $a_gpio tableau contenant les valeurs à écrire dans les GPIO
	 *	Array(
	 *		[0] => stdClass Object(
	 *			[name] => 5
	 *			[value] => on)
	 *  ... )
	 *
	 * @param true si tout s'est bien passé, false sinon
	 */
	public function write_gpio_from_array($a_gpio){
	
	
		foreach($a_gpio as $gpio){
		
			$this->write_gpio($gpio->name, $gpio->value);
		}
	}
	
	
	/**
	 * Converti le valeur on/off en valeur numerique
	 *
	 * @param $value valeur à convertir
	 *
	 * @return la valeur convertie ou null si valeur incorrecte
	 */
	private function get_numeric_value($value){
	
		if ($value === 1 || $value === 0){
			return $value;
		}
		
		if ( (is_string($value) && strtolower($value) == "on") ||$value == "1"){
			return 1;
		}
		
		if ( (is_string($value) && strtolower($value) == "off") ||$value == "0"){
			return 0;
		}
		
		return null;
	}
	
	/**
	 * Retourne la valeur actuelle d'une GPIO
	 *
	 * @param $gpio numéro de GPIO
	 *
	 * @return la valeur actuelle ou null si une erreur s'est produite
	 */
	public function read_gpio($gpio){
		
		return null;
	}
	
	
	/**
	 * Execute le programme GPIO pour agir sur les GPIO
	 *
	 * @param $action first param (action = write / mode ...)
	 * @param $pin pin number
	 * @param $param parameter (can be null - ex for read)
	 *
	 * @return value return by programme, -1 if error
	 */
	private function execute_gpio_program($action, $pin, $param = ""){
	
		global $CFG;
		$exec_ret = -1;

		
		if (!file_exists($CFG->gpios->path)){
			$this->getLogger()->error("GPIO appplication not found : " . $CFG->gpios->path);
			// return -1;
		}
		
		$cmdline = $CFG->gpios->path . " " .$action. "  " . $pin . " " . $param;
		
		$this->getLogger()->notice("GPIO cmdline = " . $cmdline);
		
		$output = array();
		$retval = 0;
		
		$retstr = exec($cmdline,$output,$retval);
		
		// $this->getLogger()->debug("retstr = " .$retstr);
		// $this->getLogger()->debug("retval = " .$retval);
		// $this->getLogger()->debug("output = " );
		// print_r($output);

		if ($action == "read"){
			if ($retval == 0){
				$exec_ret = $retstr;
			}
		} else {
			$exec_ret = $retval;
		}

		//$this->getLogger()->notice("GPIO return ".$exec_ret);
		return $exec_ret;
	
		
	}
}
	
?>
