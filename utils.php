<?php


/**
 * tools function
 */
 
 
 /**
  * Return if hour1 is before hour2
  *
  * Comparison is made on hours and minutes.
  
  * @param hour1 (associative array of information related to the timestamp - see getdate())
  * @param hour2 (associative array of information related to the timestamp - see getdate())
  * @return -1 if hour1 is before hour2, 0 if hour1 = hour2, 1 if hour1 < hour2
  */
function hour1_before_hour2($hour1, $hour2){

	if ($hour1['hours'] < $hour2['hours']){
		return -1;
	}
	
	if ($hour1['hours'] == $hour2['hours']){
	
		if ($hour1['minutes'] < $hour2['minutes']){
			return -1;
		} 
		if ($hour1['minutes'] == $hour2['minutes']){
			return 0;
		}
	}
	
	return 1;
} 

/**
 * Return true if $hour is between (or egal) $begin and $end
 * Comparison is made on hours and minutes.
 * 
 * @param hour (associative array of information related to the timestamp - see getdate())
 * @param begin (associative array of information related to the timestamp - see getdate())
 * @param end (associative array of information related to the timestamp - see getdate())
 *
 * @return true if hour is between, false otherwise
 */
function hour_in_interval($hour, $begin, $end){

	// si la date de début est avant (<0) la date actuelle
	$debut_date = hour1_before_hour2($begin,$hour);
	$date_fin = hour1_before_hour2($hour,$end);
			
	if ($debut_date < 0){
			
		// si la date actuelle est avant (<0) la date de fin 
		if ($date_fin < 0){
			// error_log("trouvé");
			return true;
		}
	}
			
	// traiter le cas des intervalles à cheval sur deux jours
	if (hour1_before_hour2($end,$begin) < 0){
		// error_log("fin avant debut");
				
		if ($debut_date > 0){
			// error_log("debut après");
			if ($date_fin < 0){
				// error_log("fin après");
				// error_log("trouvé");
				return true;
			}
		} else {
			if ($debut_date < 0){
				// error_log("debut avant");
				if ($date_fin > 0){
					// error_log("date après fin");
					// error_log("trouvé");
					return true;
				}
			}
		}
	}
			
	if ($debut_date == 0 ||$date_fin == 0){
		// error_log("equals");
		return true;
	}
}

/**
 * Read the last line of a file
 *
 * @param $filename
 *
 * return the last line read or an empty string
 */
function readlastline($filename){ 

	$t = ""; 

	if (file_exists($filename)){
		$fp = fopen($filename, "r"); 
		$pos = -2; 
		while ($t != "\n") { 
			fseek($fp, $pos, SEEK_END); 
			$t = fgetc($fp); 
			$pos = $pos - 1; 
		} 
		$t = fgets($fp); 
		fclose($fp); 
	} else {
		echo "file not found";
	}	
	return $t; 
} 

/**
 * Create a RRDTOOL graph for Temperature
 *
 * @param  filename to output the graph
 * @param  rrdfile to read data from
 * @param  start when to start the graph (timestamp, -1d -1w ....)
 * @param  title of the graph (included in the img)
 * @param  color of the line to display
 */
function create_graph($output,$rrdfile, $start, $title,$color) {

	$options = array(
		"--start", $start,
		"--title=$title",
		"DEF:temp=$rrdfile:temp:AVERAGE",
		"LINE:temp#$color:\"$title\""
	);

	$ret = rrd_graph($output, $options);
}

/**
 * Create a RRDTOOL graph for VMC state
 *
 * @param  filename to output the graph
 * @param  rrdfile to read data from
 * @param  start when to start the graph (timestamp, -1d -1w ....)
 * @param  title of the graph (included in the img)
 * @param  color of the line to display
 */
function create_action_graph($output,$rrdfile, $start, $title,$color) {

	// $rrdtool graph img/action_daily.png --start -1d DEF:action=action.tmp.rrd:action:LAST AREA:action$rouge:"Allumage [deg $TEMP_SCALE]" 

        $options = array(
                "--start", $start,
                "--title=$title",
                "DEF:action=$rrdfile:action:LAST",
                "AREA:action#$color:\"$title\""
        );

        $ret = rrd_graph($output, $options);
}

?>


