USE %aposp%;
DROP TABLE IF EXISTS `apo_aitisi`;
CREATE TABLE IF NOT EXISTS `apo_aitisi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,`ygeia` tinyint(1) NOT NULL COMMENT 'Λόγοι υγείας ιδίου, παιδιών, συζύγου',
  `ygeia_g` tinyint(1) NOT NULL COMMENT 'Λόγοι υγείας γονέων',`ygeia_a` tinyint(1) NOT NULL COMMENT 'Λόγοι υγείας αδελφών',
  `eksw` tinyint(1) NOT NULL COMMENT 'Θεραπεία για εξωσωματική γονιμοποίηση',
  `emp_id` int(11) NOT NULL COMMENT 'Α/Α υπαλλήλου (από πίνακα υπαλλήλων)',
  `p1` int(11) NOT NULL,`p2` int(11) NOT NULL,
  `p3` int(11) NOT NULL,`p4` int(11) NOT NULL,
  `p5` int(11) NOT NULL,`p6` int(11) NOT NULL,
  `p7` int(11) NOT NULL,`p8` int(11) NOT NULL,
  `p9` int(11) NOT NULL,`p10` int(11) NOT NULL,
  `p11` int(11) NOT NULL,`p12` int(11) NOT NULL,
  `p13` int(11) NOT NULL,`p14` int(11) NOT NULL,
  `p15` int(11) NOT NULL,`p16` int(11) NOT NULL,
  `p17` int(11) NOT NULL,`p18` int(11) NOT NULL,
  `p19` int(11) NOT NULL,`p20` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ημ/νία ώρα τελευταίας μεταβολής',
  `submit_date` datetime NOT NULL COMMENT 'Ημ/νία ώρα υποβολής',
  `submitted` tinyint(1) NOT NULL COMMENT 'Υποβλήθηκε',`gamos` tinyint(4) NOT NULL COMMENT '0 agamos, 1 eggamos',
  `paidia` tinyint(4) NOT NULL,`dhmos_anhk` varchar(40) NOT NULL,
  `dhmos_ent` tinyint(4) NOT NULL,`dhmos_syn` tinyint(4) NOT NULL,
  `aitisi` tinyint(4) NOT NULL COMMENT 'Έχει υποβάλει αίτηση βελτίωσης το τρέχον έτος',
  `eidikh` tinyint(1) NOT NULL COMMENT 'Ανήκει σε ειδική κατηγορία',
  `apospash` tinyint(1) NOT NULL COMMENT 'Απο τη Γενική στην Ειδική Αγωγή',
  `didakt` tinyint(1) NOT NULL COMMENT 'Διδακτορικό Ειδ.Αγ.',
  `metapt` tinyint(1) NOT NULL COMMENT 'Μεταπτυχιακό Ειδ.Αγ.',
  `didask` tinyint(1) NOT NULL COMMENT 'Διδασκαλείο Ειδ.Αγ.',
  `paidag` tinyint(1) NOT NULL COMMENT 'Πτυχίο παιδαγωγικών τμημάτων με αντικείμενο στην ειδική αγωγή',
  `eth` tinyint(4) NOT NULL COMMENT 'Έτη στην Ειδ.Αγωγή',
  `mhnes` tinyint(4) NOT NULL COMMENT 'Μήνες στην Ειδ.Αγωγή',
  `hmeres` int(11) NOT NULL COMMENT 'Ημέρες στην Ειδ.Αγωγή',
  `comments` text NOT NULL,
  `ypdil` tinyint(1) NOT NULL,
  `org_eid` tinyint(1) NOT NULL COMMENT 'Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)',
  `allo` varchar(100) NOT NULL COMMENT 'άλλο προσόν ειδικής αγωγής',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `apo_dimos`;
CREATE TABLE IF NOT EXISTS `apo_dimos` (
  `id` int(11) NOT NULL COMMENT 'Α/Α (0 για κανένα δήμο)',
  `name` varchar(50) NOT NULL COMMENT 'Όνομα καλλικρατικού δήμου',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `apo_employee`;
CREATE TABLE IF NOT EXISTS `apo_employee` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(20) NOT NULL,
  `klados` varchar(20) NOT NULL,
  `am` int(11) NOT NULL,
  `afm` bigint(10) NOT NULL,
  `org` bigint(10) NOT NULL COMMENT 'Οργανική (7ψήφιος κωδικός ΥΠΑΙΘ)',
  `eth` int(3) NOT NULL,
  `mhnes` int(3) NOT NULL,
  `hmeres` int(3) NOT NULL,
  `lastlogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Τελευταία είσοδος',
  PRIMARY KEY (`id`),
  KEY `am` (`am`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2594 ;

DROP TABLE IF EXISTS `apo_school`;
CREATE TABLE IF NOT EXISTS `apo_school` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `kwdikos` varchar(10) NOT NULL COMMENT '7ψήφιος κωδικός ΥΠΑΙΘ',
  `dim` tinyint(4) NOT NULL COMMENT 'dimotiko=2, nip=1',
  `omada` tinyint(4) NOT NULL COMMENT 'Ομάδα σχολείου (για περιορισμό αποσπάσεων)',
  `inactive` tinyint(1) NOT NULL COMMENT 'Να μην εμφανίζεται στις επιλογές',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
