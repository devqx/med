<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/30/16
 * Time: 5:27 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PAuthCode.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PAuthCodeNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
if($_POST) {
	if (is_blank($_POST['note'])) {
		exit('error:Note is blank');
	}
	if (is_blank($_POST['request_id'])) {
		exit('error:Unintended error');
	}
	if (!isset($_SESSION['staffID'])) {
		exit('error:Expired session; login from another tab and continue');
	}
	
	$note = (new PAuthCodeNote())->setNote($_POST['note'])->setPauthCode( new PAuthCode($_POST['request_id']) )->setTime(date(MainConfig::$mysqlDateTimeFormat))->setUser(new StaffDirectory($_SESSION['staffID']))->add();
	if($note!==null){
		exit('success:Note saved!');
	}
	exit('error:Failed to add note');
}

?>
<section style="width:650px;max-width:650px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, formProc)">
		<label>Note
		<textarea name="note" rows="10"></textarea>
		</label>
		<input type="hidden" name="request_id" value="<?= $_GET['id']?>">
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var formProc = {
		onStart: function(){$(document).trigger('ajaxSend');},
		onComplete: function(s){
			$(document).trigger('ajaxStop');
			var data = s.split(":");
			
			if(data[0]==='success'){
				$('.close').click();
				setTimeout(function(){
					Boxy.load('/billing/pa_code.php?id=<?=$_GET['id'] ?>', {afterShow: function(){
						Boxy.info(data[1]);
					}});
				},100);
			} else if(data[0]){
				Boxy.alert(data[1]);
			}
		}
	};
</script>
