<?php
  header('Content-type: text/html; charset=utf-8'); 
  session_start();
  require_once "../config.php";
  require_once 'functions.php';
  //$_SESSION['inserted'] = $_SESSION['auth'] = NULL;
?>
<html>
  <?php require_once('head.php'); ?>
  <body>
<?php
  if (!isset($_POST['pass']) && !isset($_SESSION['auth']))
  {
    echo "<h2>$av_title</h2>";
    echo "<h2> Αρχικοποίηση βάσης δεδομένων </h2>";
    echo "<h3>Δημιουργία βάσης δεδομένων ή επαναφορά στην αρχική κατάσταση (κενή βάση)</h3>";
    echo "ΣΗΜ.: To script αυτό δημιουργεί τη βάση με όνομα <strong>$db_name</strong> και πίνακες <strong>$av_emp, $av_ait, $av_sch, $av_dimos</strong>.<br><br>";
    echo "ΠΡΟΣΟΧΗ: Πριν προχωρήσετε, πρέπει να ρυθμίσετε τις παραμέτρους στο αρχείο <strong><i>../config.php</i></strong><br><br>";
    echo "<strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη...</strong><br><br>";
    echo "<form action='init.php' method='POST'>";
    echo "Δώστε κωδικό ασφαλείας για αρχικοποίηση <small>(βλ. ../config.php)</small>:&nbsp;&nbsp;&nbsp;<input type='text' id='pass' name='pass'><br><input type='submit' value='Αρχικοποίηση'></form>";
    exit;
  }
if (($_POST['pass'] == $av_init_pass) && !isset($_SESSION['auth'])) {
    $_SESSION['auth'] = 1;
}
elseif (!isset ($_SESSION['auth'])) {
  die ('Λάθος κωδικός');
}

if ($_SESSION['auth'])
{
    if (!$_SESSION['inserted'])
    {
      echo "<h3>Αρχικοποίηση βάσης δεδομένων</h3>";

      // create database
      # MySQL with PDO_MYSQL  
      $db = new PDO("mysql:host=$db_host", $db_user, $db_password);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $sql = "CREATE DATABASE IF NOT EXISTS $db_name DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
      $db->exec($sql);
      
      $tags = array (
        'apo_employee', 'apo_dimos',
        'apo_aitisi','apo_school','apo_params',
        '%aposp%'
      );
      $elem = array ( 
        $av_emp, $av_dimos, 
        $av_ait, $av_sch, $av_params, 
        $db_name
      );
      $query = str_replace($tags, $elem, file_get_contents('aposp.sql'));

      try {
        $db->exec($query);
      }
      catch (PDOException $e){
        echo "H αρχικοποίηση της Βάσης Δεδομένων απέτυχε...";
        echo $e->getMessage();
        die();
      }
      $db = NULL;
      $_SESSION['inserted']=1;
    }  
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password,$db_name);
    mysqli_set_charset($mysqlconnection,"utf8");    
        
    //Upload File
    if (isset($_POST['submit'])) {
      if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
          echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";


          //Import uploaded file to Database
          $handle = fopen($_FILES['filename']['tmp_name'], "r");
          switch ($_POST['type'])
          {
              case 1:
                  mysqli_query($mysqlconnection, "TRUNCATE $av_emp");
                  mysqli_query($mysqlconnection, "TRUNCATE $av_ait");
                  $tbl = $av_emp;
                  break;
              case 2:
                  mysqli_query($mysqlconnection, "TRUNCATE $av_sch");
                  $tbl = $av_sch;
                  break;
              case 3:
                  mysqli_query($mysqlconnection, "TRUNCATE $av_dimos");
                  $tbl = $av_dimos;
                  break;
          }
          $num = 0;
          $checked = 0;
          $headers = 1;
          
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
              // convert to utf8 using mb_helper
              $data = array_map('mb_helper', $data);
              // skip header line
              if ($headers){
                  $headers = 0;
                  continue;
              }
              // check if csv & table columns are equal
              if (!$checked)
              {
                  $csvcols = count($data);
                  $qry = "SELECT * FROM $tbl LIMIT 1";
                  $res = mysqli_query($mysqlconnection, $qry);
                  $tblcols = mysqli_num_fields($res);
                  if ($_POST['type'] == 1) $tblcols--;

                  if ($csvcols <> $tblcols)
                  {
                      echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
                      $ret = 0;
                      break;
                  }
                  else
                      $checked = 1;
              }

              switch ($_POST['type']){
                  // employees
                  case 1:
                      $import="INSERT into $av_emp(id,name,surname,patrwnymo,klados,am,afm,org,eth,mhnes,hmeres) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]')";
                      break;
                  // schools
                  case 2:
                      $import="INSERT into $av_sch(id,name,kwdikos,dim,omada,inactive) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]')";
                      break;
                  // dimoi
                  case 3:
                      $import="INSERT into $av_dimos(id,name) values('$data[0]','$data[1]')";
                      break;
              }
              // set max execution time (for large files)
              set_time_limit (480);
                  $ret = mysqli_query($mysqlconnection, $import);
              $num++;
          }
          // insert admin
          if ($_POST['type'] == 1){
            // insert admin user
            $query = "INSERT INTO $av_emp(name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres, lastlogin) VALUES ('admin', '', '', '', '$av_admin', '$av_admin_pass', '0', '0', '0', '0', CURRENT_TIMESTAMP)";
            $result = mysqli_query($mysqlconnection, $query);
          }

          fclose($handle);
          if ($ret){
              print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
              echo "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>";
          }
          else
          {
              echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
              echo "Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.<br>";
              echo mysqli_error($mysqlconnection) ? "Μήνυμα λάθους:".mysqli_error($mysqlconnection) : '';
              echo $num ? "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>" : '';
          }
      }
      else {
          echo "Δεν επιλέξατε αρχείο";
      }
            
    }  // of if (submit)
      
    //totals
    $total_emp = mysqli_query($mysqlconnection, "SELECT * FROM $av_emp") ? mysqli_num_rows(mysqli_query($mysqlconnection, "SELECT * FROM $av_emp")) - 1 : 0;
    $total_sch = mysqli_query($mysqlconnection, "SELECT * FROM $av_sch") ? mysqli_num_rows(mysqli_query($mysqlconnection, "SELECT * FROM $av_sch")) : 0;
    $total_dimos = mysqli_query($mysqlconnection, "SELECT * FROM $av_dimos") ? mysqli_num_rows(mysqli_query($mysqlconnection, "SELECT * FROM $av_dimos")) : 0;
    $hasdata = $total_dimos + $total_emp + $total_sch;
    
    //view upload form
    echo "<h2>$av_title</h2>";
    echo "<h2>Εισαγωγή δεδομένων</h2>";
    // display totals if any
    if ($hasdata > 0){
      echo "Σύνολα εγγραφών βάσης δεδομένων:<br>";
      echo "Υπάλληλοι: " . $total_emp . "<br>";
      echo "Σχολεία: " . $total_sch . "<br>";
      echo "Δήμοι: " . $total_dimos . "<br>";
    }
    echo "<br>";
    print "<h4>Μεταφορτώστε δεδομένα στο σύστημα</h4>";
    print "<form enctype='multipart/form-data' action='init.php' method='post'>";
    print "Όνομα αρχείου προς εισαγωγή:<br />\n";
    print "<input size='50' type='file' name='filename'><br />\n";
    echo "<br>Τύπος δεδομένων:<br>";
    echo "<input type='radio' name='type' value='1'>Υπάλληλοι<br>";
    echo "<input type='radio' name='type' value='2'>Σχολεία<br>";
    echo "<input type='radio' name='type' value='3' >Δήμοι<br>";
    print "<input type='submit' name='submit' value='Μεταφόρτωση'></form>";
    echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";

    echo "<h3>Για λόγους ασφαλείας, παρακαλώ διαγράψτε το αρχείο init.php αφου τελειώσετε...</h3><br>";
    echo "<a href='login.php'>Επιστροφή</a>";
    exit;
}	
	
?>

</body>
</html>
	