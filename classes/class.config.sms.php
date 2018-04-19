<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 6/8/15
 * Time: 3:19 PM
 */
class SmsConfig
{
	
	public static $sendSMS = 1;
	
	public static $approvedScanSms = "";
	public static $approvedLabSms = "Your %s lab result is ready";
	public static $approvedDentologySms = "";
	
	public static $approvedScanRequestSms = "%s requested on %s for %s, EMR ID: %s has been approved"; // sms for Dr and patient care Team
	public static $approvedLabResultSms = "%s Lab Result Request on %s for %s, EMR ID: %s has been approved"; // sms for Dr and patient care Team

	
	public static $sendEmail = 0;
	//todo shouldn't we use an html template
	public static $approvedScanEmail = "";
	public static $approvedLabEmail = "Your %s lab result is ready";
	public static $approvedDentologyEmail = "";
	
}