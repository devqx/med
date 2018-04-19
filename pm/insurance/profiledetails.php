
<html>
<head>
<script type="text/javascript" src="scripts/webtoolkit.aim.js"></script>
<script src="scripts/jquery-1.6.2.min.js" type="text/javascript"></script>
<div style="width: 500px;">
<?php
require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
mysql_select_db($database_dbconnection, $dbconnection);

?> 
<script type="text/javascript">
function start(){$('#msg').html('<img src="images/loading.gif"/> <em>please wait ...</em>');}
function done(s){
	status_ = s.split(":");
	if(status_[0]=='ok'){
		$('#msg').html('<span class="uploaded">Item cost Added!</span>');		
		setTimeout("$('.close').click()",1500);
	 }
	else{$('#msg').html('<span class="error">'+s+'</span>');}}
</script>
</head>
<body>
<div>
<?php
$id=$_GET['id'];
require_once  "class.insurance.php";
$insure = new InsuranceManager();
$ret = $insure->getInsuranceProfileDetails($id); ?>
<fieldset><legend> Company Details</legend>
<?php echo $ret;?>
</fieldset>
<?php $ret = $insure->getInsuranceSchemesforACompany($id);?>
<fieldset><legend> Insurance Schemes Operated</legend>
<?php echo $ret;?>
</fieldset>
</div>


<span id="msg"></span>
</div>
</body>
</html>