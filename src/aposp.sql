USE %aposp%;
DROP TABLE IF EXISTS `apo_aitisi`;
CREATE TABLE IF NOT EXISTS `apo_aitisi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,`ygeia` tinyint(1) NOT NULL COMMENT 'Λόγοι υγείας ιδίου, παιδιών, συζύγου',
  `ygeia_g` tinyint(1) NOT NULL COMMENT 'Λόγοι υγείας γονέων',`ygeia_a` tinyint(1) NOT NULL COMMENT 'Λόγοι υγείας αδελφών',
  `eksw` tinyint(1) NOT NULL COMMENT 'Θεραπεία για εξωσωματική γονιμοποίηση',
  `emp_id` int(11) NOT NULL COMMENT 'Α/Α υπαλλήλου (από πίνακα υπαλλήλων)',
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
  `choices` text NOT NULL,
  `checked` tinyint(1) NOT NULL,
  `check_date` datetime NOT NULL,
  `check_comments` text NOT NULL,
  `eid_kat` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `apo_dimos`;
CREATE TABLE IF NOT EXISTS `apo_dimos` (
  `id` int(11) NOT NULL COMMENT 'Α/Α (0 για κανένα δήμο)',
  `name` varchar(50) NOT NULL COMMENT 'Όνομα καλλικρατικού δήμου',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
  `eth` int(3) NULL DEFAULT NULL,
  `mhnes` int(3) NULL DEFAULT NULL,
  `hmeres` int(3) NULL DEFAULT NULL,
  `moria` FLOAT(5,2) NULL DEFAULT NULL,
  `entopiothta` TEXT NULL DEFAULT NULL,
  `synyphrethsh` TEXT NULL DEFAULT NULL
  `lastlogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Τελευταία είσοδος',
  PRIMARY KEY (`id`),
  KEY `am` (`am`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `apo_school`;
CREATE TABLE IF NOT EXISTS `apo_school` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `kwdikos` varchar(10) NOT NULL COMMENT '7ψήφιος κωδικός ΥΠΑΙΘ',
  `dim` tinyint(4) NOT NULL COMMENT 'dimotiko=2, nip=1',
  `omada` tinyint(4) NOT NULL COMMENT 'Ομάδα σχολείου (για περιορισμό αποσπάσεων)',
  `inactive` tinyint(1) NOT NULL COMMENT 'Να μην εμφανίζεται στις επιλογές',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `apo_params`;
CREATE TABLE IF NOT EXISTS `apo_params` (
  `id` int(11) NOT NULL,
  `pkey` varchar(20) NOT NULL,
  `pvalue` varchar(100) NOT NULL,
  `pdescr` varchar(200) NOT NULL,
  `pcheck` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Αρχικά δεδομένα του πίνακα `apo_params`
--
INSERT INTO `apo_params` (`id`, `pkey`, `pvalue`, `pdescr`, `pcheck`) VALUES
(2, 'av_type', '1', 'Τύπος εφαρμογής (1 = Αποσπάσεις, 2 = Βελτιώσεις)', 0),
(3, 'av_dntes', '1', 'Επιλογές Διευθυντών', 1),
(4, 'av_title', 'Online υποβολή αιτήσεων για βελτίωση - οριστική τοποθέτηση', 'Τίτλος εφαρμογής (π.χ. Online υποβολή αιτήσεων για απόσπαση - προσωρινή τοποθέτηση)', 0),
(5, 'av_foreas', 'ΠΥΣΠΕ Ηρακλείου', 'Φορέας αιτήσεων (ΠΥΣΠΕ / ΠΥΣΔΕ) (π.χ. ΠΥΣΠΕ Ηρακλείου)', 0),
(6, 'av_nomos', 'Ηρακλείου', 'Νομός (βλ. Περιφερειακή ενότητα)', 0),
(7, 'av_dnsh', 'Διεύθυνση Πρωτοβάθμιας Εκπ/σης Ηρακλείου', 'Δ/νση αιτήσεων (π.χ. Διεύθυνση Πρωτοβάθμιας Εκπ/σης Ηρακλείου)', 0),
(8, 'av_athmia', '1', 'Χρήση σε Δ/νση Πρωτοβάθμιας', 1),
(9, 'av_custom', ' ', 'Επιπλέον μήνυμα (εμφανίζεται στην οθόνη εισόδου, κάτω απ''το ον.χρήστη/κωδικό)', 0),
(10, 'av_choices', '20', 'Πλήθος επιλογών (προεπιλογή: 20 - <small>ΣΗΜ.: Το πρόγραμμα PPYSDE δέχεται έως 20 επιλογές)</small>', 0),
(11, 'av_display_login', '1', 'Προβολή του login', 1),
(12, 'av_is_active', '1', 'Σύστημα ενεργό', 1),
(13, 'av_active_from', 'Τρίτη 24/07/2018', 'Ενεργό από', 0),
(14, 'av_active_to', 'Τρίτη 24/07/2018', 'Ενεργό έως', 0),
(15, 'av_active_to_time', '15:00', 'Ενεργό έως ώρα', 0),
(16, 'av_endofyear', '31/08/2017', 'Τέλος σχολικού έτους (για υπολογισμό υπηρεσίας)', 0),
(17, 'av_etos', '2018', 'Έτος αιτήσεων (ημερολογιακό)', 0),
(18, 'av_sxoletos', '2017-18', 'Σχολικό έτος', 0),
(19, 'av_extra', '0', 'Εισαγωγή επιπλέον πεδίου για είσοδο στο σύστημα', 1),
(20, 'av_extra_name', ' ', 'Όνομα επιπλέον πεδίου', 0),
(21, 'av_extra_label', ' ', 'Επικεφαλίδα επιπλέον πεδίου', 0),
(22, 'av_link', ' ', 'Σύνδεσμος εγκυκλίου (εμφανίζεται στη βοήθεια της εφαρμογής)', 0),
(23, 'av_link_vel', '  ', 'Σύνδεσμος εγκυκλίου βελτιώσεων (εμφανίζεται στη βοήθεια της εφαρμογής)', 0),
(27, 'av_canundo', '1', 'Ο διαχειριστής μπορεί να αναιρέσει την υποβολή', 1),
(28, 'av_canalter', '1', 'Ο διαχειριστής μπορεί να αλλάξει την οργανική και τη συνολική υπηρεσία του εκπ/κού', 1);
