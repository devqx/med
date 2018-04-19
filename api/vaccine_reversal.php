<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/3/16
 * Time: 11:19 AM
 *
 */
if (isset($_POST)) {
	try {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.vaccines.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/BillSource.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		$pdo = (new MyDBConnector())->getPDO();
		$pdo->beginTransaction();

		$pat = (new PatientDemographDAO())->getPatient($_POST['patient'], true, $pdo);
		foreach ($_POST['vaccine'] as $v) {
			$vaccine = (new PatientVaccineDAO())->getPatientVaccine($v, TRUE, $pdo)->getVaccine();
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($vaccine->getCode(), $_POST['patient'], TRUE, $pdo);
			$sql = "UPDATE patient_vaccine pv SET pv.`billed`=FALSE WHERE pv.`patient_id` = {$_POST['patient']} AND pv.`id` =$v";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$bil = new Bill();
				$bil->setPatient($pat);
				$bil->setDescription('Reversal: ' . $vaccine->getName());
				$bil->setItem($vaccine);
				$bil->setSource((new BillSourceDAO())->findSourceById(6, $pdo));
				$bil->setTransactionType('reversal');
				$bil->setAmount(0-$price);
				$bil->setDiscounted(NULL);
				$bil->setDiscountedBy(NULL);
				$bil->setClinic(new Clinic(1));
				$bil->setBilledTo($pat->getScheme());
				$bil->setCostCentre(null);

				if ($bil->add(1, null, $pdo) === null) {
					$pdo->rollBack();
					exit('error:Transaction failed');
				}
			}
		}
		$pdo->commit();
		ob_clean();
		exit('success:Action completed successfully');

	} catch (PDOException $e) {
		errorLog($e);
	}
}