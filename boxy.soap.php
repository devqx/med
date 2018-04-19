<?php
//include 'boxy.soap.euracare.php';
//exit;
$clinic_flag = "General";

require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/FormularyDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$formulary = (new FormularyDAO())->all();
$bodyparts = (new BodyPartDAO())->all(null);
$severities = getTypeOptions('severity', 'patient_diagnoses');
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role))
{	exit ($protect->ACCESS_DENIED);}

require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/func.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
sessionExpired();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.pharmacy.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AllergenCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/Hx_TemplateDAO.php';


$allergen_cats = (new AllergenCategoryDAO())->getAll();
$super_generic = (new SuperGenericDAO())->getAll();
$MainConfig = new MainConfig();
$encounter = null;


$signAndClose = true;//Clinic::$editStyleByAdd;
if (isset($_GET['encounter_id']) && !is_blank($_GET['encounter_id'])) {
	$encounter = (new EncounterDAO())->get($_GET['encounter_id'], false);
}
$specimens = (new LabSpecimenDAO())->getSpecimens();

$templates = (new ExamTemplateDAO())->all();
$hx_templates = (new Hx_TemplateDAO())->all();

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
$scanTypes = (new ScanDAO())->getScans();

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$return = array();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.assessments.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	// $assessment = new Assessments();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientSystemsReview.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SystemsReview.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysicalExamination.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientPhysicalExam.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSystemsReviewDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SystemsReviewDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysicalExaminationDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientPhysicalExamDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDiagnosis.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Diagnosis.php';
	$VNDAO = new VisitNotesDAO();
	$patObj = new PatientDemograph($_POST['pid']);
	$this_user = new StaffDirectory($_SESSION['staffID']);
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	$patObj = (new PatientDemographDAO())->getPatient($_POST['pid'], false, $pdo, null);
	
	if($encounter->getCanceled()){
		$pdo->rollBack();
		exit("error:Failed to save encounter documentation");
	}
	if (!empty($_POST['subjective_note'])) {
		$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('subj')->setDescription($_POST['subjective_note'] . "  " . @$_POST['subjective_history'])->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($this_user)->setEncounter($encounter);
		
		if (!$VNDAO->addNote($vNote, $pdo)) {
		
		} else {
		}
	}
	
	//systems review
	/*$systems_reviews = array();
	if (!is_blank(@$_POST['system_review'])) {
		foreach (@$_POST['system_review'] as $systems_review) {
			if (!empty($systems_review)) {
				$systems_reviews[] = (new SystemsReviewDAO())->get($systems_review, $pdo);
				$review = new PatientSystemsReview();

				$review->setDate(date("Y-m-d H:i:s"));
				$review->setPatient(new PatientDemograph($_POST['pid']));
				$review->setReviewer(new StaffDirectory($_SESSION['staffID']));
				$review->setSystemsReview((new SystemsReviewDAO())->get($systems_review, $pdo));
				$review->setEncounter($encounter);
				if ((new PatientSystemsReviewDAO())->add($review, $pdo) == null) {
					$pdo->rollBack();
					exit("error:Failed to save systems review");
				} else {
				}
			}
		}
	}*/
	
	if (!is_blank(@$_POST['system_review_summary'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SystemsReviewCategoryDAO.php';
		foreach (@$_POST['system_review_summary'] as $categoryId => $summary) {
			$summary = nl2br($summary);
			$category = (new SystemsReviewCategoryDAO())->get($categoryId, $pdo)->getName();
			if (!empty($summary)) {
				$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('revw')->setDescription("<b>$category</b>: $summary")->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($this_user)->setEncounter($encounter);
				
				if (!$VNDAO->addNote($vNote, $pdo)) {
					$pdo->rollBack();
					exit("error:Failed to save Review Note");
				}
			}
		}
	}
	
	//physical exams
	$physical_exams = array();
	
	/*if (!is_blank(@$_POST['physical_exam'])) {
		foreach (@$_POST['physical_exam'] as $physical_exam) {
			if (!empty($physical_exam)) {
				$physical_exams[] = (new PhysicalExaminationDAO())->get($physical_exam, $pdo);
				$review = new PatientPhysicalExam();

				$review->setDate(date("Y-m-d H:i:s"));
				$review->setPatient(new PatientDemograph($_POST['pid']));
				$review->setReviewer(new StaffDirectory($_SESSION['staffID']));
				$review->setPhysicalExamination((new PhysicalExaminationDAO())->get($physical_exam, $pdo));
				$review->setEncounter($encounter);
				if ((new PatientPhysicalExamDAO())->add($review, $pdo) == null) {
					$pdo->rollBack();
					exit("error:Failed to save physical examination assessment");
				} else {
					// error_log("...Saved physical assessment");
				}
			}
		}
	}*/
	
	if (!is_blank(@$_POST['physical_exam_summary'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysicalExaminationCategoryDAO.php';
		foreach (@$_POST['physical_exam_summary'] as $categoryId => $summary) {
			$category = (new PhysicalExaminationCategoryDAO())->get($categoryId, $pdo)->getName();
			if (!empty($summary)) {
				$summary = nl2br($summary);
				$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('ph_ex')->setDescription("<b>$category</b>: $summary")->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($this_user)->setEncounter($encounter);
				
				if (!$VNDAO->addNote($vNote, $pdo)) {
					$pdo->rollBack();
					exit("error:Failed to save Review Note");
				}
			}
		}
	}
	
	if (!is_blank(@$_POST['hx_template_id'])){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/Hx_Template_CategoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/HxTemplate.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Hx_Template_Category.php';
		foreach (@$_POST['hx_template_id'] as $category_id => $hx_note) {
			$hx_category = (new Hx_Template_CategoryDAO())->getCategory($category_id)->getName();
			if (!empty($hx_note)) {
				$hx_note = nl2br($hx_note);
				$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('fm_hx')->setDescription("<b> $hx_category</b>: $hx_note")->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
				if (!$VNDAO->addNote($vNote, $pdo)) {
					$pdo->rollBack();
					exit("error:Failed to save History Note");
				} else {
					//errorLog($e);
				}
			}
		}
	}
	
	//drug history
	$d_history = array();
	$pds = array();
	foreach ($_POST['drugs_history'] as $i => $hist) {
		if (!empty($hist)) {
			$d_history[] = $hist;
			
			$g = (new DrugGenericDAO())->getGeneric($hist, true, $pdo);
			$pd = new PrescriptionData();
			$pd->setDrug(null);
			$pd->setGeneric($g);
			$pd->setDose("0");
			$pd->setDuration("0");
			$pd->setComment($_POST['drugs_history_comment'][$i]);
			$pd->setStatus("history");
			$pd->setFrequency("0");
			$pd->setRefillable(false);
			$pd->setRequestedBy($this_user);
			$pd->setHospital(new Clinic(1));
			$pds[] = $pd;
		}
	}
	if (count($d_history) > 0) {
		$pres = new Prescription();
		$pres->setPatient($patObj);
		$pres->setRequestedBy($this_user);
		$pres->setEncounter($encounter);
		if (is_blank($_POST['regimen_note'])) {
			$pres->setNote("Used drug in the past");
		} else {
			$pres->setNote($_POST['regimen_note']);
		}
		
		$pres->setHospital(new Clinic(1));
		
		foreach ($d_history as $generic) {
		}
		$pres->setData($pds);
		if ((new PrescriptionDAO())->addPrescription($pres, $pdo) == null) {
			$pdo->rollBack();
			exit("error:Failed to save history drugs");
		} else {
			// errorLog($e);
		}
	}
	
	
	
	
	if(!is_blank($_POST['allergies_full'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAllergens.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AllergenCategory.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SuperGeneric.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
		
		$allergensObj = json_decode($_POST['allergies_full']);
		foreach($allergensObj as $value){
			if (!is_blank($value->allergen_category)) {
				if ($value->allergen_category == 1) { // drug
					if (is_blank($value->super_generic) || is_blank($value->reaction) || is_blank($value->allergen_severity)) {
						$pdo->rollBack();
						exit('error:Incomplete details for drug-related allergen');
					}
				}
				if (!in_array($value->allergen_category, [1])) { // food and environment
					if (is_blank($value->allergen) || is_blank($value->reaction) || is_blank($value->allergen_severity)) {
						$pdo->rollBack();
						exit('error:Incomplete details for non-drug-related allergen');
					}
				}
				
				$allergy = (new PatientAllergens())
					->setPatient($patObj)
					->setAllergen(!is_blank($value->allergen) ? $value->allergen: null)
					->setReaction(!is_blank($value->reaction) ? $value->reaction: null)
					->setSeverity(!is_blank($value->allergen_severity) ? $value->allergen_severity: null)
					->setNotedBy(new StaffDirectory($_SESSION['staffID']))
					->setCategory(new AllergenCategory($value->allergen_category))
					->setEncounter($encounter)
					->setSuperGeneric( !is_blank($value->super_generic) ? new SuperGeneric($value->super_generic) : null)
					->add($pdo);
				if (!$allergy) {
					$pdo->rollBack();
					exit('error: Failed to save patient allergen data');
				}
			}
		}
	}
	
	//exams
	if (!is_blank(@$_POST['exam_note'])) {
		$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('exam')->setDescription($_POST['exam_note'])->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
		
		if (!$VNDAO->addNote($vNote, $pdo)) {
			$pdo->rollBack();
			exit("error:Failed to save Exam Note");
		} else {
			//errorLog($e);
		}
	}
	
	if (!is_blank(@$_POST['hx_note'])) {
		$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('fm_hx')->setDescription($_POST['hx_note'])->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
		if (!$VNDAO->addNote($vNote, $pdo)) {
			$pdo->rollBack();
			exit("error:Failed to save History Note");
		} else {
			//errorLog($e);
		}
	}
	
	//medical_history = history of diagnoses
	////$_POST['fake_status'] = array();
	foreach (@$_POST['medical_history'] as $i => $val) {
		if (!is_blank($val)) {
			$diagnosis = (new PatientDiagnosis())->setPatient($patObj)->setDate($_POST['diagnosis_date'][$i])->setDiagnosedBy($this_user)->setDiagnosis(new Diagnosis($val))->setNote($_POST['diagnosisComment'])->setStatus(true)->setEncounter($encounter)->setType("history");
			if ((new PatientDiagnosisDAO())->add($diagnosis, $pdo) == null) {
				$pdo->rollBack();
				exit("error:Failed to save history diagnoses");
			} else {
				$Diagnosis = (new DiagnosisDAO())->getDiagnosis($val, $pdo);
				$summaryNote = '[' . $Diagnosis->getCode() . '] ' . $Diagnosis->getName() . ' (' . ucwords('history') . ')';
				$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('diag_hist')->setDescription($summaryNote)->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
				
				if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
					$pdo->rollBack();
					exit("error:Failed to save Diagnoses summary Note");
				}
			}
		}
	}
	
	if (!is_blank($_POST['diagnosisComment']) && is_blank(@$_POST['medical_history'])) {
		$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('diag_hist')->setDescription($_POST['medical_history'])->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
		
		if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
			$pdo->rollBack();
			exit("error:Failed to save Diagnoses Note");
		}
	}
	
	//actual diagnoses
	$cases = array_filter($_POST['cases']);
	$states = array_filter($_POST['states']);
	$sevs = array_filter(@$_POST['severity']);
	$comments = array_filter(@$_POST['d_comment']);
	$bodypart = @array_filter(@$_POST['bodypart']);
	
	if (sizeof($cases) > 0 ) {
		foreach ($cases as $i => $case) {
			if (!is_blank($case)) {
				$diagnosis = (new PatientDiagnosis())->setPatient(new PatientDemograph($_POST['pid']))->setDiagnosedBy($this_user)->setDiagnosis(new Diagnosis($case))->setNote(@$comments[$i])->setSeverity(@$sevs[$i])->setBodyPart($bodypart[$i])->setStatus(true)->setType($states[$i]);
				
				if ((new PatientDiagnosisDAO())->add($diagnosis, $pdo) == null) {
					$pdo->rollBack();
					exit("error:Diagnosis failed to save");
				} else {
					$Diagnosis = (new DiagnosisDAO())->getDiagnosis($case, $pdo);
					$summaryNote = trim(ucwords(@$sevs[$i]) . ' ' ). $Diagnosis->getName() . ' [' . $Diagnosis->getCode() . ']' . ' (' . ucwords(@$states[$i]) . ')'. (!is_blank($comments[$i]) ? '<br>'.$comments[$i].'' : '');
					$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('asst')->setDescription($summaryNote)->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
					
					if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
						$pdo->rollBack();
						exit("error:Failed to save Diagnoses summary Note");
					}
				}
			}
		}
	}
	
	if( isset($_POST['diagnosis_for_encounter'])){
		foreach (array_filter($_POST['diagnosis_for_encounter']) as $eDid){
			$dia = (new PatientDiagnosisDAO())->get($eDid, $pdo);
			
			$summaryNote = $dia->getDiagnosis()->getName() . ' [' . $dia->getDiagnosis()->getCode() . ']' . ' (' . ucwords($dia->getType()) . ')';
			//$pdo->rollBack();exit('error:'.$summaryNote);
			$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('asst')->setDescription($summaryNote)->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
			
			if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
				$pdo->rollBack();
				exit("error:Failed to save Diagnoses summary Note");
			}
		}
	}
	if(!isset($_POST['diagnosis_for_encounter']) && sizeof($cases) == 0 ) {
		$pdo->rollBack();
		exit('error:At least one diagnosis is required');
	}
	
	//plan note
	if (!empty($_POST['plan_note'])) {
		$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('plan')->setDescription($_POST['plan_note'])->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
		if (!$VNDAO->addNote($vNote, $pdo)) {
			$pdo->rollBack();
			exit("error:Failed to save Plan Note");
		}
	}
	
	//diagnosis raw note
	if (!empty($_POST['diagnosisNote'])) {
		$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('diag_note')->setDescription($_POST['diagnosisNote'])->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
		if (!$VNDAO->addNote($vNote, $pdo)) {
			$pdo->rollBack();
			exit("error:Failed to save Diagnoses Note");
		}
	}
	
	//medication plan summary
	if (!empty($_POST['prescription_plan'])) {
		$notes = array_filter(explode(' || ', $_POST['prescription_plan']));
		foreach ($notes as $note){
			$vNote = (new VisitNotes())->setPatient($patObj)->setNoteType('m_plan')->setDescription($note)->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
			if (!$VNDAO->addNote($vNote, $pdo)) {
				$pdo->rollBack();
				exit("error:Failed to save Medication Plan Note");
			}
		}
	}
	//medication object
	if (isset($_REQUEST['prescription'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
		
		$reg = json_decode($_REQUEST['prescription']);
		
		if ($reg->pharmacy_id !== "" && sizeof($reg->regimens)) {
			//regimen was created but was blank ? break the operation
			if (sizeof($reg->regimens) === 0) {
				$pdo->rollBack();
				exit("error:Please add one or more regimen data");
			} else {
				if (!is_blank($_REQUEST['prescription']) && is_blank($reg->pharmacy_id)) {
					$pdo->rollBack();
					exit("error:Select a pharmacy for the prescription");
				} else {
					$pres = new Prescription();
					$pres->setPatient($patObj);
					$pres->setInPatient(null);
					
					$pres->setRequestedBy($this_user);
					$pres->setNote($reg->note);
					$pres->setHospital(new Clinic(1));
					$pds = array();
					
					foreach ($reg->regimens as $i => $pre) {
						
						if ($pre->drug === "" && $pre->generic === "") {
							$pdo->rollBack();
							echo "error:Please a drug name or a generic name";
							exit;
						}
						
						$pd = new PrescriptionData();
						$g = new DrugGeneric();
						$d = new Drug();
						if ($pre->drug !== "" && $pre->drug != "null" && isset($pre->drug->id) && isset($pre->drug->name)) {
							$d->setId($pre->drug->id);
							$d->setName($pre->drug->name);
							$d->setCode($pre->drug->code);
							$d->setStockQuantity($pre->drug->stockQuantity);
							$g->setId($pre->drug->generic->id);
							$d->setGeneric($g);
							//  Set other drug properties if there is a need for it (NOTE that the complete drug properties are here on the request object)
						} else {
							$d = null;
							if ($pre->generic !== "") {
								$g->setId($pre->generic->id);
								$g->setName($pre->generic->name);
								/*if ($pre->drug === "" || $pre->drug == "null" || isset($pre->drug->id) || isset($pre->drug->name)) {
									$d = null;
								} else {
									$d->setGeneric($g);
								}*/
							}
						}
						$pd->setDrug($d);
						$pd->setGeneric($g);
						$pd->setDose($pre->dose);
						$pd->setDuration($pre->duration);
						$pd->setComment($pre->comment);
						if (!is_blank($pre->body_part)) {
							$pd->setBodyPart((new BodyPartDAO())->get($pre->body_part, $pdo));
						}
						$pd->setFrequency($pre->freqno . ' x ' . $pre->freqtype->id);
						$pd->setRefillable($pre->refillable);
						$pd->setRefillNumber($pre->refillable ? parseNumber($pre->refill_count): null);
						$pd->setRequestedBy($this_user);
						$pd->setHospital(new Clinic(1));
						$pds[] = $pd;
					}
					$pres->setData($pds);
					$pres->setServiceCentre((new ServiceCenter($reg->pharmacy_id)));
					
					$p = (new PrescriptionDAO())->addPrescription($pres, $pdo);
					if ($p === null) {
						$pdo->rollBack();
						exit("error:Unable to save Plan Medication");
					} else {
						$pq = new PatientQueue();
						$pq->setType("Pharmacy");
						$pq->setPatient($patObj);
						(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
					}
				}
			}
		}
	}

	// for consumable items
	if (isset($_POST['item_id']) && count(array_filter($_POST['item_id'])) >= 1) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequest.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequestData.php';
		$r_code = (new PatientItemRequest())->generateItemCode($pdo);
		
		$gen = null;
		if (!empty($_POST['generic_id'])) {
			$gen = (new ItemGenericDAO())->get($_POST['generic_id'], $pdo);
		}
		$note_it = $_POST['item_request_note'];
		$data = [];
		foreach ($_POST['item_id'] as $index => $it) {
			$item = (new ItemDAO())->getItem($_POST['item_id'][$index], $pdo);
			$data[] = (new PatientItemRequestData())->setItem($item)->setHospId(1)->setGroupCode($r_code)->setQuantity($_POST['qty'][$index])->setStatus('open')->setGeneric($gen);
		}
		unset($it);
		$item_request = (new PatientItemRequest())->setServiceCenter($_POST['item_center_id'])->setPatient($patObj)->setCode($r_code)->setRequestedBy($this_user)->setRequestNote($note_it)->setEncounter($encounter)->setData($data)->add($pdo);
		if ($item_request == null) {
			$pdo->rollBack();
			exit("error:Failed to request patient consumable");
		}
	}
	//lab
	if (isset($_POST['lab-reqs']) && !empty($_POST['lab-reqs'])) {
		if(is_blank($_POST['service_centre_id'])){
			$pdo->rollBack();
			exit("error:Please select Laboratory Business Unit/Service center");
		}
		$request = new LabGroup();
		$request->setPatient($patObj);
		$request->setRequestedBy($this_user);
		$request->setEncounter($encounter);
		$request->setServiceCentre((new ServiceCenterDAO())->get($_POST['service_centre_id'], $pdo));
		
		$pref_specimens = array();
		$sel_specimens = isset($_POST['specimen_ids']) ? $_POST['specimen_ids'] : [];
		foreach ($sel_specimens as $s) {
			if (!empty($s)) {
				$pref_specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
			}
		}
		// errorLog($e);
		$request->setPreferredSpecimens($pref_specimens);
		$request->setRequestNote($_POST['lab_request_note']);
		
		$lab_data = array();
		$tests = array_filter(explode(",", $_POST['lab-reqs']));
		
		foreach ($tests as $l) {
			$lab_data[] = (new LabDAO())->getLab($l, true, $pdo);
		}
		//errorLog($e);
		$request->setRequestData($lab_data);
		if ((new PatientLabDAO())->newPatientLabRequest($request, false, $pdo) == null) {
			$pdo->rollBack();
			exit("error:Failed to create the lab request(s)");
		} else {
			// errorLog($e);
		}
	}
	
	if (isset($_POST['scan_request_ids']) && !empty($_POST['scan_request_ids'])) {
		if(is_blank($_POST['scan_service_centre_id'])){
			$pdo->rollBack();
			exit("error:Please select Radiology Business Unit/Service center");
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScan.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
		$newScan = [];
		$scans = array_filter(explode(",", $_POST['scan_request_ids']));
		foreach ($scans as $s) {
			//$scan_ids = [];
			$scan_ids = (new ScanDAO())->getScan($s, $pdo);
			$scanCentre = (new ServiceCenterDAO())->get($_POST['scan_service_centre_id'], $pdo);
			
			$scan = new PatientScan();
			$scan->setPatient($patObj)->setScan($scan_ids)->setRequestDate(date("Y-m-d H:i:s"))->setRequestedBy($this_user)->setRequestNote(!is_blank($_POST['request_note']) ? $_POST['request_note'] : '')->setEncounter($encounter)->setServiceCentre($scanCentre);
			if ((new PatientScanDAO())->addScan($scan, false, $pdo) == null) {
				$pdo->rollBack();
				exit("error:Scan Request Failed");
			}
		}
	}
	
	if($signAndClose) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$encounter_ = (new EncounterDAO())->get($encounter->getId(), false, $pdo)->setSignedOn(date("Y-m-d H:i:s", time()))->setSignedBy(new StaffDirectory($_SESSION['staffID']))->setOpen(false)->update($pdo);
		
		//update the queue
		$pq = (new PatientQueueDAO())->getApproximateQueueItem($encounter_->getStartDate(), 'Doctors', $encounter_->getSpecialization(), $encounter_->getPatient(), $pdo);
		if ($pq) {
			$pq->setStatus('Attended');
			$pq->setSeenBy(new StaffDirectory($_SESSION['staffID']));
			if (!is_null($pq->getSpecialization())) {
				$specialtyCode = $pq->getSpecialization()->getCode();
				if (boolval($pq->getFollowUp())) {
					$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialtyCode, $pq->getPatient()->getId(), true, $pdo);
				} else {
					$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialtyCode, $pq->getPatient()->getId(), true, $pdo);
				}
				$pq->setAmount($price);
			} else {
				$pq->setAmount(0);
			}
			if (!(new PatientQueueDAO())->changeQueueStatus($pq, $pdo)) {
				//$pdo->rollBack();
				//exit('error:Queue failed to update!');
			}
		}
	}
	
	$pdo->commit();
	ob_end_clean();
	
	exit("success:Data Saved");
}
$drugGenerics = [];
$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
//$drugAllergens = new ArrayObject();
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_patient_allergens.php';
$drugs = [];
$pp = new Pharmacy();
if (!$pp::$canPrescribeBrand) {
	$drugs = [];
} else {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
}
$labs = new Labs();
?>
<section style="width: 1050px;">
	<script type="text/javascript">
		var DrugGenericAllergens = <?= json_encode($drugAllergens, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		var drugs = <?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		var superGens = <?=json_encode($super_generic, JSON_PARTIAL_OUTPUT_ON_ERROR);?>;

		var _allergicGenerics = [];
		_.each(DrugGenericAllergens, function (obj) {
			_.each(obj.superGeneric.data, function(o){
				_allergicGenerics.push(o.id);
			});
		});

		function begin(form) {
			save();
			showPinBox(function () {
				$.ajax({
					url: form.action,
					data: $(form).serialize(),
					type: "POST",
					beforeSend: begin2,
					complete: function (xhr, status) {
						ended(xhr.responseText);
					}
				});
			});
			return false;
		}

		/* ##### filter drug generic according to pharmacy and drug according to generic ### */

		$('#formulary_id').on('change', function (e) {
			var id = $(this).val();
			if (id) {
				$.getJSON('/api/get_formulary.php', {id: id, action: 'generics'}, function (data) {
					var filtered = [];
					var filteredIds = [];
					_.each(data.data, function (formulary) {
						filtered.push(formulary.generic);
						filteredIds.push(formulary.generic.id);
					});
					setDrugGeneric(filtered);

					setDrugs(_.filter(drugs, function (drug) {
						return _.includes(filteredIds, drug.generic.id);
					}));
				});
			} else {
				setDrugGeneric(drugGens);
				setDrugs(drugs);
			}

			//setDrugGeneric(_.filter(drugGens, function (o) {
			//	return (o.id==id);
			//}));
		});

		$('select[name="pharmacy_id"]').on('change', function () {
			/*if($(this).val() !== ""){
			 var pharm_id = $(this).val();
			 setDrugGeneric(_.filter(drugGens, function(d){
			 return _.includes(d.service_centre_id, pharm_id);
			 }));

			 }else{
			 setDrugGeneric(drugGens);
			 setDrugs(drugs);
			 }*/
		});

		function setDrugGeneric(param) {
			$("#generic").select2('val', '').select2({
				width: '100%',
				allowClear: true,
				placeholder: "select drug generic",
				data: function () {
					return {results: param, text: 'name'};
				},
				formatResult: function (source) {
					return source.name + "(" + source.form + ") " + source.weight;
				},
				formatSelection: function (source) {
					return source.name + "(" + source.form + ") " + source.weight;
				}
			});
		}

		function setDrugs(param) {
			$("#drug").select2('val', '').select2({
				width: '100%',
				allowClear: true,
				placeholder: "Select drug",
				data: function () {
					return {results: param, text: 'name'};
				},
				formatResult: function (source) {
					return source.name + "(" + source.generic.weight + " " + source.generic.form + ")";
				},

				formatSelection: function (source) {
					return source.name + "(" + source.generic.weight + " " + source.generic.form + ")";
				}
			});
			$("#drug-info").html("");
		}
		
		function setSuperGen(param) {
			$("#super_generic").select2('val', '').select2({
				width: '100%',
				allowClear: true,
				placeholder: "--Select super generic name--",
				data: function () {
					return {results: param, text: 'name'};
				},
				formatResult: function (source) {
					return source.name;
				},

				formatSelection: function (source) {
					return source.name;
				}
			});
			//$("#drug-info").html("");
		}

		/*  ############# Filter End ########### */


		function begin2() {
		}

		function ended(s) {
			var boxy = Boxy.get($(".close"));
			var data = s.trim().split(":");
			if (data[0] === "success") {
				boxy.options.canClose = true;
				Boxy.get($(".close")).hideAndUnload();
				showTabs(1);
				Boxy.info(data[1]);
			} else {
				Boxy.warn(data[1]);
			}
			showTabs(1);
		}
	</script>
	<?php $ARR['patient_ID']=$_GET['pid']; include_once "patient_demograph.mini.php" ?>
	<form name="soapForm" method="post" id="soapForm" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return begin(this)">
		<!--    Presenting complaints-->
		<fieldset>
			<legend>Presenting Complaints</legend>
			<?php if ($clinic_flag === "private") {
				include_once "boxy.eye_review.php";
			} ?>
			<?php if ($clinic_flag === "General") { ?>
				<label>
					Presenting Complaints:
					<textarea name="subjective_note" placeholder="type here ...">
						<u>Presenting Complaints:</u>
						<br><br><br><br>
						<u>History of complaints:</u><br><br><br><br>
					</textarea>
					
				</label>
				<?php /**<label>History of presenting complaints:
					<textarea name="subjective_history" placeholder="type here ..."></textarea>
				</label> **/?>
			<?php } ?>
		</fieldset>
		<!--Review of systems-->
		<fieldset>
			<legend>Review Of Systems</legend>
			<?php include_once "boxy.system_review.php"; ?>

		</fieldset>
		<!--Drug History-->
		 <!-- <fieldset>
			<legend>Drug History</legend>
			<div class="block">
				<button type="button" class="action" onclick="add_drug_history()"><i class="icon-plus-sign"></i> add</button>
				<button type="button" class="action" onclick="remove_drug_history()"><i class="icon-minus-sign"></i> remove
				</button>
			</div>
			<label>Known drugs in the past:</label>
			<div class="drug_history_data row-fluid">
				<label class=" span7"><input type="hidden" name="drugs_history[]"></label>
				<label class="span5"><input type="text" name="drugs_history_comment[]" placeholder="Drug Comment"> </label>
			</div>
			<label>Comment <textarea name="regimen_note"></textarea> </label>
		</fieldset> -->
		<fieldset>
			<legend>Hx</legend>
			<label>Template <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_help.php">help</a>
					<!--| <i class="icon-star-empty"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_add.php">add selected to favorites</a> | <i class="icon-star"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_delete.php">remove selected from favorites</a>--> | <i
						class="icon-plus-sign"></i><a href="javascript:;" class="hx_template_link" data-href="hx_template_new.php">add to list</a></span>
				<select name="hx_template_id" id="hx_template_id" data-placeholder="Select Custom Text Templates">
					<option></option>
					<?php foreach ($hx_templates as $ht) {?>
						<option value="<?= $ht->getId() ?>" data-text="<?= ($ht->getContent()) ?>"><?= $ht->getCategory()->getName() ?>
						: <?= $ht->getName() ?></option><?php } ?>
				</select>
			</label>
			<label>Hx Note:
				<textarea rows="4" name="hx_note" placeholder="Type here ..."></textarea>
			</label>
		</fieldset>
		
		
		<!--Past medical history-->
		<fieldset>
			<legend>Past Medical History</legend>
			<div class="block">
				<button type="button" class="action" onclick="add_medical_history()"><i class="icon-plus-sign"></i> add</button>
				<button type="button" class="action" onclick="remove_medical_history()"><i class="icon-minus-sign"></i> remove
				</button>
			</div>
			<label>Past Medical
				History:<span class="pull-right"><label style="display: inline;"><input type="radio" checked="checked" name="type_diagnosis_" value="icd10">ICD10</label> <label style="display: inline;"><input type="radio" name="type_diagnosis_" value="icpc-2">ICPC-2</label>  </span></label>
			<div class="medical_history_data row-fluid">
				<label class="span9"><input type="hidden" name="medical_history[]"></label>
				<label class="span3"><input type="text" name="diagnosis_date[]" readonly="" placeholder="Date Diagnosed"></label>
			</div>
			<label>Comment:<textarea name="diagnosisComment" rows="2"></textarea></label>
		</fieldset>

		<fieldset id="allergies_fieldset">
			<legend>Allergies</legend>
			
			<div class="row-fluid">
				<!--new allergy-->
				<div class="span6">
					<label>Category <select data-placeholder="-- select allergen category --" name="allergen_category" id="allergen_category" class="wide">
							<option></option>
							<?php foreach ($allergen_cats as $cat) { ?>
								<option value="<?= $cat->getId() ?>"><?= $cat->getName() ?></option>
							<?php } ?>
						</select></label>
					<label id="drug_id">Drug
						<input type="hidden"  name="super_generic" id="super_generic">
						<!--<select data-placeholder="-- select the allergic drug --">
							<option></option>
							<?php foreach ($super_generic as $super_gen) { ?>
								<option value="<?= $super_gen->getId() ?>"><?= $super_gen->getName() ?></option>
							<?php } ?>
						</select>-->
					</label>
					<label class="allergen">Allergen<input type="text" name="allergen" id="allergen"></label>
					<label>Reaction <input type="text" name="reaction" id="reaction"></label>
					<label>Severity <select name="allergen_severity" id="allergen_severity" class="wide">
							<?php foreach ($MainConfig::allergenSeverities() as $val => $sev) { ?>
								<option value="<?= $val ?>"><?= $sev ?></option>
							<?php } ?>
						</select></label>
					<button class="action" type="button" onclick="add_allergies()">Add</button>
					<button class="action" type="button" onclick="reset_allergies()">Reset</button>
					<input type="hidden" name="allergies_full" id="allergies_full">
				</div>
				<!--/new allergy-->
				<!--existing allergies-->
				<div class="span6 overscrollLabDiv" style="height:300px;min-height:300px">
					<span class="fadedText">Existing Allergies</span>
					<ul style="list-style-type: none;margin:0">
						<?php
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
						$data = (new PatientAllergensDAO())->forPatient($_GET['pid']);
						foreach($data as $allergy){//$allergy=new PatientAllergens();?>
							<li class="row-fluid" style="display: inline-block;
    background: #FEFEFE;
    border: 2px solid #FAFAFA;
    box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
    padding: 2px;
    background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
    -webkit-transition: all .2s ease;
    -moz-transition: all .2s ease;
    -o-transition: all .2s ease;
    transition: all .2s ease;
   ">
								<div class="span11" style="padding-left:10px"><?=$allergy->getAllergen()?$allergy->getAllergen():''?><?= $allergy->getSuperGeneric()?$allergy->getSuperGeneric()->getName():''?> (<?=$allergy->getCategory()?$allergy->getCategory()->getName():''?>)</div>
								<div class="span1"><a href="javascript:;" class="resolveAllergenLink pull-right" data-pid="<?=$_GET['pid']?>" data-id="<?=$allergy->getId()?>"><i class="icon icon-remove-sign"></i></a></div>
							</li>
						<?php }?>
					</ul>
				</div>
				<!--/existing allergies-->
			</div>
			
		</fieldset>

		<!-- Family / Social History
		<fieldset>
				<legend>Refraction</legend>
		</fieldset>-->
		<!-- Physical Examination -->
		<fieldset>
			<legend>Physical Examination</legend>
			<?php include_once "boxy.physical_exam.php"; ?>
		</fieldset>
		<!--Physical Examination Summary -->
		<fieldset>
			<legend>Physical Examination Summary</legend>
			<label>Template <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_help.php">help</a>
					<!--| <i class="icon-star-empty"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_add.php">add selected to favorites</a> | <i class="icon-star"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_delete.php">remove selected from favorites</a>--> | <i
						class="icon-plus-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_new.php">add to list</a></span>
				<select name="template_id" id="template_id" data-placeholder="Select Custom Text Templates">
					<option></option>
					<?php foreach ($templates as $t) { ?>
						<option value="<?= $t->getId() ?>" data-text="<?= ($t->getContent()) ?>"><?= $t->getCategory()->getName() ?>
						: <?= $t->getTitle() ?></option><?php } ?>
				</select>
			</label>
			<label>Exam Note:
				<textarea rows="4" name="exam_note" placeholder="Type here ..."></textarea>
			</label>
		</fieldset>

		<!--Diagnosis-->
		<fieldset>
			<legend>Diagnosis</legend>
			<div class="row-fluid">
				<!--new diagnosis box-->
				<div class="span7">
					<div class="block">
						<button type="button" class="action" onclick="add_diagnosis_data()"><i class="icon-plus-sign"></i> add</button>
						<button type="button" class="action" onclick="remove_diagnosis_data()"><i class="icon-minus-sign"></i> remove
						</button>
					</div>

					<label>Diagnosis Data <span class="pull-right"><label style="display: inline;"><input type="radio" checked="checked" name="type_diagnosis" value="icd10">ICD10</label> <label
								style="display: inline;"><input type="radio" name="type_diagnosis" value="icpc-2">ICPC-2</label>  </span></label>
					<div class="diagnosis row-fluid">
            <span>
                <input type="hidden" name="cases[]" class="span7">
                <select style="display: inline-block;" name="states[]" class="span2">
                    <option value="query">Query</option>
                    <option value="differential">Differential</option>
                    <option value="confirmed">Confirmed</option>
                </select>

                <select name="severity[]" class="span2 hide">
	                <option value="">--</option>
                   <?php foreach ($severities as $s) { ?>
	                   <option value="<?= $s ?>"><?= ucwords($s) ?></option><?php } ?>
                </select>
                <input type="text" class="span3" name="d_comment[]" placeholder="Comment" style="margin-left: 2%;">
            </span>
					</div>

					<label class="hide">
						Diagnosis Note:
						<textarea name="diagnosisNote" cols="40" rows="2"></textarea>
					</label>
				</div>
				<!--/new diagnosis box-->
				<!--existing diagnoses area-->
				<div class="span5 overscrollLabDiv" style="height:300px;min-height:300px">
					<span class="pull-left fadedText">Existing Diagnoses</span>
					<a style="margin-bottom:5px" class="pull-right action" href="javascript:" title="Clear selected previous diagnoses" id="reset_diagnosis_for_encounter">Clear</a>
					<ul style="list-style-type: none;margin:0">
					<?php
					$page = 0;
					$pageSize = 9999;
					
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
					$type = null;//'confirmed';
					$active = 'true';
					$severity = null; //acute should show when none was specified?
					$data = (new PatientDiagnosisDAO())->one($_GET['pid'], $type, $active, $severity, $page, $pageSize);
					foreach($data->data as $diagnosis){//$diagnosis=new PatientDiagnosis();?>
						<li class="row-fluid" style="display: inline-block;
    background: #FEFEFE;
    border: 2px solid #FAFAFA;
    box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
    padding: 2px;
    background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
    -webkit-transition: all .2s ease;
    -moz-transition: all .2s ease;
    -o-transition: all .2s ease;
    transition: all .2s ease;
   ">
							<label class="span10" for="diag_<?=$diagnosis->id?>" title="Use this previous diagnosis for encounter" style="padding-left:10px"><?= strtoupper($diagnosis->diagnosisType)?> (<?=trim($diagnosis->code)?>): <?=$diagnosis->case?></label>
							<div class="span1"><input title="Use this previous diagnosis for encounter" type="checkbox" name="diagnosis_for_encounter[]" value="<?=$diagnosis->id?>" id="diag_<?=$diagnosis->id?>"> </div>
							<div class="span1"><a title="Resolve diagnoses" href="javascript:;" class="resolveConditionLink2 pull-right" data-pid="<?=$diagnosis->patient_ID?>" data-id="<?=$diagnosis->id?>"><i class="icon icon-remove-sign"></i></a></div>
						</li>
					<?php }?>
					</ul>
				</div>
				<!--/existing diagnoses area-->
			</div>
			
		</fieldset>
		<!--Lab-->
		<fieldset>
			<legend>Investigations</legend>
			<h4>Lab Requests</h4>
			<label class="output well well-small"></label>
			<label>Business Unit/Service Center <select name="service_centre_id" data-placeholder="Select a receiving lab center">
					<option></option>
					<?php
					$allLabCentres = (new ServiceCenterDAO())->all('Lab');
					foreach ($allLabCentres as $center) { ?>
						<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
					<?php } ?>
				</select> </label>
			<script>
				<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_lab_combos.php';?>
				var labCs = <?= (json_encode($labCombos, JSON_PARTIAL_OUTPUT_ON_ERROR))?>;
				$('.boxy-content #lab-combos').select2({
					placeholder: "Search and select lab combos",
					width: '100%',
					allowClear: true,
					data: {results: labCs, text: 'name'},

					formatResult: function (data) {
						return data.name;
					},
					formatSelection: function (data) {
						return data.name;
					}
				}).change(function (e) {
					if (e.added !== undefined) {
						select = $('.boxy-content #labs_to_request');
						var dataOld = select.select2('data');
						for (var i = 0; i < e.added.combos.length; i++) {
							dataOld.push(e.added.combos[i].lab);
						}
						select.select2("data", dataOld, true);
					}
				});
			</script>
			
			<label>Lab Combos <input type="hidden" id="lab-combos"></label>
			<label>Lab tests to request:</label>
			<label><input type="hidden" id="labs_to_request" name="lab-reqs" style="width:99% !important; max-width:99% !important;"></label>
			<label>Preferred Specimen(s) </label>
			<label><select multiple="multiple" name="specimen_ids[]">
					<?php foreach ($specimens as $s) {?>
					<option value="<?= $s->getId()?>"><?= $s->getName()?></option>
					<?php } ?>
				</select></label>
			<label>Lab Request Note
				<textarea name="lab_request_note" rows="2"></textarea>
			</label>

			<h4>Radiological Investigation</h4>
			<?php
			$scanCentres = (new ServiceCenterDAO())->all('Imaging');
			?>
			<label>Business Unit/Service Center <select name="scan_service_centre_id" data-placeholder="select service centre">
					<option></option>
					<?php foreach ($scanCentres as $k => $centre) { ?>
						<option value="<?= $centre->getId() ?>"><?= $centre->getName() ?></option>
					<?php } ?>
				</select></label>
			<label>Scans to Request:</label>
			<label>
				<input type="hidden" id="scan_request_ids" multiple="multiple" name="scan_request_ids" data-placeholder="select a scan">
			</label>
			<label>Request Note/Reason: <textarea name="request_note"></textarea></label>
		</fieldset>

		<!--Plan-->
		<fieldset>
			<legend>Plan</legend>
			<label>Plan:
				<textarea name="plan_note" placeholder="type here ...">
					<u>Treatment Plan:</u>
						<br><br><br><br>
				</textarea>
			</label>
			<div class="menu-head">Add Medication</div>
			<?php
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php');
			$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
			?>
			<div class="row-fluid">
				<label class="span6">Business Unit/Service Center <select id="pharmacy_id" name="pharmacy_id" data-placeholder="-- Select pharmacy --">
						<option value=""></option>
						<?php foreach ($pharmacies as $k => $pharm) { ?>
							<option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
						<?php } ?>
					</select></label>

				<label class="span6">Formulary <select id="formulary_id" data-placeholder="--select formulary--">
						<option></option>
						<?php foreach ($formulary as $form) { ?>
							<option value="<?= $form->getId() ?>"><?= $form->getName() ?></option>
						<?php } ?>
					</select> </label>
			</div>

			<div class="row-fluid">
				<label class="span6">Drug Generic Name<input type="hidden" name="filter-generics" id="generic"></label>

				<label class="span6">Drug Name <span id="drug-info" class="fadedText pull-right"></span>
					<input type="hidden" name="drug" id="drug" <?php $pp = new Pharmacy();
					if (!$pp::$canPrescribeBrand) { ?> disabled<?php } ?>>
				</label>
			</div>

			<div class="row-fluid">
				<label class="span2" title="Please Enter Numbers Only!">Frequency <!--<span class="" style="font-size: 90%; font-style: italic; color: #666">(number of times prescribed)</span>-->

					<input style="min-width: 10px" type="number" data-decimals="0" name="freqno" id="freqno" placeholder="eg. 3">
				</label>
				<label class="span2">
					Frequency Type
					<select name="freqtype" id="freqtype" data-placeholder="-- Select frequency type --">
						<option value=""></option>
						<?php $drugfrequencylist = MainConfig::$drugFrequencies;
						foreach ($drugfrequencylist as $f) { ?>
							<option value="<?= $f ?>"><?= ucfirst($f) ?></option><?php } ?>
					</select>
				</label>
				<label class="span2">Dose <input type="text" name="dose" id="dose" placeholder="Dose quantity"></label>
				<label class="span2">Duration
					<input type="number" name="duration" id="duration" data-decimals="0" value="" placeholder="(value in days) eg: 7">
				</label>
				<label class="span4">
					Note
					<input type="text" name="comment" placeholder="Regimen Line Instruction">
				</label>
			</div>
			<div class="row-fluid">
				<label class="span2"><input type="checkbox" name="refillable" id="refillable"> Refillable</label>
				<label class="span2 hide" id="more_refill"><input placeholder="Refills count" name="refill_count" id="refill_count" type="number" data-decimals="0"> </label>
			</div>

			<div class="">
				<button class="btn btn-mini" type="button" id="add-regimen"><i class="icon-plus-sign"></i></button>
				<button class="btn btn-mini" type="button" id="reset-regimen"><i class="icon-remove-sign"></i></button>
				<div id="added-regimen" style="display: inline-block; float: right"></div>
				<label data-name="Regimen" style="display:">Regimen Note
					<textarea name="regnote" id="regnote" cols="40" rows="2" style="width:100%"></textarea>
				</label>
			</div>

			<input id="prescription" name="prescription" type="hidden">
			<input id="prescription_plan" name="prescription_plan" type="hidden">

		</fieldset>

		<!-- CONSUMABLES -->
		<fieldset>
			<legend>Consumable</legend>
				<div>
					<label> Business Unit/Service Center
						<select id="item_center_id" name="item_center_id" data-placeholder="-- Select Service Center --">
							<option value=""></option>
							<?php
							require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
							$item_center = (new ServiceCenterDAO())->all('item');
							?>
							<?php foreach ($item_center as $center) { ?>
								<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
							<?php } ?>
						</select></label>
					<label>Item Group <input type="hidden" name="item_group" id="item_group"></label>
					<label>Item Generic Name <input type="hidden" name="generic_id" class="generic_id"></label>
					<label><span class="pull-right"><a class="btn btn-mini add_consumable"> <i class="icon-plus"></i> </a> </span> </label>
					<div class="row-fluid request_items">
						<label class="span6">Item <input class="item_id" type="text" name="item_id[]"></label>
						<label class="span5">Quantity <input type="number" data-decimals="0" class="qty" name="qty[]" placeholder="Add Type quantity"></label>
					</div>
					<label>
						Note
						<textarea name="item_request_note" placeholder="Request Note" id="note" cols="3"></textarea>
					</label>
				</div>
		</fieldset>


		<input id="SaveAll" type="submit" class="btn" value="<?php if ($signAndClose) { ?>Sign & Close<?php } else { ?>Save<?php } ?>" <?= !$this_user->hasRole($protect->doctor_role) ? 'disabled' : '' ?>/>
		<input type="hidden" id="pid" name="pid" value="<?= $_GET['pid'] ?>">
	</form>
	<script type="text/javascript">

		$(document).ready(function () {
			getItemGeneric();
			getItems();
			var $Form = $('#soapForm');
			$Form.formToWizard({
				submitButton: 'SaveAll',
				showProgress: true, //default value for showProgress is also true
				nextBtnName: 'Next',
				prevBtnName: 'Previous',
				showStepNo: true,
				postStepFn: function () {
					try {
						preparePrescription();
					} catch (exception) {
						//console.info("prescription not defined");
					}
				}
			});
			$('[name="plan_note"]').summernote(SUMMERNOTE_CONFIG);
			$('[name="subjective_note"]').summernote(SUMMERNOTE_CONFIG);
			setTimeout(function () {
				Boxy.get($(".close")).centerX();
				setSuperGen(superGens);
				$("#drug_id").hide();
				
			}, 500);
			$("#allergen_category").on('change', function () {
				if ($(this).val() === '1') {
					$("#drug_id").show();
					$(".allergen").hide();
				} else {
					$("#drug_id").hide();
					$(".allergen").show();
				}
			});
			setTimeout(function () {
				$('.boxy-content [name="states[]"]').select2();
				$('.boxy-content [name="severity[]"]').select2();
				$('.boxy-content [name="bodypart[]"]').select2();
			}, 5);
			$(".boxy-content input[name='cases[]']").select2({
				placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
				allowClear: true,
				minimumInputLength: 3,
				formatResult: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				}, formatSelection: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the diagnosis name or ICD-10/ICPC-2 code";
				},
				ajax: {
					url: '/api/get_diagnoses.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							type: $('[name="type_diagnosis"]:checked').val()
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			});

			$(".boxy-content input[name='medical_history[]']").select2({
				placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
				allowClear: true,
				minimumInputLength: 3,
				width: '100%',
				formatResult: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				}, formatSelection: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Enter the diagnosis name or ICD-10/ICPC-2 code";
				},
				ajax: {
					url: '/api/get_diagnoses.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							type: $('[name="type_diagnosis_"]:checked').val()
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			});

			var now = new Date().toISOString().split('T')[0];
			$(".boxy-content input[name='diagnosis_date[]']").datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now
					});
				}
			});

			$('.boxy-content #labs_to_request').select2({
				placeholder: "Search and select lab",
				minimumInputLength: 0,
				width: '100%',
				multiple: true,
				allowClear: true,
				ajax: {
					url: "/api/get_labs.php",
					dataType: 'json',
					data: function (term, page) {
						return {
							search: term
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				},
				formatResult: function (data) {
					return data.name + " (" + data.category.name + ")";
				},
				formatSelection: function (data) {
					return data.name + " (" + data.category.name + ")";
				}

			}).change(function (evt) {
				var pid = $('.boxy-content [name="pid"]').val();
				if (evt.added !== undefined) {
					showInsuranceNotice(pid, evt);
				}
				totalLabInvestigations = 0;
				$.each($(this).select2("data"), function () {
					totalLabInvestigations = parseFloat(this.basePrice) + totalLabInvestigations;
				});
				$("form label.output").html("Estimated Test cost: <?=$currency->getSymbolLeft()?>" + parseFloat(totalLabInvestigations+totalScanInvestigations).toFixed(2)+"<?=$currency->getSymbolRight()?>");
			});
			var totalScanInvestigations = 0;
			var totalLabInvestigations = 0;

			$("#scan_request_ids").select2({
				placeholder: "Search and select scan",
				minimumInputLength: 3,
				width: '100%',
				multiple: true,
				allowClear: true,
				ajax: {
					url: "/api/get_scans.php",
					dataType: 'json',
					data: function (term, page) {
						return {
							search: term
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				},
				formatResult: function (data) {
					return data.name + " (" + data.category.name + ")";
				},
				formatSelection: function (data) {
					return data.name + " (" + data.category.name + ")";
				}
			}).change(function (evt) {
				totalScanInvestigations = 0;
				var pid = $('.boxy-content [name="pid"]').val();
				if (evt.added !== undefined) {
					showInsuranceNotice(pid, evt);
				}
				//if ($('#scan_request_ids').select2("data")) {
				setTimeout(function () {
					_.each($('#scan_request_ids').select2("data"), function (obj) {
						totalScanInvestigations = parseFloat(obj.basePrice) + totalScanInvestigations;
					});
					$("label.output").html("Estimated Scan cost: <?=$currency->getSymbolLeft()?>" + parseFloat(totalLabInvestigations+totalScanInvestigations).toFixed(2)+"<?=$currency->getSymbolRight()?>");//.removeClass('alert-success').addClass('alert-success');
				}, 500);
				//}
			});

			$('.boxy-content #template_id').select2().change(function (data) {
				if (data.added !== undefined) {
					var content = $(data.added.element).data("text");
					$('textarea[name="exam_note"]').code(content).focus();
				} else {
					$('textarea[name="exam_note"]').code('').focus();
				}
			}).trigger('change');

			$('.boxy-content #hx_template_id').select2().change(function (data) {
				if (data.added !== undefined) {
					var content = $(data.added.element).data("text");
					$('textarea[name="hx_note"]').code(content).focus();
				} else {
					$('textarea[name="hx_note"]').code('').focus();
				}
			}).trigger('change');

			$('.boxy-content input[name="drugs_history[]"]').select2({
				width: '100%',
				allowClear: true,
				placeholder: "select drug generic",
				minimumInputLength: 3,
				/*data: {results: drugGens, text: 'name'},*/
				formatResult: function (source) {
					return source.name + " (" + source.form + ") " + source.weight;
				},
				formatSelection: function (source) {
					return source.name + " (" + source.form + ") " + source.weight;
				},
				ajax: {
					url: '/api/get_drug_generics.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							search: term, // search term
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			}).on("change", function () {
				var gen_id = $("#generic").val();
				setDrugs(_.filter(drugs, function (d) {
					return (_.includes(d.id, gen_id));
				}));
			});

			$('.boxy-content a.hx_template_link').click(function () {
				Boxy.load("/consulting/" + $(this).data("href"));
			});

			$('textarea[name="exam_note"]').summernote(SUMMERNOTE_CONFIG);
			$('textarea[name="hx_note"]').summernote(SUMMERNOTE_CONFIG);
			
			$('input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
				$(event.currentTarget).trigger('change');
			});
			
			$('.resolveConditionLink2').click(function(e){
				if(!e.handled){
					var $this = $(this);
					Boxy.ask('Are you sure to resolve this diagnoses?', ['Yes', 'No'], function(answer){
						if(answer==='Yes'){
							$(document).trigger('ajaxSend');
							$.post('/api/resolve_pre_condition.php', {pid: $($this).data('pid'), id: $($this).data('id')}, function(response){
								$(document).trigger('ajaxStop');
								if(response === true){
									$($this).parent().parent('li.row-fluid').remove();
								} else {
									$.notify2("Sorry, action failed", "error");
								}
							},'json');
						}
					});
					
					e.handled = true;
				}
			});
			$('.resolveAllergenLink').click(function(e){
				if(!e.handled){
					var $this = $(this);
					Boxy.ask('Are you sure to resolve this allergen?', ['Yes', 'No'], function(answer){
						if(answer==='Yes'){
							$(document).trigger('ajaxSend');
							$.post('/api/resolve_allergen.php', {pid: $($this).data('pid'), id: $($this).data('id')}, function(response){
								$(document).trigger('ajaxStop');
								if(response === true){
									$($this).parent().parent('li.row-fluid').remove();
								} else {
									$.notify2("Sorry, action failed", "error");
								}
							},'json');
						}
					});
					
					e.handled = true;
				}
			});
			
			$('#reset_diagnosis_for_encounter').click(function (e) {
				$('input:checkbox:checked[name="diagnosis_for_encounter[]"]').prop('checked', false).iCheck('update');
			});
			
			$('#refillable').live('change',function(e){
				if(!e.handled) {
					if (this.checked) {
						$('#more_refill').removeClass('hide');
					} else {
						$('#more_refill').addClass('hide');
						$("#refill_count").val('');
					}
					e.handled = true;
				}
			});
        $('#item_center_id').on('change', function (e) {
            if (!e.handled) {
                var id = $(this).val();
                getItemGroup(id);
            }
        });
        $('#item_group').on('change', function (e) {
            if (!e.handled) {
                var id = $(this).val();
                if(id !== ''){
                    getItemGeneric(id);
                }else{
                    getItemGeneric();
                }
            }
        });
        $('.generic_id').on('change', function (e) {
            if(!e.handled){
                var id = $(this).val();
                if(id !== ""){
                    getItems(id);
                }else{
                    getItems();
                }

            }
        });

        }).on('click', '.add_consumable', function (e) {
            $('.generic_id').select2('data', null);
            $('.item_id').select2('data', null);
            if (!e.handled) {
                var tmpholder = '<div class="row-fluid request_items"><label class="span6">Item<input class="item_id" type="text" name="item_id[]"  required></label><label class="span5">Quantity<input type="number" class="qty" data-decimals="0" name="qty[]" required placeholder="Add Type quantity" required></label><label class="span1" style="margin-top: 25px;"><a class="btn btn-mini remove_item">&minus;</a> </label></div>'
                $('.request_items:last').after(tmpholder);
                $(document).trigger('ajaxStop');
                e.handled = true;

            }
        }).on('click', '.remove_item', function (e) {
            if (!e.handled) {
                if ($('.request_items').length > 1) {
                    $(this).parents('.request_items').remove();
                }
                e.handled = true;
            }
        });

        function getItems(g) {
            $.ajax({
                url: '/api/get_item.php',
                type: 'POST',
                dataType: 'json',
                data:{gid: g},
                success: function (result) {
                    setItems(result);
                },
            });
        }

        function setItems(data) {
            $('.item_id').select2({
                width: '100%',
                allowClear: true,
                placeholder: "select item",
                data: function () {
                    return {results: data, text: 'name'};
                },
                formatResult: function (result) {
                    return result.name;
                },
                formatSelection: function (result) {
                    return result.name;
                }
            });
        }


        function getItemGroup(s) {
            $.ajax({
                url: '/api/get_item_group.php',
                type: 'POST',
                dataType: 'json',
                data:{c_id: s},
                success: function (result) {
                    setItemsGroups(result);
                },

            });
        }

        function setItemsGroups(data) {
            $('input[name="item_group"]').select2({
                width: '100%',
                allowClear: true,
                placeholder: "select item group",
                data: function () {
                    return {results: data, text: 'name'};
                },
                formatResult: function (source) {
                    return source.name ;
                },
                formatSelection: function (source) {
                    return source.name;
                }
            });
        }

        function getItemGeneric(g) {
            $.ajax({
                url: '/api/get_item_generic.php',
                type: 'POST',
                dataType: 'json',
                data: {g_id: g},
                success: function (result) {
                    setItemGenerics(result);
                }
            });
        }

        function setItemGenerics(data) {
            $('.generic_id').select2({
                width: '100%',
                allowClear: true,
                placeholder: "select item generic",
                data: function () {
                    return {results: data, text: 'name'};
                },
                formatResult: function (source) {
                    return source.name;
                },
                formatSelection: function (source) {
                    return source.name;
                }
            });
        }



        function refreshTemplates() {
			$.ajax({
				url: "/api/get_exam_templates.php",
				dataType: 'json',
				complete: function (s) {
					var data = s.responseJSON;
					var str = '<option></option>';
					for (var i = 0; i < data.length; i++) {
						str += '<option value="' + data[i].id + '" data-text="' + data[i].content + '">' + data[i].category.name + ': ' + data[i].title + '</option>';
					}
					$('#template_id').html(str);
				}
			});
		}
		
		function refreshSHXTemplates() {
			$.ajax({
				url: "/api/get_hx_templates.php",
				dataType: 'json',
				complete: function (s) {
					var data = s.responseJSON;
					var str = '<option></option>';
					for (var i = 0; i < data.length; i++) {
						str += '<option value="' + data[i].id + '" data-text="' + data[i].content + '">' + data[i].name + '</option>';
					}
					$('#hx_template_id').html(str);
				}
			});
		}

		function add_diagnosis_data() {
			//$("div.diagnosis:last").after('<div class="diagnosis row-fluid"><span><input type="hidden" name="cases[]" class="span5"><select style="display: inline-block;" name="states[]" class="span2"><option value="query">Query</option><option value="differential">Differential</option><option value="confirmed">Confirmed</option></select><select name="severity[]" class="span2"><?php foreach ($severities as $s) {?><option value="<?=$s?>"><?= ucwords($s)?></option><?php }?></select><select name="bodypart[]" class="span2" placeholder="Select the related body part"><option value=""></option><?php foreach ($bodyparts as $bp) { ?><option value="<?= $bp->getId() ?>"><?= $bp->getName() ?></option><?php } ?></select> <input type="text" class="span3" name="d_comment[]" placeholder="Comment" style="margin-left: 2%;"></span></div>');
			$("div.diagnosis:last").after('<div class="diagnosis row-fluid"><span><input type="hidden" name="cases[]" class="span7"><select style="display: inline-block;" name="states[]" class="span2"><option value="query">Query</option><option value="differential">Differential</option><option value="confirmed">Confirmed</option></select><select name="severity[]" class="span2 hide"><option value="">--</option><?php foreach ($severities as $s) {?><option value="<?=$s?>"><?= ucwords($s)?></option><?php }?></select><input type="text" class="span3" name="d_comment[]" placeholder="Comment" style="margin-left: 2%;"></span></div>');
			$('.boxy-content div.diagnosis:last [name="cases[]"]').select2({
				placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
				allowClear: true,
				minimumInputLength: 3,
				formatResult: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				}, formatSelection: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the diagnosis name or ICD-10/ICPC-2 code";
				},
				ajax: {
					url: '/api/get_diagnoses.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							type: $('[name="type_diagnosis"]:checked').val()
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			});
			$('.boxy-content div.diagnosis:last [name="states[]"]').select2();
			$('.boxy-content div.diagnosis:last [name="severity[]"]').select2();
			//$('.boxy-content div.diagnosis:last [name="bodypart[]"]').select2();
		}

		function remove_diagnosis_data() {
			if ($("div.diagnosis").length > 1) {
				$("div.diagnosis:last").remove();
			}
		}

		function add_drug_history() {
			$('div.drug_history_data:last').after('<div class="drug_history_data row-fluid"><label class=" span7"><input type="hidden" name="drugs_history[]"></label><label class="span5"><input type="text" name="drugs_history_comment[]" placeholder="Drug Comment"> </label></div>');
			$('div.drug_history_data:last input[name="drugs_history[]"]').select2({
				width: '100%',
				allowClear: true,
				placeholder: "select drug generic ...",
				minimumInputLength: 3,
				data: {results: drugGens, text: 'name'},
				formatResult: function (source) {
					return source.name + " (" + source.form + ") " + source.weight;
				},
				formatSelection: function (source) {
					return source.name + " (" + source.form + ") " + source.weight;
				},
				ajax: {
					url: '/api/get_drug_generics.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							search: term, // search term
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			}).on("change", function () {
				filterDrugs();
			});
		}

		function remove_drug_history() {
			if ($('div.drug_history_data').length > 1) {
				$('div.drug_history_data:last').remove();
			}
		}

		function add_medical_history() {
			$('div.medical_history_data:last').after('<div class="medical_history_data row-fluid"><label class="span9"><input type="hidden" name="medical_history[]"></label><label class="span3"><input type="text" name="diagnosis_date[]" readonly="" placeholder="Date Diagnosed"></label></div>');
			$(".boxy-content div.medical_history_data:last input[name='medical_history[]']").select2({
				placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
				allowClear: true,
				minimumInputLength: 3,
				width: '100%',
				formatResult: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				}, formatSelection: function (data) {
					return data.name + " (" + data.type + ": " + data.code + ")";
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the diagnosis name or ICD-10/ICPC-2 code";
				},
				ajax: {
					url: '/api/get_diagnoses.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							type: $('[name="type_diagnosis_"]:checked').val()
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			});
			var now = new Date().toISOString().split('T')[0];
			$(".boxy-content div.medical_history_data:last input[name='diagnosis_date[]']").datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now
					});
				}
			});
		}

		function remove_medical_history() {
			if ($('div.medical_history_data').length > 1) {
				$('div.medical_history_data:last').remove();
			}
		}
		
		function add_allergies() {
			var allergensObj = $('#allergies_full').val() !== '' ? JSON.parse($('#allergies_full').val()) : [];
			allergensObj.push({
				allergen_category:$('#allergen_category').val(),
				super_generic: $('#super_generic').val(),
				allergen: $('#allergen').val(),
				reaction: $('#reaction').val(),
				allergen_severity: $('#allergen_severity').val()
			});
			$('#allergies_full').val(JSON.stringify(allergensObj));
			clear_allergen_fields();
		}
		
		function reset_allergies(){
			$('#allergies_full').val('');
			clear_allergen_fields();
		}
		
		function clear_allergen_fields() {
			$('#allergen_category').select2('val','');
			$('#super_generic').select2('val','');
			$('#allergen').val('');
			$('#reaction').val('');
			$('#allergen_severity').select2('val','mild');
			//$('#mylist').val($('#mylist option:first-child').val()).trigger('change');
		}
	</script>
	<script type="text/javascript">
		var drugData =<?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		prescription = {
			"pid": "",
			"pharmacy_id": "",
			"inpatient":<?= isset($_GET['aid']) ? $_GET['aid'] : 'false'?>,
			"note": "",
			"regimens": []
		};
		
		var inPatientContext = false;
		function preparePrescription() {
			var strPrescriptions = [];
			for (var i = 0; i < prescription.regimens.length; i++) {
				var drug = (prescription.regimens[i].drug === null || prescription.regimens[i].drug === "" || prescription.regimens[i].drug.id === undefined) ? null : prescription.regimens[i].drug;
				var gen = (prescription.regimens[i].generic === null || prescription.regimens[i].generic === "") ? null : prescription.regimens[i].generic;
				strPrescriptions.push(prescription.regimens[i].dose + ' ' + ((drug !== null) ? prescription.regimens[i].drug.name : gen.name) + ' ' + prescription.regimens[i].freqno + ' x ' + prescription.regimens[i].freqtype.id + ' for ' + prescription.regimens[i].duration + ' day(s)'+ ' '+prescription.regimens[i].comment);
			}
			//prescription.regimens[i] = reg;

			$('#prescription').val(JSON.stringify(prescription));
			$('#prescription_plan').val(implode(' || ', strPrescriptions));
			prescription.note = $("#regnote").val() || "";
		}

		$(document).ready(function () {
			$("#added-regimen button").live('click', function () {
				prescription.regimens.splice($(this).data("id"), 1);
				$(this).remove();
				if( $("#added-regimen button").length === 0){
					prescription.pharmacy_id = "";
					prescription.pid = "";
					prescription.note = "";
				}
				preparePrescription();
			});
			$("#reset-regimen").click(function () {
				resetRegimen();
			});
			$("#add-regimen").click(function () {
				reg = validateRegimen();

				if (reg !== null) {
					var i = prescription.regimens.length;
					prescription.regimens[i] = reg;
					var drug = (prescription.regimens[i].drug === null || prescription.regimens[i].drug === "" || prescription.regimens[i].drug.id === undefined) ? null : prescription.regimens[i].drug;
					var gen = (prescription.regimens[i].generic === null || prescription.regimens[i].generic === "") ? null : prescription.regimens[i].generic;
					$("#added-regimen").append('<button class="btn btn-mini" type="button" data-id="' + i + '"><i class="icon-remove-sign"></i> ' + prescription.regimens[i].dose + ' ' + ((drug !== null) ? prescription.regimens[i].drug.name : gen.name) + ' ' + prescription.regimens[i].freqno + ' x ' + prescription.regimens[i].freqtype.id + ' for ' + prescription.regimens[i].duration + ' day(s)</button>');
					resetRegimen();
					//$("label[data-name='Regimen']").show();
					save();
				}
			});

			$("#generic").select2({
				width: '100%',
				allowClear: true,
				placeholder: "select drug generic",
				data: {results: drugGens, text: 'name'},
				formatResult: function (source) {
					return source.name + " (" + source.form + ") " + source.weight; // This loads Drug generic name
				},
				formatSelection: function (source) {
					return source.name + " (" + source.form + ") " + source.weight;
				}
			}).on("change", function (e) {
				if (e.added) {
					if( _.includes(_allergicGenerics, e.added.id)){
						$.notify2("Patient is allergic to "+ e.added.name, "warn");
					}
					setDrugs(_.filter(drugs, function (obj) {
						return obj.generic.id === e.added.id;
					}));
				} else {
					setTimeout(function(){$('#formulary_id').trigger('change');}, 150);
				}
			});
			refreshDrug();
		});
		
		function filterDrugs() {
			drugData = [];
			$("#drug").select2("val", "");
			$("#drug-info").html("");
			for (var i = 0; i < drugs.length; i++) {
				if ((drugs[i].generic.id === $("#generic").val()) || $("#generic").val() === "") {
					drugData[drugData.length] = drugs[i];
				}
			}
		}

		function save() {
			/*if ($("#regnote").val() === ""){
			 Boxy.alert("Please enter a note");
			 return;
			 } else */
			if (prescription.regimens.length === 0 && prescription.pharmacy_id !== "" && prescription.pid !== "") {
				Boxy.alert("Sorry you need to add one or more regimen data");
				return null;
			}
			prescription.note = $("#regnote").val() || "";

			if ($("#pid").select2("data") === null || $("#pid").select2("data").id === "") {
				Boxy.alert("Please select a patient");
				return null;
			} else {
				prescription.inpatient = false;
				prescription.pid = $("#pid").val();
			}
			if ($("#pharmacy_id").val() === "" && prescription.pid !== "" && prescription.regimens.length > 0) {
				Boxy.alert("Please select a fulfilling pharmacy");
				return null;
			} else {
				prescription.pharmacy_id = $("#pharmacy_id").val();
			}

			preparePrescription();
		}

		function validateRegimen() {
			regimen = {
				"drug": "",
				"dose": "",
				"freqno": "",
				"freqtype": "",
				"duration": "",
				"refillable": false,
				"refill_count": '',
				"generic": "",
				"comment": "",
				"body_part": ""
			};
			if ($("#drug").select2("data") === null && $("#generic").select2("data") === null) {
				Boxy.alert("Please select a drug name or drug generic name", function () {
					$("#drug").select2("open");
				});
				return null;
			} else {
				if ($("#drug").select2("data") !== null) {
					regimen.drug = $("#drug").select2("data");
				}
				if ($("#generic").select2("data") !== null) {
					regimen.generic = $("#generic").select2("data");
				}
			}

			if ($("#dose").val() === "0" || $("#dose").val() === "") {
				regimen.dose = '-';
				//Boxy.alert("Please enter the drug dosage", function () {
				//	$("#dose").focus();
				//});
				//return null;
			} else {
				regimen.dose = $("#dose").val();
			}

			if ($("#freqno").val() === "") {
				regimen.freqno = '-';
				//Boxy.alert("Please enter the frequency", function () {
				//	$("#freqno").focus();
				//});
				//return null;
			} else {
				regimen.freqno = $("#freqno").val();
			}
			regimen.refillable = $("#refillable").is(":checked");
			regimen.refill_count = $("#refill_count").val();
			regimen.comment = $('input[name="comment"]').val();

			if ($("#freqtype").select2("data") === null) {
				regimen.freqtype = {id: ' -- ', text: ' -- '};
				//Boxy.alert("Please select a frequency type", function () {
				//	$("#freqtype").select2("open");
				//});
				//return null;
			} else {
				regimen.freqtype = {id: $("#freqtype").select2("data").id, text: $("#freqtype").select2("data").text};
			}

			if ($("#duration").val() === "0" || $("#duration").val() === "") {
				regimen.duration = ' -- ';
				//Boxy.alert("Please enter drug duration", function () {
				//	$("#duration").focus();
				//});
				//return null;
			} else {
				regimen.duration = $("#duration").val();
			}
			regimen.body_part = $('select[name="bodypart"]').val() || null;
			if(regimen.refillable && regimen.refill_count <= 0){
				Boxy.alert("Invalid refill option");
				return null;
			}
			return regimen;
		}

		function resetRegimen() {
			$("#generic").select2("val", "").trigger("change");
			$("#drug").val("");
			$("#dose").val("");
			$("#freqno").val("");
			$("#freqtype").select2("val", "");
			$("#refillable").prop("checked", false).trigger('change').iCheck('update');
			$("#more_refill").addClass('hide');
			$("#refill_count").val('');
			$("#duration").val("");
			$("#drug-info").html("");
			$("input[name='comment']").val("");
			$('select[name="bodypart"]').select2('val', '');
		}

		function refreshDrug() {
			$("#drug").select2({
				width: '100%',
				allowClear: true,
				placeholder: "---select drug---",
				data: function () {
					return {results: drugData, text: 'name'};
				},
				formatResult: function (source) {
					return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
				},
				formatSelection: function (source) {
					return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
				}
			}).on("change", function (e) {
				var drug = $("#drug").select2("data");
				if (drug !== null) {
					$("#drug-info").html("<b>Stock level:</b> " + drug.stockQuantity + "; <b>Base Price: &#8358;</b>" + drug.basePrice);
					if (parseInt(drug.stockQuantity) < 1) {

						Boxy.ask(drug.name + " is unavailable in the store<br>Click <strong>Change</strong> to change the drug or <strong>Ignore</strong> to ignore this warning", ['Change', 'Ignore'], function (answer) {
							if (answer === "Change") {
								$("#drug").select2("val", "");
								$("#drug").select2("open");
								$("#drug-info").html("");
							}
						}, {title: "Low Stock Warning"});
					}
					showInsuranceNotice('<?= $_GET['pid'] ?>', e);
					if( _.includes(_allergicGenerics, drug.generic.id)){
						$.notify2("Patient is allergic to "+ drug.name, "warn");
					}
				} else {
					$("#drug-info").html("");
				}
			});
		}
	</script>
</section>