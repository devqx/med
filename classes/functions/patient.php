<?php

    
    $legacy_num = $ARR['legacy_num'];
    $fname = $ARR['fname'];
    $lname = $ARR['lname'];
    $mname = $ARR['mname'];
    $yob = $ARR['yearofbirth']; //
    $mob = $ARR['monthofbirth'];
    $dob = $ARR['dayofbirth'];
    if (isset($ARR['date_of_birth'])) {
        $date_of_birth = explode("-", $ARR['date_of_birth']);
        $yob = $date_of_birth[0]; //
        $mob = $date_of_birth[1];
        $dob = $date_of_birth[2];
    }

    $email = $ARR['email'];
    $state = $ARR['state']; //state of origin
    $lga = $ARR['lga']; //lga of origin
    $state_r = $ARR['state_r']; //state of residence
    $lga_r = $ARR['lga_r']; //lga of residence
    $address = $ARR['address'];
    $sex = $ARR['sex'];
    $kinfname = $ARR['kinfname'];
    $kinlname = $ARR['kinlname'];
    $kinphone = $ARR['kinphone'];
    $kinaddress = $ARR['kinaddress'];
    $phone = $ARR['phonen'];
    $bloodGroup = $ARR['bloodg'];
    $bloodType = $ARR['bloodt'];
    $basehospital = $ARR['hospital'];
    $socioEco = $ARR['socio_economic'];
    $lifeStyle = $ARR['lifestyle'];

    $lifeStyle = "'" . implode("|", $lifeStyle) . "'";
    if (!isset ($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION ['staffID'])) {
        return 'error:' . $this->SESSION_EXPIRED;
    }
    // do validation first
    if (trim($fname) == '' || trim($lname) == '') {
        $ret = 'error:First and last name required.';
    } else if (trim($sex) == "--") {
        $ret = 'error:Gender required.';
    } else if (!checkdate($mob, $dob, $yob)) {
        $ret = 'error:Invalid date of birth';
    } else if (trim($phone) == "" || !(preg_match('/^[+][2][3][4]+[0-9]{10}$/', $phone) || preg_match('/^[0]+[0-9]{10}$/', $phone))) {
        $ret = 'error:Valid Phone number is required.';
    } // continue other validations if required