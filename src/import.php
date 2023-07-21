<?php
  session_start();
  header('Content-type: text/html; charset=utf-8'); 
  Require "../config.php";
  require_once 'functions.php';
  
  include("class.login.php");
  $log = new logmein();
  // if not logged in
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;
  // check if admin (only admin can change params)
  if (!is_admin()){
    echo "<h3>ΣΦΑΛΜΑ: Η πρόσβαση επιτρέπεται μόνο στο διαχειριστή...</h3>";
    echo "<a class='btn btn-info' href='admin.php'>Επιστροφή</a>";
    die();
  }

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
	echo "<h2> Εισαγωγή δεδομένων στη βάση δεδομένων </h2>";
  echo "ΠΡΟΣΟΧΗ: Η δυνατότητα αυτή διαγράφει όλα τα δεδομένα απο τον πίνακα που θα επιλεγεί, πριν εισάγει σε αυτόν τα νέα.<br><br>";
  $rows = count_rows($mysqlconnection);
  echo "(Αριθμός εγγραφών που υπάρχουν στη Β.Δ.: Υπάλληλοι: ".$rows['emp'].', Σχολεία: '.$rows['sch'].', Δήμοι: '.$rows['dimos'].')<br>';
	echo "<br><strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη...</strong><br><br>";
    echo "<form enctype='multipart/form-data' action='import.php' method='post'>";
    echo "Όνομα αρχείου προς εισαγωγή:<br />\n";
    echo "<input size='50' type='file' name='filename'><br />\n";
    echo "<br>Τύπος (πίνακας) δεδομένων:<br>";
    echo "<input type='radio' name='type' value='1'>Υπάλληλοι για απόσπαση&nbsp;<a href='../files/apo_employee.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='4'>Υπάλληλοι για βελτίωση&nbsp;<a href='../files/apo_employee_velt.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='5'>Αναπληρωτές&nbsp;<a href='../files/apo_anapl.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='2'>Σχολεία&nbsp;<a href='../files/apo_school.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='3' >Δήμοι&nbsp;<a href='../files/apo_dimos.csv'>(δείγμα)</a><br>";
    print "<input type='submit' name='submit' class='btn btn-success' value='Μεταφόρτωση'></form>";
    echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
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
        switch ($_POST['type'])
        {
            case 1:
            case 4:
            case 5:
                mysqli_query($mysqlconnection, "DELETE FROM $av_emp WHERE am <> '$av_admin'");
                $tbl = $av_emp;
                mysqli_query($mysqlconnection, "TRUNCATE $av_ait");
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
                
                switch ($_POST['type']) {
                  case 1:
                  case 4:
                    $tblcols = 10;
                    break;
                  case 5:
                    $tblcols = 7;
                    break;
                  case 2:
                    $tblcols = 5;
                    break;
                  case 3:
                    $tblcols = 1;
                    break;
                }

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
                  $import="INSERT into $av_emp(name,surname,patrwnymo,klados,am,afm,org,eth,mhnes,hmeres) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]')";
                  break;
                case 4:
                  $import="INSERT into $av_emp(name,surname,patrwnymo,klados,am,afm,org,moria,entopiothta,synyphrethsh) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]',$data[7],'$data[8]','$data[9]')";
                  break;
                case 5:
                  $import="INSERT into $av_emp(name,surname,patrwnymo,klados,afm,seira,ada) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]')";
                  break;
                // schools
                case 2:
                  $import="INSERT into $av_sch(name,kwdikos,dim,omada,inactive) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]')";
                  break;
                // dimoi
                case 3:
                  $import="INSERT into $av_dimos(name) values('$data[0]')";
                  break;
            }
            // set max execution time (for large files)
            set_time_limit (480);
                $ret = mysqli_query($mysqlconnection, $import);
            $num++;
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
        echo "Δεν επιλέξατε αρχείο<br><br>";
    }
                
    echo "<a href='import.php'>Επιστροφή</a>";
?>
</div>
</body>
</html>
	