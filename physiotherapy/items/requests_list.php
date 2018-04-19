<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/14/16
 * Time: 9:00 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioItemsRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$patientId = isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : null;
$oph_centre = (isset($_REQUEST['physiotherapy_centre_id']) && !is_blank($_REQUEST['physiotherapy_centre_id'])) ? $_REQUEST['physiotherapy_centre_id'] : null;
$data = (new PhysioItemsRequestDAO())->all($_REQUEST['status'], $page, $pageSize, $patientId, $oph_centre);
$result = $data->data;
$totalSearch = $data->total;
?>
<div class="dataTables_wrapper" id="resultPage">
	<?php if ($totalSearch < 1) { ?>
		<div class="notify-bar">Nothing to display</div>
	<?php } else { ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>Patient</th>
				<th>Request Items</th>
				<th>Requested by</th>
				<th>Status</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($result as $r) {//$r=new PhysioItemsRequest;?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($r->getRequestTime())) ?></td>
					<td><span class="profile" data-pid="<?= $r->getPatient()->getId() ?>"><?= $r->getPatient()->getFullname() ?></span></td>
					<td><?php $items = [];
						foreach ($r->getItems() as $item) {
							$items[] = $item->getItem()->getName();
						} ?><?= implode(", ", $items) ?></td>
					<td><?= $r->getRequester()->getFullname() ?></td>
					<td><?= $r->getStatus() ?></td>
					<td>
						<?php if ($r->getStatus() === "Open") { ?><a href="javascript:" class="receiveItem" data-id="<?= $r->getId() ?>">Receive</a><?php } ?>
						<?php if ($r->getStatus() === "Received") { ?><a href="javascript:" class="deliverItem" data-id="<?= $r->getId() ?>">Deliver</a><?php } ?>
						<?php if ($r->getStatus() === "Open") { ?>| <a href="javascript:" class="cancelRequest" data-id="<?= $r->getId() ?>">Cancel</a><?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	<!--closed in the files that included this file </div>-->

	<script>
		$(document).ready(

		);
	</script>