<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BillDAO
 *
 * @author pauldic
 */
class BillDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Drug.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Lab.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bed.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Admission.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Blood.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/RoomType.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Scan.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Consultancy.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PaymentMethod.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Procedure.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Registration.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Item.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PaymentMethodDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Referral.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PhysiotherapyItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/MiscellaneousItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Feeding.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.bills.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			if (!isset($_SESSION))
				session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addBill($bil, $qty = 0, $pdo = null, $ipId = null)
	{
		//$bil = new Bill;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			
			} catch (PDOException $e) {
				errorLog($e);
				//Transaction is already started
			}
			//if ($ipId === null && $bil->getInPatient() != null) {
			//	$ipId = $bil->getInPatient()->getId();
			//}
			$ipId = ($ipId === null && $bil->getInPatient() == null) ? null : ($ipId || $bil->getInPatient()->getId());
			
			$priceType = $bil->getPriceType() ? ($bil->getPriceType()) : ('selling_price');
			#************************************
			# use token if available
			if ($bil->getItem() !== null && !empty((array)$bil->getItem()) && $bil->getTransactionType() != "transfer-credit" && $bil->getTransactionType() != "transfer-debit") {
				//if the patient has tokens available for this item
				//ye we know the item
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageTokenUsage.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenUsageDAO.php';
				$thisItemCode = $bil->getItem()->getCode();
				$itemsCodes = [];
				//$itemTokens = [];
				$patientTokens = (new PackageTokenDAO())->forPatient($bil->getPatient()->getId(), $pdo);
				
				foreach ($patientTokens as $token) {
					//$token = new PackageToken();
					//$itemTokens[] = array('code'=>$token->getItemCode(),'quantity_left'=>$token->getRemainingQuantity());
					$itemsCodes[] = $token->getItemCode();
				}
				
				if (in_array($thisItemCode, $itemsCodes)) {
					//check the quantity available for this item we have found
					$itemQuantity = (new PackageTokenDAO())->forPatientItem($thisItemCode, $bil->getPatient()->getId(), $pdo);
					
					$availableTokenItemQty = $itemQuantity->getRemainingQuantity();
					$billQuantity = $bil->getQuantity() && $bil->getQuantity() != 0 ? $bil->getQuantity() : $qty;
					
					if ($bil->getTransactionType() != 'reversal') {
						if ($availableTokenItemQty >= $billQuantity) {
							//continue to deplete or update quantity as appropriate
							if ($priceType == 'selling_price') {
								$itemQuantity->setRemainingQuantity($availableTokenItemQty - $billQuantity)->setPatient($bil->getPatient())->update($pdo);
								(new PackageTokenUsage())->setItemCode($thisItemCode)->setPatient($bil->getPatient())->setQuantity($billQuantity)->add($pdo);
							}
							//we have reduced token, so we need not charge this patient, exit from this function
							return $bil;//this might fail where we actually expect this data as an argument
						}
					} else if ($bil->getTransactionType() == 'reversal') {
						/*if($priceType == 'selling_price'){
							$itemQuantity->setRemainingQuantity($availableTokenItemQty+$billQuantity)->setPatient($bil->getPatient())->update($pdo);
							(new PackageTokenUsage())->setItemCode($thisItemCode)->setPatient($bil->getPatient())->setQuantity(0-$billQuantity)->add($pdo);
						}*/
						//we have reduced token, so we need not charge this patient, exit from this function
						//return $bil; //this might fail where we actually expect this data as an argument
					}
					//todo how do you handle remnant quantities or fragmented quantities or split quantities?
				}
				
				unset($itemsCodes);
				
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
				$activeAntenatalInstance = (new AntenatalEnrollmentDAO())->getActiveInstance($bil->getPatient()->getId(), false, $pdo);
				if ($activeAntenatalInstance !== null) {
					//error_log("HERE is an antenatal patient");
					//if the patient has is enrolled into antenatal and the package has the items covered
					//yay!!! we know the item
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
					$thisItemCode = $bil->getItem()->getCode();
					$itemsCodes = [];
					
					$patientTokens = (new AntenatalPackagesDAO())->get($activeAntenatalInstance->getPackage()->getId(), $pdo)->getItems();
					
					foreach ($patientTokens as $token) {
						//$token = new AntenatalPackageItem();
						$itemsCodes[$token->getItemCode()] = $token->getUsage();
					}
					
					//if(in_array($thisItemCode, $itemsCodes)){
					if (isset($itemsCodes[$thisItemCode])) {
						//check the quantity available for this item we have found
						$itemQuantity = (new PatientAntenatalUsagesDAO())->forPatientItem($bil->getPatient()->getId(), $thisItemCode, $activeAntenatalInstance->getId(), $pdo);
						$usedTokenItemQty = (int)$itemQuantity->quantity;
						$totalTokenQuantity = (int)$itemsCodes[$thisItemCode];
						$billQuantity = $bil->getQuantity() && $bil->getQuantity() != 0 ? (int)$bil->getQuantity() : (int)$qty;
						$item_type = getAntenatalItemType($thisItemCode);
						
						if ($bil->getTransactionType() != 'reversal') {
							//error_log('..its a charge');
							//error_log(json_encode(array($totalTokenQuantity,$usedTokenItemQty,$billQuantity)));
							//error_log(var_export($totalTokenQuantity - $usedTokenItemQty >= $billQuantity, true));
							if ($totalTokenQuantity - $usedTokenItemQty >= $billQuantity) {
								//there's something to use and it's not a reversal
								//continue to deplete or update quantity as appropriate
								if ($priceType == 'selling_price') {
									(new PatientAntenatalUsages())->setPatient($bil->getPatient())->setItemCode($thisItemCode)->setItem($bil->getItem()->getId())->setType($item_type)->setAntenatal($activeAntenatalInstance)->setUsages(parseNumber($billQuantity))->setDateUsed(date(MainConfig::$mysqlDateTimeFormat))->add($pdo);
								}
								//we have added an antenatal usage data, so we need not charge this patient, exit from this function
								return $bil; //this might fail where we actually expect this data as an argument
							} //else {
							//	//nothing left to use
							//}
						} else if ($bil->getTransactionType() == 'reversal') {
							// it's a reversal
							/*if($priceType == 'selling_price') {
								(new PatientAntenatalUsages())->setPatient($bil->getPatient())->setItemCode($thisItemCode)->setItem($bil->getItem()->getId())->setType($item_type)->setAntenatal($activeAntenatalInstance)->setUsages(parseNumber(0 - $billQuantity))->setDateUsed(date(MainConfig::$mysqlDateTimeFormat))->add($pdo);
							}*/
							//return $bil; // fixme: if it's a reversal, and patient was charged for the service, we have to return `money` back
						}
						//todo how do you handle remnant quantities or fragmented quantities or split quantities?
					}
				}
				
			}
			#************************************
			$parent = $bil->getParent() ? $bil->getParent()->getId() : "NULL";
			$cancelledOn = $bil->getCancelledOn() ? quote_esc_str($bil->getCancelledOn()) : "NULL";
			$cancelledBy = $bil->getCancelledBy() ? $bil->getCancelledBy()->getId() : "NULL";
			
			$priceType_ = quote_esc_str($priceType);
			$miscellaneous = $bil->getMiscellaneous() ? var_export($bil->getMiscellaneous(), true) : 'FALSE';
			$active_ = $bil->getActiveBill() ?   $bil->getActiveBill() : 'bill_active';
			if ($bil->getPatient()) {
				$outstanding = (new PatientDemographDAO())->getPatient($bil->getPatient()->getId(), false, $pdo)->getOutstanding();
			} else {
				//this would be the balance of the insurance
				$bills = new Bills();
				$credits = number_format($bills->_getPatientCreditTotals($bil->getBilledTo()->getId(), 'insurance', $pdo), 2);
				$debits = number_format(-$bills->_getPatientPaymentsTotals($bil->getBilledTo()->getId(), 'insurance', $pdo), 2);
				$outstanding = number_format(($credits - $debits), 2);
			}
			
			$copay = 0;
			
			$amounts = $billIds = [];
			//if transaction type is credit? it should happen for reversals as long as the item is set
			if ($bil->getItem() !== null && !empty((array)$bil->getItem()) && $bil->getTransactionType() != "transfer-credit") {
				//get patient's insurance
				$patInsurance = (new InsuranceDAO())->getPatientInsuranceSlim($bil->getPatient()->getId(), $pdo);
				//if it has expired, charge the patient to him/herself
				if (!(bool)$patInsurance->active && $patInsurance->pay_type == "insurance") {
					$bil->setBilledTo(new InsuranceScheme(1));
				}
				/*check if the item is in the patient scheme's coverage so that the bill would be charged to the proper scheme
				and that it's not a transfer*/
				//$ITEM = (new InsuranceItemsCostDAO())->getItemPriceByCode($bil->getItem()->getCode(), $bil->getPatient()->getId(), FALSE, $pdo);
				$ITEM = (new InsuranceItemsCostDAO())->getInsuranceItem($bil->getItem()->getCode(), $bil->getPatient()->getId(), $pdo);
				
				if ($ITEM === null) {
					// then the item is not  covered under the scheme.
					// then alter the bill's `billedTo`
					$bil->setBilledTo(new InsuranceScheme(1));
				}
				
				if ($ITEM !== null && $ITEM->type !== "primary" && in_array($bil->getTransactionType(), ["credit", "reversal"])) {
					$bil->setBilledTo(new InsuranceScheme(1));
					//if item is secondary, charge the patient at hospital's default price
					//if procedure, this will break
					//do alter the price if item is not procedure and is not a `difference` charge
					if (substr_count($bil->getDescription(), "Diff: ", 0) === 0) {
						//added a charge type to capture the type of price, selling_price, theathrePrice, etc
						//that way, all functions that call this add bill function assumes selling price as default
						//and then we change for the places where calls were made for the follow up consultation and the procedures types
						$bil->setAmount((new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($bil->getItem()->getCode(), 1, true, true, $pdo)->{$priceType} * $qty);
					}
					
					if ($bil->getTransactionType() == "reversal") {
						if ($bil->getAmount() > 0) {
							$bil->setAmount(0 - $bil->getAmount());
						}
					}
				}
				// get percentage
				// co-pay applies only if this itemCost is a `primary` coverage
				if ($ITEM !== null && $ITEM->type === "primary" && !in_array($bil->getTransactionType(), ["reversal"])) {
					$copay_percentage = (new InsuranceItemsCostDAO())->getCoPayPriceByFamily(substr($bil->getItem()->getCode(), 0, 2), $bil->getBilledTo()->getId(), $pdo);
					$amounts = [$bil->getAmount() * (100 - $copay_percentage) / 100, $bil->getAmount() * ($copay_percentage) / 100]; // [ hmoPart, patientPart]
					$copay = $bil->getAmount() * ($copay_percentage) / 100;
				}
			}
			try {
				$itemCode = ($bil->getItem() && !$bil->getItem() instanceof Registration) ? "'" . $bil->getItem()->getCode() . "'" : (!is_blank($bil->getItemCode()) ? "'" . $bil->getItemCode() . "'" : "NULL");
			} catch (Exception $e) {
				errorLog($e);
				$itemCode = "NULL";
			}
			
			// $src = $this->getSourceName($bil->getItem());
			$src = ($bil->getSource() !== null) ? $bil->getSource()->getId() : "NULL";
			$sub_source = ($bil->getSubSource() !== null) ? $bil->getSubSource()->getId() : "NULL";
			// set the receiver to the logged in user if not set
			$receiver = ($bil->getReceiver() !== null) ? "'" . $bil->getReceiver()->getId() . "'" : $_SESSION['staffID'];
			
			$payment_method_id = ($bil->getPaymentMethod() != null) ? "'" . $bil->getPaymentMethod()->getId() . "'" : "NULL";
			$payment_reference = (!empty($bil->getPaymentReference())) ? "'" . $bil->getPaymentReference() . "'" : "NULL";
			$authCode = (!empty($bil->getAuthCode())) ? "'" . $bil->getAuthCode() . "'" : "NULL";
			$reviewed = var_export($bil->getReviewed(), true);
			$transfered = $bil->getTransferred() ? var_export($bil->getTransferred(), true) : var_export(false, true);
			$referral_id = ($bil->getReferral() !== null) ? $bil->getReferral()->getId() : "NULL";
			if ($bil->getClinic() == null) {
				$bil->setClinic(new Clinic(1));
			}
			$cost_centre_id = ($bil->getCostCentre() !== null) ? $bil->getCostCentre()->getId() : "NULL";
			
			$revenue_account_id = ($bil->getRevenueAccount() !== null) ? $bil->getRevenueAccount() : "NULL";
			
			$voucher = "NULL";
			if ($bil->getVoucher()) {
				$voucher = $bil->getVoucher()->getId();
				$serviceCentre = (new VoucherDAO())->get($voucher, $pdo)->getBatch()->getServiceCentre();
				$bil->setCostCentre($serviceCentre->getCostCentre());
				if (!(new VoucherDAO())->use_($bil->getVoucher()->getId(), $pdo)) {
					if ($canCommit) {
						$pdo->rollBack();
					}
					return null;
				}
			}
			
			$billedToType = (new InsuranceSchemeDAO())->get($bil->getBilledTo()->getId(), true, $pdo)->getType();
			$transactionDate = $bil->getTransactionDate() ? quote_esc_str($bil->getTransactionDate()) : 'NOW()';
			$dueDate = $bil->getDueDate() ? quote_esc_str($bil->getDueDate()) : 'NOW()';
			$sql = "INSERT INTO bills (patient_id, description, bill_source_id, bill_sub_source_id, in_patient_id, transaction_type, transaction_date, due_date, amount, copay, discounted, discounted_by, hospid, billed_to, receiver, payment_method_id, payment_reference, auth_code, reviewed, transferred, referral_id, voucher_id, cost_centre_id, revenue_account_id, item_code, quantity, parent_id, cancelled_on, cancelled_by, price_type, balance, misc,bill_active)";
			if (count($amounts) == 2) {
				//remove the 0-part of the patient bills | do not charge patients `0`!
				if (round($amounts[1], 0) !== round(0, 0) && $billedToType !== 'self') { // and it's hmo
					$bil->setAmount($amounts[1]);
					$balance = $outstanding + $bil->getAmount();
					//if the charged to is hmo, do anything to get the hmo's balance?
					$sql1 = $sql . " VALUES (" . (($bil->getPatient() !== null) ? "'" . $bil->getPatient()->getId() . "'" : "NULL") . ", " . quote_esc_str($bil->getDescription()) . ", " . $src . ", " . $sub_source . "," . ($ipId === null ? "NULL" : "'" . $ipId . "'") . ", '" . $bil->getTransactionType() . "', $transactionDate, $dueDate,'" . $bil->getAmount() . "', " . $copay . "," . "'" . (!is_null($bil->getDiscounted()) ? $bil->getDiscounted() : 'no') . "', " . ($bil->getDiscountedBy() === null ? "NULL" : "'" . $bil->getDiscountedBy()->getId() . "'") . ", '" . $bil->getClinic()->getId() . "', '" . 1 . "', $receiver, $payment_method_id, $payment_reference, $authCode, $reviewed, $transfered, $referral_id, $voucher, $cost_centre_id, $revenue_account_id, $itemCode, $qty, $parent, $cancelledOn, $cancelledBy, $priceType_, $balance, $miscellaneous,'". $active_ ."')";
					$stmt = $pdo->prepare($sql1, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$stmt->execute();
					$billIds[] = $pdo->lastInsertId();
				}
				$bil->setAmount($amounts[0]);
			}
			//balance is not accurate for bills charged to insurance schemes
			$balance = $outstanding + $bil->getAmount();
			$sql2 = $sql . " VALUES (" . (($bil->getPatient() !== null) ? "'" . $bil->getPatient()->getId() . "'" : "NULL") . ", " . quote_esc_str($bil->getDescription()) . ", " . $src . ", " . $sub_source . "," . ($ipId === null ? "NULL" : "'" . $ipId . "'") . ", '" . $bil->getTransactionType() . "', $transactionDate, $dueDate, '" . $bil->getAmount() . "', " . $copay . ", " . "'" . (!is_null($bil->getDiscounted()) ? $bil->getDiscounted() : "no") . "', " . ($bil->getDiscountedBy() === null ? "NULL" : "'" . $bil->getDiscountedBy()->getId() . "'") . ", '" . $bil->getClinic()->getId() . "', '" . $bil->getBilledTo()->getId() . "', $receiver, $payment_method_id, $payment_reference, $authCode, $reviewed, $transfered, $referral_id, $voucher, $cost_centre_id, $revenue_account_id, $itemCode, $qty, $parent, $cancelledOn, $cancelledBy, $priceType_, $balance, $miscellaneous,'". $active_ ."')";
			
			//sleep(0.03);// allow a little time to finish the processing
			//error_log($sql2);
			
			$stmt = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			
			$stmt->execute();
			$billIds[] = $pdo->lastInsertId();
			
			if ($stmt->rowCount() > 0) {
				if (count($billIds) == 1) {
					$bil->setId($billIds[0]);
				} else {
					$bil->setId($billIds);
				}
			} else {
				if ($canCommit) {
					$pdo->rollBack();
				}
				$stmt = null;
				return null;
			}
			
			//  create a new patient object from the id so that the insurance details can be accessed
			if ($bil->getPatient() != null) {
				$patient = (new PatientDemographDAO())->getPatient($bil->getPatient()->getId(), false, $pdo, true);
				
				// if this is a new patient, this object will be null, and breaks this `method`
				if ($patient->getScheme() != null) {
					if (($patient->getScheme()->getType() == 'self' || $patient->getScheme()->getType() == 'insurance') && $bil->getTransactionType() == "credit") {
						//only self-pay patients should be added to the billing queue, if a charge is being made
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
						$bills = new Bills();
						$bb = $bills->getOutstandingTotalForPatient($patient->getId(), $pdo);
						
						$credit_limit = (new CreditLimitDAO())->getPatientLimit($patient->getId(), $pdo)->getAmount();
						
						$selfOwe = $bb - $credit_limit > 0 ? $bb - $credit_limit : 0;
						//and only when they have outstanding
						//bug: this charge will make the outstanding to be > 0 but won't add them to billing Queue
						//so we have to check if this amount being charged is > 0 too
						//but add the patient if this charge was not made to an insurance scheme
						$whoWeCharge = (new InsuranceDAO())->getInsuranceSlim($bil->getBilledTo()->getId(), $pdo);
						if ($selfOwe > 0 || $bil->getAmount() > 0 && $whoWeCharge->pay_type == "self") {
							try {
								$queue = new PatientQueue();
								$queue->setType("Billing");
								$queue->setPatient($bil->getPatient());
								(new PatientQueueDAO())->addPatientQueue($queue, $pdo);
							} catch (PDOException $e) {
								error_log("Error adding to billing queue");
							}
						}
					}
				}
			}
			$stmt = null;
			
			if ($canCommit) {
				$pdo->commit();
			}
		} catch (PDOException $e) {
			/*if ($pdo != null && $pdo->inTransaction()) {
					$pdo->rollBack();
			}*/
			errorLog($e);
			$stmt = null;
			$bil = null;
		}
		return $bil;
	}
	
	function getBillsToTransfer($ids, $pdo = null)
	{
		if (is_blank($ids))
			return [];
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			// all bills that have their ids in the array
			$sql = "SELECT * FROM bills WHERE bill_id IN ($ids) AND `transferred` IS FALSE AND cancelled_on IS NULL AND transaction_type IN ('credit', 'transfer-credit')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getBillsForRequestCode($ids, $pdo = null)
	{
		if (is_blank($ids))
			return [];
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			// all bills that have their ids in the array
			$sql = "SELECT * FROM bills WHERE bill_id IN ($ids) AND `transferred` IS FALSE AND cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getBillsByIds($ids, $pdo = null)
	{
		if (is_blank($ids))
			return [];
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			// all bills that have their ids in the array
			$sql = "SELECT * FROM bills WHERE bill_id IN ($ids) AND `transferred` IS FALSE AND cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getInsuredBills($patient_id, $type = null, $billSource = [], $startDate = null, $endDate = null, $page = 0, $pageSize = 10, $pdo = null)
	{
		$data = [];
		$TYPE = $type != null ? "AND transaction_type='$type'" : '';
		$BS = sizeof($billSource) > 0 ? "AND bill_source_id IN (" . implode(", ", $billSource) . ")" : "";
		$SD = $startDate != null ? quote_esc_str($startDate) : 0;
		$ED = $endDate != null ? quote_esc_str($endDate) : 'NOW()';
		
		$sql = "SELECT b.*, s.scheme_name FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE s.pay_type <> 'self' AND b.patient_id = $patient_id AND /*`transferred` IS FALSE AND*/ b.transaction_type IN ('credit','transfer-credit','reversal') AND b.cancelled_by IS NULL $TYPE $BS AND DATE(transaction_date) BETWEEN DATE($SD) AND DATE($ED) ORDER BY transaction_date DESC";
		$sumSql = "SELECT SUM(b.amount) AS totalSum FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE s.pay_type <> 'self' AND b.patient_id = $patient_id AND /*`transferred` IS FALSE AND*/ b.transaction_type IN ('credit','transfer-credit','reversal') AND b.cancelled_by IS NULL $TYPE $BS AND DATE(transaction_date) BETWEEN DATE($SD) AND DATE($ED) ORDER BY transaction_date DESC";
		
		$total = 0;
		$bills = [];
		
		$totalSum = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sumSql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$totalSum = $stmt->fetchColumn(0);
		} catch (PDOException $e) {
			errorLog($e);
			error_log('ERROR: Failed to get the $totalSum');
		}
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			// all bills that were charged to `non`-self pay schemes under the patient
			
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$bills = [];
		}
		
		$results = (object)null;
		$results->totalSum = $totalSum;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getBillsToClaim($ids, $self=null, $pdo = null)
	{
		$extra = "";
		if ($self == null){
			$extra = "s.pay_type <> 'self' AND";
		}
		
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			// all bills that were charged to `non`-self pay schemes under the patient
			$sql = "SELECT b.*, s.scheme_name FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE $extra b.bill_id in ($ids) AND cancelled_on IS NULL AND `claimed` IS FALSE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	
	function searchBillsToClaim($key, $pid, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			// all bills that were charged to `non`-self pay schemes under the patient
			$sql = "SELECT b.*, s.scheme_name FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE b.description LIKE '%" . $key . "%' AND b.patient_id=$pid /*AND s.pay_type <> 'self'*/ AND `claimed` IS FALSE AND cancelled_on IS NULL AND transaction_type IN ('credit', 'transfer-credit')";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getBill($bid, $getFull = false,  $pdo = null)
	{
		$bill = new Bill();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (is_blank($bid)) {
				return null;
			}
			$sql = "SELECT * FROM bills  WHERE bill_id='$bid' AND cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, "TRUE");
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], true, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod();
					$pm->setId($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bill->setCostCentre($row['cost_centre_id'] != null ? new CostCenter($row['cost_centre_id']) : null);
				$bill->setItemCode($row['item_code']);
				$bill->setQuantity($row['quantity']);
				$bill->setAuthCode($row['auth_code']);
				$bill->setReviewed((bool)$row['reviewed']);
				$bill->setTransferred((bool)$row['transferred']);
				$bill->setClaimed((bool)$row['claimed']);
				$bill->setValidated((bool)$row['validated']);
				$bill->setActiveBill($row['bill_active']);
				
				$bill->setInPatient($row['in_patient_id'] != null ? new InPatient($row['in_patient_id']) : null);
				
				$bill->setParent($row['parent_id'] != null ? new Bill($row['parent_id']) : null);
				$bill->setCancelledBy($row['cancelled_by'] != null ? new StaffDirectory($row['cancelled_by']) : null);
				$bill->setCancelledOn($row['cancelled_on'] != null ? $row['cancelled_on'] : null);
				
			} else {
				$bill = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bill = null;
			errorLog($e);
		}
		return $bill;
	}
	
	
	function getUnclaimedBills($from= null, $to=null, $scheme_id=null,$provider_id=null, $page=null, $pageSize=null,  $pdo = null)
	{
		$bills = array();
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$scid = ($scheme_id == null) ? '' : ' AND b.billed_to=' . $scheme_id;
		$providerId = ($provider_id == null) ? '' : ' AND io.id=' . $provider_id;
		$sql = "SELECT * FROM bills b LEFT JOIN  insurance_schemes ins ON b.billed_to= ins.id LEFT JOIN insurance_owners io ON ins.scheme_owner_id=io.id WHERE  `claimed` IS FALSE AND cancelled_on IS NULL AND transaction_type IN ('credit', 'transfer-credit') AND (DATE(b.transaction_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "')) $scid $providerId";
		$total = 0;
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		}catch (PDOException $e){
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY DATE(b.transaction_date) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
				
			}
			$stmt = null;
		}catch (PDOException $e){
			$bills = array();
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
		
		
	}
	
	// cross check if the cancelling bill is ready cancelled before
	function checkBill($bid, $getFull = false, $pdo = null)
	{
		$bill = new Bill();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (is_blank($bid)) {
				return null;
			}
			$sql = "SELECT * FROM bills WHERE parent_id='$bid'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, "TRUE");
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], true, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod();
					$pm->setId($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bill->setCostCentre($row['cost_centre_id'] != null ? new CostCenter($row['cost_centre_id']) : null);
				$bill->setItemCode($row['item_code']);
				$bill->setQuantity($row['quantity']);
				$bill->setAuthCode($row['auth_code']);
				$bill->setReviewed((bool)$row['reviewed']);
				$bill->setTransferred((bool)$row['transferred']);
				$bill->setClaimed((bool)$row['claimed']);
				
				$bill->setInPatient($row['in_patient_id'] != null ? new InPatient($row['in_patient_id']) : null);
				$bill->setParent($row['parent_id']);
				
				//$bill->setParent($row['parent_id'] != null ? new Bill($row['parent_id']) : null);
				$bill->setCancelledBy($row['cancelled_by'] != null ? new StaffDirectory($row['cancelled_by']) : null);
				$bill->setCancelledOn($row['cancelled_on'] != null ? $row['cancelled_on'] : null);
				
			} else {
				$bill = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bill = null;
			errorLog($e);
		}
		return $bill;
	}
	
	
	
	function getTransferCreditOnly($bid, $getFull = false,  $pdo = null)
	{
		$bill = new Bill();
		$sql = "SELECT * FROM bills  WHERE transaction_type IN ('transfer-credit') AND cancelled_on IS NULL AND cancelled_by IS NULL AND billed_to=1 AND claimed IS FALSE AND parent_id='$bid'";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (is_blank($bid)) {
				return null;
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, "TRUE");
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], true, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod();
					$pm->setId($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bill->setCostCentre($row['cost_centre_id'] != null ? new CostCenter($row['cost_centre_id']) : null);
				$bill->setItemCode($row['item_code']);
				$bill->setQuantity($row['quantity']);
				$bill->setAuthCode($row['auth_code']);
				$bill->setReviewed((bool)$row['reviewed']);
				$bill->setTransferred((bool)$row['transferred']);
				$bill->setClaimed((bool)$row['claimed']);
				$bill->setValidated((bool)$row['validated']);
				$bill->setActiveBill($row['bill_active']);
				
				$bill->setInPatient($row['in_patient_id'] != null ? new InPatient($row['in_patient_id']) : null);
				
				$bill->setParent($row['parent_id'] != null ? new Bill($row['parent_id']) : null);
				$bill->setCancelledBy($row['cancelled_by'] != null ? new StaffDirectory($row['cancelled_by']) : null);
				$bill->setCancelledOn($row['cancelled_on'] != null ? $row['cancelled_on'] : null);
				return $bill;
			} else {
				$bill = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bill = null;
			errorLog($e);
		}
		
	}
	
	
	function getBills($getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM bills WHERE cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				//$bSource = $this->getSourceObject($row['bill_source_id']);
				//$bill->setItem($bSource); //Obj
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	
	function getUnReviewedBills($getFull = false, $page = 0, $pageSize = 10, $patientId = null, $staffId = null, $billSources = null, $dateFrom = null, $dateTo = null, $pdo = null)
	{
		$bills = array();
		$total = 0;
		$sql = "SELECT * FROM bills WHERE reviewed = FALSE AND cancelled_on IS NULL AND bills.transaction_type IN ('transfer-credit')";
		$sql .= ($patientId !== null) ? ' AND bills.patient_id=' . $patientId : '';
		$sql .= ($staffId !== null) ? ' AND bills.receiver=' . $staffId : '';
		$sql .= !is_null($billSources) ? " AND bills.bill_source_id IN (" . implode(", ", $billSources) . ")" : "";
		if ($dateFrom != null && $dateTo == null) {
			$sql .= " AND DATE(transaction_date) BETWEEN DATE('$dateFrom') AND DATE(NOW())";
		} else if ($dateFrom == null && $dateTo != null) {
			$sql .= " AND DATE(transaction_date) BETWEEN DATE(NOW()) AND DATE('$dateTo')";
		} else if ($dateFrom != null && $dateTo != null) {
			$sql .= " AND DATE(transaction_date) BETWEEN DATE('$dateFrom') AND DATE('$dateTo')";
		}
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = $this->getBill($row['bill_id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$bills = array();
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getUnCompletedDischargeBills($aid, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT b.* FROM bills b LEFT JOIN in_patient i ON i.id=b.in_patient_id WHERE i.status = 'Discharging' AND i.id=$aid AND b.cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = $this->getBill($row['bill_id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getUnCompletedDischargeBillsSlim($aid, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT b.*, bs.name AS bill_source_name FROM bills b LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN in_patient i ON i.id=b.in_patient_id WHERE i.status = 'Discharging' AND i.id=$aid AND b.cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getInPatientBill($ipid, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT b.* FROM bills b WHERE b.in_patient_id=$ipid AND b.cancelled_on IS NULL";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], false, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = new StaffDirectory($row['discounted_by']);
					$receiver = new StaffDirectory($row['receiver']);
					$clinic = new Clinic($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setInPatient($row['in_patient_id'] === null ? null : (new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getInPatientBills($getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT b.* FROM bills b LEFT JOIN in_patient ip ON ip.id=b.in_patient_id WHERE ip.bill_status != 'Cleared' AND b.cancelled_on IS NULL";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], false, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = new StaffDirectory($row['discounted_by']);
					$receiver = new StaffDirectory($row['receiver']);
					$clinic = new Clinic($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				//$bSource = $this->getSourceObject($row['bill_source_id']);
				//$bill->setItem($bSource); //Obj
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setInPatient($row['in_patient_id'] === null ? null : (new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getBillsByDate($from = null, $to = null, $transaction_type = null, $payment_method_id = null, $cost_centre_id = null, $bill_source_id = null, $insurance_scheme_id = null, $provider_id = null, $insurance_type_id = null, $getFull = false, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		
		$trans_type = ($transaction_type == null) ? '' : ' AND b.transaction_type in ("' . implode('", "', $transaction_type) . '")';
		$pmid = ($payment_method_id == null) ? '' : ' AND b.payment_method_id=' . $payment_method_id;
		$csid = ($cost_centre_id == null) ? '' : ' AND b.cost_centre_id=' . $cost_centre_id;
		$bsid = ($bill_source_id == null) ? '' : ' AND b.bill_source_id=' . $bill_source_id;
		$scid = ($insurance_scheme_id == null) ? '' : ' AND b.billed_to=' . $insurance_scheme_id;
		$providerId = ($provider_id == null) ? '' : ' AND i.scheme_owner_id=' . $provider_id;
		$insurance_type = ($insurance_type_id == null) ? '' : ' AND i.insurance_type_id=' . $insurance_type_id;
		
		//$sql = "SELECT b.* FROM bills b LEFT JOIN insurance_schemes i ON i.id=b.billed_to WHERE DATE(b.transaction_date) BETWEEN DATE('".$f."') AND DATE('".$t."'){$trans_type}{$pmid}{$csid}{$bsid}{$scid}{$providerId}";
		$sql = "SELECT i.id as scheme_id,ii.enrollee_number, b.transaction_date AS bDate, b.description AS bDescription, b.patient_id AS patientId, CONCAT_WS(' ', pd.fname,pd.mname,pd.lname) AS patientName, i.scheme_name AS Coverage, bs.name AS Service, b.transaction_type AS TransactionType, pm.name AS paymentMethod, b.amount FROM bills b LEFT JOIN insurance_schemes i ON i.id=b.billed_to LEFT JOIN patient_demograph pd ON pd.patient_ID=b.patient_id LEFT JOIN insurance ii ON ii.patient_id=pd.patient_ID LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN payment_methods pm ON pm.id=b.payment_method_id WHERE (DATE(b.due_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "') OR DATE(b.transaction_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "')) AND b.cancelled_on IS NULL {$trans_type}{$pmid}{$csid}{$bsid}{$scid}{$providerId}{$insurance_type}";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY DATE(b.due_date) ASC LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)null;
				$bill->Date = $row['bDate'];
				$bill->Description = $row['bDescription'];
				$bill->PatientID = $row['patientId'];
				$bill->EnrolleeNumber = $row['enrollee_number'];
				$bill->Patient = $row['patientName'];//((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)==null)? (new InsuranceSchemeDAO())->get($row['billed_to'], FALSE, $pdo)->getName() : (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)->getFullname();
				$bill->Coverage = $row['Coverage'];//(new InsuranceSchemeDAO())->get($row['billed_to'], FALSE, $pdo)->getName();
				$bill->Service = $row['Service'];//(new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo)->getName();
				$bill->TransactionType = $row['TransactionType'];
				$bill->PaymentMethod = $row['paymentMethod'];//((new PaymentMethodDAO())->getPaymentMethod($row['payment_method_id'], FALSE, $pdo)==null)? '':(new PaymentMethodDAO())->getPaymentMethod($row['payment_method_id'], FALSE, $pdo)->getName();
				$bill->Amount = $row['amount'];
				
				$bills[] = $bill;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function exportBillReport($from = null, $to = null, $transaction_type = null, $payment_method_id = null, $cost_centre_id = null, $bill_source_id = null, $insurance_scheme_id = null, $provider_id = null, $insurance_type_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$trans_type = ($transaction_type == null) ? '' : ' AND b.transaction_type in ("' . implode('", "', $transaction_type) . '")';
		
		$pmid = ($payment_method_id == null) ? "" : " AND b.payment_method_id=" . $payment_method_id;
		$csid = ($cost_centre_id == null) ? '' : " AND b.cost_centre_id=" . $cost_centre_id;
		$bsid = ($bill_source_id == null) ? '' : " AND b.bill_source_id=" . $bill_source_id;
		$scid = ($insurance_scheme_id == null) ? '' : " AND b.billed_to=" . $insurance_scheme_id;
		$providerId = ($provider_id == null) ? '' : " AND i.scheme_owner_id=" . $provider_id;
		$insurance_type = ($insurance_type_id == null || is_blank($insurance_type_id)) ? '' : " AND i.insurance_type_id=" . $insurance_type_id;
		
		// $sql = "SELECT b.* FROM bills b LEFT JOIN insurance_schemes i ON i.id=b.billed_to WHERE DATE(b.transaction_date) BETWEEN DATE('".$f."') AND DATE('".$t."'){$trans_type}{$pmid}{$csid}{$bsid}{$scid}{$providerId}";
		$sql = "SELECT CONCAT_WS(' ', sd.firstname, sd.lastname) AS responsible, ii.enrollee_number, b.transaction_date AS bDate, b.description AS bDescription, b.patient_id AS patientId, CONCAT_WS(' ', pd.fname,pd.mname,pd.lname) AS patientName, i.scheme_name AS Coverage, bs.name AS Service, b.transaction_type AS TransactionType, pm.name AS paymentMethod, b.amount, s.name AS costCentre, b.auth_code FROM bills b LEFT JOIN insurance ii ON ii.patient_id=b.patient_id LEFT JOIN insurance_schemes i ON i.id=b.billed_to LEFT JOIN patient_demograph pd ON pd.patient_ID=b.patient_id LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN payment_methods pm ON pm.id=b.payment_method_id LEFT JOIN cost_centre s ON s.id=b.cost_centre_id LEFT JOIN staff_directory sd ON sd.staffId=b.receiver WHERE (DATE(b.due_date) BETWEEN DATE('$f') AND DATE('$t')OR DATE(b.transaction_date) BETWEEN DATE('$f') AND DATE('$t')) AND b.cancelled_on IS NULL {$trans_type}{$pmid}{$csid}{$bsid}{$scid}{$providerId}{$insurance_type}";
		//error_log($sql);
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql = "SELECT b.* FROM bills b LEFT JOIN insurance_schemes i ON i.id=b.billed_to WHERE DATE(b.transaction_date) BETWEEN DATE('".$f."') AND DATE('".$t."'){$trans_type}{$pmid}{$csid}{$bsid}{$scid}{$providerId} ORDER BY DATE(b.transaction_date) ASC LIMIT $offset, $pageSize";
			$sql .= " ORDER BY DATE(b.transaction_date) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)null;
				$bill->Date = $row['bDate'];
				$bill->Description = $row['bDescription'];
				$bill->PatientID = $row['patientId'];
				$bill->EnrolleeNumber = $row['enrollee_number'];
				$bill->Patient = $row['patientName'];//((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)==null)? (new InsuranceSchemeDAO())->get($row['billed_to'], FALSE, $pdo)->getName() : (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)->getFullname();
				$bill->Coverage = $row['Coverage'];//(new InsuranceSchemeDAO())->get($row['billed_to'], FALSE, $pdo)->getName();
				$bill->Service = $row['Service'];//(new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo)->getName();
				$bill->TransactionType = $row['TransactionType'];
				$bill->PaymentMethod = $row['paymentMethod'];//((new PaymentMethodDAO())->getPaymentMethod($row['payment_method_id'], FALSE, $pdo)==null)? '':(new PaymentMethodDAO())->getPaymentMethod($row['payment_method_id'], FALSE, $pdo)->getName();
				$bill->Amount = $row['amount'];
				$bill->CostCentre = $row['costCentre'];
				$bill->AuthCode = $row['auth_code'];
				$bill->Responsible = $row['responsible'];
				
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function exportOutstandingBillReport($page, $pageSize, $insurance_scheme, $sort, $admitted = null, $pdo = null)
	{
		$sorted = $sort; //($sort == 'creditors') ? "HAVING SUM(amount)>0":"HAVING SUM(amount)<0";
		$insId = $insurance_scheme !== null ? " AND bills.patient_id IN (SELECT patient_id FROM insurance WHERE insurance_scheme=$insurance_scheme)" : '';
		$admission = $admitted != null ? " AND IS_ADMITTED(bills.patient_id) IS TRUE" : '';//" AND IS_ADMITTED(bills.patient_id) IS FALSE";
		$total = 0;
		$sql = "SELECT SUM(amount) AS amount, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, s.scheme_name AS schemeName, pd.patient_ID FROM bills LEFT JOIN patient_demograph pd ON pd.patient_ID=bills.patient_id LEFT JOIN insurance_schemes s ON s.id = bills.billed_to LEFT JOIN insurance_schemes `is` ON `is`.id=bills.billed_to WHERE s.pay_type = 'self'$insId AND bills.cancelled_on IS NULL{$admission} AND pd.patient_ID IS NOT NULL GROUP BY bills.patient_id $sorted"; #ORDER BY transaction_date, patient_id";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$bills = [];
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)null;
				$bill->Patient = $row['patientName'];
				$bill->PatientEMR = $row['patient_ID'];
				$bill->Coverage = $row['schemeName'];
				$bill->Amount = $row['amount'];
				
				$bills[] = $bill;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$bills = [];
		}
		
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function searchBills($scheme = null, $from = null, $to = null, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$f = ($from == null) ? date("Y-m-d") : $from;
			$t = ($to == null) ? date("Y-m-d") : $to;
			$sql = "SELECT * FROM bills WHERE billed_to=" . ($scheme === null ? 1 : $scheme) . " AND transaction_type='credit' AND DATE(transaction_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "') AND cancelled_on IS NULL ORDER BY patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], false, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				//                $bSource = $this->getSourceObject($row['bill_source_id']);
				//                $bill->setItem($bSource); //Obj
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	public function getPatientOutstandingSum($pid, $pdo = null)
	{
		$oBill = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COALESCE(SUM(amount),0) as bill FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE s.pay_type = 'self' AND patient_id = $pid AND cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$oBill = $row["bill"];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$oBill = null;
		}
		return $oBill;
	}
	
	/*private*/
	function getSourceId($src)
	{
		switch (true) {
			case $src instanceof Drug:
				return 2;
				break;
			case $src instanceof Lab:
				return 1;
				break;
			case $src instanceof Vaccine:
				return 6;
				break;
			case $src instanceof Registration:
				return 10;
				break;
			case $src instanceof Admission:
			case $src instanceof RoomType:
				return 5;
				break;
			case $src instanceof StaffSpecialization:
				return 3;
				break;
			case $src instanceof Blood:
				return 4;
				break;
			case $src instanceof Scan:
				return 7;
				break;
			case $src instanceof Procedure:
				return 8;
				break;
			case $src instanceof Ward:
				return 17;
				break;
			case $src instanceof Item:
				return 11;
				break;
			case $src instanceof Ophthalmology:
				return 13;
				break;
			case $src instanceof Dentistry:
				return 14;
				break;
			case $src instanceof Antenatal:
			case $src instanceof AntenatalPackages:
				return 15;
				break;
			case $src instanceof NursingService:
				return 16;
				break;
			case $src instanceof OphthalmologyItem:
				return 18;
				break;
			case $src instanceof PhysiotherapyItem:
				return 20;
				break;
			case $src instanceof MedicalExam:
				return 12;
				break;
			case $src instanceof GeneticLab:
				return 21;
				break;
			case $src instanceof IVFPackage:
				return 22;
				break;
			case $src instanceof Package:
				return 24;
				break;
			case $src instanceof DRT:
				return 25;
				break;
			case $src instanceof Feeding:
				return 26;
				break;
			case $src instanceof MiscellaneousItem:
			default:
				return 9;
				break;
		}
	}
	//function getSourceName($src)
	//{
	//	switch (true) {
	//		case $src instanceof Drug:
	//			return "drugs";
	//			break;
	//		case $src instanceof Lab:
	//			return "labs";
	//			break;
	//		case $src instanceof Vaccine:
	//			return "vaccines";
	//			break;
	//		case $src instanceof Registration:
	//			return "registration";
	//			break;
	//		case $src instanceof Admission:
	//		case $src instanceof RoomType:
	//			return "admissions";
	//			break;
	//		case $src instanceof StaffSpecialization:
	//			return "consultations";
	//			break;
	//		case $src instanceof Blood:
	//			return "blood";
	//			break;
	//		case $src instanceof Scan:
	//			return "radiology";
	//			break;
	//		case $src instanceof Procedure:
	//			return "procedure";
	//			break;
	//		case $src instanceof Ward:
	//			return "ward";
	//			break;
	//		case $src instanceof Item:
	//			return "non - drug consumables";
	//			break;
	//		case $src instanceof Ophthalmology:
	//			return "ophthalmology";
	//			break;
	//		case $src instanceof Dentistry:
	//			return "dentistry";
	//			break;
	//		case $src instanceof Antenatal:
	//			return "antenatal";
	//			break;
	//		case $src instanceof AntenatalPackages:
	//			return "antenatal";
	//			break;
	//		case $src instanceof NursingService:
	//			return "nursing_service";
	//			break;
	//		case $src instanceof OphthalmologyItem:
	//			return "ophthalmology_item";
	//			break;
	//		case $src instanceof PhysiotherapyItem:
	//			return "physiotherapy_item";
	//			break;
	//		case $src instanceof MedicalExam:
	//			return "medical reports";
	//			break;
	//		case $src instanceof GeneticLab:
	//			return "ivf_lab";
	//			break;
	//		case $src instanceof IVFPackage:
	//			return "ivf_package";
	//			break;
	//		case $src instanceof Package:
	//			return "package";
	//			break;
	//		case $src instanceof DRT:
	//			return "drt";
	//			break;
	//		case $src instanceof Feeding:
	//			return "feeding";
	//			break;
	//		case $src instanceof MiscellaneousItem:
	//		default:
	//			return "misc";
	//			break;
	//	}
	//}
	//
	function invoiceBill($bill, $pdo)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE bills SET invoiced = 'yes' WHERE bill_id = " . $bill->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $bill;
			}
			return null;
		} catch (PDOException $e) {
			return null;
		}
	}
	
	function getBillsBySource($sourceId, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM bills WHERE bill_source_id=$sourceId AND cancelled_on IS NULL";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				//$bSource = $this->getSourceObject($row['bill_source_id']);
				//$bill->setItem($bSource); //Obj
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getBillsBySourceForPatient($sourceId, $patient_id, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM bills WHERE bill_source_id=$sourceId AND patient_id=$patient_id AND cancelled_on IS NULL";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				//$bSource = $this->getSourceObject($row['bill_source_id']);
				//$bill->setItem($bSource); //Obj
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getBillsByCustomIds($payment_type, $staffId, $from = null, $to = null, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$f = ($from == null) ? date("Y-m-d") : $from;
			$t = ($to == null) ? date("Y-m-d") : $to;
			$sql = "SELECT b.*, CONCAT_WS(' ', d.fname, d.mname, d.lname) AS patientName FROM bills b LEFT JOIN patient_demograph d ON d.patient_ID=b.patient_id LEFT JOIN payment_methods p ON b.payment_method_id=p.id WHERE p.type='$payment_type' AND b.receiver=$staffId AND DATE(b.transaction_date) BETWEEN DATE('$f') AND DATE('$t') AND cancelled_on IS NULL ORDER BY b.patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)null;
				$bill->Id = $row['bill_id'];
				$bill->PatientName = $row['patientName'];
				$bill->PatientId = $row['patient_id'];
				$bill->Amount = $row['amount'];
				$bill->Type = $row['transaction_type'];
				$bill->TransactionDate = $row['transaction_date'];
				$bill->PaymentMode = $row['payment_method_id'];
				$bill->Receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
				
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	
	function getBillsByCustom($payment_type, $from = null, $to = null, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$f = ($from == null) ? date("Y-m-d") : $from;
			$t = ($to == null) ? date("Y-m-d") : $to;
			
			//			$sql = "SELECT b.*, CONCAT_WS(' ', d.fname, d.mname, d.lname) AS patientName FROM bills b LEFT JOIN patient_demograph d ON d.patient_ID=b.patient_id LEFT JOIN payment_methods p ON b.payment_method_id=p.id WHERE p.type='$payment_type'  AND DATE(b.transaction_date) BETWEEN DATE('$f') AND DATE('$t') AND cancelled_on IS NULL ORDER BY b.patient_id";
			$sql = "SELECT b.receiver, SUM(b.amount) AS 'amount', p.name as payment_method, b.transaction_type, b.transaction_date FROM bills b LEFT JOIN payment_methods p ON b.payment_method_id = p.id WHERE p.type='$payment_type'  AND DATE(b.transaction_date) BETWEEN DATE('$f') AND DATE('$t') AND cancelled_on IS NULL group by b.receiver";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)null;
				$bill->Amount = $row['amount'];
				$bill->Type = $row['transaction_type'];
				$bill->TransactionDate = $row['transaction_date'];
				$bill->PaymentMode = $row['payment_method'];
				$bill->Receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
				
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	
	function getBillsForReferrals($from = null, $to = null, $referral_id = null, $hospital_id = null, $getFull = false, $pdo = null)
	{
		$bills = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$f = ($from == null) ? date("Y-m-d") : $from;
			$t = ($to == null) ? date("Y-m-d") : $to;
			$rid = ($referral_id == null) ? '' : ' AND r.id=' . $referral_id;
			$hid = ($hospital_id == null) ? '' : ' AND rc.id=' . $hospital_id;
			
			$sql = "SELECT b.* FROM bills b LEFT JOIN referral r ON r.id=b.referral_id LEFT JOIN referral_company rc ON rc.id=r.referral_company_id WHERE b.referral_id IS NOT NULL AND b.referral_id != 0 AND b.cancelled_on IS NULL AND DATE(b.transaction_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$rid}{$hid} ORDER BY patient_id";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = new Bill();
				$bill->setId($row['bill_id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$discountBy = (new StaffDirectoryDAO())->getStaff($row['discounted_by'], false, $pdo);
					$receiver = (new StaffDirectoryDAO())->getStaff($row['receiver'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
					$billedTo = (new InsuranceSchemeDAO())->get($row['billed_to'], true, $pdo);
					$pm = (new PaymentMethodDAO())->get($row['payment_method_id'], false, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$discountBy = $receiver = new StaffDirectory();
					$discountBy->setId($row['discounted_by']);
					$receiver->setId($row['receiver']);
					$clinic = new Clinic($row['hospid']);
					$billedTo = new InsuranceScheme($row['billed_to']);
					$pm = new PaymentMethod($row['payment_method_id']);
					$referral = new Referral($row['referral_id']);
				}
				$bill->setPatient($pat);    //Obj
				$bill->setTransactionDate($row['transaction_date']);
				$bill->setDueDate($row['due_date']);
				$bill->setDescription($row['description']);
				$bill->setSource((new BillSourceDAO())->getBillSource($row['bill_source_id'], $pdo));
				$bill->setSubSource((new BillSourceDAO())->getBillSource($row['bill_sub_source_id'], $pdo));
				$bill->setTransactionType($row['transaction_type']);
				$bill->setAmount($row['amount']);
				$bill->setCopay($row['copay']);
				$bill->setPriceType($row['price_type']);
				$bill->setDiscounted($row['discounted']);
				$bill->setDiscountedBy($discountBy);    //Obj
				$bill->setInvoiced($row['invoiced']);
				$bill->setReceiver($receiver);  //Obj
				$bill->setClinic($clinic);  //Obj
				$bill->setBilledTo($billedTo);  //Obj
				$bill->setPaymentMethod($pm);   //Obj
				$bill->setPaymentReference($row['payment_reference']);
				$bill->setReferral($referral);
				$bill->setRevenueAccount($row['revenue_account_id']);
				$bills[] = $bill;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = array();
		}
		return $bills;
	}
	
	function getNumberOfVouchersUsed($batchId, $pdo = null)
	{
		$bills = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COUNT(*) FROM bills b LEFT JOIN voucher v ON v.id=b.voucher_id LEFT JOIN voucher_batch vb ON vb.id=v.batch_id WHERE vb.id=$batchId AND b.cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$bills = $stmt->fetchColumn(0);
			//while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			//	$bills += 1;
			//}
			$stmt = null;
		} catch (PDOException $e) {
			$bills = 0;
		}
		return $bills;
	}
	
	function authorize($bill, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE bills SET auth_code ='" . escape($bill->getAuthCode()) . "', reviewed = TRUE WHERE bill_id = " . $bill->getId();
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return true;
			}
			return false;
		} catch (PDOException $e) {
			error_log("ERROR: Failed to authorize transaction");
			return false;
		}
	}
	
	function cancelConsultationVisit($bil_, $staff, $cancelMode = false, $pdo = null)
	{
		$status = false;
		try {
			//start a transaction
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
			}
			$sql = "UPDATE bills SET discounted='yes' WHERE bill_id = " . $bil_->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//$bill = TRUE;
			
			$patient = $bil_->getPatient();
			//$get_active_enrollment = (new AntenatalEnrollmentDAO())->getActiveInstance($patient->getId(), FALSE, $pdo);
			
			// check is bills has been manipulated
			
			
			$bil = new Bill();
			$bil->setPatient($patient);
			$bil->setDescription("" . $bil_->getDescription());
			$bil->setItem((new StaffSpecializationDAO())->getSpecializationByCode($bil_->getItemCode(), $pdo));
			$bil->setSource((new BillSourceDAO())->findSourceById(3, $pdo));
			$bil->setTransactionType("reversal");
			$bil->setPriceType($bil_->getPriceType());
			$bil->setTransactionDate(date("Y-m-d H:i:s"));
			$bil->setParent($bil_);
			$bil->setDueDate($bil_->getTransactionDate());
			$bil->setAmount(0 - $bil_->getAmount());
			$bil->setDiscounted(null);
			$bil->setDiscountedBy(null);
			$bil->setClinic($staff->getClinic());
			$bil->setBilledTo($patient->getScheme());
			$bil->setCostCentre($bil_->getCostCentre());
			$bil->setItemCode($bil_->getItemCode());
			$bil->setCancelledOn(date("Y-m-d H:i:s"));
			$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
			$bil->setActiveBill('not_active');
			
			if ($cancelMode) {
				$bil->setCancelledBy($staff);
				$bil->setCancelledOn(date("Y-m-d H:i:s"));
			}
			$bill = null;
			/*if($get_active_enrollment != null && $get_active_enrollment->getPackage() != null){
					$usages = new PatientAntenatalUsages();
					$usages->setType('Consultation');
					$usages->setPatient($patient);
					$usages->setAntenatal($get_active_enrollment);
					$usages->setItem((new StaffSpecializationDAO())->getSpecializationByCode($bil_->getItemCode(), $pdo)->getId());//specialization_id
					$get_usages = (new PatientAntenatalUsagesDAO())->getItemUsed($usages, $pdo);
					// remove used item
			} else {*/
			$bill = $this->addBill($bil, 1, $pdo, null);
			/*}*/
			
			if ($stmt->rowCount() <= 0 && isset($bill) && $bill === null) {
				error_log("Couldn't cancel consultancy");
				if ($canCommit) {
					$pdo->rollBack();
				}
			} else {
				$status = true;
				if ($canCommit) {
					$pdo->commit();
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $status;
	}
	
	function outstandingBills($pid = null, $page, $pageSize, $insurance_scheme = null, $sort = "HAVING SUM(bills.amount) > 0", $admitted = null, $pdo = null)
	{
		$sort = escape($sort);
		$insId = $insurance_scheme !== null ? " AND bills.patient_id IN (SELECT patient_id FROM insurance WHERE insurance_scheme=$insurance_scheme)" : '';
		$patientId = $pid !== null ? " AND bills.patient_id=$pid" : "";
		$admission = $admitted != null ? " AND IS_ADMITTED(bills.patient_id) IS TRUE" : '';// " AND IS_ADMITTED(bills.patient_id) IS FALSE";
		$total = 0;
		
		$sql = "SELECT bills.patient_id, SUM(amount) AS outstanding, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, PATIENT_SCHEME(pd.patient_ID) AS schemeName, pd.patient_ID FROM bills LEFT JOIN patient_demograph pd ON pd.patient_ID=bills.patient_id LEFT JOIN insurance_schemes s ON s.id = bills.billed_to LEFT JOIN insurance_schemes `is` ON `is`.id=bills.billed_to WHERE pd.active IS TRUE AND s.pay_type='self' AND bills.cancelled_on IS NULL {$insId}{$patientId} GROUP BY bills.patient_id $sort{$admission}";
		//$sql = "SELECT bills.patient_id, SUM(amount) AS outstanding, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, ANY_VALUE(s.scheme_name) AS schemeName, pd.patient_ID FROM bills LEFT JOIN patient_demograph pd ON pd.patient_ID=bills.patient_id LEFT JOIN insurance_schemes s ON s.id = bills.billed_to LEFT JOIN insurance_schemes `is` ON `is`.id=bills.billed_to WHERE pd.active IS TRUE {$insId}{$patientId} GROUP BY bills.patient_id $sort";
		
		//error_log($sql);
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$bills = [];
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)null;
				
				/*$patient = (new PatientDemographDAO())->getPatient($row['patient_ID'], TRUE, $pdo);
				if($patient !== null){*/
				$bill->isAdmitted = (new Manager())->isAdmitted($row['patient_ID']); //$patient->getFullname();
				$bill->Patient = $row['patientName'];//$patient->getFullname();
				$bill->PatientID = $row['patient_ID'];
				$bill->Scheme = $row['schemeName'];//$patient->getScheme()->getName();
				$bill->Outstanding = $row['outstanding']; //$this->getPatientOutstandingSum($row['patient_id'], $pdo);
				
				$bills[] = $bill;
				/*} else {
						error_log("NULL OBJECT FOR PATIENT: (".$row['patient_ID'].")");
				}*/
				
			}
		} catch (PDOException $e) {
			errorLog($e);
			$bills = [];
		}
		
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
		
	}
	
	function searchPatientBill($pid, $page = 0, $pageSize = 10, $pdo = null)
	{
		$pid = trim($pid);
		$sql = "SELECT b.*,p.* FROM bills b LEFT JOIN patient_demograph p ON b.patient_id=p.patient_ID WHERE (/*b.bill_id = '" . $pid . "' OR*/ b.patient_id LIKE '%" . $pid . "%' OR p.fname LIKE '%" . $pid . "%' OR p.lname LIKE '%" . $pid . "%' OR p.mname LIKE '%" . $pid . "%') AND b.cancelled_on IS NULL ORDER BY b.patient_id";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$bills = [];
		
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = $this->getBill($row['bill_id'], true, $pdo);
			}
			
		} catch (PDOException $e) {
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getInsuranceUnInvoicedBills($schemeId, $page = 0, $pageSize = 10, $patientId = null, $dateStart = null, $dateEnd = null, $pdo = null)
	{
		$pid = !is_null($patientId) ? " AND bills.patient_id=" . $patientId : "";
		$dates = !is_null($dateStart) && !is_null($dateEnd) ? " AND DATE(bills.transaction_date) BETWEEN '$dateStart' AND '$dateEnd'" : "";
		$sql = "SELECT * FROM bills LEFT JOIN insurance_schemes ON insurance_schemes.id = bills.billed_to WHERE (invoiced <> 'yes' OR invoiced IS NULL) AND billed_to=" . $schemeId . " AND transaction_type IN ('credit', 'transfer-credit') AND insurance_schemes.pay_type <> 'self' AND cancelled_on IS NULL {$pid}{$dates} ORDER BY transaction_date DESC, patient_id";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$bills = [];
		
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = $this->getBill($row['bill_id'], true, $pdo);
			}
			
		} catch (PDOException $e) {
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getPatientUnInvoicedBills($pId, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT b.*, bill_id AS id, ANY_VALUE(iic.type) AS type, sd.username AS receiverName FROM bills b LEFT JOIN staff_directory sd ON sd.staffId=b.receiver LEFT JOIN insurance_items_cost iic ON iic.item_code=b.item_code LEFT JOIN insurance_schemes s ON s.id = b.billed_to WHERE (b.invoiced <> 'yes' OR b.invoiced IS NULL) AND b.patient_id=$pId AND (b.transaction_type='credit') AND s.pay_type = 'self' AND b.cancelled_by IS NULL GROUP BY b.bill_id ORDER BY b.transaction_date DESC";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$bills = [];
		
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$bills[] = $this->getBill($row['bill_id'], TRUE, $pdo);
				$bills[] = (object)$row;
			}
			
		} catch (PDOException $e) {
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $bills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getEncounterBills($encounter_id, $pdo = null)
	{
		$bills = [];
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM bills WHERE encounter_id = $encounter_id AND cancelled_on IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
			}
			return $bills;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	public function getSchemesOfBills($items, $pdo = null)
	{
		$bills = [];
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT b.billed_to AS id, s.scheme_name FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE bill_id IN ($items) AND b.cancelled_on IS NULL GROUP BY b.billed_to";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
			}
			return $bills;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	public function getServiceBill($getCode, $transactionDate, $pid, $pdo = null)
	{
		$bills = [];
		$sql = "SELECT * FROM bills WHERE item_code='$getCode' AND (due_date='$transactionDate' OR transaction_date='$transactionDate') AND patient_id=$pid AND cancelled_on IS NULL";
		//error_log($sql);
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
			}
			return $bills;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	public function getRelatedBills($bid, $pdo = null, $unCancelled = true)
	{
		$bills = [];
		$getCancelled = $unCancelled ? ' AND cancelled_on IS NULL' : '';
		$sql = "SELECT * FROM bills WHERE parent_id=$bid{$getCancelled}";
		//error_log($sql);
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bills[] = (object)$row;
			}
			return $bills;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	
	public function getParentBill($bid, $pdo = null)
	{
		$bill = null;
		$sql = "SELECT * FROM bills WHERE bill_id=(SELECT parent_id FROM bills WHERE bill_id=$bid)";
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bill = (object)$row;
			}
			return $bill;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	public function cancelRelatedItems($pid, $itemCode, $transactionDate, $pdo)
	{
		$bill_id = $this->getServiceBill($itemCode, $transactionDate, $pid, $pdo);
		if (count($bill_id) >= 1) {
			foreach ($bill_id as $item) {
				//get the `related bills and cancel them
				$id = $item;
				
				$old_bill = $this->getBill($id->bill_id, true, $pdo);
				//if the bill has been transferred,
				// nullify the transfers,
				// set this one not to have been transferred
				if ($old_bill->getTransferred()) {
					$Parts = $this->getRelatedBills($id->bill_id, $pdo, false);
					foreach ($Parts as $part) {
						// if there was a write-off, convert it to a credit
						if ($part->transaction_type == 'write-off') {
							$bil3 = new Bill();
							$bil3->setPatient($old_bill->getPatient());
							$bil3->setDescription(getItem($old_bill->getItemCode(), $pdo)->getName());
							$bil3->setItem(getItem($old_bill->getItemCode(), $pdo));//$item
							$bil3->setSource($old_bill->getSource());
							$bil3->setTransactionType("credit");
							$bil3->setTransactionDate(date("Y-m-d H:i:s"));
							$bil3->setDueDate($old_bill->getTransactionDate());
							$bil3->setAmount(abs($part->amount)); // amount just like a credit
							$bil3->setDiscounted(null);
							$bil3->setDiscountedBy(null);
							$bil3->setClinic(new Clinic(1));
							$bil3->setBilledTo((new InsuranceScheme(1)));
							$bil3->setAuthCode(!is_blank($part->auth_code) ? $part->auth_code : null);
							$bil3->setReviewed(true);
							$bil3->setParent($old_bill);
							$bil3->setCancelledOn(date('Y-m-d H:i:s'));
							$bil3->setCancelledBy(new StaffDirectory($_SESSION['staffID']));
							$bil3->setActiveBill('not_active');
							$bil3->add($old_bill->getQuantity(), $old_bill->getInPatient() ? $old_bill->getInPatient()->getId() : null, $pdo);
						}
					}
					
					$related_bills = $this->getRelatedBills($id->bill_id, $pdo);
					foreach ($related_bills as $related_bill) {
						$thisBill = $this->getBill($related_bill->bill_id, true, $pdo);
						$ipId = $thisBill->getInPatient() ? $thisBill->getInPatient()->getId() : null;
						if ($thisBill->getTransactionType() == 'transfer-credit') {
							$thisBill->setAmount(0 - abs($thisBill->getAmount()))->setActiveBill('not_active')->setCancelledOn(date('Y-m-d H:i:s'))->setCancelledBy(new StaffDirectory($_SESSION['staffID']))->setTransactionType('transfer-debit')->add($thisBill->getQuantity(), $ipId, $pdo);
						} else if ($thisBill->getTransactionType() == 'transfer-debit') {
							$thisBill->setAmount(abs($thisBill->getAmount()))->setActiveBill('not_active')->setCancelledOn(date('Y-m-d H:i:s'))->setCancelledBy(new StaffDirectory($_SESSION['staffID']))->setTransactionType('transfer-credit')->add($thisBill->getQuantity(), $ipId, $pdo);
						}
						if (!$this->getBill($related_bill->bill_id, true, $pdo)->setCancelledOn(date('Y-m-d H:i:s'))->setCancelledBy(new StaffDirectory($_SESSION['staffID']))->update($pdo)) {
							if ($pdo->inTransaction()) {
								$pdo->rollBack();
							}
							return null;
						}
					}
				}
				//$old_bill->setTransferred(true)->update($pdo);
				$old_bill->setCancelledOn( date('Y-m-d H:i:s') )->setCancelledBy(new StaffDirectory($_SESSION['staffID']))->setActiveBill('not_active')->update($pdo);
			}
			return $old_bill;
		}
		return null;
	}
	
	
	
	function replaceBill($oldId, $newId, $category, $pdo = null)
	{
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			//
			if($category instanceof Scan){
				$where = "patient_scan";
			} else if($category instanceof Lab){
				$where = "patient_labs";
			} else if($category instanceof Drug){
				$where = "patient_regimens_data";
			} else {
				$where = "";
			}
			
			if(is_blank($where)){
				return null;
			}
			$sql = "UPDATE {$where} SET bill_line_id=REPLACE(bill_line_id, $oldId, $newId) WHERE FIND_IN_SET($oldId, bill_line_id) <> 0";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//
			if($stmt->rowCount()==1){
				return true;
			}
			return false;
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}
	
}
