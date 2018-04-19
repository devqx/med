<?php
exit;
class Reports {
	function alladmissionswithindate($hospital,$start_date,$end_date){
		//returns total number of admissions between start and end date
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		$total=0;
		mysql_select_db($database_dbconnection, $dbconnection);
		
		$sql="SELECT count(admissions.patient_id) as admissions,patient_demograph.* FROM admissions,patient_demograph WHERE admissions.patient_id = patient_demograph.patient_ID AND date_admitted BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY date_admitted";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
			$total=mysql_num_rows($rst);
			//$returnval .= $title;
			 $total=$row['admissions'];;
			}
		return $total;
	}
	function dayrangeadmissions($hospital,$start_date,$end_date,$status){
	//gets number of discharged patients within date in review
	//plot patients on Admission and those not on admission within the time period.
		$returnval="";
		$title='';
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		if($status=='undischarged'){
		$sql="SELECT count(admissions.patient_id) as admissions,patient_demograph.* FROM admissions,patient_demograph WHERE admissions.patient_id = patient_demograph.patient_ID AND date_discharged is NULL AND date_admitted BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY date_admitted";
		$title='<h3>ADMITTED UNDISCHARGED PATIENTS</h3>';
		}
		else
		{
		$sql="SELECT count(admissions.patient_id) as admissions,patient_demograph.* FROM admissions,patient_demograph WHERE admissions.patient_id = patient_demograph.patient_ID AND date_discharged is NOT NULL AND date_discharged BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY date_discharged";
		$title='<h3>ADMITTED BUT DISCHARGED PATIENTS</h3>';
		}
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
			$total=mysql_num_rows($rst);
			$total=$row['admissions'];
			//$returnval .= $title;
			return $total;
			$sn=1;
		$returnval.= '<table id="admissionreport" align="left" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>S/N</th><th>Patient</th><th>Date Admitted</th><th>Date Discharged</th><th>Discharged by</th></tr>
</thead>';
			do {
				require_once 'class.patient.php';
				$pt = new Manager;
				require_once 'class.staff.php';
				$staff = new StaffManager;
			$returnval.= '<tr class="fancy"><td align="right">'.$sn.'</td><td><a href="../../patient_profile.php?id='.$row['patient_id'].'">'.$pt->getPatientName($row['patient_id']).'</a></td><td>'.date('d M, Y',strtotime($row['date_admitted'])).'</td><td>'.(($row['date_discharged']!=NULL)?$row['date_discharged']:'Not discharged').'</td><td>'.$staff->getDoctorNameFromID($row['discharged_by'],2).'</td></tr>';
		
				$sn+=1;
				}while($row = mysql_fetch_assoc($rst));
				$returnval.='<tr class="fancy">
    <td>&nbsp;</td>
    <td colspan="4"><strong>TOTAL: '.$total.'</strong></td>
  </tr></table>';
			}else {
		$returnval = 'There are no Admissions during this period';
			}
		return $returnval;
		}
	function daterangevisits($hospital,$start_date,$end_date){
	//returns the visitors to the hospital between the date range
		$returnval=0;
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
				$sql1="SELECT count(patient_ID) as dsum FROM patient_visit_notes WHERE DATE(date_of_entry) BETWEEN '".$start_date."' AND '".$end_date."'";
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		$returnval=intval($row['dsum'],10);
		return $returnval;
				
		}
	function monthlyvisits($hospital,$start_date, $end_date){
	//Returns the total income for the given range
		$returnval=0;
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
				$sql1="SELECT count(patient_ID) as dsum FROM patient_visit_notes WHERE DATE(date_of_entry) BETWEEN '".$start_date."' AND '".$end_date."'";
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		$returnval=intval($row['dsum'],10);
		return $returnval;
	}
	function calculatedatedifference($start_date,$end_date){
		//returns date difference in days between two dates
		$returnval=0;
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql="SELECT abs( datediff( '".$end_date."', '".$start_date."' ) ) as dif";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
	
		$returnval=intval($row['dif'],10);
		return $returnval;
	}
	function getCurrentMonth($_date){	
		return date("M",strtotime($_date));
	}
	function addOneMonth($_date){		
		$a=strtotime(date("Y-m-d", strtotime($_date)) . "+1 month");		
		return date("F",$a);	
	}
	function addOneDay($_date){
		//Adds one day to the current date
		$timeStamp = StrToTime($_date);
		$a = StrToTime('+1 days', $timeStamp);
		$nextday=date('Y-m-d', $a);		
		return $nextday;	
	}
	function subtractOneDay($_date){
		//Adds one day to the current date
		$timeStamp = StrToTime($_date);
		$a = StrToTime('-1 days', $timeStamp);
		$nextday=date('Y-m-d', $a);		
		return $yesterday;	
	}
	function getLastOfMonth($_date) {
		$month=date('m', strtotime($_date));
		$yr=date('Y', strtotime($_date));
		$lastdateofmonth= date("Y-m-d", strtotime('-1 second',strtotime('+1 month',strtotime($yr.'-'.$month.'-01'.' 00:00:00'))));
		
		return $lastdateofmonth;		
}
function getUndoneLabs($hospital,$start_date,$end_date){
		//returns all uncompleted labs between the given dates
		$returnval="";
		require 'Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql="SELECT patient_labs.*,labtests_config.*  FROM patient_labs,labtests_config WHERE patient_labs.test_label=labtests_config.config_test_id AND  patient_labs.test_date BETWEEN '".$start_date."' AND '".$end_date."' AND patient_labs.test_value is NULL ORDER BY patient_labs.test_date";
		$rst = mysql_query($sql,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
			$total=0;
			$returnval .= '<h3>INCOMPLETE LABS</h3>';
		$returnval.= '<table id="undonelabreport" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>Patient ID</th><th>Test Type</th><th>Date Requested</th><th>Requested by</th></tr>
</thead>';
			do {
				require_once 'class.patient.php';
				$pt = new Manager;
				require_once 'class.staff.php';
				$staff = new StaffManager;
				$returnval.= '<tr><td>'.$pt->getPatientName($row['patient_id']).'</td><td>'.$row['testType'].'</td><td>'.date('d F, Y',strtotime($row['test_date'])).'</td><td>'.$staff->getDoctorNameFromID($row['requester'],2).'</td></td></tr>';
				$total+=1;
				}while($row = mysql_fetch_assoc($rst));
					
				$returnval.='<tr class="fancy">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td align="right"><strong>TOTAL UNDONE:</strong></td>
    <td><strong>'.$total.'</strong></td>
  </tr></table>';
			}else {
		$returnval = 'There are <strong>NO UNDONE</strong> Lab  Tests during this period<br>';
			}
			return $returnval;
	}
	function getNoOfUndoneLabs($hospital,$start_date, $end_date){
	//Returns the total number of undone labs within date range
		$returnval=0;
		require 'Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
				
		$sql="SELECT count(patient_id) as dsum  FROM patient_labs WHERE  patient_labs.test_date BETWEEN '".$start_date."' AND '".$end_date."' AND patient_labs.test_value is NULL ORDER BY patient_labs.test_date";
		$rst = mysql_query($sql,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		$returnval=intval($row['dsum'],10);
		return $returnval;
	}
	function getNoOfDoneLabs($hospital,$start_date, $end_date){
	//Returns the total number of Done labs within date range
		$returnval=0;
		require 'Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql="SELECT count(patient_ID) as dsum  FROM patient_labs WHERE  patient_labs.test_date BETWEEN '".$start_date."' AND '".$end_date."' AND patient_labs.test_value is NOT NULL ORDER BY patient_labs.test_date";
		$rst = mysql_query($sql,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		$returnval=intval($row['dsum'],10);
		return $returnval;
	}
	function labsbycategory($hospital,$start_date,$end_date){
		//return the number of each category of test carried out within the given time period.
		//plot a chart if possible
		$returnval="";
		require 'Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql1="SELECT y.testType, count( x.`test_label` ) AS frequency FROM `patient_labs` x, `labtests_config` y
		WHERE x.test_label = y.`config_test_id` 
		AND x.test_date BETWEEN '".$start_date."' AND '".$end_date."'
		GROUP BY x.`test_label`";

		//$sql="SELECT patient_labs.*,labtests_config.*  FROM patient_labs,labtests_config WHERE patient_labs.test_label=labtests_config.config_test_id AND  patient_labs.test_date BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY labtests_config.testType";
		$rst = mysql_query($sql1,$dbconnection);
		$row = mysql_fetch_assoc($rst);
	if(mysql_num_rows($rst)>0){
			$returnval .= '<h3>LAB REQUESTS BY CATEGORY</h3>';
			$returnval.= '<table id="labcatreport" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead><tr><th>Test Type</th><th>Request Count</th></tr></thead>';
			do {
				$returnval.= '<tr class="fancy"><td>'.$row['testType'].'</td><td align="right">'.$row['frequency'].'</td></tr>';
				}while($row = mysql_fetch_assoc($rst));
				$returnval.='<tr>
    <td>&nbsp;</td>
    <td><a href="javascript:void(0)" onclick="window.print()">Print</a></td>
  </tr></table>';
			}else {
		$retval = 'There are no Drugs configured';
			}
		return $returnval;
	}
	function getDoneLabs($hospital,$start_date,$end_date){
	//returns all complete or done test labs between the range of dates given.
	$returnval="";
		require 'Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
	$sql="SELECT patient_labs.*,labtests_config.*  FROM patient_labs,labtests_config WHERE patient_labs.test_label=labtests_config.config_test_id AND  patient_labs.test_date BETWEEN '".$start_date."' AND '".$end_date."' AND patient_labs.test_value is NOT NULL ORDER BY patient_labs.test_date";
		$rst = mysql_query($sql,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
			$total=0;
			$returnval .= '<h3>COMPLETED LABS</h3>';
		$returnval.= '<table id="undonelabreport" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>Patient</th><th>Test Type</th><th>Date Requested</th></tr>
</thead>';
			do {
				require_once 'class.patient.php';
				$pt = new Manager;
				require_once 'class.staff.php';
				$staff = new StaffManager;
				$returnval.= '<tr><td>'.$pt->getPatientName($row['patient_id']).'</td><td>'.$row['testType'].'</td><td>'.date('d F, Y',strtotime($row['test_date'])).'</td></tr>';
		
				$total+=1;
				}while($row = mysql_fetch_assoc($rst));
					
				$returnval.='<tr>
    
    
    <td><a href="javascript:void(0)" onclick="window.print()">Print</a></td>
    <td>&nbsp;</td>
    <td align="right"><strong>TOTAL DONE:</strong></td>
    <td><strong>'.$total.'</strong></td>
  </tr></table>';
			}else {
		$returnval = 'There are <strong>NO UNDONE</strong> Lab  Tests during this period<br>';
			}
		return $returnval;
	}
	function incomePerCashOfficer($hospital,$officer, $start_date,$end_date){
	//returnz the total amount of revenue generated per cash officer on a given date.
		$returnval="";
		$officer=trim($officer);
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }$sql1="SELECT *  FROM bills WHERE DATE(transaction_date) BETWEEN '".$start_date."' AND '".$end_date."' AND receiver = '". $officer. "' ORDER BY DATE(transaction_date)";
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
	if(mysql_num_rows($rst)>0){
			$total=0;
			require_once 'class.staff.php';
			$staff = new StaffManager();
			$returnval .= '<h3>Bills Generated by '.$staff->getDoctorNameFromID($officer, 2).'</h3>';
		$returnval.= '<table id="inventoryreport" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>Patient</th><th>Description</th><th>Date Paid</th><th>Amount</th><th>Receiver</th></tr>
</thead>';
			do {
				require_once 'class.patient.php';
				$pt = new Manager;require_once 'class.staff.php';
				$staff = new StaffManager;$total+=(float)$row['amount'];
				$returnval.= '<tr class="fancy"><td>'.$pt->getPatientName( $row['patient_id']).'</td><td>'.$row['description'].'</td><td>'.date('d M, Y',strtotime($row['transaction_date'])).'</td><td align="right">'.number_format($row['amount'],2).'</td><td>'.$staff->getDoctorNameFromID( $row['receiver'],2).'</td></tr>';
				}while($row = mysql_fetch_assoc($rst));
				$returnval.='<tr>
    <td><a href="javascript:void(0)" onclick="window.print()">Print</a></td>
    <td>&nbsp;</td>
    <td><strong>TOTAL:</strong></td>
    <td align="right"><strong>'.number_format($total,2).'</strong></td>
  <td>&nbsp;</td>
    </tr></table>';
			}else {
		$returnval = 'There are no <strong>NO</strong> bills generated by '.$officer.' during this period<br>';
			}
		return $returnval;
	}
	function monthlyrevenue($hospital,$start_date, $end_date){
		//Returns the total income for the given range
		$returnval=0;
		//echo $start_date."  ".$end_date;
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $sql1="SELECT sum(amount) as dsum FROM bills WHERE DATE(transaction_date) BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY DATE(transaction_date)";
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		$returnval=sprintf("%.2f",$row['dsum']);
		return $returnval;
	}
	
	function incomeByRevenueSource($hospital,$start_date,$end_date){
		//returns the revenue generated in a given hospital depending on source of revenue from bills table.
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }$sql1="SELECT bill_source,sum(amount) as dsum FROM bills  WHERE DATE(transaction_date) BETWEEN '".$start_date."' AND '".$end_date."' GROUP BY bill_source";
		$rst = mysql_query($sql1,$dbconnection);
		$numrows=mysql_num_rows($rst);
		$row = mysql_fetch_assoc($rst);
		$total=0;
	if(mysql_num_rows($rst)>0){
			$returnval .= '<h3 style="text-align:center">INCOME BY REVENUE SOURCE </h3>';//'.$start_date. ' and '.$end_date.
			$returnval.= '<table align="left" id="revenuesourcereport" width="100%" border="0" cellspacing="4" cellpadding="4">';
			$returnval.= '<thead>
<tr><th>Bill Source</th><th>Amount(N)</th></tr>
</thead>';
			do {
				$returnval.= '<tr class="fancy" ><td>'.ucwords($row['bill_source']).'</td><td align="right">'.number_format($row['dsum'],2).'</td></tr>';	
				$total = $total + $row['dsum'];
				}while($row = mysql_fetch_assoc($rst));
				$returnval.='<tr>
    <th>TOTAL:</th>
    <th style="text-align:right">'.number_format($total,2).'</th>
  </tr></table>';
			}else {
		$returnval = 'No Revenue';
			}
		return $returnval;
		//return $sql1;
	}
	function getDateDangeIncome($hospital,$start_date,$end_date, $status){
		//returns the total paid/unpaid income generated in a hospital within a date range
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $sql1="SELECT  sum( x.`amount` ) AS totalpaid FROM `bills` x
		WHERE x.paid ='t'  AND x.transaction_date BETWEEN '".$start_date."' AND '". $end_date."'";
		$sql2="SELECT  sum( x.`amount` ) AS totalunpaid FROM `bills` x 
		WHERE x.paid !='t'  AND x.transaction_date BETWEEN '".$start_date."' AND '". $end_date."'";
		$rst = mysql_query($sql1,$dbconnection);
		
		$row = mysql_fetch_assoc($rst);
		$totalpaid=number_format($row['totalpaid'],2);
		
		$rst2 = mysql_query($sql2,$dbconnection);
		$row2 = mysql_fetch_assoc($rst2);
		$unpaid=number_format($row2['totalunpaid'],2);
		$total=$row['totalpaid'] + $row2['totalunpaid'];
			$returnval .= '<h3>PAID & UNPAID INCOME</h3>';
		$returnval.= '<table id="inventoryreport" width="100%" border="0" cellspacing="3" cellpadding="5">';
			$returnval.= '<thead>
					<tr><th>Type</th><th style="text-align:right">Amount</th></tr>
					</thead><tr><td>Paid</td><td align="right">'.$totalpaid.'</td></tr><tr><td>Un-paid</td><td align="right">'.$unpaid.'</td></tr>
					<tr class="fancy"><th>TOTAL:</th><td align="right">'.number_format($total,2).'</td></tr>			
					</table>';
		if($total == 0){
				$returnval ="<em>No Income within the dates</em>";
			}
		return $returnval;
	}
	function expiredDrugs($hospital=null,$today){
		//returns all drugs that have already expired
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }$sql="SELECT * FROM drugs WHERE expiry_date < '".$today."' AND quantity <> 0 ORDER BY expiry_date";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		
		if(mysql_num_rows($rst)>0){
			$returnval .= '<h4>EXPIRED DRUGS</h4>';
			$returnval.= '<table id="expirereport" class="table">';
			$returnval.= '<thead>
<tr><th>Drug</th><th class="amount">Qty</th><th class="amount">Base [Unit] Price (&#x20a6;)</th><th class="amount">Tot Value (&#x20a6;)</th><th>Expiry</th><th>Purchased</th><th>Action</th></tr>
</thead>';
			do {
				$total=0;
				$total=(float)($row['price']*$row['quantity']);
				$returnval.= '<tr><td>'.$row['drug_name'].'</td><td class="amount">'.$row['quantity'].'</td><td class="amount">'.number_format($row['price'],2).'</td><td class="amount">'.number_format($total,2).'</td><td>'.date("d M, Y",strtotime($row['expiry_date'])).'</td><td>'.date("d M, Y",strtotime($row['date_entered'])).'</td><td><a href="" onClick="new Boxy.load(\'purgedrug.php?drugid='.$row['drug_id'].'\',{title:\'Purge Expired Drugs from the inventory\'})">Purge</a></td></tr>';
			}while($row = mysql_fetch_assoc($rst));
				$returnval.='<tr>
			    <td colspan="7">&nbsp;</td>
			  </tr></table>';
			}else {
				$returnval= '<table class="table"><tr><td><div class="warning-bar">No expired drugs</div></td></tr></table>';
			}
		return $returnval;
	}
	function getLowstockLevel($drugcategory){
	    $returnval="";
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
		$sql="SELECT * FROM drug_category WHERE name ='".$drugcategory."'";
		//echo $sql;
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
		$returnval= $row['lowstocklevel'];
		}
		return $returnval;
	}
	function getDrugCategoryName($caterid){
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
		$sql="SELECT * FROM drug_category WHERE id = ".$caterid ;
		$rst = mysql_query($sql, $dbconnection);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
		$returnval= $row['name'];
		}
		return $returnval;
	}
	
	function isDrugsInStock($hospital=null){
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
		$sql = "SELECT * FROM drugs ";
		$rst = mysql_query ( $sql, $dbconnection );
		$row = mysql_fetch_assoc ( $rst );
		if (mysql_num_rows ( $rst ) >= 1) {
			return true;
		} else {
			return false;
		}
	}

	function lowQuantityStock($hospital=null){
		//returns drugs whose quantity remaining in stock is very low
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		
		$sql="SELECT sum(quantity) as quantityleft,cater_ID FROM drugs GROUP BY cater_ID";
        $rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		$isdata=0;
		if(mysql_num_rows($rst)>0){
			$returnval.= '<table class="table table-hover">';
			$returnval.= '<thead><tr><th>Category in Low Stock</th><th align="left">Quantity Left</th></tr></thead>';
			do {
				$name= $this->getDrugCategoryName($row['cater_ID']);
				$lowlevel = $this->getLowstockLevel($name);
				if($lowlevel >= $row['quantityleft']){
					$isdata=1;
					$returnval.= '<tr><td><a title="Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=category&Id='.$row['cater_ID'].'">'.$name.'</a></td><td align="left">'.$row['quantityleft'].'</td></tr>';
				}
			}while($row = mysql_fetch_assoc($rst));
				
			$returnval.='</table>';
			if($isdata==0){
			$returnval ='No drug category has low stock';
			}
			}else {
		$returnval = 'NO DRUGS IN STOCK <BR/>';
			}
		return $returnval;
	}
	
	function drugexpiry($hospital,$start_date,$end_date){
		//gets drugname,quantity left, revenue expected,expiry date of drugs that expires bw start&end date
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		
		$sql="SELECT * FROM DRUGS WHERE expiry_date BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY expiry_date";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		
		if(mysql_num_rows($rst)>0){
			$returnval .= '<h3> DRUGS EXPIRING BETWEEN '.date('d M, Y',strtotime($start_date)). ' and '.date('d M, Y',strtotime($end_date)).'</h3>';
			$returnval.= '<table id="expirereport" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>Drug Name</th><th>Qty</th><th>Unit Price</th><th>Tot Value</th><th>Exp Date</th><th>Purch Date</th></tr>
</thead>';
			do {
				$total=0;
				$total=(float)($row['price']*$row['quantity']);
				$returnval.= '<tr class="fancy"><td>'.$row['drug_name'].'</td><td align="right">'.$row['quantity'].'</td><td align="right">'.number_format($row['price'],2).'</td><td align="right">'.number_format($total,2).'</td><td>'.date("d M, Y",strtotime($row['expiry_date'])).'</td><td>'.date("d M, Y",strtotime($row['date_entered'])).'</td></tr>';
			}while($row = mysql_fetch_assoc($rst));
				
			$returnval.='<tr class="fancy">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr></table>';
			}else {
		$returnval = 'No Data';
			}
		return $returnval;
	}
	function inventoryreportbycategory($hospital){
		//returns drugname,quantity left, revenue expected,expiry date
		$returnval="";
		require 'Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql="SELECT drugs.*,drug_category.name,sum(drugs.quantity) as dsum FROM drugs,drug_category WHERE drugs.cater_ID=drug_category.id GROUP BY drugs.cater_ID ORDER BY cater_ID";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		$grandtotal=0;
		
		if(mysql_num_rows($rst)>0){
			//$returnval .= '<h3>DRUGS INVENTORY BY CATEGORY</h3>';
			$returnval.= '<table class="table-bordered table-hover table tablescroll_body" id="inventoryrep" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>Category</th><th>Total Qty</th><th>Expiry</th></tr>
</thead>';
			do {
				$total=0;
				$total=(float)($row['dsum']);
				$grandtotal+=$total;
				$returnval.= '<tr class="fancy"><td>'.$row['name'].'</td><td align="right">'.number_format($row['dsum'],0).'</td><td>'.date('d M, Y',strtotime($row['expiry_date'])).'</td></tr>';
				}while($row = mysql_fetch_assoc($rst));
					
				$returnval.='<tr class="fancy">
    <td colspan="4">&nbsp;</td>
    <td><a href="javascript:void(0)" onclick="window.print()">Print</a></td>
  </tr></table>';
			}else {
		$returnval = 'There are no Drugs in the inventory';
			}
		return $returnval;
	}
	function inventoryreport($hospital){
            $Y=array();
            $tempCats=array();
            $idCats=array();
            //returns drugname,quantity left, revenue expected,expiry date
            $returnval="";
            require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
            mysql_select_db($database_dbconnection, $dbconnection);
//            $sql="SELECT * FROM drugs ORDER BY expiry_date";
            $sql="SELECT d.*, dc.id as cat_id, dc.name as cat_name FROM drugs d, drug_category dc WHERE d.cater_ID=dc.id ORDER BY d.cater_ID, d.expiry_date";
            
            $rst = mysql_query($sql,$dbconnection);
            $row = mysql_fetch_assoc($rst);
            if(mysql_num_rows($rst)>0){
                $returnval .= '<h3 id="tableHead"><a href="javascript:void(0)" title="Show Table">DRUG TABLE </a></h3>';
                $returnval.= '<table id="inventoryreport" width="100%" border="0" cellspacing="0" cellpadding="5">';
                $returnval.= '<thead>
                    <tr><th>SN</th><th>Drug Name</th><th>Qty</th><th>Unit Price</th><th>Total Value</th></tr>
                    </thead>';
                $grandtotal=0;
                $i=0;
                do {
                    if(!in_array($row['cat_id'], $idCats)){
                        $idCats[]=$row['cat_id'];
                        $tempCats[]=$row['cat_name'];
                    }
                      
                     
                    $i++;
                    $total=0;
                    $total=(float)($row['price']*$row['quantity']);
                    $grandtotal+=$total;
                    $returnval.= '<tr class="fancy"><td>'.$i.'</td><td align="left">'.$row['drug_name'].'</td><td align="right">'.$row['quantity'].'</td><td align="right">'.number_format($row['price'],2).'</td><td align="right">'.number_format($total,2).'</td></tr>';
                }while($row = mysql_fetch_assoc($rst));
                $returnval.='<tr class="fancy">
                    <td colspan="2">Grand Total Value(N):</td>
                    <td align="right"><strong>'.number_format($grandtotal,2).'</strong></td>
                    <td>&nbsp;</td>
                    <td><a href="javascript:void(0)" onclick="window.print()">Print</a></td>
                    </tr></table>';
            }else{
                $retval = 'There are no Drugs configured';
            }
            $total=0;
            $data=array();
            $x=0;
            $temCa=array();
//            $tempCats=  array_unique($tempCats);
            for($i=0; $i<sizeof($idCats); $i++){//Categories
                if(in_array($idCats[$i], $temCa)){
                    continue;
                }
                $temCa[]=$idCats[$i];
                $cats=array();
                    $cats[]=$tempCats[$i];
                $Y[]=$tempCats[$i];
                $tot=0;
                $datum=array();
                $sql = "SELECT dc.name as cat_name, d.drug_id, d.drug_name, d.quantity, d.price, (d.price*d.quantity) as dTotal FROM drugs d, drug_category dc WHERE d.cater_ID=dc.id AND dc.id=".$idCats[$i]." ORDER BY d.cater_ID, d.expiry_date";
                $rst = mysql_query($sql,$dbconnection);
                while($row = mysql_fetch_assoc($rst)){//Category data
                    $tot += $row['quantity'];
                    $total += $row['quantity'];
                    $cat=array();
                        $cat[]=$row['quantity'];
                        $cat[]=$row['drug_name'];
                        $cat[]=$tot;
                    $datum[]=$cat;
                }
                $cats[]=$datum;
                $data[]=$cats;
                $x++;
            }
            
            $all=array();
                $all[]=$returnval;
                $all[]=$Y;
                $all[]=$data;
                $all[]=$total;
            return $all;
    }
	
	function getInsuranceList($insurancescheme){
        if($insurancescheme=="---"){
            return '<div class="warning-bar">Please select an insurance scheme</div>';
        }
		//returns all patients in a given insurance scheme
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		
		$sql="SELECT a.* ,b.*  FROM insurance a,patient_demograph b WHERE a.patient_ID=b.patient_ID AND a.insurance_scheme=".$insurancescheme;
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		
		if(mysql_num_rows($rst)>0){
			$returnval .= '<h3>Scheme Beneficiaries</h3>';
			$returnval.= '<table id="expirereport" width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
<tr><th>EMR ID</th><th>Name</th><th>Address</th><th>Expiry Date</th></tr>
</thead>';
			do {
				
				$returnval.= '<tr class="fancy"><td>'.$row['patient_ID'].'</td><td align="left">'.$row['fname'].', '.$row['lname'].'</td><td align="left">'.$row['address'].'</td><td align="left">'.$row['insurance_expiration'].'</td></tr>';
			}while($row = mysql_fetch_assoc($rst));
		
			$returnval.='<tr class="fancy">
			    <td colspan="6">&nbsp;</td>
			  </tr></table>';
		}else {
			$returnval= '<table  width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td><Strong>No Customer in this scheme yet</Strong></td></tr></table>';
		
		
		}
		return $returnval;
	}
	function getInsuranceSchemeOwnerName($insuranceSchemeID){
		$hospid = $this->getStaffHospitalID ( $_SESSION ['staffID'] );
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$rst = mysql_query ( "SELECT a.company_name FROM insurance_profile a, insurance_programs b WHERE b.scheme_id='".$insuranceSchemeID. "' AND b.scheme_owner_id = a.id /*AND hospid = ".$hospid."*/", $dbconnection );
		$row_data = mysql_fetch_assoc ( $rst );
		return $row_data ['company_name'];
	}
	function getInsuranceSchemeName($insuranceSchemeID) {
		$hospid = $this->getStaffHospitalID ( $_SESSION ['staffID'] );
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$rst = mysql_query ( "SELECT * FROM insurance_programs WHERE scheme_id='".$insuranceSchemeID. "' AND hospid = ".$hospid, $dbconnection );
		$row_data = mysql_fetch_assoc ( $rst );
		return $row_data ['scheme_name'];
	}
	function getInsuranceSchemePayments($insuranceSchemeID) {
		
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$sql="SELECT Sum(amount) as dsum, billed_to   FROM bills WHERE transaction_type <>'credit' AND billed_to=".$insuranceSchemeID." AND cancelled_on IS NULL GROUP BY billed_to";
		$rst = mysql_query ( $sql, $dbconnection );
		$row_data = mysql_fetch_assoc ( $rst );
		return abs($row_data ['dsum']);
	}
	function getStaffHospitalID($staffid) {
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$sql = "SELECT clinicID FROM staff_directory WHERE staff_directory.staffid = " . $staffid;
		$chk = mysql_query ( $sql, $dbconnection );
		$row_data = mysql_fetch_assoc ( $chk );
		return $row_data ['clinicID'];
	}
	function getInsuranceSchemeRevenue(){
		//returns all patients in a given insurance scheme
		$returnval="";
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		
		$sql="SELECT Sum(amount) as dsum, billed_to   FROM bills WHERE transaction_type='credit' AND cancelled_on IS NULL GROUP BY billed_to ORDER BY billed_to";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
			$returnval .= '<h3>Insurance Schemes\' Account Summary</h3>';
			$returnval.= '<table  width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
			<tr><th>Scheme Name</th><th>Owner</th><th>Bills</th><th>Payments</th><th>Balance</th><th>*</th></tr>
			</thead>';
			do {
				if($row['billed_to']<>1){
					$pay=$this->getInsuranceSchemePayments($row['billed_to']);
					$balance = $row['dsum']-$pay;
					if($balance < 0){
					$type="[DR]";
					$balance=abs($balance);
					}
					else{
						$type="[CR]";
					}
					$returnval.= '<tr class="fancy"><td>'.$this->getInsuranceSchemeName($row['billed_to']).'</td><td align="left">'.$this->getInsuranceSchemeOwnerName($row['billed_to']).'</td><td align="left">'.$row['dsum'].'</td><td align="left">'.$pay.'</td><td align="left">'.$balance.$type.'</td><td><a href="">Details</a></td></tr>';
				}
				}while($row = mysql_fetch_assoc($rst));
		
			$returnval.='<tr class="fancy">
			    <td colspan="6">&nbsp;</td>
			  </tr></table>';
		}else {
			$returnval= '<table  width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td><strong>No Insurance Scheme available</strong></td></tr></table>';
		
		
		}
		return $returnval;
	}
	function getOutstandingTotalForScheme($sid){
		require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$sid = trim ( mysql_real_escape_string($sid) );
		//$sql = "SELECT SUM(amount) As sPd FROM bills WHERE billed_to = '$sid'";
		//$sql = "SELECT COALESCE(SUM(amount),0) AS sPd FROM bills WHERE billed_to = '$sid'";
		//$result = mysql_query($sql,$dbconnection);
		//$row = mysql_fetch_assoc($result);
		//return $row['sPd'];
	
		$outstanding_total = 0;
	
		//$sql = "SELECT * FROM bills WHERE invoiced='no' AND billed_to='" . mysql_real_escape_string ( $_GET['id']) . "' AND transaction_type='credit'";
		$sql = "SELECT * FROM bills WHERE billed_to=$sid AND cancelled_on IS NULL";
	
		$result = mysql_query ( $sql, $dbconnection );
		$row = mysql_fetch_assoc($result);
	
		if(mysql_num_rows($result)>0){
			do {
				$outstanding_total = $row['amount']+$outstanding_total;
			} while($row = mysql_fetch_assoc($result));
		}
		return $outstanding_total;
	}
	function getPatientName($patientID) {
		require "Connections/dbconnection.php";
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$sql = "SELECT * FROM patient_demograph WHERE patient_ID ='" . $patientID . "'";
		$chk = mysql_query ( $sql, $dbconnection );
		$row_data = mysql_fetch_assoc ( $chk );
		$retVal = "";
		do {
			$retVal = $row_data ['fname'] . ', ' . $row_data ['lname'];
		} while ( $row_data = mysql_fetch_assoc ( $chk ) );
		return $retVal;
	}
	function getTransactionByDateForScheme($sid,$type,$start,$end){
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		mysql_select_db ( $database_dbconnection, $dbconnection );
		$sid = trim ( mysql_real_escape_string($sid) );
		$sql = "SELECT *  FROM bills WHERE transaction_type ='".$type."' AND billed_to = '$sid' AND transaction_date BETWEEN '".$start."' AND '".$end."'";
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		$returnval="";
		$total=0;
		if(mysql_num_rows($rst)>0){
			$returnval .= '<h3>Insurance Scheme\'s Transactions</h3>';
			$returnval.= '<table  width="100%" border="0" cellspacing="0" cellpadding="5">';
			$returnval.= '<thead>
			<tr><th>Description</th><th>Transaction Source</th><th>Amount</th><th>Date</th></tr>
			</thead>';
			do {
				if($row['billed_to']<>1){
					$total+= abs($row['amount']);
					$source=$row['patient_id'];
					if($type=="credit"){
						//get patient name
						$source=$this->getPatientName($row['patient_id']);
					}
					else {
						$source=$this->getInsuranceSchemeOwnerName($row['patient_id']);
					}
					$returnval.= '<tr class="fancy"><td align="left">'.$row['description'].'</td><td align="left">'.$source.'</td><td align="left">'. number_format(abs($row['amount']),2).'</td><td align="left">'.$row['transaction_date'].'</td></tr>';
				}
				}while($row = mysql_fetch_assoc($rst));
		
			$returnval.='<tr class="fancy">
			    <td colspan="2">&nbsp;</td><td colspan=""><b>TOTAL:'. number_format($total,2).'</b></td>
			  </tr></table>';
		}else {
			$returnval= '<table  width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td><Strong>No such transaction within date under review</Strong></td></tr></table>';
		
		
		}
		return $returnval;
	}
}
