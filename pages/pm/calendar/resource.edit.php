<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/17
 * Time: 10:15 AM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ResourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$types = getTypeOptions('type', 'resource');
$resource = (new ResourceDAO())->getResource($_GET['id']);
if($_POST){
	if(is_blank($_POST['name'])) {exit('error:Resource name is required');}
	if(is_blank($_POST['type'])){exit('error:Resource Type is required');}
	
	$ae_title = !is_blank($_POST['ae_title']) ? $_POST['ae_title']: null;
	$modality = !is_blank($_POST['modality']) ? $_POST['modality']: null;
	$stationName = !is_blank($_POST['station_name']) ? $_POST['station_name']: null;
	$Res = (new ResourceDAO())->getResource($_POST['id'])->setName($_POST['name'])->setType($_POST['type'])->setAeTitle( $ae_title )->setModality($modality)->setStationName( $stationName )->update();
	if($Res != null){
		exit('success:Resource updated');
	}
	exit('error:Failed to update resource');
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, resourceHandler)">
		<label>Name <input type="text" name="name" required value="<?=$resource->getName() ?>"> </label>
		<label>Type <select name="type" required data-placeholder="--Select Type--">
				<option></option>
				<?php foreach ($types as $type){?>
					<option value="<?= $type?>" <?= $resource->getType()==$type ? ' selected':'' ?>><?= $type ?></option>
				<?php }?>
			</select> </label>
		<label>Modality <input type="text" maxlength="2" name="modality" value="<?=$resource->getModality()?$resource->getModality():''?>"> </label>
		<label>AE Title <input type="text" name="ae_title" value="<?=$resource->getAeTitle() ? $resource->getAeTitle() : '' ?>"> </label>
		<label>Station Name <input type="text" name="station_name" value="<?=$resource->getStationName() ? $resource->getStationName() : '' ?>"> </label>
		<p></p>
		<input type="hidden" name="id" value="<?= $resource->getId()?>">
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
