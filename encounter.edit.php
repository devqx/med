<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 1:27 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
$e = (new EncounterDAO())->get($_GET['enc_id'], TRUE);
$complaints = $diagnoses = $plans = $investigations = $systems_reviews =
$examinations = $examNotes = $addenda = $medicalHistory = $drugHistory = [];
foreach ($e->getPresentingComplaints() as $pc) {
	$complaints[] = $pc->description;
}unset($pc);
foreach ($e->getDiagnoses() as $pc) {
	$diagnoses[] = $pc->description;
}unset($pc);
foreach ($e->getPlan() as $pc) {
	$plans[] = $pc->description;
}unset($pc);
foreach ($e->getInvestigations() as $pc) {
	$investigations[] = $pc->description;
}unset($pc);
foreach ($e->getSystemsReviews() as $pc) {
	$systems_reviews[] = $pc->description;
}unset($pc);
foreach ($e->getExaminations() as $pc) {
	$examinations[] = $pc->description;
}unset($pc);
foreach ($e->getExamNotes() as $pc) {
	$examNotes[] = $pc->description;
}unset($pc);
foreach ($e->getMedicalHistory() as $pc) {
	$medicalHistory[] = $pc->description;
}unset($pc);
foreach ($e->getDrugHistory() as $pc) {
	foreach ($pc->getData() as $data){
		$drugHistory[] = /*$data->getDose() . $data->getDuration() . $data->getFrequency() . */ $data->getGeneric()->getName() . ' ['. $data->getGeneric()->getWeight() . ' '. $data->getGeneric()->getForm().']';
	}
	unset($data);
}
unset($pc);

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	foreach($_POST['note'] as $noteId=>$value){
		$note = (new VisitNotesDAO())->getNote($noteId, FALSE, $pdo)->setDescription($value);
		if((new VisitNotesDAO())->updateNote($note, $pdo) !== null){
			continue;
		}else {
			$pdo->rollBack();
			exit("error:Failed to update Note");
		}
	}
	$pdo->commit();
	exit("success:Notes updated");
}
?>
<section style="width: 850px;">
	<form id="encounter_details_edit_frm">
	<div class="e-block">
		<div class="title">Start Date</div>
		<div class="content"><?=date("Y/m/d g:ia", strtotime($e->getStartDate())) ?></div>
	</div>
	<div class="e-block">
		<div class="title">Department</div>
		<div class="content"><?=$e->getDepartment()->getName()?></div>
	</div>
	<div class="e-block">
		<div class="title">Specialization</div>
		<div class="content"><?=$e->getSpecialization()->getName()?></div>
	</div>
	<div class="e-block">
		<div class="title">Presenting Complaints</div>
		<div class="content">
			<?php if(sizeof($e->getPresentingComplaints())==0){?><span class="fadedText">No Noted Presenting complaints</span> <?php }?>
			<?php foreach ( $e->getPresentingComplaints() as $pc) {?>
				<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label>
			<?php }?>
		</div>
	</div>
	<div class="e-block">
		<div class="title">Review of Systems / Examinations</div>
		<div class="content">
			<?php if(sizeof($e->getSystemsReviews())==0){?><span class="fadedText">No Noted Systems Reviews </span><?php }?>

			<?php foreach ( $e->getSystemsReviews() as $pc) { ?>
			<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label><?php }?>
		</div>
	</div>
		<div class="e-block">
		<div class="title">Physical Examination</div>
		<div class="content">
			<?php if(sizeof($e->getSystemsReviews())==0){?><span class="fadedText">No Noted Systems Reviews </span><?php }?>

			<?php foreach ( $e->getSystemsReviews() as $pc) { ?>
			<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label><?php }?>
		</div>
	</div>
		<div class="e-block">
		<div class="title">Physical Examination Summary</div>
		<div class="content">
			<?php if(sizeof($e->getExamNotes())==0){?><span class="fadedText">No Noted Physical Examination Summary </span><?php }?>

			<?php foreach ( $e->getExamNotes() as $pc) { ?>
			<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label><?php }?>
		</div>
	</div>
		<div class="e-block">
		<div class="title">Past Medical History</div>
		<div class="content">
			<?php if(sizeof($e->getMedicalHistory())==0){?><span class="fadedText">No Noted Past Medical History </span><?php }?>

			<?php foreach ( $e->getMedicalHistory() as $pc) { ?>
			<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label><?php }?>
		</div>
	</div>
	<div class="e-block">
		<div class="title">Diagnoses</div>
		<div class="content">
			<?php if(sizeof($e->getDiagnoses())==0){?><span class="fadedText">No Noted Diagnoses</span> <?php }?>

			<?php foreach ( $e->getDiagnoses() as $pc) {?>
				<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label>
			<?php }?>
		</div>
	</div><div class="e-block">
		<div class="title">Past Drug History</div>
		<div class="content">
			<span class="fadedText">Drug History not editable</span>
		</div>
	</div>
	<div class="e-block">
		<div class="title">Investigations</div>
		<div class="content">
			<?php if(sizeof($e->getInvestigations())==0){?><span class="fadedText"> No Noted Investigations</span> <?php }?>

			<?php foreach ( $e->getInvestigations() as $pc) {?>
				<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description ?></textarea></label><?php }?>
		</div>
	</div>
	<div class="e-block">
		<div class="title">Plans</div>
		<div class="content"><?php foreach ( $e->getPlan() as $pc) {?>
			<label><textarea name="note[<?=$pc->id?>]"><?= $pc->description?></textarea></label><?php }?>
		</div>
	</div>
	<div class="">
			<span class="pull-right">
				<a class="btn" href="javascript:" id="editSubmit" data-id="<?= $e->getId()?>">Save Edit</a>
				<a href="javascript:" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</a>
			</span>
	</div>
	</form>
</section>
<script type="text/javascript">
	$(document).on('click', '#editSubmit', function(e){
		if(!e.handled){
			$.post('<?= $_SERVER['REQUEST_URI'] ?>', $('#encounter_details_edit_frm').serialize(), function(response){
				var dara = response.split(":");
				if(dara[0]==="error"){
					Boxy.warn(dara[1]);
				}else if(dara[0]==="success"){
					Boxy.get($(".close")).hideAndUnload();
					setTimeout(function(){
						Boxy.get($(".close")).hideAndUnload();
					}, 500);
				}
			});
			e.handled = true;
		}
	});
</script>
