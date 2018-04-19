<?php
if(!isset($_SESSION)){session_start();}
require_once $_SERVER['DOCUMENT_ROOT']. "/classes/class.insurance.php";
	$ins_mgr = new InsuranceManager ();
	if ($_POST) {
	sleep ( 1 );
	if($_POST['method'] == 'form'){
		$ret = $ins_mgr->addBillableItem('',$_POST ['name'],$_POST ['category'], $_POST['scheme'], $_POST['selling_price']);
	}else{
		//it's going to be 'file' then
		$ret = $ins_mgr->addBillableItemsCSV($_FILES['csv_file']);	
	}
	echo $ret;
	exit ();
}
?> 
<script type="text/javascript">
$(document).ready(function(){
    $(".what").hide('fast');
});
function start(){$('#msg').html('<img src="/img/loading.gif"/> <em>please wait ...</em>');}
function done(s){
	status_ = s.split(":");
	if(status_[0]=='success'){
		$('#msg').html('<span class="uploaded">'+status_[1]+'</span>');
		showTabs(3);	
		setTimeout("$('.close').click()",1500);
	 }else{$('#msg').html('<span class="error">'+status_[1]+'</span>');}}
function showMethod(x){
	which = $(x).attr("value");
	$(".what").fadeOut('slow');
	$("#"+which).fadeIn('slow');
}
</script>
<div id="additems">
<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
<div align="center"><span id="msg"></span><br>
<label style="display: inline; margin-right: 10%"><input type="radio" name="method" value="form" onclick="showMethod(this)" checked="checked"> Fill the Form</label>
<label style="display: inline"><input type="radio" name="method" value="file" onclick="showMethod(this)"> Upload a File</label>
<hr></div>
	<div id="file" class="what"><h4>Upload by CSV: </h4><br>
            <input type="file" name="csv_file">
            <span style="float: right">
                <button type="submit">Upload &raquo;</button>
                <button type="button" onclick="$('.close').click()">Cancel</button>
            </span>
	</div>

	<div id="form" class="what">
            <label>Item Code: <input name="code" type="text" disabled="disabled"></label>
            <label>Name of Item: <span class="required-text">*</span> <input name="name" type="text"></label>
            <label>Item Category: <span class="required-text">*</span> <select name="category">
                <?php 
                $vals=[];
                for($i=0;$i<count($vals);$i++){
                        echo '<option value="'.$vals[$i].'">'.ucwords($vals[$i]).'</option>';
                }?>
                </select> </label>
            <label>Insurance Scheme: <span class="required-text">*</span>
                <select name="scheme">
                    <?php
//                    $vals=explode("|",$ins_mgr->getAllInsuranceSchemes($notFormatted=TRUE));
//                    for($i=0;$i<count($vals);$i++){
//                            $value = explode("^", $vals[$i]);
//                            echo '<option value="'.$value[0].'">'.ucwords($value[1]).'('.$value[2].')</option>';
//                    }?>
                    </select></label>
            <label>Selling Price <span class="required-text">* </span><em> (as it applies to the selected insurance scheme)</em>
                <input type="number" min="0" step="any" class="wide" name="selling_price"></label>
            <button type="submit" name="update"	value="">Add Item &raquo;</button>
            <button type="button" onclick="$('.close').click()">Cancel</button>
	</div>
</form>
</div>