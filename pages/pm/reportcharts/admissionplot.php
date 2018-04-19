<?php
$returnval="";
		require '/../../Connections/dbconnection.php';
		require_once ('/src/jpgraph.php');
		require_once ('/src/jpgraph_pie.php');
		require_once ('/src/jpgraph_pie3d.php');
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql1="SELECT  count( x.`patient_id` ) AS frequency FROM `admissions` x 
		WHERE x.date_discharged is NULL AND x.date_admitted BETWEEN '".$_GET['startdate']."' AND '". $_GET['enddate']."'";
		$sql2="SELECT  count( x.`patient_id` ) AS frequency2 FROM `admissions` x 
		WHERE x.date_discharged is NOT NULL AND x.date_admitted BETWEEN '".$_GET['startdate']."' AND '". $_GET['enddate']."'";
		$rst = mysql_query($sql1,$dbconnection);
		
		$row = mysql_fetch_assoc($rst);
		$numrows=intval($row['frequency'],10);
		
		$rst2 = mysql_query($sql2,$dbconnection);
		$row2 = mysql_fetch_assoc($rst2);
		$numrows2=intval($row2['frequency2'],10);
		//die($sql1);
		if($numrows>0 || $numrows2>0){
		$i=0;
		$data = array($numrows,$numrows2);
		$legend= array("Still in Admission"," Discharged");
		
		}
		else{
			die('No data to Plot');
		}
		$graph = new PieGraph(400,400);
		$graph->SetShadow();
		// Set A title for the plot
		$graph->title->Set("Admission History Chart");
		$graph->title->SetFont(FF_VERDANA,FS_BOLD,14); 
		$graph->title->SetColor("darkblue");
		$graph->legend->Pos(0.1,0.1);
		// Create pie plot
		$p1 = new PiePlot3d($data);
		$p1->SetTheme("sand");
		$p1->SetCenter(0.4);
		$p1->SetAngle(60);
		$p1->value->SetFont(FF_ARIAL,FS_NORMAL,12);
		//$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct"));
		$p1->SetLegends($legend);
		$graph->Add($p1);
		$graph->Stroke();
		