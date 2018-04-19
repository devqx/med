<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/RoomType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

if (!isset($_SESSION)) {
	@session_start();
}
if (isset($_POST['addType'])) {
	if (!isset($_POST['roomType']) || strlen(trim($_POST['roomType'])) < 1) {
		exit("error:Please type the category");
	}
	if (!isset($_POST['cost']) || strlen(trim($_POST['cost'])) < 1) {
		exit("error:Please enter the cost for this category");
	}
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
	$bt = new RoomType();
	$bt->setName($_POST['roomType']);
	$bt->setDefaultPrice(parseNumber($_POST['cost']));
	$bt->setHospital(new Clinic(1));
	$bt = (new RoomTypeDAO())->addRoomType($bt);
	if ($bt === null) {
		exit("error:Sorry we are unable to add this category");
	} else {
		exit("success:Category added successfully");
	}
}
?>


<h5><a id="shCat" href="javascript:void(0)">Show/Hide Existing Room Category</a></h5>

<div id="roomTypes" style="display:none">

</div>


<div>
	<form id="addRoomCategoryForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
		<label>New Room Category
			<input type="text" name="roomType" placeholder="e.g. Executive"/></label>

		<label>Amount Chargeable (per day price)
			<input type="number" step="0.10" min="0" name="cost" id="cost"></label>


		<div class="btn-block">
			<button class="btn" type="submit" name="addType" value="true">Add</button>
			<button class="btn-link" type="reset">reset</button>
			<div id="mgniu_"></div>
		</div>
	</form>
</div>

<script type="text/javascript">
	$("document").ready(function () {
		loadList();
		$("#shCat").click(function () {
			$("#roomTypes").toggle("fast");
		});
	});
	function start_() {
		$('#mgniu_').html('<img src="/img/loading.gif">');
	}
	function done_(s) {
		var status_ = s.split(":");
		if (status_[0] == 'success') {
			loadList();
			$('#mgniu_').html('<span class="alert-success">' + status_[1] + '</span>');
			$("button[class='btn-link']").trigger("click")
		} else {
			$('#mgniu_').html('<span class="alert-error">' + status_[1] + '</span>');
		}
	}

	function loadList() {
		$("#roomTypes").load("/pages/pm/bedspaces/listRoomTypes.php");
	}
</script>