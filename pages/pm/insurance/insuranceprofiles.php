<div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsurerDAO.php";
	$providers = (new InsurerDAO())->getInsurers(true);
	?>
	<h5><?= count($providers) ?> Available Insurance Providers:</h5>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Name</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($providers as $p) { //$p=new Insurer();?>
			<tr>
				<td><i class="icon-tags"></i>
					<a href="javascript:void(0)" data-href="insurance/boxy.profiledetails.php?id=<?= $p->getId() ?>" onclick="Boxy.load($(this).attr('data-href'),{title:'Profile Details'})"><?= $p->getName() ?></a>
				</td>
				<td><i class="icon-edit"></i>
					<a href="javascript:void(0);" data-href="insurance/boxy.editprofile.php?id=<?= $p->getId() ?>" onclick="Boxy.load($(this).attr('data-href'),{title:'Edit Profile Details'})">Edit</a>
				</td>
				<!--<td><a href="javascript:void(0);" data-id="<?= $p->getId() ?>" data-href="boxy.deleteprofile.php?id=<?= $p->getId() ?>" onclick="deleteProfile(this)">Delete</a></td> -->
			</tr>
		<?php } ?>

	</table>
</div>