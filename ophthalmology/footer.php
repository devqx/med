<script type="text/javascript">
function start(){
	$('#wait').html('<em>Please wait ...</em>');
	$('input[type="submit"]').attr({'disabled':'disabled'});
}
function done(s){
	$('#content h1').html('Lab Requests Search');
	$('#searchBox_').html(s);
	$('#wait').html('');
	$('input[type="submit"],button[type="submit"]').removeAttr('disabled');
	//$('input[name="q"]').removeAttr('disabled');
	$('.close').click();
}
function showFind(){
	//new Boxy();
	Boxy.load('boxy.searchForLab.php',{title:'Search for Lab'});
}
</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/footer.queues.php';?>
<a href="/home.php">Home</a> |
<a href="javascript:void(0)" onclick="showFind()">Find Lab Request</a> | 

<a href="./">Lab Request Queue</a> 
<?php if (isset($_SESSION['staffID'])){ ?>| <a href="/logout.php">Log Out</a><?php }?></div></footer>