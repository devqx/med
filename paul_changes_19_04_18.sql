
CREATE TABLE `early_warning_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `respiration_rate` int(2) NOT NULL,
  `oxygen_saturations` int(2) NOT NULL,
  `heart_rate` int(2) NOT NULL,
  `loc` int(2) NOT NULL,
  `supplemental_oxygen` int(2) NOT NULL,
  `temperature` int(2) NOT NULL,
  `systolic_bp` int(2) NOT NULL,
  `score` int(3) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `take_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `taken_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- #############

ALTER TABLE staff_directory add `is_consultant` tinyint(1) NOT NULL after folio_number;

-- #############

ALTER TABLE in_patient add `health_state_id` varchar(55) NOT NULL after medication_code;

-- #############

ALTER TABLE in_patient add  `risk_to_fall` tinyint(1) NOT NULL after health_state_id;

