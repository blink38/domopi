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
class w1TempSensorManager extends BaseClass {

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

		$found = !empty($sensors) && in_array($id,$sensors);
		if ($found){
			$this->getLogger()->debug("sensor " .$id. " found.");
		} else {
			$this->getLogger()->error("sensor " .$id. " was not found !");
		}
		return $found;
	}


	function get_sensor_by_name($name){
	
		global $CFG;
		
		foreach ($CFG->sensors_w1_temperature as $sensor){
		
			if ($sensor->name == $name){
				return $sensor;
			}
		}
		return null;
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
						$this->getLogger()->debug($id . " - temperature = " . $data->temp . "°C");
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
		
		$debug = "";
		
		foreach ($this->get_sensors_list() as $sensor){
			$data = $this->get_sensor_data_by_cfg_item($sensor);
			$name = $sensor->name;
			
			if ($name !== null){
				$DATA->sensors->$name = $data->temp;
				if ($debug != ""){
					$debug .= ", ";
				}
				$debug .= $sensor->name."=".$data->temp."°C";
			}
			sleep(1);
		}

		if ($debug != ""){
			$this->getLogger()->info($debug);
		}
	}
	
	/**
	 * UNIQUEMENT POUR TESTS (aléatoire)
	 * Lit les différentes températures des différentes sondes de température
	 * et met le résultat dans $DATA->sensors
	 */
	function read_sensor_data_test(){
	
		global $DATA;
		unset($DATA->sensors);
		$DATA->sensors = new StdClass();
		
		$debug = "";
			
		foreach ($this->get_sensors_list() as $sensor){

			$name = $sensor->name;
			
			if ($name !== null){
				$DATA->sensors->$name = rand(15,28);
				if ($debug != ""){
					$debug .= ", ";
				}
				$debug .= $sensor->name."=".$DATA->sensors->$name."°C";
			}
		}
		
		if ($debug != ""){
			$this->getLogger()->info($debug);
		}
	}
	
	/**
	 * UNIQUEMENT POUR TESTS (aléatoire)
	 * Lit les différentes températures des différentes sondes de température
	 * et met le résultat dans $DATA->sensors
	 */
	function read_sensor_data_test_2(){
	
		global $DATA;
		unset($DATA->sensors);
		$DATA->sensors = new StdClass();
		
		$DATA->sensors->temp1 = 23.375;
		$DATA->sensors->temp2 = 24.187;
		$DATA->sensors->temp3 = 23.687;
		$DATA->sensors->temp4 = 24.375;
		
		$this->getLogger()->info("temp1=23.375°C, temp2=24.187°C, temp3=23.687°C, temp4=24.375°C");
		// print_r($DATA->sensors);
	}
}

?>
