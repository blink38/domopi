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
?>

<body>

<div class="container-fluid">

	<div class="page-header"><a href="allgraph.php"><h1>VMC Double Flux</h1></a></div>


	<?php if (!init_configuration("./config.xml")){ ?>
		<div class="row-fluid">
			<div class="alert span5 alert-danger">Erreur lors de la lecture du fichier de configuration</div>
		</div>
	 <?php exit(1); } ?>

	<div class="row-fluid">
		
		<div class="span5">
			<div class="row-fluid">


				<span12><div class="page-header"><h3>Donn&eacute;es</h3></div></span12>

				<?php // CAPTEURS DE TEMPERATURE

					$sensorMgr = new w1TempSensorManager();

					$array_rrdgraph = array();

					foreach($sensorMgr->get_sensors_list() as $sensor){

						echo "<span12>";

						echo "Nom du capteur : " . $sensor->name."<br />";
						echo "Description : <b>".$sensor->description."</b><br />";
						
						$filename = $CFG->stats->path . "/" . $sensor->name  . ".data";
						$line = readlastline($filename);

						$items = explode(":",$line);

						echo "Date de la derni&egrave;re mesure : ".date("d M Y H:i",$items[0]) ."<br />";
						echo "Temp&eacute;rature mesur&eacute;e : <b>".$items[1]." &#176;C</b><br /><br />";

						echo "</span12>";

						create_graph("img/index/index_".$sensor->name."_daily.png",$CFG->stats->path."/".$sensor->name.".rrd","-1d",$sensor->description,"00ff00");
						$array_rrdgraph[$sensor->name] = "img/index/index_".$sensor->name."_daily.png";
	
						$array_menu[$sensor->name] = $sensor->description;

					}


				?>

				<span12>&nbsp;</span12>

				<span12>
	
				<?php // ETAT DE LA VMC

					$filename = $CFG->stats->path."/action.data";
					$line = readlastline($filename);

					$items = explode(":",$line);
					
					echo "<h5>&Eacute;tat de la VMC</h5>";
                        		echo "Date du dernier &eacute;tat : ".date("d M Y H:i",$items[0]) ."<br />";

					echo "&Eacute;tat : <b>";
					switch ($items[1]){
						case 0 :
							echo "<span style='color: red;'>arr&ecirc;t&eacute;e</span>";
							break;
						case 1 :
							echo "<span style='color: green;'>allum&eacute;e &agrave; la vitesse 1</span>";
							break;
						case 2 :
							echo "<span style='color: blue;'>allum&eacute;e &agrave; la vitesse 2</span>";
							break;
						default :
							echo "inconnu.";
							break;
					};
                        		echo "</b><br />";

					create_action_graph("img/index/index_action_daily.png",$CFG->stats->path."/action.rrd","-1d","etat de la VMC","00ff00");
					$array_rrdgraph["action"] = "img/index/index_action_daily.png";

					$array_menu["action"] = "&eacute;tat de la VMC";
				?>

				</span12>
			</div>	
		</div>

		<div class="span7">
			<div class="row-fluid">
				<span12><div class="page-header"><h3>Graphiques</h3></div></span12>
				
				<div class="row-fluid">
				<?php
					foreach ($array_rrdgraph as $name => $img){
						echo "<span6>";
						echo '<a href="graph.php?name='.$name.'">';
						echo "<img class='rrdgraph' src=".$img." /></a></span6>";
					}
				
				?>
				</div>
			</div>
		</div>


	</div>

</div>

</body>
</html>
