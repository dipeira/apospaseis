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
	echo "<h2>Τοποθέτηση αναπληρωτών</h2>";
  echo "<form enctype='multipart/form-data' action='topothetish_anapl.php' method='post'>";
  echo "<br>Επιλογή κλάδου προς τοποθέτηση:<br>";
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
  $qry = "select * from $av_kena";
  $result = mysqli_query($mysqlconnection, $qry);
  echo "<h4>Καταχωρημένα κενά</h4>";
  echo "<table class='table table-striped table-hover table-sm' border='1'>";
  echo "<thead><th>Κλάδος</th><th>Ημερομηνία καταχώρησης</th></thead>";
  while ($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['klados']."</td><td>".$row['updated']."</td></tr>";
  }
    
  echo "</table>";
  
  print "<a href='import_kena.php' class='btn btn-info'>Εισαγωγή κενών</a>";
  print "<br><br><input type='submit' name='submit' class='btn btn-success' value='Τοποθέτηση'></form>";
  //echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
  echo "</form>";
  echo "<br><br>";
  echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
	exit;
}

    
  //Upload File
  if (isset($_POST['klados'])) {
    $klados = $_POST['klados'];

    // check if kena exist
    $qry = "SELECT * FROM $av_kena WHERE klados = '$klados'";
    $result = mysqli_query($mysqlconnection, $qry);
    if (mysqli_num_rows($result) == 0) {
      echo "<h2>Σφάλμα</h2>";
      echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
      die();
    }


    // get kena
    $query = "select kena from $av_kena WHERE klados='$klados'";
    $result = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($result);
    
    $kena = unserialize($row['kena']);
    $initial_kena = $kena;
    
    // get employees by seira
    $employees = Array();
    $query = "select afm,seira from $av_emp WHERE klados='$klados' AND seira > 0 ORDER BY seira";
    $result = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_assoc($result)){
      $employees[] = $row['afm'];
    }

    // get choices
    $afms = join(',',$employees);
    $choices = Array();
    $query = "select e.afm,a.choices from $av_ait a JOIN $av_emp e ON e.id = a.emp_id WHERE e.afm in ($afms) AND a.submitted = 1";
    $result = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_assoc($result)){
      $choices[$row['afm']] = unserialize($row['choices']);
    }

    
    // make placements
    $placements = Array();
    foreach ($employees as $afm) {
      foreach ($choices[$afm] as $choice) {
        
        //echo "<br>".$kena[$choice]."<br>";
        if ($kena[$choice] < 0){
          $kena[$choice] += 1;
          $placements[$afm] = $choice;
          break;
        } else {
            $placements[$afm] = 'none';
        }
      }
    }
    echo "<br><br>initial kena<br>";
    print_r($initial_kena);
    echo "<br><br>final kena<br>";
    print_r($kena);
    echo "<br><br>Placements<br>";
    print_r($placements);
      die();
      

      /*
      for x in sorted_anapl:
   prot = anapl_prot[x]
   for y in prot:
     if kena.get(str(y)) > 0:
       kena[str(y)] -= 1
       topo[x] = y
       break
     topo[x] = "none"
      */
      
      $update = false;
      
      
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
	