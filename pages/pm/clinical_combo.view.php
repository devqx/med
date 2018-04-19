<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/27/17
 * Time: 5:08 PM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
//$options = getTypeOptions('type', 'vital_sign');
$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$options = array_col($vitalTypes, 'name');
$data = (new ClinicalTaskComboDAO())->get($_GET['id']);
?>
<section style="width: 500px;">
	<label>Name/Description
		<input type="text" value="<?= $data->getName()?>" readonly>
	</label>
	<label>Component Tasks </label>
	<?php foreach ($data->getData() as $component){//$component=new ClinicalTaskComboData();?>
	<div class="data">
		<label>Type <select disabled data-placeholder="-- Task Type --">
				<?php foreach ($options as $option) { ?>
					<option value="<?= $option ?>" <?= $option == $component->getType() ? 'selected':'' ?>><?= $option ?></option><?php } ?>
			</select> </label>
		<div class="row-fluid">
			<label class="span3">Every <input disabled type="number" data-decimals="0" value="<?= $component->getFrequency()?>"> </label>
			<label class="span4 no-label">
				<select disabled>
					<option value="1"<?= $component->getInterval()==1?' selected':''?>>minutes</option>
					<option value="60"<?= $component->getInterval()==60?' selected':''?>>hours</option>
					<option value="<?= 60 * 24 ?>"<?= $component->getInterval()==(60*24)?' selected':''?>>days</option>
					<option value="<?= 60 * 24 * 7 ?>"<?= $component->getInterval()==(60*24*7)?' selected':''?>>weeks</option>
					<option value="<?= intval(60 * 24 * 7 * 4.3453) ?>"<?= $component->getInterval()==intval(60 * 24 * 7 * 4.3453)?'selected':''?>>months</option><!-- // should we use 30 or 31  -->
				</select>
			</label>
			<label class="span3">For
				<input type="number" data-decimals="0" disabled value="<?=$component->getTaskCount() ?>">
			</label>
			<label class="span2 no-label">
				<input type="text" value="times" disabled style="background: transparent !important;border:none !important;box-shadow: none !important;">
			</label>
		</div>
	</div>
	<?php }?>
</section>

<style type="text/css" style="display:none">
	.data {
		display: inline-block;
		background: #FEFEFE;
		border: 2px solid #FAFAFA;
		box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
		margin: 10px 0 !important;
		/*-webkit-column-break-inside: avoid;*/
		/*-moz-column-break-inside: avoid;*/
		/*column-break-inside: avoid;*/
		padding: 5px;
		background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
		opacity: 1;
		
		-webkit-transition: all .2s ease;
		-moz-transition: all .2s ease;
		-o-transition: all .2s ease;
		transition: all .2s ease;
	}
	
	.svgcloser {
		cursor: pointer;
		opacity: 0.5;
	}
	
	.svgcloser:hover {
		opacity: 1;
	}
</style>
