<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/16/16
 * Time: 6:21 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PAuthCodeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$patientId = !is_blank(@$_POST['patient_id']) ? @$_POST['patient_id'] : null;
$pageSize = 10;
$data = (new PAuthCodeDAO())->all($page, $pageSize, $patientId);
$totalSearch = $data->total;
?>
<div class="row-fluid">

<div class="pull-right"><?php if(!$_REQUEST['patient_id']){ ?>
		<button class="action drop-btn" id="new_pa_code_lnk">New Request</button>
	<?php } ?>
</div>
	<div class="row-fluid clear clearBoth">
		<h5 class="span5 no-label">Available Authorization Codes</h5>
		<label class="span7">Filter by Patient<input type="hidden" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>"></label>
	</div>
	
	<div id="requestsList">
  <div class="row-fluid">
	<div class="pull-left">
		<?php if(isset($_REQUEST['patient_id']) && $totalSearch > 0) { ?>
			<button class="action drop-btn"  id="p_new_pa_code_lnk">New Request</button>
		<?php } ?>
	</div>
  </div>
		<div class="clear"></div>
		<div class="clear"></div>
			<?php if ($totalSearch > 0) { ?>
			<table class="table table-striped">
				<tr>
					<th>Request Date</th>
					<?php if(!isset($_REQUEST['patient_id'])) { ?> <th>Patient</th> <?php } ?>
					<th>Request by</th>
					<th>Code</th>
					<th>Status</th>
					<th>*</th>
				</tr>
				<?php foreach ($data->data as $datum) { error_log("Datum::::".json_encode($datum)); //$datum = new PAuthCode(); ?>
					<tr>
						<td><?= date(MainConfig::$dateTimeFormat, strtotime($datum->getCreateDate())) ?></td>
					<?php if(!isset($_REQUEST['patient_id'])) { ?> 	<td><span class="profile" data-pid="<?= $datum->getPatient() ? $datum->getPatient()->getId() : ''  ?>"><abbr><?= $datum->getPatient() ? $datum->getPatient()->getFullName() : 'no longer active' ?></abbr></span></td> <?php } ?>
						<td><?= $datum->getCreator()->getFullname() ?></td>
						<td><?= !is_blank($datum->getCode()) ? $datum->getCode() : '<span class="fadedText">- -</span>' ?></td>
						<td><?= ucwords($datum->getStatus()) ?></td>
						<td>

							<div class="dropdown pull-right">
								<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
									Action
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
									<li>
										<a class="view_code" href="javascript:" data-id="<?= $datum->getId() ?>">View</a>
										
									</li>
									<?php if($datum->getCode()==null){?>

									<li>
										<a class="resend_code" href="javascript:" data-id="<?= $datum->getId() ?>">Resend</a>
									</li>
									
									<li>
										<a class="receive_code" href="javascript:" data-id="<?= $datum->getId() ?>">Receive</a>
									</li>
									<?php }?>
								</ul>
							</div>
							
						</td>
					</tr>
				<?php } ?>
			</table>
			<div class="list2-pcodes dataTables_wrapper no-footer">
				<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
				<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
					<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
				</div>
			</div>
			<script type="text/javascript">
				var getCode = function (requestId) {
					vex.dialog.prompt({
						message: 'Please enter the Authorization Code',
						placeholder: '',
						value: null,
						overlayClosesOnClick: false,
						beforeClose: function (e) {
							e.preventDefault();
							alert('testing the close');
						},
						callback: function (value) {
							if (value !== false && value !== '') {
								//do the auth and proceed the action
								$.post('/api/receive_auth_code.php', {code: value, id: requestId}, function (response) {
									var data = response.split(':');
									if(data[0]==='error'){
										Boxy.warn(data[1]);
									} else if(data[0]==='success'){
										Boxy.info(data[1]);
										goTo(0);
									}
								});
							}
						}, afterOpen: function ($vexContent) {
							//$('.vex-dialog-prompt-input');
							$submit = $($vexContent).find('[type="submit"]');
							$submit.attr('disabled', true);
							$vexContent.find('input').on('input', function () {
								if ($(this).val()) {
									$submit.removeAttr('disabled');
								} else {
									$submit.attr('disabled', true);
								}
							});//.trigger('input');
						}
					});
				};

				$(document).ready(function () {
					$('[name="patient_id"]').select2({
						placeholder: "Filter List by Patient EMR or Name",
						minimumInputLength: 3,
						width: '100%',
						allowClear: true,
						ajax: {
							url: "/api/search_patients.php",
							dataType: 'json',
							data: function (term, page) {
								return {
									q: term
								}
							},
							results: function (data, page) {
								return {results: data};
							}
						},
						formatResult: function (data) {
							var details = [];
							details.push(data.patientId ? "EMR ID:" + data.patientId : null);
							details.push(data.fname ? data.fname : null);
							details.push(data.mname ? data.mname : null);
							details.push(data.lname ? data.lname : null);
							return implode(" ", details);
							//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
						},
						formatSelection: function (data) {
							var details = [];
							details.push(data.patientId ? "EMR ID:" + data.patientId : null);
							details.push(data.fname ? data.fname : null);
							details.push(data.mname ? data.mname : null);
							details.push(data.lname ? data.lname : null);
							return implode(" ", details);
							//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
						},
						id: function (data) {
							return data.patientId;
						},
						initSelection: function (element, callback) {
							var id = $(element).val();
							if (id !== "") {
								$.ajax("/api/search_patients.php?pid=" + id, {
									dataType: "json"
								}).done(function (data) {
									callback(data);
								});
							}
						}
					}).change(function (e) {
						if (!e.handled) {
							goTo(0);
							e.handled = true;
						}
					});
					$('#new_pa_code_lnk').on('click', function (e) {
						if (!e.handled) {
							Boxy.load('/billing/pa_codes.new.php', {title: 'New P.A. code'});
							e.handled = true;
						}
					})

					$('#p_new_pa_code_lnk').on('click', function (e) {
						if (!e.handled) {
							Boxy.load('/billing/pa_codes.new.php?id=<?= $_REQUEST["patient_id"] ?>', {title: 'New P.A. code'});
							e.handled = true;
						}
					})
				
				}).on('click', '.receive_code', function (event) {
					if (!event.handled) {
						Boxy.ask('Have you got the authorization code?', ['Yes', 'No'], function (choice) {
							if (choice === 'Yes') {
								getCode(event.target.dataset['id']);
							}
						});
						event.handled = true;
					}
				}).on('click', '.view_code', function (event) {
					if (!event.handled) {
						Boxy.load('/billing/pa_code.php?id=' + event.target.dataset['id']);
						event.handled = true;
					}
				}).on('click', '.resend_code', function (event) {
					if (!event.handled) {
						//Boxy.load('/billing/pa_code.resend.php?id=' + event.target.dataset['id']);
						event.handled = true;
					}
				}).on('click', '.list2-pcodes.dataTables_wrapper a.paginate_button', function (e) {
					if (!e.clicked) {
						var page = $(this).data("page");
						if (!$(this).hasClass("disabled")) {
							goTo(page);
						}
						e.clicked = true;
					}
				});

				var goTo = function (page) {
					var pid = $('[name="patient_id"]').val() || location.search.split("=")[1];
					$.post('/billing/pa_codes.php', {page: page, patient_id: pid}, function (s) {
						if($(".blockElem.code").length === 1) {
							$(".blockElem.code").html($(s).find('#requestsList').html());
						} else if($('#requestsList').length === 1) {
							$('#requestsList').html($(s).find('#requestsList').html());
						}
					});
				}
			
			</script>
		
		<?php } else { ?>
       <!--<div class="row-fluid">-->
				<div class="pull-left">
					<?php if(isset($_REQUEST['patient_id'])) { ?>
						<button class="action drop-btn"  id="p_new_pa_code_">New Request</button>
					<?php } ?>
				</div>
       <!--</div>-->
				<div class="clear"></div>
				<div class="clear"></div>
				<script>
					$('#p_new_pa_code_').on('click', function (e) {
						if (!e.handled) {
							Boxy.load('/billing/pa_codes.new.php?id=<?= $_REQUEST["patient_id"] ?>', {title: 'New P.A. code'});
							e.handled = true;
						}
					})
				</script>
			<div class="notify-bar">
				No available Authorization Codes
			</div>
		<?php } ?>
	</div>
</div>

