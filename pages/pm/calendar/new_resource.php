<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/17
 * Time: 12:52 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Resource.php';
$types = getTypeOptions('type', 'resource');
if($_POST){
	if(is_blank($_POST['name'])) {exit('error:Resource name is required');}
	if(is_blank($_POST['type'])){exit('error:Resource Type is required');}
	
	$ae_title = !is_blank($_POST['ae_title']) ? $_POST['ae_title']: null;
	$modality = !is_blank($_POST['modality']) ? $_POST['modality']: null;
	$stationName = !is_blank($_POST['station_name']) ? $_POST['station_name']: null;
	
	$resource = (new Resource())->setName($_POST['name'])->setType($_POST['type'])->setAeTitle( $ae_title )->setModality($modality)->setStationName( $stationName )->add();
	if($resource!=null){
		exit('success:Resource added');
	}
	exit('error:Failed to add resource');
}?>

<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, resourceHandler)">
		<label>Name <input type="text" name="name" required> </label>
		<label>Type <select name="type" required data-placeholder="--Select Type--">
				<option></option>
				<?php foreach ($types as $type){?>
				<option value="<?= $type?>"><?= $type ?></option>
				<?php }?>
			</select> </label>
		<label>Modality <input type="text" maxlength="2" name="modality"> </label>
		<label>AE Title <input type="text" name="ae_title"> </label>
		<label>Station Name <input type="text" name="station_name"> </label>
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

