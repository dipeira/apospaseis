<?php
  session_start();
  header('Content-type: text/html; charset=utf-8'); 
  Require "../config.php";
  require_once 'functions.php';
  
  include("class.login.php");
  $log = new logmein();
  // if not logged in or not admin
  //if($log->logincheck($_SESSION['loggedin']) == false || $_SESSION['user'] != $av_admin)
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;

  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_set_charset($mysqlconnection,"utf8");
?>
<html>
  <?php require_once('head.php'); ?>
  <body>
  <div class="container">
<?php
if (!isset($_POST['submit']))
{
	echo "<h2> Εισαγωγή κενών στη βάση δεδομένων </h2>";
  $rows = count_rows($mysqlconnection);
    echo "<form enctype='multipart/form-data' action='import_kena.php' method='post'>";
    echo "Αρχείο προς εισαγωγή: &nbsp;&nbsp;<a href='../files/apo_kena.csv'>(Δείγμα)</a><br />\n";
    echo "<input size='50' type='file' name='filename'><br />\n";
    echo "<br>Κλάδος:<br>";
    ?>
    <select name="klados" id="klados" class="form-control">
      <option value="">-- Επιλέξτε κλάδο --</option>
      <option value="ΠΕ70">ΠΕ70</option>
      <option value="ΠΕ60">ΠΕ70</option>
      <option value="ΠΕ05">ΠΕ05</option>
      <option value="ΠΕ07">ΠΕ07</option>
      <option value="ΠΕ11">ΠΕ11</option>
    </select>
    <br>
    <?php
    
    print "<input type='submit' name='submit' class='btn btn-success' value='Μεταφόρτωση'></form>";
    //echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
    echo "</form>";
    echo "<br><br>";
    echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
	exit;
}

    
    
    //Upload File
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";
        //Import uploaded file to Database
        $handle = fopen($_FILES['filename']['tmp_name'], "r");
       
        $update = false;
        // check if kena exist
        $qry = "SELECT * FROM $av_kena WHERE klados = '".$_POST['klados']."'";
        
        $result = mysqli_query($mysqlconnection, $qry);
        if (mysqli_num_rows($result) > 0) {
          $update = true;
        }
        
        $num = $kena_num = 0;

        $checked = 0;
        $headers = 1;
        
        $kena = Array();

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
                
                $tblcols = 2;
                if ($csvcols <> $tblcols)
                {
                    echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
                    $ret = 0;
                    break;
                }
                else
                    $checked = 1;
            }
            $kena[$data[0]] = $data[1];

            $num++;
            $kena_num -= $data[1];
        }

        fclose($handle);
        
        $kena_ser = serialize($kena);
        // update db
        if ($update) {
          $import = "UPDATE $av_kena SET kena = '$kena_ser' WHERE klados = '" . $_POST['klados'] . "'";
        } else {
          $import="INSERT into $av_kena(klados,kena) values('".$_POST['klados']."','".$kena_ser."')";
        }
        $result = mysqli_query($mysqlconnection, $import);
        if ($result){
            print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
            echo "Έγινε εισαγωγή $kena_num κενών κλάδου ".$_POST['klados']." σε $num σχολεία.<br>";
        }
        else
        {
            echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
            echo "Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.<br>";
            echo mysqli_error($mysqlconnection) ? "Μήνυμα λάθους:".mysqli_error($mysqlconnection) : '';
            //echo $num ? "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>" : '';
        }
    }
    else {
        echo "Δεν επιλέξατε αρχείο<br><br>";
    }
                
    echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
?>
</div>
</body>
</html>
	