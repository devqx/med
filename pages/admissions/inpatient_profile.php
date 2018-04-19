<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InPatientDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff(@$_SESSION['staffID']);

$aid = $_GET['aid'];
$ip = (new InPatientDAO())->getInPatient($aid, true);
$pdo = (new MyDBConnector())->getPDO();

$pid = $ip->getPatient()->getId();
$sql = "SELECT * FROM patient_demograph p LEFT OUTER JOIN  socio_economic_status s ON (p.socio_economic=s.id) /*LEFT OUTER JOIN life_style l on  (p.lifestyle=l.id)*/ WHERE  patient_ID='" . $pid . "' LIMIT 1";
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$stmt->execute();
$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
$retVal = "";


//if(!$patient){$patient=new Manager();}

if ($ip->getPatient() == null) { ?>
	<div class="warning-bar"><p align="center">FATAL ERROR: Patient Profile Not found!<br/> Return to <a
				href="/admissions/">Home</a></p></div>
	<?php exit;
} else { ?>
	<input name="id" type="hidden" value="<?= $_GET['pid'] ?>">
	<?php if (!isset($_SESSION)) {
		@session_start();
	}
	$_SESSION['patientID'] = $pid;
	$_SESSION['patient_sex'] = $row_data['sex'];
	?>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc.webcam.php'; ?>
	<div class="patient_profile_container">
		<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
			<tbody>
			<?php if (!$patient->isImmunization($pid) && ($patient->getPatientAgeInMonths($pid) <= (18 * 12))) { ?>
				<tr>
				<td>
					<div class="warning-bar"><?= $patient->getPatientName($pid) ?> is not yet enrolled into immunization.
						<div class="close-warning"><a href="javascript:void(0)">Enroll Now</a> <a href="javascript:void(0)">No</a>
						</div>
					</div>
				</td></tr><?php } ?>
			<tr>
				<td>
					<!-- patient demographic details -->
					<div id="profile_container">
						<?php $ARR = $row_data;
						include $_SERVER['DOCUMENT_ROOT'] . '/admissions/inpatient_demograph.php'; ?>
					</div>
					<!-- /patient demographic details -->
				</td>
			</tr>
			<tr>
				<td>
					<div id="tabbedPane" class="clearfix">
						<ul>
							<li><a href="javascript:void(0)" id="task" onClick="showTabs(1)" title="Clinical Tasks"><span><span>Clinical Tasks</span></a>
							</li>
							<li><a href="javascript:void(0)" id="ip-notes" onClick="showTabs(2)" title="InPatient Notes"><span>InPatient Notes</span></a>
							</li>
							<li><a href="javascript:void(0)" id="op-notes" onClick="showTabs(16)" title="Out Patient Encounters"><span>Out Patient Encounters</span></a>
							</li>
							<li><a href="javascript:void(0)" id="observation" onClick="showTabs(14)" title="Observations"><span>Nurses Observations</span></a>

							</li>

                            <li><a href="javascript:void(0)" id="early_warning" onClick="showTabs(30)" title="Early Warning"><span>EWS</span></a>

                            </li>

							<li><a href="javascript:void(0)" id="fluid_chart" onClick="showTabs(15)" title="Fluid Chart"><span>Fluid Chart</span></a>
							</li>
							<li><a href="javascript:void(0)" id="vitals" onClick="showTabs(11)" title="Vital Signs"><span>Vital Signs</span></a></li>
							<li>
								<a href="javascript:void(0)" id="regimens" onClick="showTabs(3)" title="Regimens"><span>Regimens</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="allergens" onClick="showTabs(7)" title="Allergens"><span>Allergens</span></a>
							</li>
							<li><a href="javascript:void(0)" id="problem_list" onClick="showTabs(5)" title="Problem List"><span>Problem List</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="labs" onClick="showTabs(6)" title="Patient's Laboratory"><span>Labs</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="scans" onClick="showTabs(8)" title="Medical Imaging"><span>Imaging</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="procedures" onClick="showTabs(10)" title="Procedures"><span>Procedure</span></a>
							</li>
							<li>
								<a href="javascript:void(0)" id="careTeam" onClick="showTabs(9)" title="Care Team"><span>Care Team</span></a>
							</li>
							<li><a href="javascript:void(0)" id="documents" onClick="showTabs(12)" title="Documents"><span>Documents</span></a></li>
							<li><a href="javascript:void(0)" id="billing" onClick="showTabs(13)" title="Billing"><span>Billing</span></a></li>
							<li><a href="javascript:void(0)" id="consumables" onClick="showTabs(20)" title="Consumables"><span>Consumables</span></a></li>
							<li class="dropdown pull-right" id="more">
								<a href="#" id="myTabDrop1" class="dropdown-toggle" data-toggle="dropdown"><span>More <b class="caret"></b></span></a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="myTabDrop1"></ul>
							</li>

						</ul>
					</div>
					<div id="contentPane_">
						<span id="contentPane"></span>
					</div>
				</td>
			</tr>
			<tr>
				<td align="right" valign="baseline">&nbsp;</td>
			</tr>
			<tr>
				<td height="17" align="right">&nbsp;</td>
			</tr>
		</table>
	</div>
	<?php //} while ($row_data = mysql_fetch_assoc($chk)); ?>
	<?php
} ?>
<script type="text/javascript">
	$(document).ready(function () {
		showTabs(1);
		$('img.passport').parent('a').click(function () {
			$("#camera").show('slow');
		});
		$("a.pager_").live("click", function (e) {
			$("#contentPane").load($(this).attr("data-href"));
			e.preventDefault();
			return false;
		});
		$("#save-ip-notes").live('click', function () {
			alert("test save" + $(this).parent("form").serialize().toString());
		});
		<?php
		//if patient is not enrolled into immunization and age is b/w 0 and 18 years
		//        if(!$patient->isImmunization($pid) && $patient->getPatientAgeInMonths($pid)  <= (18*12)){
		if (!$patient->isImmunization($pid) && $patient->getPatientAgeInDays($pid) <= (18 * 364)) {// use 364 days as 1 year
		?>
		$(".warning-bar .close-warning a:first-child").click(function () {
			enroll('<?=$pid ?>');
		});
		$(".warning-bar .close-warning a:last-child").click(function () {
			$(this).parent().parent().slideUp('slow');
		});
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

		function enroll(x) {
			$.ajax({
				url: '/immunization/ajax.enroll_patients.php',
				type: 'POST',
				data: {"patient_to_be_enrolled[]": x, "type": "immunization"},
				success: function (s) {
					if (s == 'ok') {
						$(".warning-bar .close-warning a:first-child").parent().parent().html('<i class="icon-ok-sign"></i>Patient enrolled into immunization successfully.');
						setTimeout(function () {
							$(".warning-bar").parents('tr').slideUp('slow');
						}, 2000);
					} else {
						$(".warning-bar .close-warning a:first-child").parent().parent().html('<i class="icon-exclamation-sign"></i>Enrollment failed! <br> However you can try again from the immunization home page');
					}
				}
			});
		}
		<?php } ?>
	});

	//Highcharts.setOptions({global: {useUTC: false}});
	function loading() {
		$("#contentPane").html('<table align="center" width="100%" height="100%"><tr><td valign="middle" align="center"><img src="/img/loading.gif" class="preloader" /></td></tr></table>');
	}
	var dates = "", filter = "&active&type=&severity=";
	function showTabs(t, index, param) {
		isHistory = typeof param !== 'undefined';
		$('.active').removeClass('active');
		loading();
		var url = "";
		switch (t) {
			case 1:
			{
				if (typeof index === "undefined" || index == '1') {
					url = "/admissions/patientTabs/clinicalTask.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>" + (isHistory ? "&history=true" : "");
				} else if (index == '2') {
					url = "/admissions/patientTabs/taskHistory.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				} else if (index == '3') {
					url = "/admissions/patientTabs/showCareTeam.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				} else if (index == '4') {
					url = "/admissions/patientTabs/taskCompleted.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				}
				$('#task').parent().attr('class', 'active');
				break;
			}
			case 2:
			{
				$('#ip-notes').parent().attr('class', 'active');
				url = "/admissions/patientTabs/progressNote.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}

            case 30:
            {
                url = "/admissions/patientTabs/earlyWarning.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
                break;
            }
			case 3:
			{
				$('#regimens').parent().attr('class', 'active');
				url = "/prescriptionDetails.php?id=<?= $_GET['pid'] ?>&aid=<?= $_GET['aid'] ?>";
				break;
			}
			case 4:
			{
				$('#diagnoses').parent().attr('class', 'active');
				url = "/admissions/patientTabs/diagnoses.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 5:
			{
				$('#problem_list').parent().attr('class', 'active');
				//problem list
				url = "/admissions/patientTabs/problem_list.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>" + filter;
				break;
			}
			case 6:
			{
				$('#labs').parent().attr('class', 'active');
				url = "/admissions/patientTabs/labs.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 7:
			{
				$('#allergens').parent().attr('class', 'active');
				url = "/admissions/patientTabs/allergens.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 8:
			{
				$('#scans').parent().attr('class', 'active');
				url = "/admissions/patientTabs/imaging.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 9:
			{
				$('#careTeam').parent().attr('class', 'active');
				url = "/admissions/patientTabs/showCareTeam.php?pid=<?php echo $_GET['pid'] ?>&aid=<?php echo $_GET['aid'] ?>";
				break;
			}
			case 10:
			{
				$('#procedures').parent().attr('class', 'active');
				url = "/admissions/patientTabs/procedures.php?pid=<?php echo $_GET['pid'] ?>&aid=<?php echo $_GET['aid'] ?>";
				break;
			}
			case 11:
			{
				$('#vitals').parent().attr('class', 'active');
				url = "/vitals-all.php?id=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 12:
			{
				$('#documents').parent().attr('class', 'active');
				url = "/documents/patient_docs.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 13:
			{
				$('#billing').parent().attr('class', 'active');
				url = "/billing/patient_bill_doc.php?id=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 14:
			{
				$('#observation').parent().attr('class', 'active');
				url = "/admissions/patientTabs/observations.php?id=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}
			case 15:
			{
				$('#fluid_chart').parent().attr('class', 'active');
				url = "/admissions/patientTabs/fluid_chart.php?id=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>";
				break;
			}case 16:
			{
				$('#op-notes').parent().attr('class', 'active');
				url = "/patient_profile.php?id=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>&view=encounters";
				break;
			}
			case 20:
			{
				$('#consumables').parent().attr('class', 'active');
				url = "/consumableItemDetails.php?id=<?= $_GET['pid']?>&aid=<?= $_GET['aid'] ?>"
				break;
			}
		}
		$('#contentPane').load(url);

	}

	function sendDischarge(x) {
		//TODO: if the admissions module exists
		Boxy.load('/admissions/boxy.get-discharged-why.php?aid=' + x, {title: 'Discharge Patient'});
	}
</script>