<?php
if($_POST){
	sleep(1);
	require 'class.bedspaces.php';
	$vac=new Bedspaces;
	exit($vac->addBedType($_POST['bedtype'],$_POST['cost']));
}
?>
<div>
<script type="text/javascript" src="/scripts/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="/scripts/webtoolkit.aim.js"></script>
<script type="text/javascript">
function start_(){$('#mgniu_').html('<img src="/images/loading.gif">');}
function done_(s){
	status_ = s.split(":");
	if(status_[0] =='success'){
		$('#mgniu_').html('<span style="color:#00c;font-weight:bold;">'+status_[1]+'</span>');
		$('.close').delay(2000).click();
		$('#bedmgt').load('bedspaces/index.php?t='+Math.random());
	}else{
		$('#mgniu_').html('<span style="color:#C00;font-weight:bold;">'+status_[1]+'</span>');
	}
}
</script>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart' : start_, 'onComplete' : done_});">
<table style="width:100%">
<tr>
<td valign="top" width="100%"><label>New Bed Category</label>
<input type="text" name="bedtype" /></td></tr>

<tr><td><label>Amount Chargeable</label>
<input type="number" step="0.10" min="0" name="cost" id="cost">
e.g. Executive</td></tr>
<tr><td align="right" valign="top">
<button type="submit">Add &raquo;</button> 
<button type="button" onclick="$('.close').click()">Cancel &raquo;</button> </td></tr>
<tr><td colspan="2" id="mgniu_"></td></tr>
</table>
</form>
</div>
