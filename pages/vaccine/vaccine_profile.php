<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientDemographDAO.php";
$pdo = (new MyDBConnector())->getPDO();
$patientID = escape($_GET['id']);

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$sql = "SELECT * FROM patient_demograph p LEFT OUTER JOIN  socio_economic_status s ON (p.socio_economic=s.id) LEFT OUTER JOIN life_style l ON  (p.lifestyle=l.id) WHERE patient_ID='" . $patientID . "' AND patient_ID IN (SELECT patient_id FROM enrollments_immunization)  LIMIT 1";
$pid = $patientID;
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$chk = $stmt->execute();
$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
$retVal = "";
if ((new PatientDemographDAO())->getPatient($pid) === NULL || $stmt->rowCount() == 0) {
	?>
	<div class="warning-bar"><p align="center">FATAL ERROR: Patient Immunization Profile Not Found!<br/> Return to <a
				href="/immunization/">Home</a></p></div>
<?php } else {
	do { ?>
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
			function showTabs(t) {
				var urn;
				var t1Link = '<div style="width:auto;float:left"><a class="action" title="Register Visit" href="javascript:void(0)" onclick="Boxy.load(\'/immunization/boxy.prepare_due_vaccines.php?id=<?= $_GET['id'] ?>\',{title:\'Register Visit\'})"><i class="icon-play"></i> Register Visit</a> <a class="action" href="javascript:void(0)" onClick="Boxy.load(\'/immunization/boxy.select_administer_due_vaccines.php?id=<?php echo $_GET['id'] ?>\',{title:\'Apply Due Vaccines\'})"><i class="icon-edit"></i> Apply Due Vaccines/Reverse billed vaccines</a> <a href="javascript:;" class="action" id="directUpdateWithoutPay"><i class="icon-upload"></i> Update Vaccine</a> <em>N.B. You can click directly on the chart to update the vaccine (prepared only)</em></div><div style="float:right"><a class="action" href="javascript:void(0)" rel="contentPane"><i class="icon-print"></i> Print</a></div>';
				var t2Link = '<div style="width:50%;float:left"></div><div style="float:right"><a class="action" href="javascript:void(0)" rel="contentPane"><i class="icon-print"></i> Print</a></div>';
				var t3Link = '<div>&nbsp;</div>';
				if (t === 1) {
					urn = location.href + "&view=immu-map";
					$('.active').attr('class', '');
					$('#vaccines').parent().attr('class', 'active');
					$('#newLink').html(t1Link);
				}
				else if (t === 2) {
					urn = location.href + "&view=reminders";
					$('.active').attr('class', '');
					$('#reminders').parent().attr('class', 'active');
					$('#newLink').html(t2Link);
				}
				else if (t === 3) {
					urn = location.href + "&view=boosters";
					$('.active').attr('class', '');
					$('#boosters').parent().attr('class', 'active');
					$('#newLink').html(t3Link);
				}
				else {
				}

				$.ajax({
					url: urn,
					cache: true,
					success: function (s) {
						$("#contentPane").html(s);
						if (t === 1)
							setTimeout(function () {
								$('.vaccine-block[title]').tooltipster({contentAsHTML: true});
							}, 200);
//                    setTimeout(function(){$('.vaccine-block[title]').tipTip({delay:10});},200);
						if (t === 2)
							setTimeout(function () {
								$("#contentPane table").tableScroll({height: 250});
							}, 200);
					}, beforeSend: function () {
						loading();
					}
				});
			}

		</script>
		<input name="id" type="hidden" value="<?php echo $_GET['id'] ?>">
		<script src="/assets/webcam/webcam.js"></script>
		<script src="/assets/js/camScript.js"></script>
		<link rel="stylesheet" type="text/css" href="/assets/css/camStyles.css?<?= rand() ?>"/>
		<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc.webcam.php'; ?>
		<div class="patient_profile_container">
			<table>
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
					<td width="59%" height="400" valign="top">
						<div id="tabbedPane" class="clearfix">
							<ul>
								<li><a href="javascript:void(0)" id="vaccines" onClick="showTabs(1)" title="Vaccine Charts">
										<!--<span></span>--><span>Vaccine Charts</span></a></li>
								<!--<li><a href="javascript:void(0)" id="update_vacc" onClick="showTabs(4)" title="Update Vaccines"><span></span><span>Update Vaccine</span></a></li>-->
								<li><a href="javascript:void(0)" id="boosters" onClick="showTabs(3)" title="Booster">
										<!--<span></span>--><span>Adult Vaccines/Booster</span></a></li>
								<li><a href="javascript:void(0)" id="reminders" onClick="showTabs(2)" title="Notifications">
										<!--<span></span>--><span>Notifications</span></a></li>
							</ul>
						</div>
						<div id="contentPane_">
							<div class="menu-head"><span id="newLink"></span></div>
							<span id="contentPane" style="width:1020px;overflow-x:scroll"></span>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<script src="/js/jquery.PrintArea.js_4.js"></script>
		<script src="/js/core.js"></script>
	<?php } while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
} ?>
