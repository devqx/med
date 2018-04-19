<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/16/16
 * Time: 10:31 AM
 */
class IVFEnrollment implements JsonSerializable
{
	private $id;
	private $active;
	private $fileNo;
	private $patient;
	private $husband;
	private $dateEnrolled;
	private $enrolledBy;
	private $indication;
	private $hormone;
	private $husbandHormone;
	private $sfa;
	private $serology;
	private $husbandSerology;
	private $andrologyDetails;
	private $stimulation;
	private $package;
	private $closedDate;
	private $closedBy;

	/**
	 * IVFEnrollment constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return IVFEnrollment
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 * @return IVFEnrollment
	 */
	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFileNo()
	{
		return $this->fileNo;
	}

	/**
	 * @param mixed $fileNo
	 * @return IVFEnrollment
	 */
	public function setFileNo($fileNo)
	{
		$this->fileNo = $fileNo;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPatient()
	{
		return $this->patient;
	}

	/**
	 * @param mixed $patient
	 * @return IVFEnrollment
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHusband()
	{
		return $this->husband;
	}

	/**
	 * @param mixed $husband
	 * @return IVFEnrollment
	 */
	public function setHusband($husband)
	{
		$this->husband = $husband;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateEnrolled()
	{
		return $this->dateEnrolled;
	}

	/**
	 * @param mixed $dateEnrolled
	 * @return IVFEnrollment
	 */
	public function setDateEnrolled($dateEnrolled)
	{
		$this->dateEnrolled = $dateEnrolled;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolledBy()
	{
		return $this->enrolledBy;
	}

	/**
	 * @param mixed $enrolledBy
	 * @return IVFEnrollment
	 */
	public function setEnrolledBy($enrolledBy)
	{
		$this->enrolledBy = $enrolledBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIndication()
	{
		return $this->indication;
	}

	/**
	 * @param mixed $indication
	 * @return IVFEnrollment
	 */
	public function setIndication($indication)
	{
		$this->indication = $indication;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHormone()
	{
		return $this->hormone;
	}

	/**
	 * @param mixed $hormone
	 * @return IVFEnrollment
	 */
	public function setHormone($hormone)
	{
		$this->hormone = $hormone;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHusbandHormone()
	{
		return $this->husbandHormone;
	}

	/**
	 * @param mixed $husbandHormone
	 * @return IVFEnrollment
	 */
	public function setHusbandHormone($husbandHormone)
	{
		$this->husbandHormone = $husbandHormone;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAndrologyDetails()
	{
		return $this->andrologyDetails;
	}

	/**
	 * @param mixed $andrologyDetails
	 * @return IVFEnrollment
	 */
	public function setAndrologyDetails($andrologyDetails)
	{
		$this->andrologyDetails = $andrologyDetails;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getSfa()
	{
		return $this->sfa;
	}

	/**
	 * @param mixed $sfa
	 * @return IVFEnrollment
	 */
	public function setSfa($sfa)
	{
		$this->sfa = $sfa;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSerology()
	{
		return $this->serology;
	}

	/**
	 * @param mixed $serology
	 * @return IVFEnrollment
	 */
	public function setSerology($serology)
	{
		$this->serology = $serology;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHusbandSerology()
	{
		return $this->husbandSerology;
	}

	/**
	 * @param mixed $husbandSerology
	 * @return IVFEnrollment
	 */
	public function setHusbandSerology($husbandSerology)
	{
		$this->husbandSerology = $husbandSerology;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getStimulation()
	{
		return $this->stimulation;
	}

	/**
	 * @param mixed $stimulation
	 * @return IVFEnrollment
	 */
	public function setStimulation($stimulation)
	{
		$this->stimulation = $stimulation;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @param mixed $package
	 * @return IVFEnrollment
	 */
	public function setPackage($package)
	{
		$this->package = $package;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClosedDate()
	{
		return $this->closedDate;
	}

	/**
	 * @param mixed $closedDate
	 * @return IVFEnrollment
	 */
	public function setClosedDate($closedDate)
	{
		$this->closedDate = $closedDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClosedBy()
	{
		return $this->closedBy;
	}

	/**
	 * @param mixed $closedBy
	 * @return IVFEnrollment
	 */
	public function setClosedBy($closedBy)
	{
		$this->closedBy = $closedBy;
		return $this;
	}


	function add($pdo=null) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$patient = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$husband = $this->getHusband() ? $this->getHusband()->getId() : 'NULL';
			$dateEnrolled = $this->getDateEnrolled() ? quote_esc_str($this->getDateEnrolled()): 'NOW()';
			$enrolledBy = $this->getEnrolledBy() ? $this->getEnrolledBy()->getId() : 'null';
			$indication = !is_blank($this->getIndication()) ? quote_esc_str($this->getIndication()) : 'NULL';
			$hormone_fsh = !is_blank($this->getHormone()['fsh']) ? quote_esc_str($this->getHormone()['fsh']) : 'NULL';
			$hormone_lh = !is_blank($this->getHormone()['lh']) ? quote_esc_str($this->getHormone()['lh']) : 'NULL';
			$hormone_prol = !is_blank($this->getHormone()['prol']) ? quote_esc_str($this->getHormone()['prol']) : 'NULL';
			$hormone_amh = !is_blank($this->getHormone()['amh']) ? quote_esc_str($this->getHormone()['amh']) : 'NULL';
			$husband_hormone_fsh = !is_blank($this->getHusbandHormone()['fsh']) ? quote_esc_str($this->getHusbandHormone()['fsh']) : 'NULL';
			$husband_hormone_lh = !is_blank($this->getHusbandHormone()['lh']) ? quote_esc_str($this->getHusbandHormone()['lh']) : 'NULL';
			$husband_hormone_prol = !is_blank($this->getHusbandHormone()['prol']) ? quote_esc_str($this->getHusbandHormone()['prol']) : 'NULL';
			$husband_hormone_testos = !is_blank($this->getHusbandHormone()['testosterone']) ? quote_esc_str($this->getHusbandHormone()['testosterone']) : 'NULL';
			$sfa_count = !is_blank($this->getSfa()['count']) ? quote_esc_str($this->getSfa()['count']): 'null';
			$sfa_motility = !is_blank($this->getSfa()['motility']) ? quote_esc_str($this->getSfa()['motility']): 'null';
			$sfa_morphology = !is_blank($this->getSfa()['morphology']) ? quote_esc_str($this->getSfa()['morphology']): 'null';
			$serology_hiv = !is_blank($this->getSerology()['hiv']) ? quote_esc_str($this->getSerology()['hiv']) : 'NULL';
			$serology_hep_b = !is_blank($this->getSerology()['hep_b']) ? quote_esc_str($this->getSerology()['hep_b']) : 'NULL';
			$serology_hep_c = !is_blank($this->getSerology()['hep_c']) ? quote_esc_str($this->getSerology()['hep_c']) : 'NULL';
			$serology_vdrl = !is_blank($this->getSerology()['vdrl']) ? quote_esc_str($this->getSerology()['vdrl']) : 'NULL';
			$serology_chlamydia = !is_blank($this->getSerology()['chlamydia']) ? quote_esc_str($this->getSerology()['chlamydia']) : 'NULL';
			$husband_serology_hiv = !is_blank($this->getHusbandSerology()['hiv']) ? quote_esc_str($this->getHusbandSerology()['hiv']) : 'NULL';
			$husband_serology_hep_b = !is_blank($this->getHusbandSerology()['hep_b']) ? quote_esc_str($this->getHusbandSerology()['hep_b']) : 'NULL';
			$husband_serology_hep_c = !is_blank($this->getHusbandSerology()['hep_c']) ? quote_esc_str($this->getHusbandSerology()['hep_c']) : 'NULL';
			$husband_serology_vdrl = !is_blank($this->getHusbandSerology()['vdrl']) ? quote_esc_str($this->getHusbandSerology()['vdrl']) : 'NULL';
			$husband_serology_rbs = !is_blank($this->getHusbandSerology()['rbs']) ? quote_esc_str($this->getHusbandSerology()['rbs']) : 'NULL';
			$husband_serology_fbs = !is_blank($this->getHusbandSerology()['fbs']) ? quote_esc_str($this->getHusbandSerology()['fbs']) : 'NULL';
			$andrologyDetails = !is_blank($this->getAndrologyDetails()) ? quote_esc_str($this->getAndrologyDetails()) : 'NULL';
			//$blood_group = !is_blank($this->getSerology()['blood_group']) ? quote_esc_str($this->getSerology()['blood_group']) : 'NULL';
			//$genotype = !is_blank($this->getSerology()['genotype']) ? quote_esc_str($this->getSerology()['genotype']) : 'NULL';
			$stimulation_cycle = !is_blank($this->getStimulation()['cycle']) ? quote_esc_str($this->getStimulation()['cycle']) : 'NULL';
			$stimulation_lmp_date = !is_blank($this->getStimulation()['lmp_date']) ? quote_esc_str($this->getStimulation()['lmp_date']) : 'NULL';
			$stimulation_method = !is_blank($this->getStimulation()['method']) ? quote_esc_str($this->getStimulation()['method']) : 'NULL';
			//$stimulation_suprefact = !is_blank($this->getStimulation()['goserelin']) ? quote_esc_str($this->getStimulation()['goserelin']) : 'NULL';
			//$stimulation_zoladex = !is_blank($this->getStimulation()['buserelin']) ? quote_esc_str($this->getStimulation()['buserelin']) : 'NULL';
			//$stimulation_fsh = !is_blank($this->getStimulation()['fsh']) ? quote_esc_str($this->getStimulation()['fsh']) : 'NULL';
			//$stimulation_hmg = !is_blank($this->getStimulation()['hmg']) ? quote_esc_str($this->getStimulation()['hmg']) : 'NULL';
			$package_id = $this->getPackage() ? $this->getPackage()->getId() : 'null';

			$closedBy = $this->getClosedBy() ? $this->getClosedBy()->getId() : 'NULL';
			$closedOn = $this->getClosedDate() ? quote_esc_str($this->getClosedDate()) : 'NULL';

			$sql = "INSERT INTO enrollments_ivf (patient_id, husband_id, date_enrolled, enrolled_by_id, indication, hormone_fsh, hormone_lh, hormone_prol, hormone_amh, husband_hormone_fsh, husband_hormone_lh, husband_hormone_prol, husband_hormone_testosterone, sfa_count, sfa_motility, sfa_morphology, serology_hiv, serology_hep_b, serology_hep_c, serology_vdrl, serology_chlamydia, husband_serology_hiv, husband_serology_hep_b, husband_serology_hep_c, husband_serology_vdrl, husband_serology_rbs, husband_serology_fbs, andrology_details, stimulation_cycle, stimulation_lmp_date, stimulation_method, closed_on, closed_by, package_id) VALUES ($patient, $husband, $dateEnrolled, $enrolledBy, $indication, $hormone_fsh, $hormone_lh, $hormone_prol, $hormone_amh, $husband_hormone_fsh, $husband_hormone_lh, $husband_hormone_prol, $husband_hormone_testos, $sfa_count, $sfa_motility, $sfa_morphology, $serology_hiv, $serology_hep_b, $serology_hep_c, $serology_vdrl, $serology_chlamydia,$husband_serology_hiv, $husband_serology_hep_b, $husband_serology_hep_c, $husband_serology_vdrl, $husband_serology_rbs, $husband_serology_fbs, $andrologyDetails, $stimulation_cycle, $stimulation_lmp_date, $stimulation_method, $closedBy, $closedOn, $package_id)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
function update($pdo=null) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$patient = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$dateEnrolled = $this->getDateEnrolled() ? quote_esc_str($this->getDateEnrolled()): 'NOW()';
			$enrolledBy = $this->getEnrolledBy() ? $this->getEnrolledBy()->getId() : 'null';
			$indication = !is_blank($this->getIndication()) ? quote_esc_str($this->getIndication()) : 'NULL';
			$hormone_fsh = !is_blank($this->getHormone()['fsh']) ? quote_esc_str($this->getHormone()['fsh']) : 'NULL';
			$hormone_lh = !is_blank($this->getHormone()['lh']) ? quote_esc_str($this->getHormone()['lh']) : 'NULL';
			$hormone_prol = !is_blank($this->getHormone()['prol']) ? quote_esc_str($this->getHormone()['prol']) : 'NULL';
			$hormone_amh = !is_blank($this->getHormone()['amh']) ? quote_esc_str($this->getHormone()['amh']) : 'NULL';
			$sfa_count = !is_blank($this->getSfa()['count']) ? quote_esc_str($this->getSfa()['count']): 'null';
			$sfa_motility = !is_blank($this->getSfa()['motility']) ? quote_esc_str($this->getSfa()['motility']): 'null';
			$sfa_morphology = !is_blank($this->getSfa()['morphology']) ? quote_esc_str($this->getSfa()['morphology']): 'null';
			$serology_hiv = !is_blank($this->getSerology()['hiv']) ? quote_esc_str($this->getSerology()['hiv']) : 'NULL';
			$serology_hep_b = !is_blank($this->getSerology()['hepb']) ? quote_esc_str($this->getSerology()['hepb']) : 'NULL';
			$serology_hep_c = !is_blank($this->getSerology()['hepc']) ? quote_esc_str($this->getSerology()['hepc']) : 'NULL';
			$serology_vdrl = !is_blank($this->getSerology()['vdrl']) ? quote_esc_str($this->getSerology()['vdrl']) : 'NULL';
			$serology_chlamydia = !is_blank($this->getSerology()['chlamydia']) ? quote_esc_str($this->getSerology()['chlamydia']) : 'NULL';
			$stimulation_cycle = !is_blank($this->getStimulation()['cycle']) ? quote_esc_str($this->getStimulation()['cycle']) : 'NULL';
			$stimulation_lmp_date = !is_blank($this->getStimulation()['lmp_date']) ? quote_esc_str($this->getStimulation()['lmp_date']) : 'NULL';
			$stimulation_method = !is_blank($this->getStimulation()['method']) ? quote_esc_str($this->getStimulation()['method']) : 'NULL';
			$stimulation_suprefact = !is_blank($this->getStimulation()['buserelin']) ? quote_esc_str($this->getStimulation()['buserelin']) : 'NULL';
			$stimulation_zoladex = !is_blank($this->getStimulation()['goserelin']) ? quote_esc_str($this->getStimulation()['goserelin']) : 'NULL';
			$stimulation_fsh = !is_blank($this->getStimulation()['fsh']) ? quote_esc_str($this->getStimulation()['fsh']) : 'NULL';
			$stimulation_hmg = !is_blank($this->getStimulation()['hmg']) ? quote_esc_str($this->getStimulation()['hmg']) : 'NULL';
			$package_id = $this->getPackage() ? $this->getPackage()->getId() : 'null';

			$closedBy = $this->getClosedBy() ? $this->getClosedBy()->getId() : 'NULL';

			$closedOn = $this->getClosedDate() ? quote_esc_str($this->getClosedDate()) : 'NULL';

			$sql = "UPDATE enrollments_ivf SET patient_id=$patient, date_enrolled=$dateEnrolled, indication,=$indication, hormone_fsh=$hormone_fsh, hormone_lh=$hormone_lh, hormone_prol=$hormone_prol, hormone_amh=$hormone_amh, sfa_count=$sfa_count, sfa_motility=$sfa_motility, sfa_morphology=$sfa_morphology, serology_hiv=$serology_hiv, serology_hep_b=$serology_hep_b, serology_hep_c=$serology_hep_c, serology_vdrl=$serology_vdrl, serology_chlamydia=$serology_chlamydia, stimulation_cycle=$stimulation_cycle, stimulation_lmp_date=$stimulation_lmp_date, stimulation_method=$stimulation_method, stimulation_suprefact=$stimulation_suprefact, stimulation_zoladex=$stimulation_zoladex, stimulation_fsh=$stimulation_fsh, stimulation_hmg=$stimulation_hmg, package_id=$package_id, enrolled_by_id=$enrolledBy, closed_by=$closedBy, closed_on=$closedOn WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

}