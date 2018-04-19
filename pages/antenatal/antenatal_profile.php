<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
$pdo = (new MyDBConnector())->getPDO();
$patientID = escape($_GET['id']);
$sql = "SELECT * FROM patient_demograph p LEFT OUTER JOIN socio_economic_status s ON (p.socio_economic=s.id) LEFT OUTER JOIN life_style l ON (p.lifestyle=l.id) WHERE patient_ID='" . $patientID . "' AND patient_ID IN (SELECT patient_id FROM enrollments_antenatal) LIMIT 1";

$pid = $patientID;
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$stmt->execute();

$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
$retVal = "";
if ($stmt->rowCount() == 0) {
	?>
	<div class="warning-bar"><p align="center">Sorry, we couldn't load the requested profile<br/>
			<i class="icon-home"></i><a href="/">Home</a></p></div>
	<?php exit;
} else {  //do {

	require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InPatientDAO.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalEnrollmentDAO.php";

	$protect = new Protect();
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
	$ip = (new InPatientDAO())->getActiveInPatient($patientID, TRUE);
//    $antenatal = (new AntenatalEnrollmentDAO())->getActiveInstance($patientID, TRUE);
	$antenatal = (new AntenatalEnrollmentDAO())->get($_GET['aid'], TRUE);
	?>
	<style type="text/css">
		@media print {
			body * {
				visibility: hidden;
			}

			#contentPane * {
				visibility: visible;
			}

			#contentPane {
				position: absolute;
				top: 40px;
				left: 30px;
			}

			div.vaccine-block {
				border: 1px solid #000;
			}
		}
	</style>

	<script type="text/javascript">
		function loading() {
			$("#contentPane").html('<table align="center" width="100%" height="100%"><tr><td valign="middle" align="center"><img src="/img/loading.gif" class="preloader" /></td></tr></table>');
		}

		var filter = "&active&type=";
		function showTabs(t) {
			if (t == 1) {
				var urn = "/antenatal/tabs/notes.php?instance=<?= $antenatal->getId()?>";
				$('.active').attr('class', '');
				$('#visitn').parent().attr('class', 'active');
			}
			else if (t == 2) {
				var urn = location.href + "&view=vaccines";
				$('.active').attr('class', '');
				$('#vaccines').parent().attr('class', 'active');
			}
			else if (t == 3) {
				var urn = location.href + "&view=medications";
				$('.active').attr('class', '');
				$('#rx_').parent().attr('class', 'active');
			}	else if (t == 4) {
				var urn = location.href + "&view=assessments";
				$('.active').attr('class', '');
				$('#assessments').parent().attr('class', 'active');
			} else if (t == 5) {
				urn = location.href + "&view=precon";
				if (filter !== "") {
					urn = location.href + "&view=precon" + filter;
				}
				$('.active').attr('class', '');
				$('#pre-con').parent().attr('class', 'active');
			}
			else if (t == 6) {
				var urn = location.href + "&view=labs";
				$('.active').attr('class', '');
				$('#labs').parent().attr('class', 'active');
			}
			else if (t == 7) {
				var urn = location.href + "&view=vitals";
				$('.active').attr('class', '');
				$('#vitals').parent().attr('class', 'active');
			}
			else if (t == 8) {
				var urn = location.href + "&view=obstetrics_history";
				$('.active').attr('class', '');
				$('#pp').parent().attr('class', 'active');
			}
			else if (t == 9) {
				var urn = location.href + "&view=riskAss";
				$('.active').attr('class', '');
				$('#riskAss').parent().attr('class', 'active');
			}
			else if (t == 10) {
				var urn = location.href + "&view=imaging";
				$('.active').attr('class', '');
				$('#scans').parent().attr('class', 'active');
			} else if (t == 11) {
				var urn = location.href + "&view=gyn_history";
				$('.active').attr('class', '');
				$('#gyn').parent().attr('class', 'active');
			}	else if (t == 12) {
				$('.active').attr('class', '');
				$('#appointments').parent().attr('class', 'active');
			}	else {
			}

			if (t != 7 && t != 3 && t != 12) {
				$.ajax({
					url: urn,
					cache: true,
					success: function (s) {
						$("#contentPane").html(s);
						$('.vaccine-block[title]').tipTip({delay: 10});
						if (t == 5) {
							$('#history, #active').click(function () {
								h = $("#history").is(":checked") ? '1' : '0';
								k = $("#active").is(":checked") ? '1' : '0';
								filter = "&active=" + k + "&history=" + h;

								showTabs(5);
							});
						}
					}, beforeSend: function () {
						loading();
					}
				});
			} else if (t == 3) {
				loading();
				$('#contentPane').load('/prescriptionDetails.php?id=<?= $_GET['id'] ?>');
			}	else if (t == 7) {
				loading();
				$('#contentPane').load('/antenatal/tabs/vitals-all.php?id=<?= $_GET['id'] ?>');
			}	else if (t == 12) {
				loading();
				$('#contentPane').load('/appointments/appointments.php?pid=<?= $_GET['id'] ?>');
			}
		}

		function sendCloseEnrollment(aid) {
			Boxy.load('/antenatal/boxy.close-antenatal-enrollment.php?aid=' + aid, {title: 'Close Enrollment'});
		}

		function showNewDiagnosisDlg() {
			Boxy.load('/boxy.addDiagnosis.php?pid=<?= $patientID ?>', {title: 'New Diagnosis'});
		}
		$(document).ready(function () {
			$('a.resolveConditionLink').live('click', function () {
				var pid = $(this).attr('data-pid');
				var id = $(this).attr('data-id');
				$.post('/api/resolve_pre_condition.php', {id: id, pid: pid}, function (data) {
					if (data) {
						showTabs(5);
					} else {
						Boxy.alert("Request failed");
					}
				}, 'json').error(function (e) {
					Boxy.alert("Request not sent");
				});
				showTabs(5);
			});

			<?= ((isset($_GET['startnewvisit']) && $_GET['startnewvisit'] == "true") ? 'newAntenatalVisit();' : '')?>
		});
	</script>
	<input name="id" type="hidden" value="<?php echo $_GET['id'] ?>">
	<script src="/assets/webcam/webcam.js"></script>
	<script src="/assets/js/camScript.js"></script>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc.webcam.php'; ?>
	<div class="patient_profile_container">
		<table style="width: 100%">
			<tbody>
			<tr>
				<td>
					<!-- patient demographic details -->
					<div id="profile_container">
						<?php if (!isset($_SESSION)) {
							@session_start();
						}
						$_SESSION['patientID'] = $row_data['patient_ID'];
						$_SESSION['patient_sex'] = $row_data['sex']; ?>

						<?php $ARR = $row_data;
						include $_SERVER['DOCUMENT_ROOT'] . '/patient_demograph.php'; ?>

					</div>
					<!-- /patient demographic details -->
				</td>
			</tr>
			<tr>
				<td>
					<div id="tabbedPane" class="clearfix container-fluid">
						<ul>
							<li><a href="javascript:void(0)" id="visitn" onClick="showTabs(1)" title="Notes"><span>Notes</span></a>
							</li>
							<li><a href="javascript:void(0)" id="assessments" onClick="showTabs(4)" title="Assessments/Exams"><span>Assessments/Exams</span></a>
							</li>
							<li><a href="javascript:void(0)" id="gyn" onClick="showTabs(11)" title="Gynaecological History"><span>Gynaecological History</span></a>
							</li>
							<li><a href="javascript:void(0)" id="pp" onClick="showTabs(8)" title="Past Pregnancies"><span>Obstetrics History</span></a>
							</li>
							<li><a href="javascript:void(0)" id="vitals" onClick="showTabs(7)" title="Antenatal Vital Signs"><span>Vital Signs</span></a>
							</li>
							<li><a href="javascript:void(0)" id="labs" onClick="showTabs(6)" title="Antenatal Labs/Tests"><span>Labs/Tests</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="scans" onClick="showTabs(10)" title="Medical Imaging"><span>Imaging</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="rx_" onClick="showTabs(3)" title="Medications"><span>Medications</span></a>
							</li>
							<li><a href="javascript:void(0)" id="riskAss" onClick="showTabs(9)" title="Risk Assessment"><span>Risk Assessment</span></a>
							</li>
							<li><a href="javascript:void(0)" id="vaccines" onClick="showTabs(2)" title="Antenatal Vaccines"><span>Vaccines</span></a>
							</li>
							<li><a href="javascript:void(0)" id="pre-con" onClick="showTabs(5)" title="Patient's Problems List"><span>Problems List</span></a>
							</li>
							<li><a href="javascript:void(0)" id="appointments" onClick="showTabs(12)" title="Appointments"><span>Appointments</span></a></li>
							<li class="dropdown pull-right" id="more">
								<a href="#" id="myTabDrop1" class="dropdown-toggle" data-toggle="dropdown"><span>More <b class="caret"></b></span></a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="myTabDrop1">
								</ul>
							</li>
						</ul>
					</div>
					<div id="contentPane_">
						<span id="contentPane"></span>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<script src="/js/jquery.PrintArea.js_4.js"></script>
	<script src="/js/core.js"></script>
	<?php
//}while($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
} ?>
