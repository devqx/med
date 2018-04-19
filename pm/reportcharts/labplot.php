<?php
$returnval="";
		require '/../../Connections/dbconnection.php';
		require_once ('/src/jpgraph.php');
		require_once ('/src/jpgraph_pie.php');
		require_once ('/src/jpgraph_pie3d.php');
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql1="SELECT y.testType, count( x.`test_label` ) AS frequency FROM `patient_labs` x, `labtests_config` y
		WHERE x.test_label = y.`config_test_id` 
		GROUP BY x.`test_label`";
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		if($numrows>0){
		$i=0;
		$data = array();
		$legend= array();
		do{
		$data[$i]=intval($row['frequency'],10);
		$legend[$i]=$row['testType'];
		$i+=1;
		}while($row = mysql_fetch_assoc($rst));
		}
		else{
			die('No data to Plot');
		}
		$graph = new PieGraph(550,400);
		$graph->SetShadow();
		// Set A title for the plot
		$graph->title->Set("Comparison of Lab Request Types");
		$graph->title->SetFont(FF_VERDANA,FS_BOLD,13); 
		$graph->title->SetColor("darkblue");
		$graph->legend->Pos(0.5,0.5);
		// Create pie plot
		$p1 = new PiePlot3d($data);
		$p1->SetTheme("sand");
		$p1->SetCenter(0.5);
		$p1->SetAngle(90);
		$p1->value->SetFont(FF_ARIAL,FS_NORMAL,12);
		$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct"));
		$p1->SetLegends($legend);
		$graph->Add($p1);
		$graph->Stroke();
		