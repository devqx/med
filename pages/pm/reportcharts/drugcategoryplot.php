<?php
$returnval="";
		require '/../../Connections/dbconnection.php';
		require_once ('/src/jpgraph.php');
		require_once ('/src/jpgraph_pie.php');
		require_once ('/src/jpgraph_pie3d.php');
		mysql_select_db($database_dbconnection, $dbconnection);
		//$sql1="SELECT   *  FROM drugs"; 
		$sql1="SELECT drugs.*,drug_category.name,sum(drugs.quantity) as dsum FROM drugs,drug_category WHERE drugs.cater_ID=drug_category.id GROUP BY drugs.cater_ID ORDER BY cater_ID";
		//die($sql);
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		if($numrows>0){
		$i=0;
		$data = array();
		$legend= array();
		do{
		$data[$i]=intval($row['dsum'],10);
		$legend[$i]=$row['name'];
		$i+=1;
		}while($row = mysql_fetch_assoc($rst));
		}
		else{
			die('No data to Plot');
		}
		$graph = new PieGraph(600,500);
		$graph->SetShadow();
		// Set A title for the plot
		$graph->title->Set("Drug Category Chart");
		$graph->title->SetFont(FF_VERDANA,FS_BOLD,14); 
		$graph->title->SetColor("darkblue");
		$graph->legend->Pos(0.1,0.1);
		// Create pie plot
		$p1 = new PiePlot3d($data);
		$p1->SetTheme("sand");
		$p1->SetCenter(0.4);
		$p1->SetAngle(30);
		//$p1->SetSize(0.3);
		$p1->value->SetFont(FF_ARIAL,FS_NORMAL,12);
		//$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct"));
		$p1->SetLegends($legend);
		$graph->Add($p1);
		$graph->Stroke();
		