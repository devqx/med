<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/22/16
 * Time: 10:25 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SimulationSizeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFSimulationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$cy_st_days = [1, 2, 3, 4, 5, '6 [E. C. S.]', 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19 ,20];
$cy_st_daysMax = 20;
$sizes = (new SimulationSizeDAO())->all(FALSE);

$_ = (new IVFSimulationDAO())->getForEnrolment($_GET['aid']);

$data = [];
foreach ($_ as $__) {
	$inner = (object)null;
	$inner->day = $__->getDay();
	$inner->data = $__->getData();
	$data[] = $inner;
}

?>
<div class="menu-head">
	<span id="newLink"><a id="newSimulationLnk" href="javascript:;" data-href="/ivf/profile/tabs/stimulation.new.php?id=<?= $_GET['aid'] ?>">New Record</a></span>
</div>

<div class="zui-wrapper">
	<div class="zui-scroller">
		<table class="zui-table table table-bordered table-striped">
			<thead>
			<tr>
				<th class="zui-sticky-col text-center">DAY</th><?php for ($day =1; $day <= $cy_st_daysMax; $day++) { ?>
					<th colspan="2" class="text-center"><?= $day ?></th><?php } ?>
			</tr>
			<tr>
				<th class="zui-sticky-col">&nbsp;</th>
				<?php foreach ($cy_st_days as $day) { ?>
					<th class="text-center">R</th>
					<th class="text-center">L</th><?php } ?>
			</tr>
			</thead>
			<tr>
				<td class="zui-sticky-col">DATE</td>
				<?php foreach ($cy_st_days as $day) { ?>
					<td class="text-center" id="right_<?=$day?>" colspan="2">
						<?php foreach ($_ as $item) { ?><?= $item->getDay() == $day && $item->getRecordDate() ? '<span title="'.date(MainConfig::$dateTimeFormat, strtotime($item->getRecordDate())).'">'.date(MainConfig::$dateFormat, strtotime($item->getRecordDate())).'</span>'  : '' ?><?php }?>
					</td>
				<?php }?>
			</tr>
			<tr>
				<td class="zui-sticky-col"><span class="fadedText">TOTAL</span></td>
				<?php foreach ($cy_st_days as $day) { ?>
					<td class="text-center" id="right_<?=$day?>"><?php foreach ($_ as $item) { ?><?= $item->getDay()==$day ? '<span class="fadedText">'. $item->getTotal()['right'].'</span>': '' ?><?php }?></td>
					<td class="text-center" id="left_<?=$day?>"><?php foreach ($_ as $item) { ?><?= $item->getDay()==$day ? '<span class="fadedText">'. $item->getTotal()['left'].'</span>': '' ?><?php }?></td>
				<?php } ?>
			</tr>
			<tbody>
			<?php foreach ($sizes as $size) { ?>
				<tr>
					<td nowrap="nowrap" class="zui-sticky-col"><span style="margin-left:10px"></span><?= $size->getName() ?></td>
					<?php foreach ($cy_st_days as $day) { ?>

						<td title="Right" class="simulation_data"><?php foreach ($_ as $item) { ?><?php foreach ($item->getData() as $value){?><?= $item->getDay() == $day && $value->getSize()->getId() == $size->getId() ? ($value->getRightSide()):'' ?><?php }?><?php }?></td>
						<td title="Left" class="simulation_data"><?php foreach ($_ as $item) { ?><?php foreach ($item->getData() as $value){?><?= $item->getDay() == $day && $value->getSize()->getId() == $size->getId() ? ($value->getLeftSide()):'' ?><?php }?><?php }?></td>

					<?php } ?>
				</tr>
			<?php } ?>
			<tr>
				<td class="zui-sticky-col">
					<span style="/*margin-left:10px*/"></span>ENDO <span class="text-right fadedText small">mm</span>
				</td>
				<?php foreach ($cy_st_days as $day) {?>
					<td colspan="2" class="text-center">&nbsp;<?php foreach ($_ as $item) { /*$item= new IVFSimulation();*/?><?= $item->getDay() == $day ? $item->getEndo() : '&nbsp;' ?><?php } ?></td><?php } ?>
			</tr>
			<tr>
				<td class="zui-sticky-col">&nbsp;<span style="/*margin-left:10px*/"></span>E2/P4 Level <span class="text-right fadedText small">pg/ml</span></td><?php foreach ($cy_st_days as $day) { ?>
					<td colspan="2" class="text-center"><?php foreach ($_ as $item) { /*$item= new IVFSimulation();*/?><?= $item->getDay() == $day ? $item->getE2Level() : '&nbsp;' ?><?php } ?></td><?php } ?>
			</tr>
			<tr>
				<td class="zui-sticky-col">&nbsp;<span style="/*margin-left:10px*/"></span>GNRHa <span class="fadedText text-right small">[ml]</span></td><?php foreach ($cy_st_days as $day) { ?>
					<td colspan="2" class="text-center"><?php foreach ($_ as $item) { /*$item= new IVFSimulation();*/?><?= $item->getDay() == $day ? $item->getGnrha() : '&nbsp;' ?><?php } ?></td><?php } ?>
			</tr>
			<tr>
				<td class="zui-sticky-col">&nbsp;<span style="/*margin-left:10px*/"></span>ANT <span class="fadedText text-right small">[ml]</span></td><?php foreach ($cy_st_days as $day) { ?>
					<td colspan="2" class="text-center"><?php foreach ($_ as $item) { /*$item= new IVFSimulation();*/?><?= $item->getDay() == $day ? $item->getAnt() : '&nbsp;' ?><?php } ?></td><?php } ?>
			</tr>
			<tr>
				<td class="zui-sticky-col">&nbsp;<span style="/*margin-left:10px*/"></span>FSH <span class="fadedText text-right small">[IU]</span></td><?php foreach ($cy_st_days as $day) { ?>
					<td colspan="2" class="text-center"><?php foreach ($_ as $item) { /*$item= new IVFSimulation();*/?><?= $item->getDay() == $day ? $item->getFsh() : '&nbsp;' ?><?php } ?></td><?php } ?>
			</tr>
			<tr>
				<td class="zui-sticky-col">&nbsp;<span style="/*margin-left:10px*/"></span>HMG <span class="fadedText text-right small">[IU]</span></td><?php foreach ($cy_st_days as $day) { ?>
					<td colspan="2" class="text-center"><?php foreach ($_ as $item) { /*$item= new IVFSimulation();*/?><?= $item->getDay() == $day ? $item->getHmg() : '&nbsp;' ?><?php } ?></td><?php } ?>
			</tr>
			<tr>
				<td class="zui-sticky-col">&nbsp;<span style="/*margin-left:10px*/"></span>Remarks</td>
					<td colspan="<?= 2*sizeof($cy_st_days) ?>" class="">
						<ul>
							<?php foreach ($_ as $item) { ?><?php foreach ($cy_st_days as $day) { ?><?= $item->getDay() == $day ? '<li>'. $item->getRemarks().'</li>' : '' ?><?php } ?><?php } ?>
						</ul>
					</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
	$('#newSimulationLnk').on('click', function (e) {
		if (!e.handled) {
			Boxy.load($(e.target).data('href'), {
				title: $(e.target).data('title'), afterHide: function () {
					$('#tabbedPane').find('li.active a').click();
				}
			});
		}
	});
</script>
<?php exit;
foreach ($data as $item) { ?>
	<?php if ($item->day == $day) { ?>
		<td title="Right">
			<code><?php foreach ($item->data as $value) { ?><?= $value->getSize()->getId() == $size->getId() ? $value->getRightSide() : '' ?><?php } ?></code>
		</td>
		<td title="Left">
			<code><?php foreach ($item->data as $value) { ?><?= $value->getSize()->getId() == $size->getId() ? $value->getLeftSide() : '' ?><?php } ?></code>
		</td>
	<?php } else { ?>
		<td title="Right"> &times;</td>
		<td title="Left"> &times; </td>
	<?php } ?>

<?php } ?>
