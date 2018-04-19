<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 4:29 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticSpecimen.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlTypeDAO.php';
$templates = (new GeneticTemplateDAO())->all();
$qc_items = (new QualityControlTypeDAO())->all();
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabDAO.php';

$lab = (new GeneticLabDAO())->get($_GET['id']);

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticLab.php';

	$lab = (new GeneticLabDAO())->get($_POST['id']);

	if (is_blank($_POST['name'])) {
		exit("error:GeneticLab Name required");
	}
	if (is_blank($_POST['print_layout'])) {
		exit("error:Template Print Layout required");
	}
	if (is_blank($_POST['template_id'])) {
		exit("error:Template required");
	}

	$item = $lab->setName($_POST['name'])->setPrintLayout($_POST['print_layout'])->setQualityControls($_POST['qc_items'])->setTemplate(new GeneticTemplate($_POST['template_id']))->setBasePrice(parseNumber($_POST['price']))->update();

	if (!is_null($item)) {
		exit("success:GeneticLab saved");
	} else {
		exit("error:Failed to save GeneticLab");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			Genetic Test Name/Description
			<input type="text" name="name" value="<?= $lab->getName()?>">
		</label>
		<label>Default Charge <input type="number" value="<?= $lab->getBasePrice()?>" name="price" required> </label>
		<label>Template <select name="template_id">
				<?php foreach ($templates as $template) { ?>
					<option value="<?= $template->getId() ?>" <?= $lab->getTemplate()->getId()==$template->getId() ? 'selected': '' ?>><?= $template->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Print Layout </label>
		<div class="row-fluid">
			<label class="span12"><input type="radio" name="print_layout" value="portrait" <?= $lab->getPrintLayout()=="portrait" ? " checked": ""?> > Portrait </label>
		</div>
		<div class="row-fluid">
			<label class="span12"><input type="radio" name="print_layout" value="landscape"<?= $lab->getPrintLayout()=="landscape" ? " checked": ""?>> Landscape </label>
		</div>
		<input type="hidden" name="id" value="<?= $_GET['id']?>"

		<label>Quality Control Parameters
			<select name="qc_items[]" multiple="multiple">
				<?php foreach ($qc_items as $item) { ?>
					<option value="<?= $item->getId() ?>" <?php foreach ($lab->getQualityControls() as $item_) { ?><?= $item->getId() == $item_ ? "selected":""?><?php } ?> ><?= $item->getName() ?></option>
				<?php } ?>
			</select>
		</label>

		<p class="clear"></p>

		<button type="submit" class="btn">Update GeneticLab</button>
		<button type="button" class="btn-link" reset>Cancel</button>
		<span name="messageBox"></span>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('select').select2({width: '100%'});
	});
	function finish(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			$('span[name="messageBox"]').html('<div class="error alert-box">' + data[1] + '</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">' + data[1] + '</div>')
			$('button[reset]').click();
		}

	}
</script>
