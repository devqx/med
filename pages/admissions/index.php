<?php
exit;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';

//$adms = (new InPatientDAO())->getActiveInPatients(TRUE);
$adms = (new InPatientDAO())->getInPatients(true);
$wards = (new WardDAO())->getWards(false);
?>
In-patients
<br/>
<div id="admission_container" ng-app="medicPlus">
	<!--<ul class="adm_list">-->
	<!--TODO: implement a filter or search bar-->

	<div class="input-append" style="border: 1px solid rgba(#ebebeb);padding: 4px;background-color: #eee" id="filter_header">
		<form class="filterform" action="javascript:;">
			<table style="width: 100%">
				<tr>
					<td style="width: 60%"><input class="filterinput" type="text" style="width:95%" placeholder="search ward or patient emr/name"></td>
					<td style="width: 40%"><select>
							<option>-- filter patients by ward --</option>
							<?php foreach ($wards as $ward) { ?>
								<option><?= $ward->getName() ?></option><?php } ?>
						</select>
					</td>
				</tr>
			</table>
		</form>
	</div>

	<ul class="ui-listview ui-listview-inset ui-corner-all ui-shadow" id="posts">
		<?php
		$currentWard = "";
		if (sizeof($adms) > 0) {
			foreach ($adms as $adm) {
				if ((is_null($adm->getBed()) && $currentWard === "") || (!is_null($adm->getBed()) && $currentWard != $adm->getBed()->getRoom()->getWard()->getName())) {
					?>
					<li class="ui-li ui-li-divider ui-bar-d"><h4><i class="icon-building"></i><?= (is_null($adm->getBed()) ? 'Not Assigned' : $adm->getBed()->getRoom()->getWard()->getName()) ?></h4></li>
					<?php
					$currentWard = (is_null($adm->getBed()) ? '...' : $adm->getBed()->getRoom()->getWard()->getName());
				}
				?>
				<li class="ui-li ui-li-static ui-btn-up-c">
					<table class="table">
						<tbody>
						<tr>
							<td>
								<span class="fadedText pull-left">Patient:</span><br/><a class="item" data-ward="<?= (is_null($adm->getBed()) ? 'Not Assigned' : $adm->getBed()->getRoom()->getWard()->getName()) ?>" data-pid="<?= $adm->getPatient()->getId() ?>" href="/admissions/inpatient_profile.php?pid=<?= $adm->getPatient()->getId() ?>&aid=<?= $adm->getId() ?>"><?= $adm->getPatient()->getFullname() ?></a>
								(<?= $adm->getPatient()->getId() ?>)
							</td>
							<td><span class="fadedText pull-left">Bed:</span><br/>
								<?= (is_null($adm->getBed()) ? "<a href=\"javascript:void(0)\" onclick=\"Boxy.load('/admissions/assignBed.php?pid=" . $adm->getPatient()->getId() . "&aid=" . $adm->getId() . "', {title:'Assign Bed'})\">Assign bed</a>" : $adm->getBed()->getName()) ?></td>
							<td><span class="fadedText pull-left">Date of InPatient:</span><br/><?= date("Y M, d", strtotime($adm->getDateAdmitted())) ?></td>
							<td><span class="fadedText pull-left">Admitted by:</span><br/><?= $adm->getAdmittedBy()->getFullname() ?></td>
							<td><span class="fadedText pull-left">reason:</span><br/><?= $adm->getReason() ?></td>
						</tr>
						</tbody>
					</table>
				</li>
				<?php
			}
		} else {
			echo '<div class="alert-info">No admitted patient<div>';
		}
		?>
	</ul>
	<!--TODO: paginate this list-->

	<!-- end admisisons_container-->
</div>
<script type="text/javascript">
	(function ($) {
		medicPlus = angular.module("medicPlus", []);
		// custom css expression for a case-insensitive contains()

		jQuery.expr[':'].Contains = function (a, i, m) {
			return (a.getAttribute("data-ward") + a.getAttribute("data-pid") + a.textContent + a.innerText + "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
		};


		function listFilter(header, list) {
			// header is any element, list is an unordered list
			// create and add the filter form to the header

			input = $('input.filterinput');
			$(input)
				.change(function () {
					var filter = $(this).val();
					if (filter) {
						// this finds all links in a list that contain the input,
						// and hide the ones not containing the input while showing the ones that do
						//console.log("hide:"+$(list).find("a[class='item']:not(:Contains(" + filter + "))").parents().find('li').attr('class'));
						$(list).find("a[class='item']:not(:Contains(" + filter + "))").parents('li.ui-li-static').hide();//slideUp('fast');
						$(list).find("a[class='item']:Contains(" + filter + ")").parents('li.ui-li-static').show();//slideDown('fast');
					} else {
						$(list).find("li.ui-li-static").show();//slideDown();
//                        $(list).find("li").parent().parent().show();//slideDown();
					}
					return false;
				})
				.keyup(function () {
					// fire the above change event after every letter
					$(this).change();
				});
		}


		//ondomready
		$(function () {
			listFilter($("#filter_header"), $("#posts"));
		});
	}(jQuery));
</script>