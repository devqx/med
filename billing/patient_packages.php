<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/15/16
 * Time: 10:08 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageSubscriptionDAO.php';

$patientId = $_GET['pid'];

$data = (new PackageSubscriptionDAO())->forPatient($patientId, true);

?>
<div class="">
	<div class="clearBoth">
		<button class="action drop-btn" href="javascript:" data-name="subscribeToPkg">Subscribe to a package</button>
	</div>
	<p></p>
	<?php if (count($data) > 0) { ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Package Name</th>
				<th>Date subscribed</th>
				<th>Status</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($data as $packageSub) {
				//$packageSub=new PackageSubscription();
				$items = $packageSub->getPackage()->getItems();
				$rawItems = [];
				foreach ($items as $item){$rawItems[] = '(' . $item->getQuantity() . ') '. getItem($item->getItemCode(), null)->getName(); }
				?>
				<tr>
					<td title="<?=htmlentities(implode('<br>', $rawItems))?>"><?= $packageSub->getPackage()->getName()?></td>
					<td><?= date(MainConfig::$dateFormat, strtotime($packageSub->getDateSubscribed())) ?></td>
					<td><?= $packageSub->getActive() ? 'Active' : 'Deactivacted' ?></td>
					<td><a href="javascript:" title="<?=htmlentities(implode('<br>', $rawItems))?>">Details</a></td>
				</tr>
			<?php } ?>
		</table>
	<?php } else { ?>
		<div class="notify-bar">Patient is not subscribed to any promo/package</div>
	<?php }?>
</div>


<script type="text/javascript">
	$('[data-name="subscribeToPkg"]').click(function (e) {
		if (!e.handled) {
			Boxy.load('/billing/patient_package_subscribe.php?patient=<?=$patientId?>');
			e.handled = true;
		}
	})
</script>
