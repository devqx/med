DROP TABLE `bills_source`;

CREATE TABLE `bills_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `bills_source` (`id`, `name`) VALUES
(1, 'labs'),
(2, 'drugs'),
(3, 'consultancy'),
(4, 'blood'),
(5, 'admissions'),
(6, 'vaccines'),
(7, 'scans'),
(8, 'procedure'),
(9, 'misc'),
(10, 'registration'),
(11, 'consumables'),
(12, 'examination'),
(13, 'ophthalmology'),
(14, 'dentistry'),
(15, 'antenatal'),
(16, 'nursing_service'),
(17, 'ward'),
(18, 'ophthalmology_item'),
(19, 'physiotherapy'),
(20, 'physiotherapy_item'),
(21, 'ivf_lab'),
(22, 'ivf_package'),
(23, 'collections'),
(24, 'package'),
(25, 'drt'),
(26, 'feeding');

