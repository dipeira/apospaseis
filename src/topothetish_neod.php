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

  define(MORIA_SYNYP, 2);
  define(MORIA_ENTOP, 2);
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
            order: [[ 3, "desc" ], [ 4, "desc"], [ 5, "asc"]],
            buttons: [
              { extend: 'copy', text: 'Αντιγραφή',  },
              { extend: 'excel', text: 'Εξαγωγή σε excel', filename: 'export' },
              { extend: 'print', text: 'Εκτύπωση', }
            ],
            "lengthMenu": [ [25, 50, -1], [25, 50, "Όλα"] ]
        });
        $('.kenatable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Greek.json"
            },
            dom: 'Bflrtip',
            buttons: [
              { extend: 'copy', text: 'Αντιγραφή',  },
              { extend: 'excel', text: 'Εξαγωγή σε excel', filename: 'export' },
              { extend: 'print', text: 'Εκτύπωση', }
            ],
            "lengthMenu": [ [25, 50, -1], [25, 50, "Όλα"] ]
        });
    });
  </script>
  <body>
  <div class="container">
<?php
if (!isset($_POST['submit']))
{
	echo "<h2>Τοποθέτηση νεοδιόριστων</h2>";
  echo "<form enctype='multipart/form-data' action='topothetish_neod.php' method='post'>";
  echo "<br>Επιλογή κλάδου προς τοποθέτηση:<br>";
  kladoi_select($mysqlconnection);
  echo "<br>";
  echo "Επιλογή ΑΔΑ προς τοποθέτηση:<br>";
  ada_select($mysqlconnection);
  print "<br><input type='submit' name='submit' class='btn btn-success' value='Τοποθέτηση'></form><br>";
  // check existing kena
  uploaded_kena($mysqlconnection);
  
  print "<a href='import_kena.php' class='btn btn-sm btn-info'>Εισαγωγή κενών</a>";
  
  //echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
  echo "</form>";
  echo "<br><br>";
  echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
	exit;
}

    
  // if selection is made
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
    
    // get employees by moria (oikogeneiaka)
    $employees = Array();
    $query = "select surname,afm,moria,synyphrethsh,entopiothta,neod_yphr,seira from $av_emp e JOIN $av_ait a ON e.id = a.emp_id WHERE klados='$klados' AND ada = '$ada' and a.submitted = 1 ORDER BY moria DESC";
    $result = mysqli_query($mysqlconnection, $query);

    while ($row = mysqli_fetch_assoc($result)){
      $afm = $row['afm'];
      //$row['moria'] = str_replace('.', ',', $row['moria']);
      unset($row['afm']);
      $employees[$afm] = $row;
    }
// print_r($employees);
// echo "<br><br>";

    // get choices
    $afms = implode(",", array_keys($employees));
    $choices = Array();
    $query = "select e.afm,a.choices from $av_ait a JOIN $av_emp e ON e.id = a.emp_id WHERE e.afm in ($afms) AND a.submitted = 1";
    $result = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_assoc($result)){
      $choices[$row['afm']] = unserialize($row['choices']);
    }
// echo "choices: ";
// print_r($choices);
// echo "<br><br>";
    // get school dimoi
    $school_dimoi = Array();
    $query = "select kwdikos,dimos from $av_sch";
    $result = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_assoc($result)){
      $school_dimoi[$row['kwdikos']] = $row['dimos'];
    }
// print_r($school_dimoi);
// echo "<br><br>";
    // add moria to choices
    $schools_with_moria = Array();
    $choices_with_moria = Array();
    foreach ($choices as $afm => $schools) {
      $employee = $employees[$afm];
      
      $with_moria = Array();
      foreach ($schools as $sch) {
        $moria = $employee['moria'];
        if ($school_dimoi[$sch] == $employee['synyphrethsh']){
          $moria += MORIA_SYNYP;
        }
        if ($school_dimoi[$sch] == $employee['entopiothta']){
          $moria += MORIA_ENTOP;
        }
        $with_moria[$sch] = $moria;
        $schools_with_moria[$sch][] = Array('surname'=>$employee['surname'], 'afm'=>$afm, 'moria'=>$moria, 'yphr'=>$employee['neod_yphr'], 'seira'=>$employee['seira']);
      }
      $choices_with_moria[$afm] = $with_moria;
    }
    // echo "choices with moria: ";
    // print_r($choices_with_moria);
    // echo "<br><br>";
    // echo "schools with moria: ";
    // print_r($schools_with_moria);
    // echo "<br><br>";
    


    // sort schools_with_moria by moria, yphr, seira
    foreach ($schools_with_moria as $sch => $school_moria) {
      //$sorted = php_array_sort($school_moria, array('moria','yphr','seira'), 'SORT_NUMERIC');
      $moria  = array_column($school_moria, 'moria');
      $yphr = array_column($school_moria, 'yphr');
      $seira = array_column($school_moria, 'seira');

      array_multisort($moria, SORT_DESC, $yphr, SORT_DESC, $seira, SORT_ASC, $school_moria);
      // echo "<h5>$sch - Sorted</h5>";
      // print_r($school_moria);
      $schools_with_moria[$sch] = $school_moria;
    }
    // echo "<br><br>Sorted:<br>";
    // print_r($schools_with_moria);

    // make placements
    $placements = Array();
    $placed = $unplaced = $num = 0;
    // run through employees
    foreach ($employees as $emp_afm => $value) {
      $num++;
      // run through employee choices
      foreach ($choices[$emp_afm] as $choice) {
        // ensure kena exist
        if ($kena[$choice] < 0){
          $school_with_moria = $schools_with_moria[$choice];

          // echo "<h3>$emp_afm - $choice</h3>";
          // print_r($school_with_moria);

          // run through school's moria
          foreach ($school_with_moria as $arr) {

            // echo "<br>".$arr['surname'].": ".$emp_afm;
            // echo "<br>inarray: ".array_key_exists($arr['afm'],$placements);
            
            if ($arr['afm'] = $emp_afm && array_key_exists($emp_afm,$placements)){
              break;
            } else {
              $kena[$choice] += 1;
              $placements[$emp_afm] = $choice;

              // echo "<br> **** Placed $emp_afm at $choice";

              $placed++;
              break;
            }
          }
          
        }
        //$placements[$emp_afm] = 'none';

      }
    }
    echo "<h3>Placements</h3>";
    print_r($placements);
    // die();

    ?>
    <div id="accordion">
      <div class="card">
      <div class="card-header" id="headingOne">
        <h5 class="mb-0">
          <button class="btn btn-link " data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Τοποθετήσεις
          </button>
        </h5>
      </div>
      <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
        <div class="card-body">
          <?php
          echo "<h3>Τοποθετήσεις</h3>";
          placements_neod_tbl($placements, $mysqlconnection);
          $unplaced = $num - $placed;
          echo "Στατιστικά: Τοποθετήθηκαν: $placed, Δεν τοποθετήθηκαν: $unplaced, Σύνολο: $num";
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
          <button class="btn btn-link" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            Αρχικά κενά
          </button>
        </h5>
      </div>

      <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
        <div class="card-body">
          <?php
          echo "<h3>Αρχικά κενά</h3>";
          kena_tbl($initial_kena, $mysqlconnection);
          echo "Σύνολο: ".abs(array_sum($initial_kena));
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
	