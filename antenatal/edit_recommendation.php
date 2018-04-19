<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/29/16
 * Time: 12:20 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
$ant = (new AntenatalEnrollmentDAO())->get($_GET['id'], false);
if($_POST){
	if(is_blank($_POST['recommendation_note'])){
		exit('error:Recommendation is blank');
	}
	$instance = (new AntenatalEnrollmentDAO())->get($_POST['instance_id'], false);

	if($instance->setRecommendation($_POST['recommendation_note'])->update()){
		exit('success:ok');
	}
	exit('error:Failed to update antenatal recommendation<br> or nothing was changed');
}

?>

<section style="width: 650px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: __sodfs__1})">
		<span class="pull-right alert-box notice">Please include your name in the recommendation [if necessary]</span>
		<label>Recommendation:
		<textarea name="recommendation_note"><?=$ant->getRecommendation() ?></textarea></label>
		<input type="hidden" name="instance_id" value="<?= $_GET['id']?>">
		<div class="clear clearBoth">
			<div class="btn-block">
				<button class="btn" type="submit">Update</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
			</div>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('[name="recommendation_note"]').summernote(SUMMERNOTE_MINI_CONFIG);
	});
	var __sodfs__1 = function (s) {
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload(function(){
				location.reload();
			});
		}
	}
</script>
