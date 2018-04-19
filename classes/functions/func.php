<?php
/**
 * Created by JetBrains PhpStorm.
 * User: robot
 * Date: 7/1/13
 * Time: 1:50 PM
 * To change this template use File | Settings | File Templates.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
function sessionExpired()
{
	if (!isset($_SESSION)) {
		session_start();
	}
	if (!isset ($_SESSION ['staffID']) && !isset($_SESSION['patientID'])) {
		//also allow patients to view some pages
		echo '<div class="warning-bar">Your session has expired. Please <a href="/login.php">Login</a></div>';
		exit;
	}
}


function convert_number_to_words($number)
{
	/*
	 * source: http://www.karlrixon.co.uk/writing/convert-numbers-to-words-with-php/
	 */
	$hyphen = '-';
	$conjunction = ' and ';
	$separator = ', ';
	$negative = 'minus ';//negative
	$decimal = ' point ';
	$dictionary = array(0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'fourty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety', 100 => 'hundred', 1000 => 'thousand', 1000000 => 'million', 1000000000 => 'billion', 1000000000000 => 'trillion', 1000000000000000 => 'quadrillion', 1000000000000000000 => 'quintillion');

	if (!is_numeric($number)) {
		return false;
	}

	if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
		// overflow
		trigger_error('convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING);
		return false;
	}

	if ($number < 0) {
		return $negative . convert_number_to_words(abs($number));
	}

	$string = $fraction = null;

	if (strpos($number, '.') !== false) {
		list($number, $fraction) = explode('.', $number);
	}

	switch (true) {
		case $number < 21:
			$string = $dictionary[$number];
			break;
		case $number < 100:
			$tens = ((int)($number / 10)) * 10;
			$units = $number % 10;
			$string = $dictionary[$tens];
			if ($units) {
				$string .= $hyphen . $dictionary[$units];
			}
			break;
		case $number < 1000:
			$hundreds = $number / 100;
			$remainder = $number % 100;
			$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
			if ($remainder) {
				$string .= $conjunction . convert_number_to_words($remainder);
			}
			break;
		default:
			$baseUnit = pow(1000, floor(log($number, 1000)));
			$numBaseUnits = (int)($number / $baseUnit);
			$remainder = $number % $baseUnit;
			$string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
			if ($remainder) {
				$string .= $remainder < 100 ? $conjunction : $separator;
				$string .= convert_number_to_words($remainder);
			}
			break;
	}

	if (null !== $fraction && is_numeric($fraction)) {
		$string .= $decimal;
		$words = array();
		foreach (str_split((string)$fraction) as $number) {
			$words[] = $dictionary[$number];
		}
		$string .= implode(' ', $words);
	}

	return $string;
}



define("MAJOR", (new CurrencyDAO())->getDefault()->getTitle());
define("MINOR", ' ');

class toWords
{
	private $currency;
	//function __construct() {
	
	//}
	
	var $pounds;
	var $pence;
	var $major;
	var $minor;
	var $words = '';
	var $number;
	var $magind;
	var $units = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');
	var $teens = array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen');
	var $tens = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety');
	var $mag = array('', 'thousand', 'million', 'billion', 'trillion');

	public function __construct($amount, $major = MAJOR, $minor = MINOR)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
		$this->currency = (new CurrencyDAO())->getDefault();
		
		$amount = abs($amount);
		$this->major = $this->currency->getTitle();
		$this->minor = '';//$minor;
		$this->number = number_format($amount, 2);
		list($this->pounds, $this->pence) = explode('.', $this->number);
		$this->words = " $this->major $this->pence$this->minor";
		if ($this->pounds == 0) $this->words = "Zero $this->words"; else {
			$groups = explode(',', $this->pounds);
			$groups = array_reverse($groups);
			for ($this->magind = 0; $this->magind < count($groups); $this->magind++) {
				if (($this->magind == 1) && (strpos($this->words, 'hundred') === false) && ($groups[0] != '000')) $this->words = ' and ' . $this->words;
				$this->words = $this->_build($groups[$this->magind]) . $this->words;
			}
		}
	}

	function _build($n)
	{
		$res = '';
		$na = str_pad("$n", 3, "0", STR_PAD_LEFT);
		if ($na == '000') return '';
		if ($na{0} != 0) $res = ' ' . $this->units[$na{0}] . ' hundred';
		if (($na{1} == '0') && ($na{2} == '0')) return $res . ' ' . $this->mag[$this->magind];
		$res .= $res == '' ? '' : ' and';
		$t = (int)$na{1};
		$u = (int)$na{2};
		switch ($t) {
			case 0:
				$res .= ' ' . $this->units[$u];
				break;
			case 1:
				$res .= ' ' . $this->teens[$u];
				break;
			default:
				$res .= ' ' . $this->tens[$t] . ' ' . $this->units[$u];
				break;
		}
		$res .= ' ' . $this->mag[$this->magind];
		return $res;
	}
}

//echo convert_number_to_words(123456789);
// one hundred and twenty-three million, four hundred and fifty-six thousand, seven hundred and eighty-nine

//echo convert_number_to_words(123456789.123);
// one hundred and twenty-three million, four hundred and fifty-six thousand, seven hundred and eighty-nine point one two three

//echo convert_number_to_words(-1922685.477);
// negative one million, nine hundred and twenty-two thousand, six hundred and eighty-five point four seven seven

// float rounding can be avoided by passing the number as a string
//echo convert_number_to_words(123456789123.12345); // rounds the fractional part
// one hundred and twenty-three billion, four hundred and fifty-six million, seven hundred and eighty-nine thousand, one hundred and twenty-three point one two
//echo convert_number_to_words('123456789123.12345'); // does not round
// one hundred and twenty-three billion, four hundred and fifty-six million, seven hundred and eighty-nine thousand, one hundred and twenty-three point one two three four five

function convert_minutes_to_readable($minutes)
{
	$d = floor($minutes / 1440);
	$h = floor(($minutes - $d * 1440) / 60);
	$m = $minutes - ($d * 1440) - ($h * 60);

	$str = [];
	
	if($d > 0){return "{$d}days";}
	if($h > 0){return "{$h}hours";}
	if($m > 0){return "{$m}minutes";};
	return "";

	//$str[] = ($d > 0) ? "{$d}days" : "";
	//$str[] = ($h > 0) ? "{$h}hours" : "";
	//$str[] = ($m > 0) ? "{$m}minutes" : "";
	
	//return $str[0];

	//return implode(" ", $str);
}

function formatCount($count)
{
	//todo: maybe there's room for improvement
	return $count;
	/*switch ($count){
			case 0:
					return "N/A";
			case 1:
					return "once";
			case 2:
					return "twice";
			default:
					return $count." times";
	}*/
}

function describeTask($task)
{
	if ($task->getDrug() != null || $task->getGeneric() != null) {
		return "Give " . ($task->getDrug() !== null ? $task->getDrug()->getName() : $task->getGeneric()->getName()) . " " . ($task->getDrug() !== null ? $task->getDrug()->getGeneric()->getForm() : $task->getGeneric()->getForm());
	} else if ($task->getType() == NULL && $task->getDrug() == null && $task->getGeneric() == null) {
		return $task->getDescription();
	} else {
		return "Check " . $task->getType()->getName();
		//it has to be a vital sign then
	}
}

function checkDateInRange($start_date)
{
	$date1 = new DateTime(date("Y-m-d", strtotime($start_date)));
	$date2 = new DateTime(date("Y-m-d"));

	$interval = $date2->diff($date1);
	if ($interval->days >= 0 && $interval->days <= 7) {
		return true;
	}
	return false;
}