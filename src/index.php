<?php
  session_start();
  require_once "../config.php";
  require_once 'functions.php';
?>
<html>
  <?php require_once('head.php'); ?>
<?php
  $timeout = 0;

  include_once("class.login.php");
  $log = new logmein();
  if($_SESSION['loggedin'] == false)
  //if($log->logincheck($_SESSION['loggedin']) == false)
  //if($log->logincheck($_SESSION['loggedin']) == false && $_SESSION['timeout']<time())
  {   
      //header("Location: login.php");
    $page = 'login.php';
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$page.'";';
    echo '</script>';
  }
  else {
      $loggedin = 1;
  }
  // if veltiwseis, goto index2.php
  if ($av_type == 2){
    //header("Location: index2.php");
    $page = 'index2.php';
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$page.'";';
    echo '</script>';
  }
  if (time() > $_SESSION['timeout'])  
    $timeout = 1;
  $diff = $_SESSION['timeout'] - time();

  if ($timeout){
//    echo "<body>Η συνεδρία σας έχει λήξει.<br>Παρακαλώ κάντε είσοδο στο σύστημα.";
//    echo "<form action='login.php'><input type='submit' value='Είσοδος'></form></body></html>";
//    exit;
//    header("Location: login.php");
    $page = 'login.php';
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$page.'";';
    echo '</script>';
  }

  if ($loggedin)
  {
    // if admin redirect to admin page
    if ($_SESSION['user']=="$av_admin")
        echo "  <meta http-equiv=\"refresh\" content=\"0; URL=admin.php\">";
?>
  <div id="left1">
      <?php include('help.php'); ?>
  </div>
  <div id="right1">
<?php
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
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
    $organ = $row['org'];
    $organ = getSchooledc($organ, $mysqlconnection);
    $eth = $row['eth'];
    $mhnes = $row['mhnes'];
    $hmeres = $row['hmeres'];
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
                
                $(document).ready(function(){
                    $('#apospash').change(function(){
                        var checked = $(this).attr('checked');
                        if (checked) {
                        //$('#other').show();             
                        $('#other').fadeIn();             
                        } else {
                            //$('#other').hide();
                            $('#other').fadeOut();
                        }
                    });        
                });
                
        </script>
        <?php
    //check if employee has aitisi
    $query = "SELECT * from $av_ait WHERE emp_id=$id";
    $result = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($result);
    if (mysqli_num_rows($result)>0)
    {
        $has_aitisi = 1;
        $submitted = $row['submitted'];
    }
    
    // if user has already saved an application
    if ($has_aitisi)
    {
        $gamos = $row['gamos'];
        $paidia = $row['paidia'];
        $dhmos_anhk = $row['dhmos_anhk'];
        $dhmos_anhk = str_replace(" ", "&nbsp;", $dhmos_anhk);
        $dhmos_ent = $row['dhmos_ent'];
        $dhmos_syn = $row['dhmos_syn'];
        $aitisi = $row['aitisi'];
        $eidikh = $row['eidikh'];
        $apospash = $row['apospash'];
        $didakt = $row['didakt'];
        $metapt = $row['metapt'];
        $didask = $row['didask'];
        $paidag = $row['paidag'];
        $ethea = $row['eth'];
        $mhnesea = $row['mhnes'];
        $hmeresea = $row['hmeres'];
        $ygeia = $row['ygeia'];
        $ygeia_g = $row['ygeia_g'];
        $ygeia_a = $row['ygeia_a'];
        $eksw = $row['eksw'];
        $comments = $row['comments'];
        $ypdil = $row['ypdil'];
        $org_eid = $row['org_eid'];
        $allo = $row['allo'];
        $allo = str_replace(" ", "&nbsp;", $allo);
        
        if ($submitted)
            echo "<h3><center>Η αίτηση έχει υποβληθεί και δεν μπορείτε να την επεξεργαστείτε.</center></h3>";
        echo "<center>";
        echo "<table id=\"mytbl\" class=\"imagetable\" border=\"2\">\n";
        echo "<thead><th colspan=7>Βήμα 1: Υποβολή στοιχείων</th></thead>";
        echo "<tr><td colspan=2>Ονοματεπώνυμο Εκπ/κού:</td><td colspan=5>".$name." ".$surname."</td></tr>";
        echo "<tr><td colspan=2>Πατρώνυμο: </td><td colspan=5>".$patrwnymo."</td></tr>";
        echo "<tr><td colspan=2>Κλάδος: </td><td colspan=5>".$klados."</td></tr>";
        echo "<tr><td colspan=2>A.M.: </td><td colspan=5>".$am."</td></tr>";
        echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>".$organ."</td></tr>";
        echo "<tr><td colspan=2>Συνολική υπηρεσία: <small>(Έως $av_endofyear)</small></td><td colspan=5>$eth Έτη, $mhnes Μήνες, $hmeres Ημέρες</td></tr>";
        
        // if user has submitted
        if ($submitted)
        {
            echo "<tr><td colspan=2>Ανάλυση μορίων: </td><td colspan=5>";
            $moria = compute_moria($id, $mysqlconnection);
            foreach ($moria as $key => $value) {
                echo moria_key2per($key).": $value<br>";
            }
            echo "</td></tr>";
            echo "<form id='src' name='src' action='index2.php' method='POST'>\n";
            if ($org_eid)
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1' checked disabled>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1' disabled>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
            if ($aitisi)
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1' disabled checked>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1' disabled>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Οικογενειακή Κατάσταση</center></td></tr>";
            echo "<tr><td>Γάμος</td><td>";
            echo getGamos($gamos);
            echo "</td><td>Παιδιά</td><td>$paidia</td><td>Δήμος</td><td>$dhmos_anhk</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Εντοπιότητα</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας $av_nomos που έχω εντοπιότητα</td><td colspan=5>";
            getDimos($dhmos_ent,$mysqlconnection);
            echo "</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Συνυπηρέτηση</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας $av_nomos που έχω συνυπηρέτηση</td><td colspan=5>";
            getDimos($dhmos_syn, $mysqlconnection);
            echo "</td></tr>";
            if ($eidikh)
                echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία (κατά προτεραιότητα)</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' disabled checked>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία (κατά προτεραιότητα)</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' disabled>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
            if ($apospash)
                echo "<tr height=20></tr><tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><input type='checkbox' name='apospash' value='1' disabled checked>Απο τη Γενική στην Ειδική Αγωγή</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><input type='checkbox' name='apospash' value='1' disabled>Απο τη Γενική στην Ειδική Αγωγή</td></tr>";
            echo "<div id='ea'><tr><td colspan=2></td><td colspan=5>";
            if ($didakt)
                echo "α) Διδακτορικό Ειδ.Αγωγής<input type='checkbox' name='didakt' value='1' disabled checked><br>";
            else
                echo "α) Διδακτορικό Ειδ.Αγωγής<input type='checkbox' name='didakt' value='1' disabled><br>";
            if ($metapt)
                echo "β) Μεταπτυχιακό Ειδ.Αγωγής<input type='checkbox' name='metapt' value='1' disabled checked><br>";
            else
                echo "β) Μεταπτυχιακό Ειδ.Αγωγής<input type='checkbox' name='metapt' value='1' disabled><br>";
            if ($didask)
                echo "γ) Διδασκαλείο Ειδ.Αγωγής<input type='checkbox' name='didask' value='1' disabled checked><br>";
            else
                echo "γ) Διδασκαλείο Ειδ.Αγωγής<input type='checkbox' name='didask' value='1' disabled><br>";
            if ($paidag)
                echo "δ) Πτυχίο παιδαγωγικών τμημάτων με αντικείμενο στην ειδική αγωγή<input type='checkbox' name='paidag' value='1' disabled checked><br>";
            else
                echo "δ) Πτυχίο παιδαγωγικών τμημάτων με αντικείμενο στην ειδική αγωγή<input type='checkbox' name='paidag' value='1' disabled><br>";
            echo "ε) Προϋπηρεσία στην Ειδ.Αγωγή: $ethea Έτη, $mhnesea Μήνες, $hmeresea Ημέρες<br>";
            echo "στ) Άλλο προσόν (π.χ. Braille, νοηματική): $allo";
            echo "<tr><td colspan=7><small>Αν επιθυμείτε απόσπαση ΚΑΙ σε σχολεία της Γενικής εκπ/σης, συμπληρώστε τη <a href='aposp2013.doc'>φόρμα</a> και στείλτε την στο $av_foreas</small></td></tr>";
            echo "</td></tr>";
            
            echo "</div>";
            
            echo "<tr height=20></tr><tr><td colspan=7><center>Σοβαροί λόγοι υγείας</center></td></tr>";
            echo "<tr><td colspan=2><center>Του ιδίου, παιδιών ή συζύγου</center></td><td colspan=5>";
            echo getYgeia($ygeia);
            echo "</td></tr>";
            echo "<tr><td colspan=2><center>Γονέων</center></td><td colspan=5>";
            echo getYgeia_g($ygeia_g);
            echo "</td></tr>";
            echo "<tr><td colspan=2><center>Αδελφών</center></td><td colspan=5>";
            echo getYgeia_a($ygeia_a);
            echo "</td></tr>";
            if ($eksw)
                echo "<tr><td colspan=2><center>Θεραπεία για εξωσωματική γονιμοποίηση</center></td><td colspan=5><input type='checkbox' name='eksw' value='1' checked disabled></td></tr>";
            else
                echo "<tr><td colspan=2><center>Θεραπεία για εξωσωματική γονιμοποίηση</center></td><td colspan=5><input type='checkbox' name='eksw' value='1' disabled></td></tr>";
            echo "<tr height=20></tr><tr><td colspan=2>Σχόλια - Παρατηρήσεις</td><td colspan=5>$comments</td></tr>";
            $blabla = "Δηλώνω υπεύθυνα ότι δεν έχω οριστεί στέλεχος εκπαίδευσης (λ.χ. προϊστάμενος/μένη ολιγοθέσιας σχολικής μονάδας, διευθυντής/ντρια σχολ. μονάδας) και ότι δεν υπηρετώ σε θέση με θητεία που λήγει μετά τις $av_endofyear.";
            echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='ypdil' value='1' checked disabled>$blabla</td></tr>";
            echo "<tr><td colspan=7><small>Υποβλήθηκε στις: ".  date("d-m-Y, H:i:s", strtotime($row['submit_date']))."</small></td></tr>";
            echo "<input type='hidden' name = 'id' value='$id'>";
            echo "</form>";
            echo "<tr><td colspan=7><center><form action='index2.php' method='POST'><input type='submit' value='Συνέχεια στο Βήμα 2'></form></center></td></tr>";
            echo "<tr><td colspan=7><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' value='Έξοδος'></form></center></td></tr>";
        }
        // if not submitted
        else
        {
            //form
            echo "<form id='src' name='src' action='save.php' method='POST'>\n";
            if ($org_eid)
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1' checked>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1'>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
            if ($aitisi)
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1' checked>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1'>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Οικογενειακή Κατάσταση</center></td></tr>";
            echo "<tr><td>Γάμος</td><td>";
            cmbGamos_edit($gamos);
            echo "</td><td>Παιδιά</td><td>";
            cmbPaidia_edit($paidia);
            echo "</td><td>Δήμος</td><td>";
            //cmbDimos_edit('anhk',$dhmos_anhk);
            echo "<input size=30 name='dhmos_anhk' value=$dhmos_anhk>";
            echo "</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Εντοπιότητα</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας $av_nomos που έχω εντοπιότητα</td><td colspan=5>";
            cmbDimos_edit('ent',$dhmos_ent,$mysqlconnection);
            echo "</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Συνυπηρέτηση</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας $av_nomos που έχω συνυπηρέτηση</td><td colspan=5>";
            cmbDimos_edit('syn',$dhmos_syn,$mysqlconnection);
            echo "</td></tr>";
            if ($eidikh)
                echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία (κατά προτεραιότητα)</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' checked>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία (κατά προτεραιότητα)</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1'>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";

            if ($apospash)
                echo "<tr height=20></tr><tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><div name='main'><input type='checkbox' id='apospash' name='apospash' value='1' checked='1'>Απο τη Γενική στην Ειδική Αγωγή</div></td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><div name='main'><input type='checkbox' id='apospash' name='apospash' value='1'>Απο τη Γενική στην Ειδική Αγωγή</div></td></tr>";
            if (!$apospash)
                $style = "display:none;";
            else $style = '';
            echo "<tr><td colspan=2></td><td colspan=5><div class='other' name='other' id='other' style='$style'>";
            if ($didakt)
                echo "α) Διδακτορικό Ειδ.Αγωγής<input type='checkbox' name='didakt' value='1' checked><br>";
            else
                echo "α) Διδακτορικό Ειδ.Αγωγής<input type='checkbox' name='didakt' value='1'><br>";
            if ($metapt)
                echo "β) Μεταπτυχιακό Ειδ.Αγωγής<input type='checkbox' name='metapt' value='1' checked><br>";
            else
                echo "β) Μεταπτυχιακό Ειδ.Αγωγής<input type='checkbox' name='metapt' value='1'><br>";
            if ($didask)
                echo "γ) Διδασκαλείο Ειδ.Αγωγής<input type='checkbox' name='didask' value='1' checked><br>";
            else
                echo "γ) Διδασκαλείο Ειδ.Αγωγής<input type='checkbox' name='didask' value='1'><br>";
            if ($paidag)
                echo "δ) Πτυχίο παιδαγωγικών τμημάτων με αντικείμενο στην ειδική αγωγή<input type='checkbox' name='paidag' value='1' checked><br>";
            else
                echo "δ) Πτυχίο παιδαγωγικών τμημάτων με αντικείμενο στην ειδική αγωγή<input type='checkbox' name='paidag' value='1'><br>";
            echo "ε) Προϋπηρεσία στην Ειδ.Αγωγή: <input size=2 name='eth' value=$ethea> Έτη,<input size=2 name='mhnes' value=$mhnesea> Μήνες,<input size=2 name='hmeres' value=$hmeresea> Ημέρες<br>";
            echo "στ) Άλλο προσόν (π.χ. Braille, νοηματική): <input size=25 name='allo' value=$allo>";
            echo "<br><small>Αν επιθυμείτε απόσπαση ΚΑΙ σε σχολεία της Γενικής εκπ/σης, συμπληρώστε τη <a href='aposp2013.doc'>φόρμα</a> και στείλτε την στο $av_foreas </small></div></td></div></tr>";
            //echo "</div>";
            
            echo "<tr height=20></tr><tr><td colspan=7><center>Σοβαροί λόγοι υγείας</center></td></tr>";
            echo "<tr><td colspan=2><center>Ποσοστό αναπηρίας του ιδίου, παιδιών ή συζύγου</center></td><td colspan=5>";
            cmbYgeia_edit($ygeia);
            echo "</td></tr>";
            echo "<tr><td colspan=2><center>Ποσοστό αναπηρίας Γονέων</center></td><td colspan=5>";
            cmbYgeia_g_edit($ygeia_g);
            echo "</td></tr>";
            echo "<tr><td colspan=2><center>Ποσοστό αναπηρίας Αδελφών</center></td><td colspan=5>";
            cmbYgeia_a_edit($ygeia_a);
            echo "</td></tr>";
            if ($eksw)
                echo "<tr><td colspan=2><center>Θεραπεία για εξωσωματική γονιμοποίηση</center></td><td colspan=5><input type='checkbox' name='eksw' value='1' checked></td></tr>";
            else
                echo "<tr><td colspan=2><center>Θεραπεία για εξωσωματική γονιμοποίηση</center></td><td colspan=5><input type='checkbox' name='eksw' value='1'></td></tr>";
            echo "<tr height=20></tr><tr><td colspan=2>Σχόλια - Παρατηρήσεις</td><td colspan=5><textarea cols=60 name='comments' >$comments</textarea></td></tr>";
            
            $blabla = "Δηλώνω υπεύθυνα ότι δεν έχω οριστεί στέλεχος εκπαίδευσης (λ.χ. προϊστάμενος/μένη ολιγοθέσιας σχολικής μονάδας, διευθυντής/ντρια σχολ. μονάδας) και ότι δεν υπηρετώ σε θέση με θητεία που λήγει μετά τις 31-08-$av_etos.";
            if ($ypdil)
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='ypdil' value='1' checked>$blabla</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='ypdil' value='1'>$blabla</td></tr>";
            echo "<tr><td colspan=7><small>Τελευταία ενημέρωση: ". date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
            echo "<input type='hidden' name = 'id' value='$id'>";
            echo "<input type='hidden' name = 'part' value='1'>";
            echo "<tr><td colspan=7><center><INPUT TYPE='submit' name='save' VALUE='Αποθήκευση'></center></td></tr>";
            echo "<tr><td colspan=7><center><INPUT TYPE='submit' name='next' VALUE='Συνέχεια στο Βήμα 2'></center></td></tr>";
            echo "</form>";
            //echo "<tr><td colspan=7><center><form action='index2.php' method='POST'><input type='submit' value='Επόμενη σελίδα'></form></center></td></tr>";            
            //echo "<tr><td colspan=4><center><INPUT TYPE='submit' onclick='return myaction()' name='submit' VALUE='Οριστική Υποβολή'></center></td>\n";
            echo "</tr>\n";
            echo "</form>";
            echo "<tr><td colspan=7><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' value='Έξοδος'></form></center></td></tr>";
        }
        echo "</table>";
        echo "</center>";
    }
    // if user has NOT saved an application
    else
    {
        echo "<center>";        
        echo "<table id=\"mytbl\" class=\"imagetable\" border=\"2\">\n";
        echo "<thead><th colspan=7>Φόρμα υποβολής στοιχείων</th></thead>";
        echo "<tr><td colspan=2>Ονοματεπώνυμο Εκπ/κού:</td><td colspan=5>".$name." ".$surname."</td></tr>";
        echo "<tr><td colspan=2>Πατρώνυμο: </td><td colspan=5>".$patrwnymo."</td></tr>";
        echo "<tr><td colspan=2>Κλάδος: </td><td colspan=5>".$klados."</td></tr>";
        echo "<tr><td colspan=2>A.M.: </td><td colspan=5>".$am."</td></tr>";
        echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>".$organ."</td></tr>";
        echo "<tr><td colspan=2>Συνολική υπηρεσία: <small>(Έως $av_endofyear)</small></td><td colspan=5>$eth Έτη, $mhnes Μήνες, $hmeres Ημέρες</td></tr>";
        
        echo "<form id='src' name='src' action='save.php' method='POST'>\n";
        echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1'>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1'>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><center>Οικογενειακή Κατάσταση</center></td></tr>";
        echo "<tr><td>Γάμος</td><td>";
        cmbGamos();
        echo "</td><td>Παιδιά</td><td>";
        cmbPaidia();
        echo "</td><td>Δήμος</td><td>";
        echo "<input size=30 name='dhmos_anhk'>";
        echo "</td><td></td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><center>Εντοπιότητα</center></td></tr>";
        echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας $av_nomos που έχω εντοπιότητα</td><td colspan=5>";
        cmbDimos('ent',$mysqlconnection);
        echo "</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><center>Συνυπηρέτηση</center></td></tr>";
        echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας $av_nomos που έχω συνυπηρέτηση</td><td colspan=5>";
        cmbDimos('syn',$mysqlconnection);
        echo "</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία (κατά προτεραιότητα)</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1'>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";

            echo "<tr height=20></tr><tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><div name='main'><input type='checkbox' id='apospash' name='apospash' value='1'>Απο τη Γενική στην Ειδική Αγωγή</div></td></tr>";
            $style = "display:none;";
            echo "<tr><td colspan=2></td><td colspan=5><div class='other' name='other' id='other' style='$style'>";
            echo "α) Διδακτορικό Ειδ.Αγωγής<input type='checkbox' name='didakt' value='1'><br>";
            echo "β) Μεταπτυχιακό Ειδ.Αγωγής<input type='checkbox' name='metapt' value='1'><br>";
            echo "γ) Διδασκαλείο Ειδ.Αγωγής<input type='checkbox' name='didask' value='1'><br>";
            echo "δ) Πτυχίο παιδαγωγικών τμημάτων με αντικείμενο στην ειδική αγωγή<input type='checkbox' name='paidag' value='1'><br>";
            echo "ε) Προϋπηρεσία στην Ειδ.Αγωγή: <input size=2 name='eth'> Έτη,<input size=2 name='mhnes'> Μήνες,<input size=2 name='hmeres'> Ημέρες<br>";
            echo "στ) Άλλο προσόν (π.χ. Braille, νοηματική): <input size=25 name='allo' value=$allo>";
            echo "<br><small>Αν επιθυμείτε απόσπαση ΚΑΙ σε σχολεία της Γενικής εκπ/σης, συμπληρώστε τη <a href='aposp2013.doc'>φόρμα</a> και στείλτε την στο $av_foreas </small></div></td></div></tr>";
        
        echo "<tr height=20></tr><tr><td colspan=7><center>Σοβαροί λόγοι υγείας</center></td></tr>";
        echo "<tr><td colspan=2><center>Ποσοστό αναπηρίας του ιδίου, παιδιών ή συζύγου</center></td><td colspan=5>";
        cmbYgeia_edit(0);
        echo "</td></tr>";
        echo "<tr><td colspan=2><center>Ποσοστό αναπηρίας Γονέων</center></td><td colspan=5>";
        cmbYgeia_g_edit(0);
        echo "</td></tr>";
        echo "<tr><td colspan=2><center>Ποσοστό αναπηρίας Αδελφών</center></td><td colspan=5>";
        cmbYgeia_a_edit(0);
        echo "</td></tr>";
        echo "<tr><td colspan=2><center>Θεραπεία για εξωσωματική γονιμοποίηση</center></td><td colspan=5><input type='checkbox' name='eksw' value='1'></td></tr>";
        echo "<tr height=20></tr><tr><td colspan=2>Σχόλια - Παρατηρήσεις</td><td colspan=5><textarea cols=60 name='comments' value='$comments'></textarea></td></tr>";        
        $blabla = "Δηλώνω υπεύθυνα ότι δεν έχω οριστεί στέλεχος εκπαίδευσης (λ.χ. προϊστάμενος/μένη ολιγοθέσιας σχολικής μονάδας, διευθυντής/ντρια σχολ. μονάδας) και ότι δεν υπηρετώ σε θέση με θητεία που λήγει μετά τις 31-08-$av_etos.";
        echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='ypdil' value='1'>$blabla</td></tr>";
        echo "<tr height=20><tr><tr><td colspan=7><small>Αποθηκεύστε για να μπορέσετε να προχωρήσετε στην υποβολή προτιμήσεων.</small></td></tr>";
        echo "<input type='hidden' name = 'id' value='$id'>";
        echo "<input type='hidden' name = 'part' value='1'>";
        echo "<tr><td colspan=7><center><INPUT TYPE='submit' name='save' VALUE='Αποθήκευση'></center></td></tr>";
        echo "<tr><td colspan=7><center><INPUT TYPE='submit' name='submit' VALUE='Συνέχεια στο Βήμα 2' disabled></center></td></tr>";
        echo "</form>";
        echo "<tr><td colspan=7><center><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' value='Έξοδος'></form></center></td></tr>";
        echo "</table>";
        echo "</center>";
    }
     mysqli_close($mysqlconnection);   
    }
?>
  </div>
</center>
</div>
  </body>
</html>