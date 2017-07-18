<?php
  session_start();
  header('Content-type: text/html; charset=utf-8'); 
  Require_once "config.php";
  require_once "functions.php";
  // check if admin 
    if ($_SESSION['user']==$av_admin)
    {
        //$isadmin = 1;
        $page = 'admin.php';
	echo '<script type="text/javascript">';
	echo 'window.location.href="'.$page.'";';
	echo '</script>';
	}
?>
<html>
  <?php require_once('head.php'); ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
  <body>
<?php
  $timeout = 0;

  include("class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false)
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
  <div id="left1">
      <?php include('help2.php'); ?>
  </div>
  <div id="right1">
<?php
  if ($loggedin)
  {
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password,$db_name);
    mysqli_set_charset($mysqlconnection,"utf8");
  
    echo "<center><h2>$av_title ($av_foreas)</h2></center>";
    
    $query = "SELECT * from $av_emp WHERE am = ".$_SESSION['user'];
    
    $result = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($result);
    
    $name = $row['name'];
    $surname = $row['surname'];
    $patrwnymo = $row['patrwnymo'];
    $klados = $row['klados'];
    $id = $row['id'];
    $am = $_SESSION['user'];
    $organ_code = $row['org'];
    $organ = getSchooledc($organ_code, $mysqlconnection);
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
        if (strpos($klados,"ΠΕ6") !== false)
            $dim = 1;
        else
            $dim = 2;
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
        echo "<center>";
        
        echo "<table id=\"mytbl\" class=\"imagetable\" border=\"2\">\n";
        if ($av_type == 1)
            echo "<thead><th colspan=4>Βήμα 2: Υποβολή προτιμήσεων</th></thead>";
        else
            echo "<thead><th colspan=4>Υποβολή προτιμήσεων</th></thead>";
        echo "<tr><td>Ονοματεπώνυμο Εκπ/κού:</td><td colspan=3>".$name." ".$surname."</td></tr>";
        echo "<tr><td>Πατρώνυμο: </td><td colspan=3>".$patrwnymo."</td></tr>";
        echo "<tr><td>Κλάδος: </td><td colspan=3>".$klados."</td></tr>";
        echo "<tr><td>A.M.: </td><td colspan=3>".$am."</td></tr>";
        echo "<tr><td>Οργανική θέση: </td><td colspan=3>".$organ."</td></tr>";
        echo "<tr><td colspan=4><center><strong>Προτιμήσεις</strong></center></td></tr>";
        
        //echo "<tr><td colspan=4><center><INPUT TYPE='button' onclick='toggleFormElements(true)' name='submit' VALUE='Αρνητική Δήλωση'></center></td></tr>\n";
        
        // if user has submitted
        if ($submitted)
        {
            $sch_arr = array();
            for ($i = 0; $i < $av_choices; $i++) {
              if (strlen(${'s'.$i}) == 0) continue;
              echo "<tr><td>".($i+1)."η προτίμηση</td><td>".${'s'.$i}."</td></tr>\n";
              array_push($sch_arr, ${'s'.$i});
            }
            echo "<tr><td colspan=4><small>Υποβλήθηκε στις: ".  date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
            $ser = serialize($sch_arr);
            echo "<tr><td colspan=4><center><form action='print.php' method='POST'><input type='hidden' name = 'cred_arr' value='$ser_cred'><input type='hidden' name = 'sch_arr' value='$ser'><input type='submit' value='Εκτύπωση'></form></center></td></tr>";
            if ($av_type == 1)
                echo "<tr><td colspan=4><center><form action='index.php'><input type='submit' value='Επιστροφή στο Βήμα 1'></form></center></td></tr>";
            echo "<tr><td colspan=4><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' value='Έξοδος'></form></center></td></tr>";
        }
        // if not submitted
        else
        {
            echo "<form id='src' name='src' action='save.php' method='POST' >\n";
            ?>
                <script language="javascript" type="text/javascript">
                    function myaction(){
                        r=confirm("Είστε σίγουροι ότι θέλετε να υποβάλετε οριστικά την αίτηση;");
                        if (r==false){
                            return false;
                        }
                    }
                </script>
                   
                  <?php
            if ($av_type == 1)
            {
                for ($i=0; $i<$av_choices; $i++)
                  echo "<tr><td>".($i+1)."η Προτίμηση</td><td>".getSchools($i+1, $dim, $omada, $mysqlconnection, ${"s".$i});
            }
            else
            {
                for ($i=0; $i<$av_choices; $i++)
                  echo "<tr><td>".($i+1)."η Προτίμηση</td><td>".getSchools($i+1, $dim, 0, $mysqlconnection, ${"s".$i});
            }
            if ($has_aitisi)
                  echo "<tr><td colspan=4><small>Τελευταία ενημέρωση: ". date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
            echo "<input type='hidden' name = 'id' value='$id'>";
            echo "<tr><td colspan=4><center><INPUT TYPE='submit' name='save' VALUE='Αποθήκευση'></center></td></tr>";
            if ($av_type == 1)
                echo "<tr><td colspan=4><center><INPUT TYPE='submit' name='prev' VALUE='Επιστροφή στο Βήμα 1'></center></td></tr>";
            if (!$has_aitisi){
              echo "<tr><td colspan=4><center><INPUT TYPE='submit' onclick='return myaction()' name='submit' VALUE='Οριστική Υποβολή' disabled></center></td>\n";
            } else {
              echo "<tr><td colspan=4><center><INPUT TYPE='submit' onclick='return myaction()' name='submit' VALUE='Οριστική Υποβολή'></center></td>\n";
            }
            echo "</tr>\n";
            echo "</form>";
            echo "<tr><td colspan=4><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' value='Έξοδος'></form></center></td></tr>";
        }
        echo "</table>";
        echo "</center>";
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
      placeholder: ' ',
      allowClear: true
  });
</script>