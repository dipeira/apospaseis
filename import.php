<?php
  session_start();
  header('Content-type: text/html; charset=utf-8'); 
  Require "config.php";
  require_once 'functions.php';
  
  include("class.login.php");
  $log = new logmein();
  // if not logged in or not admin
  if($log->logincheck($_SESSION['loggedin']) == false || $_SESSION['user'] != $av_admin)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;
?>
<html>
  <?php require_once('head.php'); ?>
  <body>
<?php
if (!isset($_POST['submit']))
{
	echo "<h2> Εισαγωγή δεδομένων στη βάση δεδομένων </h2>";
	echo "ΠΡΟΣΟΧΗ: Η δυνατότητα αυτή διαγράφει όλα τα δεδομένα απο τον πίνακα που θα επιλεγεί, πριν εισάγει σε αυτόν τα νέα.<br><br>";
	echo "<strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη...</strong><br><br>";
        echo "<form enctype='multipart/form-data' action='import.php' method='post'>";
        echo "Όνομα αρχείου προς εισαγωγή:<br />\n";
        echo "<input size='50' type='file' name='filename'><br />\n";
        echo "<br>Τύπος (πίνακας) δεδομένων:<br>";
        echo "<input type='radio' name='type' value='1'>Υπάλληλοι<br>";
        echo "<input type='radio' name='type' value='2'>Σχολεία<br>";
        echo "<input type='radio' name='type' value='3' >Δήμοι<br>";
        print "<input type='submit' name='submit' value='Μεταφόρτωση'></form>";
        echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
        echo "</form>";
        echo "<br><br>";
        echo "<a href='admin.php'>Επιστροφή</a>";
	exit;
}

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    mysqli_set_charset($mysqlconnection,"utf8");
    
    //Upload File
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";

        switch ($_POST['type'])
        {
            case 1:
                $del_qry = "DELETE FROM $av_emp WHERE am <> '$av_admin'";
                $tbl = $av_emp;
                break;
            case 2:
                $del_qry = "TRUNCATE $av_sch";
                $tbl = $av_sch;
                break;
            case 3:
                $del_qry = "TRUNCATE $av_dimos";
                $tbl = $av_dimos;
                break;
        }
        
        //Import uploaded file to Database
        $handle = fopen($_FILES['filename']['tmp_name'], "r");
        // check columns & skip headers line
        $data = fgetcsv($handle, 1000, ";");
        $csvcols = count($data);
        $qry = "SELECT count(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '$db_name' AND table_name = '$tbl'";
        $res = mysqli_query($mysqlconnection, $qry);
        $tblcols = mysqli_num_fields($res);
        // if error exit, else proceed to data deletion
        if ($csvcols <> $tblcols)
        {
            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
            $ret = 0;
            echo "<a href='admin.php'>Επιστροφή</a>";
            exit;
        }
        else
        {
            mysqli_query($mysqlconnection, $del_qry);
        }
        
        $num = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            switch ($_POST['type']){
                // employees
                case 1:
                    $import="INSERT into $av_emp(id,name,surname,patrwnymo,klados,am,afm,org,eth,mhnes,hmeres,lastlogin) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]')";
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

        fclose($handle);
        if ($ret){
            echo "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία</h3>";
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
                
    echo "<a href='login.php'>Επιστροφή</a>";
    exit;
?>

</body>
</html>
	