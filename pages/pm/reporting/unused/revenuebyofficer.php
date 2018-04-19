<?php 
    $start='1970-01-01';//set to start of time, :)
    $end=date("Y-m-d", time());//get today's date
    require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
    $revreport = new Reports();
    $hospital='Government';//TODO: dummy for now
    if($_POST){
    $start=$_POST['startdate'];
    $end=$_POST['enddate'];
    $officer=$_POST['staff'];
    echo $revreport->incomePerCashOfficer($hospital,$officer, $start,$end);
?>
<?php exit; }?>

<?php include("datecalendar.php");?>
<script type="text/javascript">
jQuery(function() {
      jQuery("#startdate,#enddate").datepicker({ dateFormat: 'yy-mm-dd' });
});

	function start(){
		$('#ttty').html("<em>loading ...</em>");}
	function finished(s) {
		$('#ttty').html(s);
		$("#admgraph").attr("src","/reporting/reportcharts/admissionplot.php?startdate="+$("#startdate").val()+"&enddate="+$("#enddate").val());
	}
</script></head>
<body>
<!-- Begin Header -->
<div id="header"> <a href="javascript:void(0)" id="logo"> </a> </div> 
<!-- End Header --> 
<!-- Begin Content -->
<div id="content" class="login">
<br/>
    <div class="reportTitle"><h2>REVENUE REPORT </h2><h3>(by staff, by date)</h3></div>
<?php if(!$_POST){ /*echo  '<strong>REVENUE RECEIVED BY STAFF BETWEEN:</strong><hr>'*/ ?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:100px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:100px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
Staff ID:<input type="text" style="width:100px" id="staff" name="staff" value=""/>
<button type="submit" name="submit">Submit</button><hr>
			</form><?php }?>
<div id="ttty">

</div>
</div>