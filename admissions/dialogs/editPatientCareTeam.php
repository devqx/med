<?php
$_GET['suppress'] = TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CareTeam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientCareMember.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CareTeamDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientCareMemberDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';
$aid = $_REQUEST['aid'];

$teams = (new CareTeamDAO())->getPatientCareTeamList($aid);
$members = (new StaffDirectoryDAO())->getPatientCareMembersList($aid);

if (isset($_POST['careTeam']) && count($_POST['careTeam']) > 0) {
//    if (!isset($_POST['reason']) || trim($_POST['reason']) === "") {
//        exit('error: Please specify reason for the change');
//    }

	if (!isset($_POST['careTeam'])) {
		exit('error: You need to select at least one or more Care Teams for this patient');
	}

	$pcts = [];
	foreach ($_POST['careTeam'] as $c) {
		$pct = new PatientCareMember();
		$pct->setInPatient(new InPatient($_POST['aid']));
		$pct->setStatus("Active");
		$pct->setCreateBy($staff);

		$team = explode("_", $c);
		if ($team[0] === 'staff') {
			$pct->setType("Member");
			$pct->setCareMember(new StaffDirectory($team[1]));
		} else {
			$pct->setType("Team");
			$pct->setCareTeam(new CareTeam($team[1]));
		}
		$pct->setId($team[2]);
		$pcts[] = $pct;
	}

	//error_log(json_encode($pcts));

	if ((new PatientCareMemberDAO())->changePatientCareMember($pcts)) {
		exit("ok:Modifications saved!");
	} else {
		exit("error:Request failed");
	}
}
?>
<form name="editPCTForm">
	<div class="notify-bar">No changes made</div>
	<div class="warning-bar">You have changed the Patient Care Team/Members please click on save</div>

	<label>
		<select multiple="multiple" name="careTeam[]" id="careTeams">
			<?php
			foreach ($teams as $item) {
				echo '<option ' . (($item->getAnId() !== null) ? ' selected="selected"' : '') . ' value="team_' . $item->getId() . '_' . $item->getAnId() . '">' . $item->getName() . ' (' . $item->getDescription() . ')</option>';
			}
			?>
			<?php
			foreach ($members as $staff) {
				echo '<option ' . (($staff->getAnId() !== null) ? ' selected="selected"' : '') . ' value="staff_' . $staff->getId() . '_' . $staff->getAnId() . '">' . $staff . ' (' . $staff->getProfession() . ')</option>';
			}
			?>
		</select>
	</label>

	<!--    <label name='reason' style="display: none">Reason for the change
					<textarea name="reason" class="form-control"></textarea>
			</label>-->
	<div>
		<input type="hidden" name="aid" value="<?= $aid ?>"/>
		<button type="button" class="btn btn-primary" name='editPCT' disabled="disabled" onclick="commitEditPCT()">Save
		</button>
		<button type="button" class="btn-link" onclick='Boxy.get(this).hideAndUnload()'>Cancel</button>
	</div>
</form>
<script type="text/javascript">
	var dTeams = [];
	$(document).ready(function () {
		$("div[class*='warning-bar']").hide();
		setTimeout(function () {
			dTeams = $("#careTeams").select2("val");
		}, 100);
		$("#careTeams").change(function () {
			if (compareStringArray($("#careTeams").select2("val"), dTeams)) {
				$("button[name='editPCT']").prop("disabled", true);
				$("div[class*='warning-bar']").hide();
				$("div[class*='notify-bar']").show();
				$("label[name='reason']").hide();
			} else {
				$("button[name='editPCT']").prop("disabled", false);
				$("div[class*='warning-bar']").show();
				$("div[class*='notify-bar']").hide();
				$("label[name='reason']").show();
			}
		});
	});

	function compareStringArray(arr1, arr2) {
		if (arr1.length != arr2.length) {
			return false;
		}
		return arr1.sort().join() == arr2.sort().join();
	}

	function editPCTCancel() {
		$("#careTeams").select2("val", dTeams).trigger("change")
	}

	function commitEditPCT() {
		if (!compareStringArray($("#careTeams").select2("val"), dTeams)) {
			$.ajax({
				url: "<?= $_SERVER['PHP_SELF'] ?>",
				type: "post",
//            dataType: "json",
				data: $('form[name="editPCTForm"]').serialize(),
				beforeSend: function () {
				},
				success: function (data) {
					console.log("Success")
					if (data.split(":")[0] === "ok") {
						Boxy.get($('.close')).hideAndUnload();
					} else {
						Boxy.alert(data.split(":")[1]);
					}
				},
				error: function (data) {
					console.log("Error")
					console.log(data)
				}
			});
		}
	}
</script>