<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/14/17
 * Time: 2:13 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterFormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EncounterForm.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Form.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$sForms = (new SFormDAO())->all();

$eF = (new EncounterFormDAO())->forEncounter($_GET['encounter_id']);

if($_POST){
	if (!isset($_POST['pre_enc_forms'])) {
		exit('error:Select at least a form');
	}
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	$outputs = 0;
	foreach ($_POST['pre_enc_forms'] as $formId){
		if((new EncounterForm())->setEncounter(new Encounter($_POST['encounter_id']))->setForm( new Form($formId) )->setDateAdded(date(MainConfig::$mysqlDateTimeFormat))->add($pdo)){
			$outputs += 1;
		}
	}
	
	if(count($_POST['pre_enc_forms'])==$outputs){
		$pdo->commit();
		exit('success:Forms attached to encounter');
	}
	$pdo->rollBack();
	exit('error:Failed to save');
}
?>
<section style="width:700px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, fomrHandler)">
		<input type="hidden" name="encounter_id" value="<?=$_GET['encounter_id']?>">
		<div class="row-fluid">
			<label class="span10">
				<select multiple name="pre_enc_forms[]" data-placeholder="Select the forms to attach to encounter">
					<option></option>
					<?php foreach ($sForms as $form) { ?>
						<option <?php foreach ($eF as $ef){$datum = $ef->getForm();?>
							<?= $datum->getId()=== $form->getId() ? 'selected':''?>
						<?php }?> value="<?= $form->getId() ?>"><?= $form->getName() ?> (<?= $form->getCategory()->getName() ?>)</option>
					<?php } ?>
				</select>
			</label>
			<button class="btn span2" id="load_forms_btn" type="submit">Attach</button>
		</div>
		<span class="fadedText notify-bar">*You cannot add the same form to the same encounter more than once</span>
	</form><?php if(count($eF)>0){?>
		<ul class="list-blocks sforms" style="column-count: 2;">
			<?php foreach ($eF as $ef){$datum = $ef->getForm();?>
				<li class="tag" data-id="<?=$datum->getId()?>">
					<?=$datum->getName()?>
					<div class="pull-right">
						<a href="javascript:" title="Preview" class="_sform" data-title="<?= escape($datum->getName())?>" data-href="/pages/pm/sforms/boxy.sform.fill.php?id=<?=$datum->getId()?>&readonly"><i class="icon-eye-open"></i></a> |
						<a href="javascript:" title="Fill Form" class="_sform" data-title="<?= escape($datum->getName())?>" data-href="/pages/pm/sforms/boxy.sform.fill.php?id=<?=$datum->getId()?>&encounter_id=<?=$_GET['encounter_id']?>&pid=<?=$_GET['pid']?>"><i class="icon-pencil"></i></a>
					</div>
				</li>
			<?php }?>
		</ul>
	<?php }?>
</section>
<script type="text/javascript">
	var fomrHandler = {
		onStart: function () {
			$(document).trigger('ajaxSend');
		}, onComplete: function (s) {
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if(data[0]==='error'){
				Boxy.warn(data[1]);
			} else if(data[0]==='success'){
				Boxy.info(data[1], function () {
					Boxy.get($('.close')).hideAndUnload();
				});
			}
		}
	};
	
	$(document).on('click', '._sform', function(e){
		if(!e.handled){
			Boxy.load($(this).data('href'), {title: $(this).data('title')});
			e.handled = true;
		}
	})
</script>