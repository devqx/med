<?php // Plots end of year bar chart
require '/../../Connections/dbconnection.php';
require_once ('/src/jpgraph.php');
require_once ('/src/jpgraph_bar.php');
require '../../class.reports.php';
$revreport = new Reports();

// Create the graph. These two calls are always required
$graph = new Graph(700,500); 
$graph->SetScale("textint");

$graph->SetShadow();
$graph->img->SetMargin(40,30,20,40);
$hospital='Health Facility';
//Get data to plot
		if($_GET['startdate']){
			$data = array();
			$legend= array();
			$i=0;
			$income=0.0;
			$start=$_GET['startdate'];
			$lastdate=$_GET['enddate'];
			$end='';
			$monthlyincome="<table><tr><th>Month</th><th>Total Income</th></tr>";
			do{
			//get name of each month, calculate income for the month, move to next month till last as selected
				
				$legend[$i]=$revreport->getCurrentMonth($start);
				$end=$revreport->getLastOfMonth($start);
				//echo ($end > $lastdate)."<br/>";
				if($end > $lastdate) $end=$lastdate;
				
				$data[$i]=$revreport->monthlyrevenue($hospital,$start, $end);
				$monthlyincome += "<tr><td>".$legend[$i]."</td><td>".$data[$i]."</td></tr>";
				$start= $revreport->addOneDay($end);			
				//echo $i.".) ".$data[$i].":".$legend[$i]."<hr>";
				//echo	$monthlyincome;
				$i = $i + 1;
			}while($i<=13 && $end < $lastdate );// 
			$monthlyincome += "</table>";
			//}while($i < 13 );
		
		
		}
		
// Create the bar plots
$b1plot = new BarPlot($data);
$b1plot->SetFillColor("orange");
$b1plot->value->Show();
//$b2plot = new BarPlot($data2y);
//$b2plot->SetFillColor("blue");
//$b2plot->value->Show();

// Create the grouped bar plot
//$gbplot = new AccBarPlot(array($b1plot,$b2plot));

// ...and add it to the graPH
$graph->Add($b1plot);

$graph->title->Set("Annual Revenue Report for: ". date('d M, Y',strtotime($_GET['startdate']))." TO ".date('d M, Y',strtotime($lastdate)));
$graph->xaxis->title->Set("Month");
$graph->xaxis->SetTickLabels($legend);
$graph->yaxis->title->Set("Income");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
$graph->Stroke();
?>
