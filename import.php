<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/20/13
 * Time: 9:00 AM
 */

$row = 0;
$sql = [];
//echo "<table border='1'>";
//echo "Analysing file contents....<br/><br/>";
if (($handle = fopen("update_vaccine_new.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
        $statement = "";
        if($row>0){
            if(trim($data[1]) && strlen($data[1])==10){
//                echo "Row $row is OK.<br/>";
                if(trim($data[2]) && ($data[2])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 1 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[3]) && ($data[3])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 2 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[4]) && ($data[4])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 2 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[5]) && ($data[5])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 2 AND vaccine_level = 3 AND patient_id = '$data[1]'";}
                if(trim($data[6]) && ($data[6])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 3 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[7]) && ($data[7])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 3 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[8]) && ($data[8])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 3 AND vaccine_level = 3 AND patient_id = '$data[1]'";}
                if(trim($data[9]) && ($data[9])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 3 AND vaccine_level = 4 AND patient_id = '$data[1]'";}
                if(trim($data[10]) && ($data[10])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 4 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[11]) && ($data[11])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 4 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[12]) && ($data[12])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 4 AND vaccine_level = 3 AND patient_id = '$data[1]'";}
                if(trim($data[13]) && ($data[13])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 6 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[14]) && ($data[14])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 6 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[15]) && ($data[15])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 6 AND vaccine_level = 3 AND patient_id = '$data[1]'";}
                if(trim($data[16]) && ($data[16])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 7 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[17]) && ($data[17])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 7 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[18]) && ($data[18])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 7 AND vaccine_level = 3 AND patient_id = '$data[1]'";}
                if(trim($data[19]) && ($data[19])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 8 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[20]) && ($data[20])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 8 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[21]) && ($data[21])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 9 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[22]) && ($data[22])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 10 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[23]) && ($data[23])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 11 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[24]) && ($data[24])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 12 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[25]) && ($data[25])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 13 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[26]) && ($data[26])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 14 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[27]) && ($data[27])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 15 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[28]) && ($data[28])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 15 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
                if(trim($data[29]) && ($data[29])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 16 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[30]) && ($data[30])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 17 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[31]) && ($data[31])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 18 AND vaccine_level = 1 AND patient_id = '$data[1]'";}
                if(trim($data[32]) && ($data[32])=='1'){$sql[]="UPDATE patient_vaccine SET date_taken = NOW(), taken_by = '00000000001' WHERE vaccine_id = 18 AND vaccine_level = 2 AND patient_id = '$data[1]'";}
            }else{
//                echo "Row $row is NOT OK contains '$data[1]'<br/>";
            }
//            echo "<tr>";
//            for ($c=0; $c < $num; $c++) {
//                echo '<td>'.$data[$c] . "</td>";
//            }
//            echo "</tr>";
        }
    }
    fclose($handle);
}
//echo "</table>";
//echo "<p> $num fields in line $row: <br /></p>\n";

echo implode(";",$sql);