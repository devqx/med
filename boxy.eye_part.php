<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PhysicalExaminationCategoryDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PhysicalExaminationDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/EyeDAO.php";
$eyes = (new EyeDAO())->getAll();

?>
<div style="text-align: center;">
	<label></label>

	<img src="img/eye_.png" alt="Mustaches" type="image/png" border="0" usemap="#Map2"/>

	<map name="Map2" id="Map2">
		<?php foreach ($eyes as $eye) { ?>
			<area title="<?= $eye->getName() ?>" alt="" data-id="<?= $eye->getId() ?>" data-rel-id="<?= $eye->getId()?>" onclick="getAttr(this)" shape="<?= $eye->getShape() ?>" coords="<?= $eye->getCoords() ?>" target="_blank">
		<?php } ?>
	</map>
</div>


