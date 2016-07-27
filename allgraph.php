<!DOCTYPE html>

<head>
        <title>VMC Double Flux</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" type="text/css" />


        <style type="text/css">
                IMG.rrdgraph {
                    margin-top: 5px;
                        margin-right: 5px;
                }
        </style>

</head>

<?php
        include_once("Autoload.php");
        include_once("utils.php");
        include_once("init_cfg.php");

	$c_bleu="#0059FF";
	$c_orange="#FFA600";
	$c_violet="#5E0094";
	$c_vert="#369400";
	$c_rouge="#CC0000";

	$a_colors  = array( $c_bleu, $c_orange, $c_violet, $c_vert, $c_rouge);


	/*
	$rrdtool graph img/all_daily.png --start -1d DEF:temp1=temp1.tmp.rrd:temp:AVERAGE LINE:temp1$bleu:"Air neuf avant" \
        DEF:temp2=temp2.tmp.rrd:temp:AVERAGE LINE:temp2$orange:"Air neuf après" \
        DEF:temp3=temp3.tmp.rrd:temp:AVERAGE LINE:temp3$violet:"Air vicié avant" \
        DEF:temp4=temp4.tmp.rrd:temp:AVERAGE LINE:temp4$vert:"Air vicié après"
	*/

	/*
	  $options = array(
                "--start", $start,
                "--title=$title",
                "DEF:temp=$rrdfile:temp:AVERAGE",
                "LINE:temp#$color:\"$title\""
        );

        $ret = rrd_graph($output, $options);
	*/

	$a_capteurs = array();
	$a_graph = array();
?>
	
<body>

<div class="container-fluid">

        <div class="page-header"><a href="index.php"><h1>VMC Double Flux</h1> </a></div>


		<?php if (!init_configuration("./config.xml")){ ?>
			<div class="row-fluid">
				<div class="alert span5 alert-danger">Erreur lors de la lecture du fichier de configuration</div>
			</div>
		 <?php exit(1); } ?>

	
		<?php
				$sensorMgr = new w1TempSensorManager();
				$cpt = 0;
				foreach($sensorMgr->get_sensors_list() as $sensor){

					
					array_push ($a_capteurs,
						"DEF:".$sensor->name."=".$CFG->stats->path."/".$sensor->name.".rrd:temp:AVERAGE",
						"LINE:".$sensor->name.$a_colors[$cpt++].":\"".$sensor->description."\""
					);
				}

			
				$a_graph = array ( "--start", "-1d", "--title", "jour");
				$ret  = rrd_graph("img/graph/all_daily.png", array_merge($a_graph, $a_capteurs));

				$a_graph = array ( "--start", "-1w", "--title", "semaine");
                                $ret  = rrd_graph("img/graph/all_weekly.png", array_merge($a_graph, $a_capteurs));

				$a_graph = array ( "--start", "-1m", "--title", "mois");
                                $ret  = rrd_graph("img/graph/all_monthly.png", array_merge($a_graph, $a_capteurs));

				$a_graph = array ( "--start", "-1y", "--title", "annee");
                                $ret  = rrd_graph("img/graph/all_yearly.png", array_merge($a_graph, $a_capteurs));

				$capteur["name"] = "all";
		?>

	        <div class="row-fluid">
			<span12><h3>Tous les capteurs</h3></span12>

			<span12><h5>Journ&eacute;e</h5></span12>
			<span12><a href="img/graph//<?php echo $capteur["name"]; ?>_daily.png"><img src="img/graph//<?php echo $capteur["name"]; ?>_daily.png" /></a></span12>

			<span12><h5>Semaine</h5></span12>
			<span12><a href="img/graph/<?php echo $capteur["name"]; ?>_weekly.png"><img src="img/graph/<?php echo $capteur["name"]; ?>_weekly.png" /></a></span12>

			<span12><h5>Mois</h5></span12>
			<span12><a href="img/graph/<?php echo $capteur["name"]; ?>_monthly.png"><img src="img/graph/<?php echo $capteur["name"]; ?>_monthly.png" /></a></span12>

			<span12><h5>Ann&eacute;e</h5></span12>
			<span12><a href="img/graph/<?php echo $capteur["name"]; ?>_yearly.png"><img src="img/graph/<?php echo $capteur["name"]; ?>_yearly.png" /></a></span12>
		</div>

	</div>


</div>


</body>
</html>
