<?php
$_GET['suppress'] = TRUE;
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staff.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CareTeamDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CareTeamDAO.php';

$teams = (new CareTeamDAO())->getCareTeamsByStaffMembership($staff->getId());
$staffs = (new StaffDirectoryDAO())->getStaffsMin();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
$wards = (new WardDAO())->getWards();
?>
<section style="width: 700px">
	<form method="post" action="/admissions/ajax.place-admission.php" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<span id="message"></span>
		<label>Admit to Ward
			<select required name="ward_id">
				<option value="">-- Select Ward --</option><?php foreach ($wards as $ward) { ?>
					<option value="<?= $ward->getId() ?>"><?= $ward->getName() ?></option><?php } ?>
			</select></label>
		<label>Specify reason for admission <textarea name="reason" required="required"></textarea></label>
		<label>Anticipated discharge date <input required type="text" name="anticipatedDischarge" id="anticipatedDischarge"/></label>
		<label class="menu-head"><input type="checkbox" name="rounding" id="rounding"> <strong>Create a clinical
				Task?</strong> </label>
		<label>Care Givers</label>
		<label>
			<select multiple="multiple" name="careTeam[]" id="careTeams">
				<?php
				foreach ($teams as $item) {
					echo '<option ' . (($item->getAnId() !== null) ? ' selected="selected"' : '') . ' value="team_' . $item->getId() . '">' . $item->getName() . ' (' . $item->getDescription() . ')</option>';
				}
				foreach ($staffs as $staff) {
					echo '<option value="staff_' . $staff->getId() . '">' . $staff . ' (' . $staff->getProfession() . ')</option>';
				}
				?>
			</select>
		</label>
		<label>Primary Care Giver</label>
		<label>
			<select name="primary" id='primary'>
				<option value=""> --- select primary care giver ---</option>
			</select>
		</label>

		<div class="clearfix"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<input type="hidden" name="pid" id="pid" value="<?= $_GET['id'] ?>">
			<button type="reset" class="btn-link" onclick="cancel()">cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		jQuery('#anticipatedDischarge').datetimepicker({
			minDate: 0,
			timepicker: false,
			formatDate: "Y-m-d",
			format: "Y-m-d"
		});

		format = function (item) {
			return item.name;//+ " (" + item.description + ")";
		};

		$("#careTeams").change(function () {
			var data = $("#careTeams").select2("data");
			var html = "<option value=''>--- select primary care giver ---</option>"
			for (var i = 0; i < data.length; i++) {
				html += "<option value='" + data[i].id + "' >" + data[i].text + "</option>";
			}
			$("#primary").select2({width: '100%'}).html(html).trigger("change");
		});
		setTimeout(function () {
			$("#careTeams").trigger("change");
		}, 100);
	});

	function start() {
		$('form span#message').html('<img src="/img/loading.gif"> Please wait...').attr('class', '')
	}
	function done(data) {
		var s = data.split(":");
		if (s[0] == "error") {
			$('form span#message').html(s[1]).attr('class', 'warning-bar');
		} else if (s[0] == "ok") {
			if ($("#rounding").is(":checked")) {
				Boxy.load("/admissions/dialogs/newClinicalTask.php?aid=" + s[1] + "&pid=" + $("#pid").val(), {
					title: "New Clinical Task", afterHide: function () {
						location.href = "/admissions/inpatient_profile.php?pid=" + $("#pid").val() + "&aid=" + s[1];
					}
				});
			} else {
				location.href = "/admissions/inpatient_profile.php?pid=" + $("#pid").val() + "&aid=" + s[1];
			}
		} else {
			Boxy.alert("Oops! something went wrong!");
		}
	}

	function cancel() {
		$('input[name="ger89"]').prop('checked', false).iCheck('update');
		Boxy.get($('.close')).hideAndUnload();
	}


</script>
