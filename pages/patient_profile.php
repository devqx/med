<?php
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff(@$_SESSION['staffID']);
if ($this_user && ($this_user->hasRole($protect->records) || $this_user->hasRole($protect->pharmacy) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->accounts))) {
	require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
	$patientID = escape($_GET['id']);
	$sql = "SELECT * FROM patient_demograph p LEFT OUTER JOIN socio_economic_status s ON (p.socio_economic=s.id) /*LEFT OUTER JOIN life_style l on  (p.lifestyle=l.id)*/ WHERE  patient_ID=" . $patientID . " LIMIT 1";
	$pid = $patientID;
	$pdo = (new MyDBConnector())->getPDO();
	$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$chk = $stmt->execute();
	
	$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
	if ((new PatientDemographDAO())->getPatient($patientID) === null || $stmt->rowCount() == 0) {
		?>
		<!-- Begin Content -->
		<div class="warning-bar"><p>FATAL ERROR: Patient Profile Not found!<br/> Return to <a
					href="/patient_find-or-create.php">Home</a></p></div>
		<?php
	} else {
		
		// do {
		?>
		<link rel="stylesheet" type="text/css" href="/assets/css/camStyles.css?<?= rand() ?>"/>
		<script type="text/javascript">
			$(document).ready(function () {
				$('img.passport').parent('a').click(function () {
					$("#camera").show('slow');
				});
				$("a.pager_").live("click", function (e) {
					$("#contentPane").load($(this).attr("data-href"));
					e.preventDefault();
					return false;
				});
				$("#save-progress").live('click', function () {
					alert("test save" + $(this).parent("form").serialize().toString());
				});
				var tItems = $("#tabbedPane ul li");
				var ntot = tItems.length;
				////tItems.css({'min-width': ($("#tabbedPane").width() / ntot) - 20});
				//////$("#tabbedPane ul li").css({"padding-top":"7px","padding-bottom":"-5px", "height":"15px", "white-space":"nowrap"});
				<?php
				//if patient is not enrolled into immunization and age is b/w 0 and 18 years
				if (!$patient->isImmunization($pid) && $patient->getPatientAgeInDays($pid) <= (18 * 364)) {// use 364 days as 1 year
				?>
				$(".warning-bar .close-warning a:first-child").click(function () {
					enroll('<?= $pid ?>');
				});
				$(".warning-bar .close-warning a:last-child").click(function () {
					$(this).parent().parent().slideUp('slow');
				});

				function enroll(x) {
					$.ajax({
						url: '/immunization/ajax.enroll_patients.php',
						type: 'POST',
						data: {"patient_to_be_enrolled[]": x, "type": "immunization"},
						success: function (s) {
							if (s === 'ok') {
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
				$("#contentPane").html('<div class="ball"></div>');
			}
			var dates = "", filter = "&active=&type=&severity=";
			function showTabs(t, k, e) {
				$('#newLink').html('');
				var xLink = '<?php if ($ip === null ) { ?><input type="checkbox" id="toAdmit" name="ger89" value="<?= $pid ?>" onClick="askAdmit($(this), \'<?= $pid ?>\')"> Request Admission<?php } else { ?><input type="checkbox" name="ger89" value="<?= $pid ?>" onClick="askDischarge($(this), \'<?= $pid ?>\')">Discharge<?php } ?> ';
				var t1Link = '<div style="width:50%;float:left"><?php if ($this_user->hasRole($protect->doctor_role)) { ?><i class="icon-folder-open"></i><label style="display: inline-block"><a href="javascript:void(0)" onClick="Boxy.load(\'boxy.soap.php?pid=<?= $_GET['id'] ?>\',{title:\'Add Note / Diagnosis Report\'})">S. O. A. P.</a></label> |<?php } ?> <?php if ($this_user->hasRole($protect->doctor_role)) {
						if (is_dir("admissions")) { ?><a href="javascript:void(0)"><label style="cursor:pointer;display:inline-block">' + xLink + '</label></a> | <?php }
						} ?> <?php if ($this_user->hasRole($protect->doctor_role)) { ?><label style="display: inline-block"><i class="icon-book"></i><a href="javascript:void(0)" onclick="showNewDocNote()">Doc. Note</a></label> <?php } ?> </div><?php if ($this_user->hasRole($protect->doctor_role)) { ?> <div style="width:auto;float:right"><i class="icon-exchange"></i><a href="javascript:void(0)" onClick="Boxy.load(\'/boxy.transferPatient.php?id=<?= $_GET['id'] ?>\',{title:\'Transfer Patient\'})" title="Transfer Patient to another Doctor">Refer Patient</a></div><?php } ?>';
				var urn;
				if (t === 1) {
					urn = location.href + "&view=encounters";
					$('.active').attr('class', '');
					$('#enc').parent().attr('class', 'active');
					//$('#newLink').html(t1Link);
				}
				if (t === '1b') {
					urn = location.href + "&view=visits";
					$('.active').attr('class', '');
					$('#visitn').parent().attr('class', 'active');
					//$('#newLink').html(t1Link);
				}
				else if (t === 2) {
					$('.active').attr('class', '');
					$('#vitals').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 3) {
					urn = location.href + "&view=rx";
					$('.active').attr('class', '');
					$('#rx_').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 4) {
					urn = location.href + "&view=allergens";
					$('.active').attr('class', '');
					$('#allerg').parent().attr('class', 'active');
					$('#newLink').html('<?php if ($this_user->hasRole($protect->pharmacy) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) { ?><a href="javascript:void(0)" onClick="Boxy.load(\'/boxy.addPatientAllergen.php?id=<?= $_GET['id'] ?>\',{title: \'New Allergen\'})">Add New Allergen</a><?php } ?>');
				}
				else if (t === 5) {
					urn = location.href + "&view=precon";
					if (filter !== "") {
						urn = location.href + "&view=precon" + filter;
					}
					$('.active').attr('class', '');
					$('#pre-con').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 6) {
					urn = location.href + "&view=labs";
					$('.active').attr('class', '');
					$('#labs').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 7) {
					urn = location.href + "&view=billings";
					$('.active').attr('class', '');
					$('#billings').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 8) {
					urn = location.href + "&view=procedures";
					$('.active').attr('class', '');
					$('#procedures').parent().attr('class', 'active');
					$('#newLink').html('<a id="newProc" title="New Procedure" href="javascript:;" data-href="/procedures/new_patient_procedure.php?id=<?= trim($_GET['id']); ?>">New Procedure</a>');
				}
				//purposely skipped 9
				else if (t === 10) {
					urn = location.href + "&view=meds";
					$('.active').attr('class', '');
					$('#meds').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 11) {
					urn = location.href + "&view=imaging";
					$('.active').attr('class', '');
					$('#scans').parent().attr('class', 'active');
					$('#newLink').html('');
				}
				else if (t === 12) {
					urn = location.href + "&view=visit_summary";
					if (dates !== "") {
						urn += dates;
					}
					$('.active').attr('class', '');
					$('#summaries').parent().attr('class', 'active');
					$('#newLink').html('<i class="icon-print"></i><a href="javascript:void(0)" id="printSummaryBtn" title="Print">Print</a>');
				}
				else if (t === 13) {
					$('.active').attr('class', '');
					$('#appointments').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 14) {
					if (typeof k === "undefined") {
						urn = "/outpatient_tasks/patient_rounds.php?outpatient=true&pid=<?= $pid ?>&aid=";
					} else if (k === 4) {
						urn = "/admissions/patientTabs/taskCompleted.php?outpatient=true&pid=<?=$pid ?>";
					} else {
						urn = "/admissions/patientTabs/taskHistory.php?outpatient=true&pid=<?=$pid ?>&aid=";
					}
					$('.active').attr('class', '');
					$('#op_tasks').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 15) {
					$('.active').attr('class', '');
					urn = "/documents/patient_docs.php?pid=<?= $pid ?>";
					$('#documents').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 16) {
					urn = location.href + "&view=dentistry";
					$('.active').attr('class', '');
					$('#dentology').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 17) {
					if (k === undefined || k === 1) {
						urn = location.href + "&view=ophthalmology";
					} else if (k !== undefined && k === 2) {
						urn = location.href + "&view=ophthalmology&pane=items";
					}
					$('.active').attr('class', '');
					$('#ophthalmology').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 18) {
					urn = location.href + "&view=physio";
					$('.active').attr('class', '');
					$('#physiotherapy').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 19) {
					$('.active').attr('class', '');
					$('#medical_exam').parent().attr('class', 'active');
					$('#newLink').html('');
				}else if(t === 20){
					$('.active').attr('class', '');
					$('#item_request').parent().attr('class', 'active');
					$('#newLink').html('');
				} else if (t === 100) {
					//jump straight to 100 :)
					$('.active').attr('class', '');
					$('#arv').parent().attr('class', 'active');
					$('#newLink').html('');
				}else {
				}
				if (t !== 2 && t !== 7 && t !== 10 && t !== 3 && t !== 13 && t !== 19 && t !== 20 && t !== 100) {
					$.ajax({
						url: urn,
						cache: true,
						success: function (s) {
							$("#contentPane").html(s);
							//special case for tab1
							if (t === 1) {
								setTimeout(function () {
									$('#visits').dataTable({
										"pagingType": "simple_numbers",
										"bLengthChange": false,
										"order": []
									});
								}, 4);
							} else if (t === 5) {
							} else if (t === 12) {
								$("#from").datetimepicker({
									format: 'Y-m-d',
									formatDate: 'Y-m-d',
									timepicker: false,
									onChangeDateTime: function (dp, $input) {
										if ($input.val().trim() !== "") {
											$("#to").val('').removeAttr('disabled');
										}
										else {
											$("#to").val('').attr({'disabled': 'disabled'});
										}

									}
								});
								$("#to").datetimepicker({
									format: 'Y-m-d',
									formatDate: 'Y-m-d',
									timepicker: false,
									onShow: function (ct) {
										this.setOptions({minDate: $("#from").val() ? $("#from").val() : false});
									},
									onSelectDate: function (ct, $i) {
										if ($("#from").val() && $("#to").val()) {
											dates = "&from=" + $("#from").val() + "&to=" + $("#to").val();
											showTabs(12);
										}
									}
								});

								if ($("#from").val().trim() !== "") {
									$("#to").removeAttr('disabled');
								}
							}
							else {
								dates = "";
							}
						}, beforeSend: function () {
							loading();
						}
					});
				} else if (t === 2) {
					loading();
					$('#contentPane').load('/vitals-all.php?id=<?= $_GET['id'] ?>');
				} else if (t === 7) {
					loading();
					$('#contentPane').load('/billing/patient_bill_doc.php?id=<?= $_GET['id'] ?>');
				} else if (t === 3) {
					loading();
					$('#contentPane').load('/prescriptionDetails.php?id=<?= $_GET['id'] ?>');
				} else if (t === 13) {
					loading();
					$('#contentPane').load('/appointments/appointments.php?pid=<?= $_GET['id'] ?>');
				} else if (t === 19) {
					loading();
					$('#contentPane').load('/medical_exam/patient_requests.php?pid=<?= $_GET['id'] ?>');
				}else if(t === 20){
					loading();
					$('#item_request').parent().attr('class', 'active');
					$('#contentPane').load('/consumableItemDetails.php?id=<?= $_GET['id'] ?>');
			  } else if (t === 10) {
					showTabs(1);
				} else if (t === 100) {
					loading();
					$('#contentPane').load('/arvMobile/web/home.php?pid=<?= $_GET['id'] ?>');
				}

				var spanBox = $('span#newLink');
				if (spanBox.is(':empty')) {
					spanBox.parent().hide();
				} else {
					spanBox.parent().show();
				}
			}

			$(document).ready(function () {
				<?php
				if (isset($_SESSION['pharm_url'], $_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SESSION['pharm_url']) !== false) {
					unset($_SESSION['pharm_url']);
					echo 'showTabs(3);';
				} else if (isset($_SESSION['bill_url'], $_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SESSION['bill_url']) !== false) {
					unset($_SESSION['bill_url']);
					echo 'showTabs(7);';
				} else if (isset($_SESSION['lab_url'], $_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SESSION['lab_url']) !== false) {
					unset($_SESSION['lab_url']);
					echo 'showTabs(6);';
				} else if (isset($_SESSION['scan_url'], $_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SESSION['scan_url']) !== false) {
					unset($_SESSION['scan_url']);
					echo 'showTabs(11);';
				} else if (isset($_SESSION['proc_url'], $_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SESSION['proc_url']) !== false) {
					unset($_SESSION['proc_url']);
					echo 'showTabs(8);';
				} else if (is_dir('admissions') && isset($_GET['admission']) && $_GET['admission'] == 1) {
					echo 'showTabs(10);';
				} else if (isset($_GET['startnewvisit']) && $_GET['startnewvisit'] == "true") {
					echo 'showTabs(1);newVisit();';
				} else if (is_dir('outpatient_tasks') && isset($_SESSION['op_tasks_page']) && $_SESSION['op_tasks_page'] == 1) {
					echo 'showTabs(14);';
					unset($_SESSION['op_tasks_page']);
				} else if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'messaging/menu_up.php?type=aqueue') !== false) {
					if (isset($_GET['qtype']) && $_GET['qtype'] == 'imaging') {
						echo 'showTabs(11);';
					} else if (isset($_GET['qtype']) && $_GET['qtype'] == 'lab') {
						echo 'showTabs(6);';
					} else if (isset($_GET['qtype']) && $_GET['qtype'] == 'ophthalmology') {
						echo 'showTabs(17);';
					} else if (isset($_GET['qtype']) && $_GET['qtype'] == 'dentology') {
						echo 'showTabs(16);';
					} else {
						echo 'showTabs(1);';
					}
				} else if (isset($_GET['type']) && $_GET['type'] == 'physio') {
					echo 'showTabs(18);';
				}else if (isset($_GET['type']) && $_GET['type'] == 'item'){
					echo 'showTabs(20);';
				} else {
					echo 'showTabs(1);';
				}
				?>

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
			});
			<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/admissions")) { ?>
			function sendAdmission(pid) {
				Boxy.load('/admissions/boxy.get-admission-why.php?id=' + pid, {title: "Admit Patient"});
			}
			function sendDischarge(aid) {
				Boxy.load('/admissions/boxy.get-discharged-why.php?aid=' + aid, {title: 'Discharge Patient'});
			}
			<?php } ?>

			function showNewDocNote() {
				Boxy.load('/boxy.doctor.note.new.php?pid=<?= $patientID ?>', {title: 'New Doctor\'s Note'});
			}
		</script>
		<!-- Begin Content -->
		<input name="id" type="hidden" value="<?= $_GET['id'] ?>">
		<script src="/assets/webcam/webcam.js"></script>
		<script src="/assets/js/camScript.js"></script>
		<?php
		if (!isset($_SESSION)) {
			@session_start();
		}
		$_SESSION['patientID'] = $pid;
		$_SESSION['patient_sex'] = $row_data['sex'];
		?>
		<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc.webcam.php'; ?>
		<div class="patient_profile_container">
			<table style="width: 100%">
				<tbody>
				<?php if (!$patient->isImmunization($pid) && ($patient->getPatientAgeInMonths($pid) <= (18 * 12))) { ?>
					<tr>
					<td>
						<div class="warning-bar top-round_"><?= $patient->getPatientName($pid) ?> is not yet enrolled into
							immunization.
							<div class="close-warning">
								<a href="javascript:void(0)">Enroll Now</a>
								<a href="javascript:void(0)">No</a></div>
						</div>
					</td></tr><?php } ?>
				<tr>
					<td>
						<!-- patient demographic details -->
						<div id="profile_container">
							<?php $ARR = $row_data;
							include $_SERVER['DOCUMENT_ROOT'] . '/patient_demograph.php';
							?>
						</div>
						<!-- /patient demographic details -->
					</td>
				</tr>
				<tr>
					<td>
						<div id="tabbedPane" class="clearfix container-fluid">
							<ul>
								<li><a href="javascript:void(0)" id="enc" onClick="showTabs(1)"><span>Encounters</span></a></li>
								<li><a href="javascript:void(0)" id="visitn" onClick="showTabs('1b')"><span>Visit Notes</span></a></li>
								<?php
								if (is_dir("arvMobile")) {
									require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvEnrollmentDAO.php';
									if ((new ArvEnrollmentDAO())->isEnrolled($patientID)) { ?>
										<li><a href="javascript:void(0)" id="arv" onClick="showTabs(100)"><span>ARV Clinic</span></a></li><?php } ?>
								<?php } ?>

								<li><a href="javascript:void(0)" id="vitals" onClick="showTabs(2)"><span>Vital Signs</span></a></li>
								<li><a href="javascript:void(0)" id="rx_" onClick="showTabs(3)">
										<span>RX(Regimens)</span></a></li>
								<li><a href="javascript:void(0)" id="allerg" onClick="showTabs(4)"><span>Allergens</span></a></li>
								<li><a href="javascript:void(0)" id="pre-con" onClick="showTabs(5)"><span>Problem List</span></a>
								</li>
								<?php if (is_dir('outpatient_tasks')) { ?>
									<li><a href="javascript:void(0)" id="op_tasks" onClick="showTabs(14)"><span>O/P. Clinical Tasks</span></a>
									</li>
								<?php } ?>
								<li><a href="javascript:void(0)" id="labs" onClick="showTabs(6)"><span>Labs</span></a></li>
								<li><a href="javascript:void(0)" id="scans" onClick="showTabs(11)"><span>Imaging</span></a></li>
								<li><a href="javascript:void(0)" id="procedures" onClick="showTabs(8)"><span>Procedures</span></a></li>
								<li><a href="javascript:void(0)" id="billings" onClick="showTabs(7)"><span>Billing</span></a></li>
								<li><a href="javascript:void(0)" id="documents" onClick="showTabs(15)"><span>Documents</span></a></li>
								
								<?php if (is_dir("physiotherapy")) { ?>
									<li><a href="javascript:void(0)" id="physiotherapy" onClick="showTabs(18)"><span>PhysioTherapy</span></a></li>
								<?php } ?>
								<?php if (is_dir('ophthalmology')) { ?>
									<li>
										<a href="javascript:void(0)" id="ophthalmology" onClick="showTabs(17)"><span>Ophthalmology</span></a>
									</li>
								<?php } ?>
								
								<?php if (is_dir("dentistry")) { ?>
									<li><a href="javascript:void(0)" id="dentistry" onClick="showTabs(16)"><span>Dentistry</span></a></li>
								<?php } ?>

								<li><a href="javascript:void(0)" id="medical_exam" onClick="showTabs(19)"><span>Medical Reports</span></a>
								<?php if (is_dir("consumableItems")) { ?>
								<li><a href="javascript:void(0)" id="item_request" onclick="showTabs(20)"><span>Consumables</span></a> </li>
							<?php } 	?>
								<li><a href="javascript:void(0)" id="summaries" onClick="showTabs(12)"><span>Visit Summary</span></a></li>
								<li><a href="javascript:void(0)" id="appointments" onClick="showTabs(13)"><span>Appointments</span></a></li>
								<li class="dropdown pull-right" id="more">
									<a href="#" id="myTabDrop1" class="dropdown-toggle" data-toggle="dropdown"><span>More <b class="caret"></b></span></a>
									<ul class="dropdown-menu" role="menu" aria-labelledby="myTabDrop1">
									</ul>
								</li>
							</ul>
						</div>
						<div id="contentPane_">
							<div class="menu-head"><span id="newLink"></span></div>
							<span id="contentPane"></span>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}
} else {
	echo $protect->ACCESS_DENIED;
}
unset($_SESSION['bill_url'], $_SESSION['pharm_url']);
?>
