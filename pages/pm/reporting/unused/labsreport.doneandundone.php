<?php if($_POST){
//$start='2011-01-01';
//$end='2012-08-02';
 require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
 $invreport = new Reports();
$hospital="Government";
$start=$_POST['startdate'];
$end=$_POST['enddate'];


?>
<br/>
<table width="100%"><tr><td><?php echo $invreport->getDoneLabs($hospital,$start,$end);?></td><td align="right"><?php include '../reportcharts/plotdoneandundonelabs.php';?>
</td></tr>
</table>
<?php exit;}?>

<?php include("datecalendar.php");?>
<script type="text/javascript">
function start(){
		$('#inv').html("<em>loading ...</em>");}
function finished(s) {
		$('#inv').html(s);
	}
</script>
</head>
<body>
<!-- Begin Header -->
<div id="header"> <a href="javascript:void(0)" id="logo"> </a> </div> 
<!-- End Header --> 
<!-- Begin Content -->
<div id="content" class="login">
<br/>
<strong>Complete and Incomplete Labs between:</strong>
<?php if(!$_POST){?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",mktime(0, 0, 0, date("m") , date("d")-1, date("Y")));?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit">Submit</button><hr></form><?php }?>
<div id="inv" align="center">
<?php 
?>
</div>
</div>