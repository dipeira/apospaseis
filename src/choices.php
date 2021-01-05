<?php
  session_start();
  header('Content-type: text/html; charset=utf-8'); 
  Require_once "../config.php";
  require_once "functions.php";
  require_once('head.php');

  // check if admin 
    if ($_SESSION['user']==$av_admin || $_SESSION['user']=='admin')
    {
        //$isadmin = 1;
        $page = 'admin.php';
	echo '<script type="text/javascript">';
	echo 'window.location.href="'.$page.'";';
	echo '</script>';
	}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

<script>
  $(function () {
    $('#src').on('submit', function (e) {
      e.preventDefault();
      $.ajax({
        type: 'post',
        url: 'save.php',
        data: $('#src').serialize(),
        success: function (data) {
          Swal.fire({
            title: data.title,
            html: data.message,
            icon: data.type,
            confirmButtonText: 'OK'
          }).then((result) => {
            location.reload();  
          });
        },
        dataType:'json'
      });
    });
});
</script>

<html>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
  <link href="css/select2-bootstrap4.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
  <body>
<?php
  $timeout = 0;

  include("class.login.php");
  $log = new logmein();
  //if($log->logincheck($_SESSION['loggedin']) == false)
  if($_SESSION['loggedin'] == false)
  //if($log->logincheck($_SESSION['loggedin']) == false && $_SESSION['timeout']<time())
  {   
      //header("Location: login.php");
      $page = 'login.php';
	echo '<script type="text/javascript">';
	echo 'window.location.href="'.$page.'";';
	echo '</script>';
  }
  else
      $loggedin = 1;
  
  if (time() > $_SESSION['timeout'])  
    $timeout = 1;
  $diff = $_SESSION['timeout'] - time();
  if ($timeout){
//    echo "<body>Η συνεδρία σας έχει λήξει.<br>Παρακαλώ κάντε είσοδο στο σύστημα.";
//    echo "<form action='login.php'><input type='submit' value='Είσοδος'></form></body></html>";
//    exit;
    //header("Location: login.php");
    $page = 'login.php';
	echo '<script type="text/javascript">';
	echo 'window.location.href="'.$page.'";';
	echo '</script>';
  }
?>
  <!-- <div id="left1">
      <!-- <?php include('help2.php'); ?> -->
  <!-- </div> -->
  <!-- <div id="right1"> -->
  <div class="container">
    
  
<?php
  if ($loggedin)
  {
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password,$db_name);
    mysqli_set_charset($mysqlconnection,"utf8");
    echo "<div class='row'>";
    echo "<center><h2>$av_title ($av_foreas)</h2></center>";
    echo "</div>";
    
    if ($av_type == 3) {
      $query = "SELECT * from $av_emp WHERE afm = '".$_SESSION['loggedin']."'";  
    } else {
      $query = "SELECT * from $av_emp WHERE am = ".$_SESSION['user'];
    }
    
    $result = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($result);
    
    $name = $row['name'];
    $surname = $row['surname'];
    $patrwnymo = $row['patrwnymo'];
    $klados = $row['klados'];
    $id = $row['id'];
    if ($av_type == 3){
      $afm = $_SESSION['loggedin'];
    } else {
      $am = $_SESSION['user'];
    }
    $organ_code = $row['org'];
    $organ = getSchooledc($organ_code, $mysqlconnection);
    $moria = $row['moria'];
    $entopiothta = $row['entopiothta'];
    $synyphrethsh = $row['synyphrethsh'];
    if ($av_type == 3) {
      $ada = $row['ada'];
      // override av_choices
      $av_choices = getKenaSchoolNumber($klados,$ada,$mysqlconnection);
      if ($av_choices == 0){
        $nokena = true;
      }
    }
    // find school team if apospaseis
    if ($av_type == 1)
    {
      $qry1 = "SELECT omada from $av_sch WHERE kwdikos = '$organ_code'";
      $res1 = mysqli_query($mysqlconnection, $qry1);
      $row = mysqli_fetch_assoc($res1);
      $omada = $row['omada'];
    }
    if ($av_athmia)
    {
        // if (strpos($klados,"ΠΕ6") !== false)
        //     $dim = 1;
        // else
        //     $dim = 2;
        $dim = 0;
    }
    else
        $dim = 0;
    ?>
    <script type="text/javascript">
        function stopRKey(evt) {
            var evt = (evt) ? evt : ((event) ? event : null);
            var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
            if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
        }

        document.onkeypress = stopRKey;

        function toggleFormElements() {
            var inputs = document.getElementsByTagName("input");
            ret=confirm("Είστε σίγουροι;");
            if (ret){
                for (var i = 0; i < 40; i++) {    
                    inputs[i].value = '';
                    if (inputs[i].disabled == true)
                        inputs[i].disabled = false;
                    else
                        inputs[i].disabled = true;
                }
            }
            mytag = document.getElementById("null_btn").value;
            if (inputs[0].disabled == 1)
                document.getElementById("null_btn").value = "Απενεργοποίηση Αρνητικής Δήλωσης";
            else
                document.getElementById("null_btn").value = "Αρνητική Δήλωση";
            
        }
    </script>
    <?php
    //check if employee has submitted aitisi
    $query = "SELECT * from $av_ait WHERE emp_id=$id";
    $result = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($result)>0)
    {
        $has_aitisi = 1;
        $row = mysqli_fetch_assoc($result);
        $submitted = $row['submitted'];
    }
    
    // if user has already saved an application
    if ($has_aitisi)
    {
        if (strlen($row['choices']) > 0) {
            $sch_arr = unserialize($row['choices']);
            for ($i = 0; $i < $av_choices; $i++) {
                ${'s'.$i} = strlen($sch_arr[$i]) > 0 ? getSchooledc($sch_arr[$i],$mysqlconnection) : null;
            }
        }
        
        // 26-07-2013: eksairesh gia genikh 2 eidikh agwgh
        $apospash = $row['apospash'];
        if ($apospash == 1)
          $omada = 0;
    }	
        if ($submitted)
            echo "<h3><center>Η αίτηση έχει υποβληθεί και δεν μπορείτε να την επεξεργαστείτε.</center></h3>";
        echo "<div class='row'>";            
        //echo "<center>";
        
        echo "<table id=\"mytbl\" class=\"table table-striped table-bordered\" border=\"2\">\n";
        if ($av_type == 1)
            echo "<thead><th colspan=2>Βήμα 2: Υποβολή προτιμήσεων</th></thead>";
        else
            echo "<thead><th colspan=2>Υποβολή προτιμήσεων</th></thead>";
        echo "<tr><td>Ονοματεπώνυμο Εκπ/κού:</td><td >".$name." ".$surname."</td></tr>";
        echo "<tr><td>Πατρώνυμο: </td><td >".$patrwnymo."</td></tr>";
        echo "<tr><td>Κλάδος: </td><td >".$klados."</td></tr>";
        if ($av_type != 3){
          echo "<tr><td>A.M.: </td><td >".$am."</td></tr>";
          echo "<tr><td>Οργανική θέση: </td><td >".$organ."</td></tr>";
        }
        if ($av_type == 2) {
          echo "<tr><td>Μόρια βελτίωσης: </td><td >".$moria."</td></tr>";
          echo "<tr><td>Δήμος εντοπιότητας: </td><td >".$entopiothta."</td></tr>";
          echo "<tr><td>Δήμος συνυπηρέτησης: </td><td >".$synyphrethsh."</td></tr>";
        }
        echo "<tr><td colspan=2><center><strong>Προτιμήσεις</strong></center></td></tr>";
        
        //echo "<tr><td colspan=2><center><INPUT TYPE='button' onclick='toggleFormElements(true)' name='submit' VALUE='Αρνητική Δήλωση'></center></td></tr>\n";
        
        // if user has submitted
        if ($submitted)
        {
            $sch_arr = array();
            for ($i = 0; $i < $av_choices; $i++) {
              if (strlen(${'s'.$i}) == 0) continue;
              echo "<tr><td>".($i+1)."η προτίμηση</td><td>".${'s'.$i}."</td></tr>\n";
              array_push($sch_arr, ${'s'.$i});
            }
            echo "<tr><td colspan=2><small>Υποβλήθηκε στις: ".  date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
            $ser = serialize($sch_arr);
            if ($av_type == 1){
              echo "<tr><td colspan=2><center>";
              echo "<form action='print.php' method='POST'>";
              echo "<input type='hidden' name = 'cred_arr' value='$ser_cred'>";
              echo "<input type='hidden' name = 'sch_arr' value='$ser'>";
              echo "<input type='submit' class='btn btn-success' value='Εκτύπωση'></form></center></td></tr>";
            } else {
              echo "<tr><td colspan=2><center><input type='button' class='btn btn-success' value='Εκτύπωση' onclick='javascript:window.print()' /></center></td></tr>";
            }
            if ($av_type == 1)
                //echo "<tr><td colspan=2><center><form action='criteria.php'><input type='submit' class='btn btn-info' value='Επιστροφή στο Βήμα 1'></form></center></td></tr>";
                echo "<tr><td colspan=2><center><a href='criteria.php' class='btn btn-info'>Επιστροφή στο Βήμα 1</a></center></td></tr>";
            echo "<tr><td colspan=2><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' class='btn btn-danger' value='Έξοδος'></form></center></td></tr>";
        }
        // if not submitted
        else
        {
            echo "<form id='src' name='src' action='save.php' method='POST' >\n";
            ?>
                <script language="javascript" type="text/javascript">
                  $(function () {
                    $('#submitbtn').on('click', function (e) {
                      e.preventDefault();
                        swal.fire({
                        title: 'Είστε σίγουροι ότι θέλετε να υποβάλετε οριστικά την αίτηση;',
                        text: "Δεν μπορείτε να αναιρέσετε αυτήν την ενέργεια!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ναι',
                        cancelButtonText: 'Όχι',
                      }).then((result) => {
                        if (result.value) {
                          $.ajax({
                          type: 'post',
                          url: 'save.php',
                          data: $('#src').serialize() + '&submitbtn=true',
                          success: function (data) {
                            //location.reload();
                            Swal.fire({
                              title: data.title,
                              html: data.message,
                              icon: data.type,
                              confirmButtonText: 'OK'
                            }).then((result) => {
                              location.reload();  
                            });
                          },
                          dataType:'json'
                        });
                        } else if (
                          /* Read more about handling dismissals below */
                          result.dismiss === Swal.DismissReason.cancel
                        ) {
                          swal.fire(
                            'Ακυρώθηκε',
                            'Η υποβολή ακυρώθηκε από τον χρήστη...',
                            'error'
                          )
                          return false;
                        }
                      })
                    });
                  });
                  $(function () {
                    $('[data-toggle="tooltip"]').tooltip()
                  })
                </script>
                   
                  <?php
            if ($av_type == 1)
            {
                for ($i=0; $i<$av_choices; $i++)
                  echo "<tr><td>".($i+1)."η Προτίμηση</td><td>".getSchools($i+1, $dim, $omada, $mysqlconnection, ${"s".$i});
            }
            // if anaplirotes
            else if ($av_type == 3) {
              if ($nokena) {
                echo "<tr><td colspan=2><h3>Δεν έχουν καταχωρηθεί κενά για την ειδικότητά σας. Παρακαλώ προσπαθήστε αργότερα ή επικοινωνήστε με τη Δ/νση.</h3></td></tr>";
              } else {
                for ($i=0; $i<$av_choices; $i++)
                  echo "<tr><td>".($i+1)."η Προτίμηση</td><td>".getKenaSchools($i+1, $klados, $ada, $mysqlconnection, ${"s".$i});              
              }
            } else
            {
                for ($i=0; $i<$av_choices; $i++)
                  echo "<tr><td>".($i+1)."η Προτίμηση</td><td>".getSchools($i+1, $dim, 0, $mysqlconnection, ${"s".$i});
            }
            if ($has_aitisi)
                  echo "<tr><td colspan=2><small>Τελευταία ενημέρωση: ". date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
            echo "<input type='hidden' name = 'id' value='$id'>";
            echo "<tr><td colspan=2><center><INPUT TYPE='submit' name='save' class='btn btn-success' VALUE='Αποθήκευση'></center></td></tr>";
            if ($av_type == 1)
                //echo "<tr><td colspan=2><center><INPUT TYPE='submit' name='prev' class='btn btn-info' VALUE='Επιστροφή στο Βήμα 1'></center></td></tr>";
                echo "<tr><td colspan=2><center><a href='criteria.php' class='btn btn-info'>Επιστροφή στο Βήμα 1</a></center></td></tr>";
            if (!$has_aitisi){
              echo "<tr><td colspan=2><center><button TYPE='submit' id='submitbtn' name='submitbtn' class='btn btn-warning' disabled data-toggle='tooltip' data-placement='left' title='Πρέπει να αποθηκεύσετε πριν υποβάλετε οριστικά'>Οριστική Υποβολή</button></center></td>\n";
            } else {
              echo "<tr><td colspan=2><center><button TYPE='submit' id='submitbtn' name='submitbtn' class='btn btn-warning'>Οριστική Υποβολή</button></center></td>\n";
            }
            echo "</tr>\n";
            echo "</form>";
            echo "<tr><td colspan=2><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' class='btn btn-danger' value='Έξοδος'></form></center></td></tr>";
        }
        echo "</table>";
        //echo "</center>";
        echo "</div>";
    //} //has aitisi
     mysqli_close($mysqlconnection);   
    }
?>
  </div>
</center>
</div>
  </body>
</html>

<script type="text/javascript">
  $('select').select2({
      placeholder: 'Επιλογή σχολείου',
      allowClear: true,
      theme: 'bootstrap4'
  });
</script>