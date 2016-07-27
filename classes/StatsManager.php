<?php

/** 
 * Classe de gestion des statistiques
 */
class StatsManager extends BaseClass {


	/**
	 * Enregistre les données de statistiques
	 *
	 * @return string contenant le rapport complet
	 */
	public function record_stats(){
	
		global $DATA;
		global $CFG;
		
		$timestamp = time();
		
		$this->report_stats_temperatures($timestamp);
		$this->report_stats_action($timestamp);
		
		$this->report_rrdtool_temperatures($timestamp);
		$this->report_rrdtool_action($timestamp);
	}

	/** ---------------------------------------------------------- */
	

	/**
         * Retourne le rapport concernant les températures relevées
         *
         * @return string contenant le rapport
         */
	private function report_stats_temperatures($timestamp){
	
		
		$sensorMgr = new w1TempSensorManager();
		
		global $CFG, $DATA;
		
		
		foreach($DATA->sensors as $key => $value){
			
			if ($value != 85 && $value != "" ){
				
				$sensor = $sensorMgr->get_sensor_by_name($key);

				$filename = $CFG->stats->path . "/" . $key  . ".data";
				
				$data = $timestamp . ":" . $value."\n";
			
				file_put_contents($filename, $data, FILE_APPEND);	
			}
			
		}
	}
	

	/**
         * Retourne le rapport concernant l'action sélectionnée
         *
         * @return string contenant le rapport
         */
        private function report_stats_action($timestamp){

		$actionMgr = new ActionManager();

		global $CFG, $DATA;

		$act = $actionMgr->get_action_by_name($DATA->action);

		$filename = $CFG->stats->path . "/action.data";
			
		if ($DATA->action == "on1"){
			$data = $timestamp . ":1\n";
		}
			
		if ($DATA->action == "on2"){
			$data = $timestamp . ":2\n";
		}
			
		if ($DATA->action == "off"){
			$data = $timestamp . ":0\n";
		}
			
		file_put_contents($filename, $data, FILE_APPEND);
		
	}


	private function report_rrdtool_temperatures($timestamp){

		$sensorMgr = new w1TempSensorManager();

                global $CFG, $DATA;


                foreach($DATA->sensors as $key => $value){

			if ($value != 85 && $value != ""){
				$sensor = $sensorMgr->get_sensor_by_name($key);

				$filename = $CFG->stats->path . "/" . $key  . ".rrd";

				$data = $timestamp . ":" . $value;

				print_r($data);
				print_r('\n');
				print_r($filename);
				print_r('\n');
				print_r($timestamp);
				print_r('\n');
		
				
				if (file_exists($filename)){
					$ret = rrd_update($filename, array($data));
					if ($ret){
						print_r(" update ok ");
					} else {
						print_r(" update failed ");
						print_r(rrd_error());
					}
					$info = rrd_lastupdate($filename);
			//		print_r($info);
				}
			}
                }
	}


	private function report_rrdtool_action($timestamp){

                $actionMgr = new ActionManager();

                global $CFG, $DATA;

                $act = $actionMgr->get_action_by_name($DATA->action);

                $filename = $CFG->stats->path . "/action.rrd";

                if ($DATA->action == "on1"){
                        $data = $timestamp . ":1";
                }

                if ($DATA->action == "on2"){
                        $data = $timestamp . ":2";
                }

                if ($DATA->action == "off"){
                        $data = $timestamp . ":0";
                }

		if (file_exists($filename)){
			$ret = rrd_update($filename, array($data));
		}

        }

	
}

?>
