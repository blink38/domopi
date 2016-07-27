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

	$capteur["name"] = "nom du capteur";
	$capteur["description"] = "description du capteur";
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
			if ($_GET["name"] == "action"){

				$capteur["name"] = "action";
				$capteur["description"] = "allumage de la VMC";

				create_action_graph("img/graph/action_daily.png",$CFG->stats->path."/action.rrd","-1d",$capteur["description"],"00ff00");
                                create_action_graph("img/graph/action_weekly.png",$CFG->stats->path."/action.rrd","-1w",$capteur["description"],"00ff00");
                                create_action_graph("img/graph/action_monthly.png",$CFG->stats->path."/action.rrd","-1m",$capteur["description"],"00ff00");
                                create_action_graph("img/graph/action_yearly.png",$CFG->stats->path."/action.rrd","-1y",$capteur["description"],"00ff00");

			} else {
				$sensorMgr = new w1TempSensorManager();

				foreach($sensorMgr->get_sensors_list() as $sensor){
					if ($sensor->name == $_GET["name"]){
						$capteur["name"] = $sensor->name;
						$capteur["description"] = $sensor->description;
						$capteur["id"] = $sensor->id;
						$capteur["type"] = $sensor->type;

						create_graph("img/graph/".$sensor->name."_daily.png",$CFG->stats->path."/".$sensor->name.".rrd","-1d",$sensor->description,"00ff00");
						create_graph("img/graph/".$sensor->name."_weekly.png",$CFG->stats->path."/".$sensor->name.".rrd","-1w",$sensor->description,"00ff00");
						create_graph("img/graph/".$sensor->name."_monthly.png",$CFG->stats->path."/".$sensor->name.".rrd","-1m",$sensor->description,"00ff00");
						create_graph("img/graph/".$sensor->name."_yearly.png",$CFG->stats->path."/".$sensor->name.".rrd","-1y",$sensor->description,"00ff00");
					}
				}
			}

		?>

	        <div class="row-fluid">
			<span12><h3>Capteur : <?php echo $capteur["description"]; ?></h3></span12>

			<span12><h5>Journ&eacute;e</h5></span12>
			<span12><a href="img/graph/<?php echo $capteur["name"]; ?>_daily.png"><img src="img/graph/<?php echo $capteur["name"]; ?>_daily.png" /></a></span12>

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
