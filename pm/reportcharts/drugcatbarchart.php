<?php // content="text/plain; charset=utf-8"
require '/../../Connections/dbconnection.php';
require_once ('/src/jpgraph.php');
require_once ('/src/jpgraph_bar.php');


// Create the graph. These two calls are always required
$graph = new Graph(360,400); 
$graph->SetScale("textint");

$graph->SetShadow();
$graph->img->SetMargin(40,30,20,40);
//Get data to plot
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

$graph->title->Set("Comparing Categories of drugs in Stock");
$graph->xaxis->title->Set("Category");
$graph->xaxis->SetTickLabels($legend);
$graph->yaxis->title->Set("Quantity(Capsules|Tablets)");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
$graph->Stroke();
?>
