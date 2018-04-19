ALTER TABLE `patient_labs` CHANGE `lab_group_id` `lab_group_id` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `lab_requests` CHANGE `lab_group_id` `lab_group_id` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `patient_labs` CHANGE `patient_id` `patient_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `lab_requests` CHANGE `patient_id` `patient_id` INT(11) NULL DEFAULT NULL;

DELIMITER //
CREATE DEFINER =`root`@`localhost` PROCEDURE `lab_requests_recon`()
  BEGIN
    DECLARE GROUP_CODE VARCHAR(50);
    DECLARE x INT DEFAULT 0;

    DECLARE done_1 INT DEFAULT FALSE;
    DECLARE cur_1 CURSOR FOR
      SELECT COUNT(*), lab_group_id FROM lab_requests GROUP BY lab_group_id HAVING COUNT(*) > 1;

      block_1: BEGIN
      DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1 = TRUE;

      OPEN cur_1;
      for_each_requests_to_normalize: LOOP
        FETCH cur_1 INTO x, GROUP_CODE;
        IF (done_1) THEN
          LEAVE for_each_requests_to_normalize;
        END IF;

          block_2: BEGIN
          DECLARE PatientID INT;
          DECLARE Lab_group_name VARCHAR(50);
          DECLARE newVal VARCHAR(50);
          DECLARE y INT DEFAULT 0;
          DECLARE done_2 INT DEFAULT FALSE;
          DECLARE cur_2 CURSOR FOR SELECT patient_id, lab_group_id FROM lab_requests WHERE lab_group_id = GROUP_CODE;
          DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_2 = TRUE;
          SET y=0;

          OPEN cur_2;
          group_x_to_normalize: LOOP
            FETCH cur_2 INTO PatientID, Lab_group_name;
            SET y=y+1;
            IF (done_2) THEN
              LEAVE group_x_to_normalize;
            END IF;

            SELECT CONCAT(Lab_group_name, '/', y) INTO newVal;
            # Lab_group_name, PatientID;

            UPDATE lab_requests SET lab_group_id = newVal WHERE lab_group_id = Lab_group_name AND patient_id = PatientID;
            UPDATE patient_labs SET lab_group_id = newVal WHERE lab_group_id = Lab_group_name AND patient_id = PatientID;

          END LOOP group_x_to_normalize;
        CLOSE cur_2;
      END block_2;
    END LOOP for_each_requests_to_normalize;
    CLOSE cur_1;
  END block_1;
END //
DELIMITER ;

CALL lab_requests_recon;
ALTER TABLE `lab_requests` ADD UNIQUE(`lab_group_id`);