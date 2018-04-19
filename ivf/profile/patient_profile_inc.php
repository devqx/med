<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/17/16
 * Time: 3:09 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
if ((new IVFEnrollmentDAO())->get($_GET['aid'], FALSE) === null) {
	echo('<div class="warning-bar">Profile Not Found! <a href=".">Go Home</a> </div>');
} else {
	?>

	<div class="patient_profile_container">
		<table style="width: 100%">
			<tbody>
			<tr>
				<td>
					<!-- patient demographic details -->
					<div id="profile_container">
						<?php
						include $_SERVER['DOCUMENT_ROOT'] . '/ivf/profile/ivf_demograph.php';
						?>
					</div>
					<!-- /patient demographic details -->
				</td>
			</tr>
			<tr>
				<td>
					<div id="tabbedPane" class="clearfix container-fluid">
						<ul>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/summary.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="Summary"><span>Summary</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/notes.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="Notes"><span>Notes</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/treatments.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="Down Regulation Chart"><span>Down Regulation Chart</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/stimulation.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="Stimulation Chart"><span>Stimulation Chart</span></a></li>
							<li><a href="javascript:void(0)" data-href="/patient_profile.php?id=<?= $_GET['id'] ?>&view=labs&aid=<?=$_GET['aid']?>&ivf=true" onclick="showTabs(this)" title="Lab Requests"><span>Lab Requests</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/triggers.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="Triggers"><span>Triggers/Tasks</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/procedures.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="IVF Procedures"><span>IVF Procedures</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/embryology.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="Embryology"><span>Embryology</span></a></li>
							<li><a href="javascript:void(0)" data-href="/appointments/appointments.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>&ivf=true" onclick="showTabs(this)" title="Appointments"><span>Appointments</span></a></li>
							<li><a href="javascript:void(0)" data-href="/ivf/profile/tabs/pgd_labs.php?pid=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>" onclick="showTabs(this)" title="PGD Labs"><span>PGD Labs</span></a></li>
							<li><a href="javascript:void(0)" data-href="/billing/patient_bill_doc.php?id=<?= $_GET['id'] ?>&aid=<?=$_GET['aid']?>&ivf=true" onclick="showTabs(this)" title="Billing"><span>Billing</span></a></li>
							<li><a href="javascript:void(0)" data-href="/patient_profile.php?id=<?= $_GET['id'] ?>&view=rx&aid=<?=$_GET['aid']?>&ivf=true" onclick="showTabs(this)" title="Regimens"><span>Regimens</span></a></li>
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
	<script type="text/javascript">
		$(document).ready(function () {
			$(".container-fluid ul").droptabs({});

			$('#tabbedPane').find('li:first a').click();
		});
		function showTabs(element) {
			$('#tabbedPane').find('.active').attr('class', '');
			$(element).parent().attr('class', 'active');
			$.ajax({
				url: $(element).data('href'),
				cache: true,
				success: function (s) {
					$("#contentPane").html(s);
				}, beforeSend: function () {
					loading();
				},
				error: function(){
					$('#contentPane').html('<div class="alert-box error">Failed to load document</div>');
				}
			});
		}

		function loading() {
			$("#contentPane").html('<div class="ball"></div>');
		}
	</script>
<?php } ?>