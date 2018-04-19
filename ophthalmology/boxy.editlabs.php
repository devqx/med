<?php
if(!isset($_SESSION)){session_start();}
require $_SERVER ['DOCUMENT_ROOT'] . "/classes/class.labs.php";
$lab = new Labs();
if($_POST){
	sleep(3);	
	echo $lab->updateLab($_POST['test_id'], $_POST['testtype'], $_POST['testclass'], $_POST['ref'], $_POST['testunit'], $_POST['cost']);
	exit;
}
?>
<script type="text/javascript">
function start(){$('#msg2').html('<img src="/img/loading.gif"/> <em>please wait ...</em>');}
function done(s){
	status_ = s.split(":");
	if(status_[0]=='ok'){
		$('#msg2').html('<span class="alert alert-info">Lab updated !</span>');
		setTimeout("$('.close').click()",1500);
	 }else{
        $('#msg2').html('<span class="alert alert-error">'+status_[1]+'</span>');
    }
}
</script>
<div style="width: 500px;">


<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
<?php require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
$sql = "SELECT * FROM labtests_config WHERE id='".$_GET['id']."'";
$sql = "SELECT lc.*, ic.selling_price as cost FROM labtests_config lc left join insurance_items_cost ic on lc.billing_code=ic.item_code WHERE lc.id='".$_GET['id']."'";
//error_log($sql);
$rst = mysql_query($sql,$dbconnection);
$row = mysql_fetch_assoc($rst);
?>
<label>
    Test Type:<input name="testtype" type="text" value="<?php echo $row['name']; ?>">
</label>
    <label>
        Test Category:<select name="testclass"><?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
            $lab = new Labs();
            $vals = explode("|", $lab->getTestClasses());
            for ($i = 0; $i < count($vals); $i++) {
                $data = explode("=",$vals[$i]);
                echo '<option value="'. $data[0] . '" '.($data[0]==$row['category_id']?' selected="selected"':'').'>' . ucwords($data[1]) . '</option>';
            }
            ?></select>
    </label>
    <label>
        Reference:<input name="ref" type="text" value="<?php echo $row['reference']; ?>">
    </label>
    <label>
        Test Unit:<input name="testunit" type="text" value="<?php echo $row['testUnit_Symbol']; ?>">
    </label>
    <label>
        Cost:<input name="cost" type="number" value="<?php echo trim($row['cost']); ?>">
        <?php //how to update the billable items with the recent price? ?>
    </label>
    <div class="btn-block">
        <input type="hidden" name="test_id" value="<?= $row['id']?>">
        <button class="btn" type="submit" name="update">Save Changes</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
    </div>
</form>
<span id="msg2"></span>
</div>
