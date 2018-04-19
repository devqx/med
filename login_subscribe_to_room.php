<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamRoomDAO.php';
@session_start();
$ROOMS = (new ExamRoomDAO())->getExamRooms();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$pdo = (new MyDBConnector())->getPDO();
if ($_POST) {
	@session_start();
	$_SESSION['room'] = escape($_POST['room']);

	$sql_check = "SELECT * FROM `doctors_subscribed` WHERE `staffID` =  " . $_SESSION ['staffID'];
	$stmt = $pdo->prepare($sql_check, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$rst_check = $stmt->execute();
	$row_check = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

	if ($stmt->rowCount() >= 1) {
		//ignore it, doctor is already logged in to another room
	} else {

		if ($_SESSION['specialization_id'] <= 0) {
			exit("error:You do not have a specialization information attached to your profile.");
		};
		$sq = "INSERT INTO doctors_subscribed (roomID, staffID, specialization_id, `timestamp`) VALUES (" . $_SESSION['room'] . "," . $_SESSION ['staffID'] . ", " . $_SESSION['specialization_id'] . ", UNIX_TIMESTAMP(NOW()))";

		$runSubscribe = $pdo->prepare("UPDATE exam_rooms SET available = FALSE, specialization_id =" . $_SESSION['specialization_id'] . ", consultant_id = " . $_SESSION ['staffID'] . " WHERE room_id = " . $_SESSION['room'], array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$runSubscribe->execute();
		$chk = $pdo->prepare($sq, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk->execute();
	}
	exit("ok");
} ?>
<div>
    <h4>Subscribe to Exams/Consultation Rooms</h4>
    <?php
    if (!class_exists('StaffManager')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
    }
    $staff = new StaffManager();
    if (!$staff->isSubscribedToroom($_SESSION['staffID'])) {
        ?>
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
              onsubmit="return AIM.submit(this, {onStart: begin, onComplete:last})">
            <div class="notify-bar" id="subscribeBoxInfo" style="display: none"></div>
            <?php if (sizeof($ROOMS) > 0) { ?><label class="sub">Available Rooms:
                <select name="room">
                    <?php foreach ($ROOMS as $r) {
                        echo($r->getAvailable() ? '<option value="' . $r->getId() . '">' . $r->getName() . '</option>' : '');
                    }?>
                </select>
                </label><?php } else { ?>
                <div class="warning-bar">No Rooms Available
                <span class="pull-right"><i class="icon-plus-sign"></i>
                    <a id="configRoom" href="javascript:;">Add Consultation Rooms</a>
                </span>
                </div>
            <?php } ?>
            <div class="clearfix" style="margin-bottom: 10px"></div>
            <div class="btn-block">
                <button type="submit" class="btn btn-default">Subscribe &raquo;</button>
                <button type="button" class="btn-link pull-right" onclick="Boxy.get(this).hide();">Skip</button>
            </div>
        </form>
    <?php
    } else {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SubscribedDoctorDAO.php';
        $subscribedRoom = (new SubscribedDoctorDAO())->getSubscribedDoctor($_SESSION['staffID'], FALSE)->getRoom();?>
        <div class="notify-bar">You are already subscribed to consultation </div>
    <?php } ?>
    <script type="text/javascript">
        $("#configRoom").click(function (e) {
            Boxy.load('/pages/pm/boxy_adminmgt.php', {
                afterHide: function () {
                    //close the subscribe dialog
                    Boxy.get($(".close")).hideAndUnload();
                    //re-open it, so that the new room will be shown, if any was added
                    $("#swub_link").click();
                },
                afterShow: function () {
                    loadCfgXamRoom();
                }
            });
            e.preventDefault()
        });
        function begin() {
        }
        function last(s) {
            if (s === "ok") {
                location.reload();
            } else {
                var data = s.split(":");
                if(data[0]==="error"){
                    $("#subscribeBoxInfo").html(data[1]).show();
                    setTimeout(function () {
//                        $("#subscribeBoxInfo").fadeOut().html('');
                        $("#subscribeBoxInfo").animate({'opacity':'0'},"slow",function(){
                            $(this).css({'opacity':1}).fadeOut().html('');
                        });
                    }, 1500);
                }
            }
        }
    </script>
<?php exit() ?>