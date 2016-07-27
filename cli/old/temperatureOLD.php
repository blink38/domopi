<?php

unset($CFG_W1);
global $CFG_W1;
$CFG_W1 = new stdClass();

$CFG_W1->w1_path="/sys/devices/w1_bus_master1/";
$CFG_W1->w1_slaves_list="w1_master_slaves";
$CFG_W1->w1_slaves_data="w1_slave";





/**
 * Vérifie qu'une sonde de température est bien enregistrée
 *
 * @param id identifiant de la sonde 
 * @return true ou false
 */
function checkSensorExist($id){

	global $CFG_W1;
	$sensors = file($CFG_W1->w1_path . $CFG_W1->w1_slaves_list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	/*foreach ($sensors as $line_num => $line) {
		    error_log("sensors $line_num : " . $line);
	}*/

	$found = in_array($id,$sensors);
	if (in_array($id,$sensors)){
		error_log("sensor " .$id. " found.");
	} else {
		error_log("sensor " .$id. " was not found !");
	}
	return $found;
}



function getSensorData($id){

	global $CFG_W1;
	$data = new stdClass();
	$data->crc = false;
	$data->temp = -1;

	if (checkSensorExist($id)){
		$lines = file($CFG_W1->w1_path . $id . "/" . $CFG_W1->w1_slaves_data, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);


		$pos = strpos($lines[0], "YES");
			
		if ($pos !== false ){
			$data->crc = true;

			$pos = strpos($lines[1], "t=");
			
			if ($pos !== false){
				$sub = substr($lines[1],$pos+2);
				if (is_numeric($sub)){
					$data->temp = intval($sub) / 1000;
					error_log("temperature = " . $data->temp . "°C");
				}
			}
		
		}

		/*
		foreach ($lines as $line_num => $line) {
                    error_log("sensors $line_num : " . $line);
        	}
		*/
	}	

	return $data;
}

error_log("air neuf avant");
getSensorData("28-0000041cdf0a");
error_log("air neuf après");
getSensorData("28-0000041cb50c");
error_log("air vicié avant");
getSensorData("28-000004c577ec");
error_log("air vicié après");
getSensorData("28-0000041d047b");








?>
