<?php


class AdmissionSetting
{
	//true for paragon
	//false for garki, and danferd
	public static $ipMedicationTaskRealTimeDeduct = true;
	public static $allowUndueTaskToBeTaken = true;

	function __construct()
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
	}

}
