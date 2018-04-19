<?php
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$beds = (new BedDAO())->getBeds(true);
if (sizeof($beds) > 0) { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>SN</th>
			<th>Name</th>
			<th>Room (Type)</th>
			<th>Description</th>
			<th>Default Price (<?= $currency ?>)</th>
			<th>Available?</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($beds as $key => $b) { ?>
			<tr>
				<td><?= $key + 1 ?></td>
				<td><?= $b->getName() ?></td>
				<td><?= is_null($b->getRoom()) ? "N/A" : $b->getRoom()->getName() ?>
					(<?= $b->getRoom()->getRoomType()->getName() ?>)
				</td>
				<td><?= $b->getDescription() ?></td>
				<td class="amount"><?= ($b->getRoom()->getRoomType()->getDefaultPrice() === null) ? "N/A" : $b->getDefaultPrice() ?></td>
				<td><?= ($b->isAvailable() ? "Yes" : "No") ?></td>
				<td>
					<div class="dropdown pull-right">
						<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
							Action
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
							<li>
								<a class="__phL" href="javascript:;" data-href="/pages/pm/bedspaces/editBed.php?id=<?= $b->getId() ?>">Edit</a>
							</li>
							<?php if (!$b->isAvailable()) { ?>
							<li>
								<a onclick="forceAvailable(<?= $b->getId() ?>)" href="javascript:;" data-href="/pages/pm/bedspaces/editBed.php?id=<?= $b->getId() ?>" data-id="<?= $b->getId() ?>">Force Available</a>
								<?php } ?>
							</li>
						</ul>
					</div>

				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
} else {
	echo '<div class="well">No Bed Space currently exists</div>';
}
?>
<script type="text/javascript">
	$('a.__phL').live('click', function (e) {
		if (!e.handled) {
			Boxy.load($(this).data("href"));
			e.handled = true;
		}
	})
	
	 function forceAvailable(id) {
		 Boxy.ask("Are you sure you want to FREE this Bed?", ["Yes", "No"], function(choice) {
			 if (choice == "Yes") {
				 $.post('/api/force_bed_available.php', {id: id, action:"update"}, function(s){
					 if(s.trim()=="ok"){
						 showTabs(2);
					 } else {
						 Boxy.alert("An error occurred");
					 }
				 });
			 }
		 });
	 }
	
</script>
