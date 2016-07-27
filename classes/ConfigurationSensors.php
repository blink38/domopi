<?php

/**
 * Classe de lecture de la configuration des différentes sondes de température.
 * 
 * @author matthieu.marc@gmail.com
 * @date 07/2013
 * @licence GPLv3
 *
 */

class ConfigurationSensors extends BaseClass {

	
	/**
	 * Read the configuration file - section Sensors
	 *
	 * @param $config the DOMNode <configuration /> containing the <sensors /> data
	 */
	public function read_sensors(DOMNode $config){
	
		$this->getLogger()->debug("reading sensors configuration");
		$sensors = $config->getElementsByTagName("sensors");
		
		if ($sensors->length > 0){
			$this->read_sensors_w1_temp($sensors->item(0));
		}
	}
	
	
	
	/**
	 * Read the configuration file - sections Sensors -> W1 temperature
	 *
	 * @param $config the DOMNode <sensors /> containing sensors data
	 */
	private function read_sensors_w1_temp(DOMNode $config){
	
		$this->getLogger()->debug("reading w1 temperature sensors configuration");
		$w1cfg = $config->getElementsByTagName("w1_temp_cfg");
		$this->read_sensors_w1_temp_cfg($w1cfg);
		
		$w1sensors = $config->getElementsByTagName("w1_temp_sensor");
		$this->read_sensors_w1_temp_sensor($w1sensors);
		
	}

	/**
	 * Read the configuration file - sections Sensors -> >W1 temperature CFG
	 *
	 * @param $config the DOMNode <sensors> <w1_temp_cfg>
	 */
	private function read_sensors_w1_temp_cfg(DOMNodeList $config){
		global $CFG;

		$this->getLogger()->debug("reading w1 temperature sensors configuration cfg");
		
		if ($config->length > 0){
			
			foreach($config->item(0)->childNodes as $node){
				
				switch ($node->nodeName){
					case "path" :
						$CFG->sensors_w1_temp_path = $node->textContent;
						break;
						
					case "slaves_list" :
						$CFG->sensors_w1_temp_slaves_list = $node->textContent;
						break;
						
					case "slave_data" :
						$CFG->sensors_w1_temp_slave_data = $node->textContent;
						break;
				}		
			}
		}
	}
		
	/**
	 * Read the configuration file - sections Sensors -> >W1 temperature Sensors
	 *
	 * @param $config the DOMNode <sensors> <w1_temp_cfg>
	 */
	private function read_sensors_w1_temp_sensor($w1sensors){
	
		global $CFG;
		
		$cpt = 0;
		
		if ($w1sensors->length > 0){
			$sensors = array();
			
			foreach ($w1sensors as $w1sensor){
			
				$sensor = new StdClass();
				
				foreach ($w1sensor->childNodes as $node){
					switch($node->nodeName){
						case "name" :
							$sensor->name = $node->textContent;
							break;
						case "id" :
							$sensor->id = $node->textContent;
							break;
						case "description" :
							$sensor->description = $node->textContent;
							break;
						case "type" :
							$sensor->type = $node->textContent;
							break;
					}
				}
			array_push($sensors,$sensor);
			$cpt += 1;
			}
		}
		$CFG->sensors_w1_temperature = $sensors;
		$this->getLogger()->debug("found ".$cpt." sensors");
	}
}

?>
