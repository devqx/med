<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/16
 * Time: 4:29 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabResultDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
$req = (new GeneticRequestDAO())->get($_REQUEST['request_id']);
$result = $req->getResult();
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$result = (new GeneticLabResultDAO())->get($_POST['result_id'], $pdo)->setNote($_POST['content'])->setUser(new StaffDirectory($_SESSION['staffID']))->update($pdo);
	//$req_ = $req->setStatus('awaiting_review')->update($pdo);
	if(/*$req_ != null && */ $result != null){
		$pdo->commit();
		exit("success:Result Note updated successfully");
	}
	$pdo->rollBack();
	exit("error:Failed to update Content");
}
?>
<section style="width: 1000px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: _posted})">
		<label></label>
		<label>Result <span class="pull-right">Previously Saved content</span>
			<textarea id="genetic_lab_content" name="content">
				<?= $result->getNote()?>
			</textarea>
		</label>
		<div class="clear"></div>
		<div class="clear"></div>
		<input type="hidden" name="result_id" value="<?=$result->getId()?>">
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('#genetic_lab_content').summernote(SUMMERNOTE_CONFIG);
	});

	function _posted(response) {
		var data = response.split(":");
		if(data[0]==="error"){
			Boxy.alert(data[1])
		} else if(data[0]==="success"){
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload(function () {
					setTimeout(function () {
						Boxy.get($(".close")).hideAndUnload();
						$('.openLink[data-id="<?= $_REQUEST['request_id']?>"]').get(0).click();
					}, 1000);
				});
			})
		}
	}
</script>