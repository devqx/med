<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 3:58 PM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MedicalExamDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamReportingTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$s_center = (new ServiceCenterDAO())->all('MedicalReport');
$data = (new MedicalExamDAO())->all();
$templates = (new ExamReportingTemplateDAO())->all();


?>
<section>
	<h6 class="menu-head">Existing Business Unit/Service Center <a href="javascript:;" class="pull-right new_center action">Add New</a></h6>
	<div class="clear"></div>
	<ul class="list-blocks scr"><?php foreach ($s_center as $s) { ?>
			<li class="tag"><a class="centers" data-id="<?= $s->getId() ?>" href="javascript:;"><?= $s->getName() ?></a>
			<span class="pull-right">| <a href="javascript:;" class="edit_center" data-id="<?= $s->getId() ?>"></a></span></li><?php } ?></ul>
	
	
	<h6 class="menu-head">Existing Exams <a href="javascript:;" class="pull-right new_exam action">Add New</a></h6>
	<div class="clear"></div>
	<ul class="list-blocks ex"><?php foreach ($data as $H) {//$H=new MedicalExam()?>
			<li class="tag"><a class="details" data-id="<?= $H->getId() ?>" href="javascript:;"><?= $H->getName() ?></a>
			<span class="pull-right">| <a href="javascript:;" class="edit_exam" data-id="<?= $H->getId() ?>"> Edit</a></span></li><?php } ?></ul>

	<h6 class="menu-head">Templates <a href="javascript:;" class="pull-right new_template action">Add New</a></h6>
	<div class="clear"></div>
	<ul class="list-blocks tpl"><?php foreach ($templates as $H) {//$H=new MedicalExam()?>
			<li class="tag"><a class="templates" data-id="<?= $H->getId() ?>" href="javascript:;"><?= $H->getTitle() ?></a>
			<span class="pull-right">| <a href="javascript:;" class="edit_template" data-id="<?= $H->getId() ?>">Edit</a></span></li><?php } ?></ul>

</section>
<script type="text/javascript">
	function reloadExams() {
		$.getJSON('/api/get_medical_exams.php', function (data) {
			var str = '';
			for(var i =0; i<data.length;i++){
				str += '<li class="tag"><a class="details" data-id="'+data[i].id+'" href="javascript:;">'+data[i].name+'</a><span class="pull-right ">| <a href="javascript:;" class="edit_exam" data-id="'+data[i].id+'">Edit</a></span></li>';
			}
			$('section > ul.list-blocks.ex').html(str);
		})
	}
	function reloadExamTemplates() {
		$.getJSON('/api/get_medical_report_templates.php', function (data) {
			var str = '';
			for(var i =0; i<data.length;i++){
				str += '<li class="tag"><a class="templates" data-id="'+data[i].id+'" href="javascript:;">'+data[i].title+'</a><span class="pull-right ">| <a href="javascript:;" class="edit_template" data-id="'+data[i].id+'">Edit</a></span></li>';
			}
			$('section > ul.list-blocks.tpl').html(str);
		})
	}
	function reloadServiceCenter() {
		$.getJSON("/api/get_service_centers.php?type=MedicalReport", function (data) {
			var str = '';
			for(var i =0; i<data.length;i++){
				str += '<li class="tag"><a class="centers" data-id="'+data[i].id+'" href="javascript:;">'+data[i].name+'</a><span class="pull-right ">| <a href="javascript:;" class="edit_center" data-id="'+data[i].id+'"></a></span></li>';
			}
			$('section > ul.list-blocks.scr').html(str);
		})
	}
	$(document).on('click', '.details', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/medical_exam.edit.php?id=" + $(this).data("id"), {title: "Edit Medical Exam", afterHide: function(){
				reloadExams();
			}});e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click', '.templates', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/medical_exam_template.php?id=" + $(this).data("id"));
			e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click', '.edit_exam', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/medical_exam.edit.php?id=" + $(this).data("id"), {title: "Edit Medical Exam", afterHide: function(){
				reloadExams();
			}});
			e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click', '.edit_template', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/medical_exam_template.edit.php?id=" + $(this).data("id"), {title: "Edit Medical Exam Report", afterHide: function(){
				reloadExamTemplates();
			}});
			e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click', '.new_exam', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/medical_exam.add.php", {title: "Add Medical Exam", afterHide: function(){
				reloadExams();
			}});
			e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click', '.new_template', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/medical_exam_template.add.php", {title: "Add Medical Exam", afterHide: function(){
				reloadExamTemplates();
			}});
			e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click',  '.new_center', function (e) {
		if(!e.handled){
			Boxy.load("/pages/pm/medical_exam/medical_exam_center_new.php", {title: "Add  Business Unit/ Service Center", afterHide: function () {
				reloadServiceCenter();
			}});
			e.preventDefault();
			e.handled = true;
			return false;
		}
	}).on('click',  '.edit_center', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_exam/edit_centre.php?id=" + $(this).data("id"), {
				title: "Edit Business Unit/ Service Center", afterHide: function () {
					reloadServiceCenter();
				}
			});
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
</script>
