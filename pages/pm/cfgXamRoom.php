<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamRoomDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ExamRoom.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
	if(is_blank($_POST['roomName'])){exit('error:Blank data');}
	$room = (new ExamRoom())->setName($_POST['roomName'])->add();
	if($room!==null){
		exit('success:Room added');
	}
	exit('error:Failed to add room');
}
?>
<div>
	<script type="text/javascript">
		function start() {
			$('#wait').html('<img src="/img/loading.gif"/> please wait ... ');
		}
		function done(s) {
			var data= s.split(':');
			
			if (data[0] === 'success') {
				$('#creator').attr('data', "none").html("");
				loadCfgXamRoom();
			} else if (data[0] === 'error') {
				$('#wait').html('<span class="error">'+data[1]+'</span>');
			}
		}
	</script>
	<div>
		<?php
		$DATA = (new ExamRoomDAO())->getExamRooms(); ?>
		<h5>Existing Examination/Consultation Rooms</h5>
		<table class="table table-hover table-striped">
			<thead>
			<tr>
				<th>Room Name</th>
				<th>Available?</th>
				<th>Consultant</th>
				<th>Specialization</th>
				<th></th>
			</tr>
			</thead>
			
			<?php foreach ($DATA as $er) {?>
			<tr><td><?= $er->getName()?></td><td><?=($er->getAvailable() == 1 ? 'Yes' : 'No') ?></td><td><?=($er->getConsultant() != null ? $er->getConsultant()->getFullname() : 'N/A')?></td><td><?=($er->getSpecialization() != null ? $er->getSpecialization()->getName() : 'N/A')?></td><td><i class="icon-edit"></i><a href="javascript:void(0);" data-href="boxy.edit.exam-rooms.php?id=<?=$er->getId()?>" onClick="Boxy.load($(this).attr('data-href'),{title:'Edit Examination/Consultation room'})">Edit </a></td></tr>
			<?php } ?>
		</table>

	</div>
	<hr>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"
	      onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':done});">
		<label><input placeholder="Room Label or Name" type="text" name="roomName"/></label>
		<button class="btn" type="submit">Add Room &raquo;</button>
		<div id="wait"></div>
	</form>
</div>
