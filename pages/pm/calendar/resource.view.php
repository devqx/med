<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/17
 * Time: 10:09 AM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ResourceDAO.php';
$resource = (new ResourceDAO())->getResource($_GET['id']);
?>
<section style="width: 500px;">
	<div class="row-fluid"><div class="span6">Resource Name</div><div class="span6"><?= $resource->getName() ?></div> </div>
	<div class="row-fluid"><div class="span6">Type</div><div class="span6"><?= $resource->getType() ? $resource->getType() : '- -' ?></div> </div>
	<div class="row-fluid"><div class="span6">Modality</div><div class="span6"><?= $resource->getModality() ? $resource->getModality() : '- -' ?></div> </div>
	<div class="row-fluid"><div class="span6">AE Title</div><div class="span6"><?= $resource->getAeTitle() ? $resource->getAeTitle() : '- -' ?></div> </div>
	<div class="row-fluid"><div class="span6">Station Name</div><div class="span6"><?= $resource->getStationName() ? $resource->getStationName() : '- -' ?></div> </div>
</section>
