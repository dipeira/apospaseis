<?php
  // Σύστημα αποσπάσεων βελτιώσεων 
  // Βαγγέλης Ζαχαριουδάκης, ΠΕ86
  // Πληροφορίες: it@dipe.ira.sch.gr
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
  // ΣΗΜ: το init.php φτιάχνει πίνακες με τα παρακάτω ονόματα. Αν επιθυμείτε διαφορετικά, ορίστε τα παρακάτω.
  $av_emp = "apo_employee";
  $av_ait = "apo_aitisi";
  $av_sch = "apo_school";
  $av_dimos = "apo_dimos";
  $av_params = "apo_params";
  $av_kena = "apo_kena";


  // Επιλογές Διαχειριστή για αρχικοποίηση βάσης δεδομένων
  // Όνομα χρήστη διαχειριστή (ΠΡΟΣΟΧΗ: πρέπει να είναι ΑΡΙΘΜΗΤΙΚΟ έως 11 χαρακτήρες)
  $av_admin = "ΧΧΧΧΧΧ";
  // Χρήστες που χειρίζονται το σύστημα αλλά δεν έχουν δικαιώματα διαχειριστή. Νε εισάγονται με εισαγωγικά, π.χ. ['123456','654321']
  $av_staff = [];
  // Αρχικός κωδικός διαχειριστή (μόνο για την αρχικοποίηση - ΠΡΕΠΕΙ να είναι ΑΡΙΘΜΗΤΙΚΟΣ έως 10 χαρακτήρες).
  $av_admin_pass = "ΧΧΧΧΧΧ";
  // Κωδικός ασφαλείας για το αρχικοποίηση βάσης (init.php)
  $av_init_pass = "ΧΧΧΧΧΧ";

  // Ρυθμίσεις CAS Authentication
  $av_use_cas = false;                    // Ενεργοποίηση CAS authentication
  $av_cas_host = 'sso.sch.gr';       // CAS server hostname
  $av_cas_port = 443;                     // CAS server port
  $av_cas_context = '';               // CAS server context
  $av_cas_server_ca_cert_path = '';       // Path to CA certificate (optional)
  $av_cas_local_server = "";

?>