<?php
  // Σύστημα αποσπάσεων βελτιώσεων 
  // Βαγγέλης Ζαχαριουδάκης, ΠΕ20
  // Πληροφορίες: it@dipe.ira.sch.gr
  // (c) 2013-18
  //
  // ΣΗΜ.: Κατά την εγκατάσταση, μετά τη μεταβολή του config.php, προτείνεται να τρέξετε το init.php
    
  //
  // Παράμετροι βάσης δεδομένων
  // Στοιχεία σύνδεσης
  // sch.gr
  $db_host = "userdb";
  $db_user = "xxxxxxxx";
  $db_password = "xxxxxxxxx";
  $db_name = "aposp";
  // Ονόματα πινάκων
  // ΣΗΜ: το init.php φτιάχνει πίνακες με τα παρακάτω ονόματα:
  $av_emp = "apo_employee";
  $av_ait = "apo_aitisi";
  $av_sch = "apo_school";
  $av_dimos = "apo_dimos";
  $av_params = "apo_params";


  // Επιλογές Διαχειριστή για αρχικοποίηση βάσης δεδομένων
  // Όνομα χρήστη διαχειριστή (ΠΡΟΣΟΧΗ: πρέπει να είναι ΑΡΙΘΜΗΤΙΚΟ)
  $av_admin = "ΧΧΧΧΧΧ";
  // Ο κωδικός διαχειριστή ΠΡΕΠΕΙ να είναι ΑΡΙΘΜΗΤΙΚΟΣ.
  $av_admin_pass = "ΧΧΧΧΧΧ";
  // Κωδικός ασφαλείας για το αρχικοποίηση βάσης (init.php)
  $av_init_pass = "ΧΧΧΧΧΧ";

  ////////////////////////////////////////////////////////////////////////
  //////// ΠΡΟΣΟΧΗ: Από εδώ και κάτω δεν μεταβάλλουμε τίποτα! ////////////
  ////////////////////////////////////////////////////////////////////////
  // init params
  
  $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_set_charset($conn,"utf8");
  $query = "SELECT pkey, pvalue from $av_params";
  $result = mysqli_query($conn, $query);
  if (mysqli_num_rows($result)==0) 
      return 0;
  else {
      while ($row = mysqli_fetch_array($result)) {
        $key = $row['pkey'];
        $$key = $row['pvalue'];
      }
      $_SESSION['initialized'] = true;
  }
  
  
// Report all errors except E_NOTICE
// This is the default value set in php.ini  
// to avoid notices on some configurations
  error_reporting(E_ALL ^ E_NOTICE);
  
?>