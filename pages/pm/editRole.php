<?php
if (!isset($_SESSION)) {
	session_start();
}
require $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/StaffDirectoryDAO.php";
require $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/StaffRolesDAO.php";
require $_SERVER ['DOCUMENT_ROOT'] . "/classes/StaffRole.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AuditLog.php';

$staff_Roles = new StaffRolesDAO();
$protect = new Protect();
$ROLES = $staff_Roles->getRoles();
$dao = new StaffDirectoryDAO();
$staff = $dao->getStaff($_GET['id']);
$this_user = $dao->getStaff($_SESSION['staffID']);
$oldValue = (new StaffRolesDAO())->getStaffRoles($_GET['id']);
$userId = $_GET['id'];
if ($_POST) {
	$roles = (isset($_POST['roles']) ? $_POST['roles'] : []);
	echo json_encode($staff_Roles->updateStaffRoles($_POST['sid'], $roles));//flat non-object style :(
	exit;
}
?>
<div>
	<script type="text/javascript">
		function start() {
			$('#msg').html('<img src="/img/loading.gif"/> <em> Please wait ...</em>');
		}
		function done(s) {
			s = $.parseJSON(s);
			$(".boxy-content").animate({scrollTop: 0}, "slow");
			if (s.status == "ok") {
				Boxy.info(s.message);
				setTimeout(function () {
					Boxy.get($(".close")).hideAndUnload();
				}, 1000);
			} else {
				Boxy.alert(s.message);
			}
		}
	</script>
	<?php
	if (!$this_user->hasRole($protect->user_management)) {
		echo($protect->ACCESS_DENIED);
	} else { ?>
		<form id="form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
		      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
			<h5>Modifying roles for <strong><em><?= $staff->getUsername() ?></em></strong></h5>
			<table class="table table-striped">
				<tr>
					<td colspan="3"><span id="msg"></span></td>
				</tr>

				<thead>
				<tr>
					<th><input type="checkbox" id="checkAll"></th>
					<th>Role/Group</th>
					<th>Description</th>
				</tr>
				</thead>
				<?php
				if ($this_user->hasRole($protect->mgt)) {
					foreach ($ROLES as $roles) { ?>
						<tr>
							<td><input id="role_<?= $roles->getId() ?>" <?= (in_array($roles, $staff->getRoles())) ? ' checked="checked"' : '' ?> type="checkbox" name="roles[]" value="<?= $roles->getId() ?>"/></td>
							<td><label for="role_<?= $roles->getId() ?>"><?= $roles->getCode() ?></label></td>
							<td><label for="role_<?= $roles->getId() ?>"><?= $roles->getDescription() ?></label></td>
						</tr>
					<?php }
				} else {
					echo $protect->ACCESS_DENIED;
				} ?>
				<tr>
					<td colspan="3">
						<button class="btn" type="submit" name="update">Update Roles &raquo;</button>
						<button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
						<input type="hidden" name="sid" value="<?= $_GET['id'] ?>">
					</td>
				</tr>
			</table>
		</form>
	<?php } ?>
</div>
<script type="text/javascript">
	$(document).on('change', '#checkAll', function (evt) {
		if (!evt.handled) {
			if ($(this).is(":checked")) {
				$('input[id*=role_]').prop('checked', true).iCheck('update');
			} else {
				$('input[id*=role_]').prop('checked', false).iCheck('update');
			}
			evt.handled = true;
		}
	});
</script>
