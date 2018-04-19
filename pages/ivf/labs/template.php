<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/16
 * Time: 1:08 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
?>
<?php if (count($requests) > 0) { ?>
<div class="dataTables_wrapper">
<table class="table">
  <thead>
  <tr>
      <th>Date</th>
      <th>RQ #</th>
      <?php if (!isset($_GET['pid'])) { ?><th>Female Patient</th><?php } ?>
      <th>Requester *</th>
      <th>Status</th>
      <th>*</th>
  </tr>
  </thead>
	<?php foreach ($requests as $request){ //$request=new GeneticRequest()?>
		<tr>
		<td><?= date(MainConfig::$dateTimeFormat, strtotime($request->getRequestDate()))?></td>
		<td><a href="javascript:;" class="openLink" data-heading="<?= $request->getLab()->getName() ?>" data-id="<?= $request->getId() ?>"><?= $request->getRequestCode()?></a></td>
		<?php if (!isset($_GET['pid'])) { ?><td><a href="/patient_profile.php?id=<?= $request->getFemalePatient()->getId() ?>" target="_blank" data-pid="<?= $request->getFemalePatient()->getId() ?>" class="profile"><?= $request->getFemalePatient()->getFullname() ?></a></td><?php } ?>
		<td><?= $request->getUser()->getUsername() ?></td>
		<td><?= ucwords(str_replace("_", " ", $request->getStatus()))?>
		| <?php if($request->getStatus()=="awaiting_review"){?><a href="javascript:" class="approveLink" data-id="<?= $request->getId()?>">Approve</a><?php } else if($request->getStatus() !== "result_approved"){?>
			<a href="javascript:" class="cancelLink" data-id="<?= $request->getId()?>">Cancel</a><?php }?>
		</td>
		<td><?php if($request->getStatus()=="result_approved"){?><a target="_blank" href="print.php?id=<?= $request->getId() ?>" class="">Print</a><?php } else {?>--<?php }?></td>
	</tr>
	<?php }?>
</table>

	<!--todo add pagination here-->

</div>
<?php } else { ?>
	<div class="notify-bar">Nothing found to display at the moment</div>
<?php } ?>

<script type="text/javascript">
	$(document).on('click', '.openLink', function (evt) {
		if(!evt.handled){
			var id = $(this).data("id");
			var title = $(this).data("heading");
			Boxy.load("lab_details.php?id="+id, {title: title});
			evt.handled = true;
		}
	}).on('click', '.approveLink', function (evt) {
		if(!evt.handled){
			var id = $(this).data("id");
			if(window.confirm("Are you sure to approve this result?")){
				$.post('/api/alter_pgd_lab.php', {request_id: id, action:'approve'}, function (data) {
					if(data==="success"){
						$('.mini-tab .tab.on').get(0).click();
						try {
							//try to close the Boxy and open it again
							Boxy.get($(".close")).hideAndUnload();
						} catch (exception){
							//no boxy to close
						}
					} else {
						Boxy.warn("Failed to approve result");
					}
				});
			}

			evt.handled = true;
		}
	}).on('click', '.cancelLink', function (evt) {
		if(!evt.handled){
			var id = $(this).data("id");
			if(window.confirm("Are you sure to cancel this request?")){
				$.post('/api/alter_pgd_lab.php', {request_id: id, action:'cancel'}, function (data) {
					if(data==="success"){
						$('.mini-tab .tab.on').get(0).click();
					} else {
						Boxy.warn("Failed to cancel Request");
					}
				});
			}

			evt.handled = true;
		}
	});
</script>
