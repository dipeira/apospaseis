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
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title><?php echo $av_title." ($av_foreas) - Διαχείριση"; ?></title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<!--https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js
https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js
 -->
  <script>
    $(document).ready(function() {
        $('.toptable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Greek.json"
            },
            dom: 'Bflrtip',
            buttons: [
              {
                extend: 'copy',
                text: 'Αντιγραφή',
              },
              {
                extend: 'excel',
                text: 'Εξαγωγή σε excel',
                filename: 'export'
              },
              {
                extend: 'print',
                text: 'Εκτύπωση',
              }
            ]
        });
    });
  </script>
  <body>
  <div class="container">
<?php
if (!isset($_POST['submit']))
{
	echo "<h2>Τοποθέτηση αναπληρωτών</h2>";
  echo "<form enctype='multipart/form-data' action='topothetish_anapl.php' method='post'>";
  echo "<br>Επιλογή κλάδου προς τοποθέτηση:<br>";
  kladoi_select($mysqlconnection);
  echo "<br>";
  echo "Επιλογή ΑΔΑ προς τοποθέτηση:<br>";
  ada_select($mysqlconnection);
  print "<br><br><input type='submit' name='submit' class='btn btn-success' value='Τοποθέτηση'></form>";
  // check existing kena
  uploaded_kena($mysqlconnection);
  
  print "<a href='import_kena.php' class='btn btn-sm btn-info'>Εισαγωγή κενών</a>";
  
  //echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
  echo "</form>";
  echo "<br><br>";
  echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
	exit;
}

    
  //Upload File
  if (isset($_POST['klados']) && isset($_POST['ada'])) {
    $klados = $_POST['klados'];
    $ada = $_POST['ada'];
    echo "<h1>Τοποθετήσεις κλάδου $klados (ΑΔΑ: $ada)</h1>";
    // check if kena exist
    $qry = "SELECT * FROM $av_kena WHERE klados = '$klados' AND ada = '$ada'";
    $result = mysqli_query($mysqlconnection, $qry);
    if (mysqli_num_rows($result) == 0) {
      echo "<h2>Σφάλμα: Δεν έχουν καταχωρηθεί κενά σχολείων για το συνδυασμό κλάδου-ΑΔΑ που επιλέξατε...</h2>";
      echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
      die();
    }

    // get kena
    $query = "select kena from $av_kena WHERE klados='$klados' AND ada = '$ada'";
    $result = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($result);
    
    $kena = unserialize($row['kena']);
    $initial_kena = $kena;
    
    // get employees by seira
    $employees = Array();
    $query = "select afm,seira from $av_emp e JOIN $av_ait a ON e.id = a.emp_id WHERE klados='$klados' AND ada = '$ada' AND seira > 0 and a.submitted = 1 ORDER BY seira";
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
    $placed = $unplaced = $num = 0;
    foreach ($employees as $afm) {
      $num++;
      foreach ($choices[$afm] as $choice) {
        if ($kena[$choice] < 0){
          $kena[$choice] += 1;
          $placements[$afm] = $choice;
          $placed++;
          break;
        }
        $placements[$afm] = 'none';
      }
    }

    ?>
    <div id="accordion">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Αρχικά κενά
        </button>
      </h5>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">
        <?php
        echo "<h3>Αρχικά κενά</h3>";
        kena_tbl($initial_kena, $mysqlconnection);
        echo "Σύνολο: ".abs(array_sum($initial_kena));
        ?>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Τελικά κενά
        </button>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
      <div class="card-body">
        <?php
         echo "<h3>Τελικά κενά</h3>";
         kena_tbl($kena, $mysqlconnection);
         echo "Σύνολο: ".abs(array_sum($kena));
        ?>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingThree">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Τοποθετήσεις
        </button>
      </h5>
    </div>
    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
      <div class="card-body">
        <?php
        echo "<h3>Τοποθετήσεις</h3>";
        placements_tbl($placements, $mysqlconnection);
        $unplaced = $num - $placed;
        echo "Στατιστικά: Τοποθετήθηκαν: $placed, Δεν τοποθετήθηκαν: $unplaced, Σύνολο: $num";
        ?>
      </div>
    </div>
  </div>
</div>
<?php
    // end of debug
  }
  else {
      echo "Δεν επιλέξατε αρχείο<br><br>";
  }
                
    echo "<br><a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
?>
</div>
</body>
</html>
	