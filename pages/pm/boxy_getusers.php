<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
if (!isset($_SESSION)) {
	@session_start();
}
$this_user = NULL;
if (isset($_SESSION['staffID'])) {
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
}
?>
<script>
	function start() {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		$('span.error').html('<img src="/img/loading.gif"> please wait');
	}
	function finished(s) {
		if (s.indexOf("success") != -1) {
			$('#adminmgt').load('adminmgt.php');
		} else {
			var s1 = s.split(":");
			if (s1[0] == "error") {
				$('span.error1').html('<img src="/img/warning.png" /><span style="color:#C00">' + s1[1] + '</span>');
			}
		}
	}

	function startUpload() {
		$('#msg').html('<img src="/img/loading.gif" align="absmiddle" /> <em>Please wait ...</em>');
	}
	function finishedUpload(s) {
		if (s.indexOf("success") != -1) {
			$('#msg').html('<span class="uploaded">Logo uploaded successfully</span>');
		} else {
			var s1 = s.split(":");
			if (s1[0] == "error") {
				$('#msg').html('<img src="/img/warning.png"><span style="color:#C00">' + s1[1] + '</span>');
			}
		}
	}

	function end(s) {
		if (s.indexOf("success") != -1) {
			$('#adminmgt').load('adminmgt.php');
		} else {
			s1 = s.split(":");
			if (s1[0] == "error") {
				$('span.error2').html('<img src="/img/warning.png"/><span style="color:#C00">' + s1[1] + '</span>');
			}
		}
	}
	function doDelete(userid) {
		$.ajax({
			url: "index.php?action=delete&user=" + userid,
			success: function (s) {
				if (s.indexOf("success") != -1) {
				} else {
					alert("Delete Failed");
				}
				$('#usermgt').load('getusers.php');
			}
		});
	}
	function doDisable(userid) {
		<?php if($this_user->hasRole($protect->user_management)){?>
		$.ajax({
			url: "index.php?action=disable&user=" + userid,
			success: function (s) {
				var ret = s.split(":");
				if (ret[0] === "success") {
					$('#existingUsers').load("/pages/pm/getUserList.php");
				} else {
					Boxy.alert(ret[1]);
				}
			}
		});<?php } else {?>
		Boxy.warn("Sorry, you do not have access to this function");
		<?php }?>

	}
	function doEnable(userid) {
		<?php if($this_user->hasRole($protect->user_management)){?>
		$.ajax({
			url: "index.php?action=enable&user=" + userid,
			success: function (s) {
				var ret = s.split(":");
				if (ret[0] === "success") {
					$('#existingUsers').load("/pages/pm/getUserList.php");
				} else {
					Boxy.alert(ret[1]);
				}
			}
		});
		<?php } else {?>
		Boxy.warn("Sorry, you do not have access to this function");
		<?php }?>

	}
	function finish(s) {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		if (s.indexOf("success") !== -1) {
			$('span.error').html("");
			$('#creator').html("User created").attr('data', "none");
			$('#existingUsers').load("/pages/pm/getUserList.php");
		} else {
			var s1 = s.split(":");
			if (s1[0] === "error") {
				$('span.error').html('<img src="/img/warning.png"/><span style="color:#C00">' + s1[1] + '</span>');
			}
		}
	}
	function done(s) {
		if (s.indexOf("success") !== -1) {
			$('span.error').html("");
			$('#creator').html("Clinic Created").attr('data', "none");
		} else {
			var s1 = s.split(":");
			if (s1[0] == "error") {
				$('span.error').html('<img src="/img/warning.png" align="absmiddle" /><span style="color:#C00">' + s1[1] + '</span>');
			}
		}
	}
	function deleteProfile(obj) {
		alert(obj);
	}

	function loadDisabledPatients() {
		$('#existingUsers').html("")
		$('#creator').load('/pages/pm/disabledPatients.php').attr('data', "user");
		$('span.error').html("");
		/*if ($('#creator').attr('data') === "none" || $('#creator').attr('data') === "hospital") {

		 } else {
		 $('#creator').attr('data', "none").html("");
		 }*/
	}

	function loadCreateUser() {
		$('#existingUsers').load("/pages/pm/getUserList.php");
		if ($('#creator').attr('data') === "none" || $('#creator').attr('data') === "hospital") {
			$('#creator').load('/pages/pm/createUser.php').attr('data', "user");
			$('span.error').html("");
		} else {
			$('#creator').attr('data', "none").html("");
		}
	}
	function loadEditUser(id) {
		<?php if($this_user->hasRole($protect->user_management) || intval($this_user->getId()) === 1){?>
		Boxy.load('/pages/pm/editRole.php?id=' + id, {title: 'Modify user roles'});
		<?php } else {?>
		Boxy.warn("Sorry, you do not have access to this function");
		<?php }?>
	}
	function loadEditHospital() {
		$('#creator').load('/pages/pm/editHospital.php').attr('data', "hospital");
		$('span.error').html("");
		/*if ($('#creator').attr('data') === "none" || $('#creator').attr('data') === "user") {

		 } else {
		 $('#creator').attr('data', "none").html("");
		 }*/
	}

	function loadCreateDepartment() {
		Boxy.load('/pages/pm/department.new.php', {
			title: 'New Department', afterHide: function () {
				$.ajax({
					url: '/api/get_departments.php',
					complete: function (xhr, status) {
						if (status == "success") {
							departments = $.parseJSON(xhr.responseText);
							html = '';
							for (var i = 0; i < departments.length; i++) {
								html += '<option value="' + departments[i].id + '">' + departments[i].name + '</option>';
							}
							$("#department_id").html(html);
						} else {
							Boxy.alert("failed to get list of departments")
						}
					}
				})
			}
		});
	}

	function loadMergePatients() {
		$('#creator').load('/pages/pm/mergePatients.php').attr('data', "merge");
		$('#existingUsers').empty();
		$('span.error').html("");
	}
	function loadDepartments() {
		$('#creator').load('/pages/pm/departmentsList.php').attr('data', "depts");
		$('#existingUsers').empty();
		$('span.error').html("");
	}
	function loadClinics() {
		$('#creator').load('/pages/pm/clinics.php').attr('data', "clinics");
		$('#existingUsers').empty();
		$('span.error').html("");
	}


</script>
<div>
	<a href="javascript:void(0)" class="subs action" onclick="loadCreateUser()"><i class="fa fa-user-md"></i> Create User</a>
	<a href="javascript:void(0)" class="subs action" onclick="loadEditHospital()"><i class="fa fa-hospital-o"></i> Edit Hospital</a>
	<a href="javascript:void(0)" class="subs action" onclick="loadDepartments()"><i class="fa fa-home"></i> Departments</a>
	<a href="javascript:void(0)" class="subs action" onclick="loadClinics()"><i class="fa fa-home"></i> Clinics</a>
	<a href="javascript:void(0)" class="subs action" onclick="loadCreateDepartment()"><i class="fa fa-home"></i> Create Department</a>
	<a href="javascript:void(0)" class="subs action" onclick="loadDisabledPatients()"><span class="fa fa-child"></span> Deactivated Patient Profiles</a>
	<a href="javascript:void(0)" class="subs action" onclick="loadMergePatients()"><span class="fa fa-compress"></span> Merge Patient Profiles</a>


	<br>
	<span class="error"></span>
	<div id="creator" data="none"></div>
	<div id="existingUsers"></div>
</div>
