<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/16
 * Time: 12:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticTemplateDAO.php';
$req = (new GeneticRequestDAO())->get($_REQUEST['request_id']);
$template = (new GeneticTemplateDAO())->get($req->getLab()->getTemplate()->getId());
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$result = (new GeneticLabResult())->setNote($_POST['content'])->setRequest($req)->setUser(new StaffDirectory($_SESSION['staffID']))->add($pdo);
	$req_ = $req->setStatus('awaiting_review')->update($pdo);
	if($req_ != null && $result != null){
		$pdo->commit();
		exit("success:Result Note added successfully");
	}
	$pdo->rollBack();
	exit("error:Failed to update Content");
}
?>
<section style="width: 1000px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: _posted})">
		<label><select>
			<option value="<?= $template->getId()?>"><?= $template->getName() ?></option>
		</select></label>
		<label>Result <span class="pull-right fadedText">Content has been loaded based on the configured template</span>
			<textarea id="genetic_lab_content" name="content">
				<?= $template->getContent()?>
			</textarea>
		</label>
		<div class="clear"></div>
		<div class="clear"></div>
		<input type="hidden" name="request_id" value="<?=$_REQUEST['request_id']?>">
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
