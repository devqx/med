<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:39 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EmbryoAssessmentDAO.php';
$data = (new EmbryoAssessmentDAO())->forInstance($_GET['aid']);

?>
<div class="menu-head"></div>
<div class="paper-card">
	<div class="heading">Assessment of Embryo Quality <a style="font-size: 14px" class="pull-right" href="javascript:" data-href="/ivf/profile/tabs/embryology/embryo-ass-new.php?aid=<?= $_GET['aid'] ?>" onclick="Boxy.load($(this).data('href'))">New Record</a></div>
	<div class="card-content">
		<?php if (count($data) == 0) { ?>
			No data available to display
		<?php } else { ?>
			<table class="table table-striped small">
				<thead>
				<tr>
					<th>Record Date</th>
					<th>Day of Assessment</th>
					<th>Comment</th>
					<th>Record by</th>
					<th>Witnesses</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($data as $datum) {//$datum=new EmbryoAssessment();?>
					<tr class="toggler" data-key="<?= $datum->getId()?>">
						<td><?= date(MainConfig::$shortDateFormat, strtotime($datum->getCreateDate())) ?></td>
						<td>Day <?= $datum->getDay() ?></td>
						<td><?= $datum->getComment() ?></td>
						<td><?= $datum->getCreateUser()->getUsername() ?></td>
						<td>
							<?php if(count($datum->getWitnesses()) ==0){ ?>- -<?php }?>
							<ul><?php foreach ($datum->getWitnesses() as $witness) { ?>
									<li><?= $witness->getFullname() ?></li>
								<?php } ?></ul>
						</td>
					</tr>
					<tr style="display: none;" data-key="<?= $datum->getId()?>">
						<td colspan="5">
							<table class="table small table-bordered">
								<thead>
								<tr>
									<th>Embryo #</th>
									<th>Quality</th>
									<th>Stage</th>
									<th>Status</th>
								</tr>
								</thead>
								<tbody>
								<?php foreach ($datum->getData() as $item){//$item=new EmbryoAssessmentData();?>
									<tr>
										<td><?= $item->getEmbryoNumber()?></td>
										<td><?= ucwords($item->getQuality())?></td>
										<td><?= $item->getStage() ?></td>
										<td><?= ucwords($item->getState())?></td>
									</tr>
								<?php }?>
								</tbody>
							</table>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.toggler', function (e) {
		if(!e.handled){
			key = ($(this).data('key'));
			$('tr[data-key="'+key+'"]:not(.toggler)').toggle();
			e.handled=true;
		}
	})
</script>
