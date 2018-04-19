<?php
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.assessments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff(@$_SESSION['staffID']);

$pid = $_REQUEST['pid'];
$aid = $_GET['aid'];
$pNotes = (new ProgressNoteDAO())->all($aid, TRUE);
$ip = (new InPatientDAO())->getInPatient($aid, FALSE);
?>
<?php if ($ip->getDateDischarged() == null) { ?>
	<div class="menu-head">
		<?php if ($this_user->hasRole($protect->doctor_role)) { ?>
			<i class="icon-folder-open"></i>
			<a href="javascript:void(0)" onClick="Boxy.load('boxy.soap.php?pid=<?= $_GET['pid'] ?>&aid=<?= $_GET['aid'] ?>',{title:'Add S.O.A.P Report'})">S.
				O. A. P.</a> <?php } ?><!--|
		<a href="javascript:void(0)" onclick="Boxy.load('/admissions/dialogs/newProgressNote.php?aid=<?= $aid ?>&pid=<?= $pid ?>', {title: 'New InPatient Note', afterHide: function() {
			showTabs(2) }})" title="Record new InPatient Note"> New Note</a>-->
	</div>
<?php } ?>
<div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th width="15%">Date</th>
			<th>Note</th>
			<th width="15%">Noted By</th>
		</tr>
		</thead>
		<tbody>
		<?php if (count($pNotes) === 0) { ?>
			<tr>
				<td colspan="4">
					<div class="warning-bar">No Progress Notes have been made for this patient</div>
				</td>
			</tr>
			<?php
		} else {
			$_SESSION['date'] = '';
			foreach ($pNotes as $i => $pn) {
				$nType = $pn->getNoteType();
				$retVal = '';
				$edit_option = ((date("d/m/Y", strtotime($pn->getEntryTime())) == date("d/m/Y")) && $pn->getNotedBy()->getId() == $this_user->getId()) ? ' | <a href="javascript:;" onClick="Boxy.load(\'/admissions/boxy.doctor.note.edit.php?aid=' . $aid . '&note_id=' . $pn->getId() . '\',{title:\'Edit Doctor Note\'})">Edit</a>' : '';
				$edit_option = '';
				if ($nType == 'o') {
					$retVal .= (new Assessments())->formatObjectiveNote($pn->getNote());
				} else if ($nType == 'd') {
					$retVal .= '<span class="doc_note">Doc Note:</span>' . $pn->getNote() . $edit_option;
				} else if ($nType == 'p') {
					$retVal .= '<span class="plan_note">Plan:</span>' . $pn->getNote() . $edit_option;
				} else if ($nType == 'a') {
					$retVal .= '<span class="diag_note">Diagnosis:</span>' . $pn->getNote();
				} else if ($nType == 'i') {
					$retVal .= '<span class="inv_note">Investigation:</span>' . $pn->getNote();
				} else if ($nType == 'g') {
					//$retVal .= $pn->getNote();
					$retVal .= '<span class="diag_note">Diagnosis:</span>' . $pn->getNote();
				} else if ($nType == 'e') {
					$retVal .= '<span class="inv_note">Examination:</span>' . $pn->getNote();
				} else if ($nType == 'r') {
					$retVal .= '<span class="ref_note">Referral:</span>' . $pn->getNote();
				} else if ($nType == 'v') {
					$retVal .= '<span class="review_note">Systems Review:</span>' . $pn->getNote();
				} else if ($nType == 'x') {
					$retVal .= '<span class="review_note">Physical Exam:</span>' . $pn->getNote();
				} else if ($nType == 'n') {
					$retVal .= '<span class="review_note">Progress Note:</span>' . $pn->getNote();
				} else {
					$retVal .= '<span class="com_note">Complaint:</span>' . $pn->getNote() . $edit_option;
				}
				?>
				<tr>
					<td align="left" class="nowrap" nowrap><?= ($_SESSION['date'] != date(MainConfig::$dateTimeFormat, strtotime($pn->getEntryTime())) ) ?  date(MainConfig::$dateTimeFormat, strtotime($pn->getEntryTime())) : '' ?></td>
					<td><div style="max-width: 700px;"><?= $retVal ?></div></td>
					<td>
						<?php if($_SESSION['date'] != date(MainConfig::$dateTimeFormat, strtotime($pn->getEntryTime())) ){?>
						<a href="/staff_profile.php?id=<?= $pn->getNotedBy()->getId() ?>"><?= $pn->getNotedBy()->getUsername() ?></a>
					<?php }?>
					</td>
				</tr>
				<?php
				$_SESSION['date'] = date(MainConfig::$dateTimeFormat, strtotime($pn->getEntryTime()));
			}
		}
		?>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function () {
//        $("td[data-date='true']").each(function(ind, ele) {
//            var date = moment($(this).text());
//            $(this).html(date.calendar());
//        })
	})
</script>
