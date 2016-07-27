<?php
/**
 * Classe de gestion des sondes de température W1
 *
 * Testée avec les sondes Dallas DS18B20
 *
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 *
 */
class w1TempSensor {

	// local debug
	private $ldebug = false;
	
	
	/**
	 * Active ou désactive le mode debug pour la classe en question
	 */
	function enable_debug($val){
		$this->ldebug = $val;
	}
	
	/**
	 * Affiche un message de debug, seulement si le mode debug est activé
	 * 
	 * @param msg message à afficher
	 */
	 function log_debug($msg){
	 
		if (!$this->ldebug)
			return;
		
		error_log(get_class($this).": ".$msg);
	 }
	 
	/**
	 * Vérifie qu'une sonde de température est bien enregistrée
	 *
	 * @param id identifiant de la sonde 
	 * @return true ou false
	 */
	function check_sensor_exist($id){

		global $CFG;
		$filename = $CFG->sensors_w1_temp_path . "/" . $CFG->sensors_w1_temp_slaves_list;
		
		if (file_exists($filename)){
			$sensors = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		}

		/*foreach ($sensors as $line_num => $line) {
				error_log("sensors $line_num : " . $line);
		}*/
		
		$found = !empty($sensors) && in_array($id,$sensors);
		if ($found){
			$this->log_debug("sensor " .$id. " found.");
		} else {
			$this->log_debug("sensor " .$id. " was not found !");
		}
		return $found;
	}


	/**
	 * Get sensor data from sensor file
	 *
	 * @param $id id of the sensor
	 * @return $data{temp -> ?, crc -> ?}
	 */
	function get_sensor_data_by_id($id){

		global $CFG;
		
		$data = new stdClass();
		$data->crc = false;
		$data->temp = null;

		if ($this->check_sensor_exist($id)){
			$lines = file($CFG->sensors_w1_temp_path . "/" . $id . "/" . $CFG->sensors_w1_temp_slave_data, 
					FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			$pos = strpos($lines[0], "YES");
				
			if ($pos !== false ){
				$data->crc = true;

				$pos = strpos($lines[1], "t=");
				
				if ($pos !== false){
					$sub = substr($lines[1],$pos+2);
					if (is_numeric($sub)){
						$data->temp = intval($sub) / 1000;
						$this->log_debug("temperature = " . $data->temp . "°C");
					}
				}
			
			}
		}	

		return $data;
	}

	/** 
	 * Return the list of sensor in configuration
	 *
	 * @return the list of the sensor
	 */
	 function get_sensors_list(){
	 
		global $CFG;
		return $CFG->sensors_w1_temperature;
	}
	
	/**
	 * Get sensor data from configuration item
	 *
	 * @param $item configuration item got from $CFG
	 * @return $data{temp -> ?, crc -> ?}
	 */
	function get_sensor_data_by_cfg_item($item){

		return $this->get_sensor_data_by_id($item->id);
	}

	/**
	 * Lit les différentes températures des différentes sondes de température
	 * et met le résultat dans $DATA->sensors
	 */
	function read_sensor_data(){
	
		global $DATA;
		unset($DATA->sensors);
		$DATA->sensors = new StdClass();
		
		foreach ($this->get_sensors_list() as $sensor){
			$data = $this->get_sensor_data_by_cfg_item($sensor);
			$name = $sensor->name;
			
			if ($name !== null){
				$DATA->sensors->$name = $data->temp;
			}
		}
	}
	
	/**
	 * UNIQUEMENT POUR TESTS
	 * Lit les différentes températures des différentes sondes de température
	 * et met le résultat dans $DATA->sensors
	 */
	function read_sensor_data_test(){
	
		global $DATA;
		unset($DATA->sensors);
		$DATA->sensors = new StdClass();
		
		foreach ($this->get_sensors_list() as $sensor){

		$name = $sensor->name;
			
			if ($name !== null){
				$DATA->sensors->$name = rand(15,28);
			}
		}
	}
}

?>