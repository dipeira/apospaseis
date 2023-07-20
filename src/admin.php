<?php

  header('Content-type: text/html; charset=utf-8'); 
  require_once "../config.php";
  require_once 'functions.php';
  session_start();

  include("class.login.php");
  $log = new logmein();
  //if($log->logincheck($_SESSION['loggedin']) == false)
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;
?>
<html>
  <head>
  <?php require_once('head.php'); ?>
	<!-- <LINK href="style.css" rel="stylesheet" type="text/css"> -->
    <!-- <LINK href="style_sorter.css" rel="stylesheet" type="text/css"> -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title><?php echo $av_title." ($av_foreas) - Διαχείριση"; ?></title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script language="javascript" type="text/javascript">
        function myaction(){
            r=confirm("Είστε σίγουροι ότι θέλετε να αλλάξετε τα στοιχεία του υπαλλήλου;");
            if (r==false){
                return false;
            }
        }
        function myaction_yp(){
            r=confirm("Είστε σίγουροι ότι θέλετε να αναιρέσετε την υποβολή της αίτησης;");
            if (r==false){
                return false;
            }
        }
        $(document).ready(function() {
            $('#mytbl').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Greek.json"
                },
                "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "Όλα"] ]
            });
        });
    </script>
    
    <style>
        table.dataTable.table-sm .sorting:before, table.dataTable.table-sm .sorting_asc:before, table.dataTable.table-sm .sorting_desc:before	{
	        right: 1.1em !important;
        }
    </style>
    <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed:400,400italic&subset=greek,latin' rel='stylesheet' type='text/css'>
    </head>
    <body>
    <div class="container-fluid">
        
    
<?php
  if ($loggedin)
  {
    // check if admin 
    if ($_SESSION['user']!=$av_admin && $_SESSION['user']!='admin')
        die("Σφάλμα αυθεντικοποίησης...");

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    mysqli_set_charset($mysqlconnection,"utf8");

    // if POST change apo_employee organ & synolikh yphresia
    if (isset($_POST['porgan']) || isset($_POST['ethy']) || isset($_POST['mhnesy']) || isset($_POST['hmeresy']))
    {
        $organ = $_POST['porgan'];
        $eth = $_POST['ethy'];
        $mhnes = $_POST['mhnesy'];
        $hmeres = $_POST['hmeresy'];
        $emp_id = $_POST['emp_id'];
        $id = $_POST['id'];
        $query = "UPDATE $av_emp SET org=$organ, eth=$eth, mhnes=$mhnes, hmeres=$hmeres WHERE id=$emp_id";
        $result = mysqli_query($mysqlconnection, $query);
        $url = "admin.php?id=$id&action=view";
        echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
        exit;
    }
    // if POST change checked status, special category or check_comments
    if (isset($_POST['checked']) || isset($_POST['check_comments']) || isset($_POST['eid_kat']))
    {
        $checked = (!isset($_POST['checked'])) ? 0 : $_POST['checked'];
        $eid_kat = (!isset($_POST['eid_kat'])) ? 0 : $_POST['eid_kat'];
        $comments = $_POST['check_comments'];
        $id = $_POST['id'];
        // check if checked
        $query = "SELECT checked from $av_ait WHERE id = $id";
        $result = mysqli_query($mysqlconnection, $query);
        $row = mysqli_fetch_array($result);
        if ($row['checked']){
            $query = "UPDATE $av_ait SET checked=$checked, check_comments='$comments',eid_kat=$eid_kat WHERE id=$id";
        } else {
            $query = "UPDATE $av_ait SET checked=$checked, check_comments='$comments',check_date=NOW(),eid_kat=$eid_kat WHERE id=$id";
        }
        $result = mysqli_query($mysqlconnection, $query);
        $url = "admin.php?id=$id&action=view";
        echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
        exit;
    }
    // Depending on $_GET['action'] value:
    /////////////////////////////////
    // Cancel application submission
    /////////////////////////////////
    if ($_GET['action']=="undo")
    {
        $id = $_GET['id'];
        $query = "UPDATE $av_ait SET submitted=0, submit_date=NULL WHERE id=$id";
        $result = mysqli_query($mysqlconnection, $query);
        echo "Έγινε αναίρεση της υποβολής της αίτησης με Α/Α $id.";
        echo "<br>Παρακαλώ περιμένετε...";
        echo "<meta http-equiv=\"refresh\" content=\"3; URL=admin.php\">";
    }
    /////////////////////////////
    // View selected application
    /////////////////////////////
    elseif ($_GET['action']=="view")
    {
        $id = $_GET['id'];
        $query = "SELECT *,a.eth as ethea, a.mhnes as mhnesea, a.hmeres as hmeresea, e.eth as ethy, e.mhnes as mhnesy,e.hmeres as hmeresy from $av_ait a JOIN $av_emp e ON a.emp_id=e.id WHERE a.id=$id";
        $result = mysqli_query($mysqlconnection, $query);
        $row = mysqli_fetch_assoc($result);
        
        $emp_id = $row['emp_id'];
        $name = $row['name'];
        $surname = $row['surname'];
        $patrwnymo = $row['patrwnymo'];
        $klados = $row['klados'];
        $am = $row['am'];
        $organ = $row['org'];
        $organ = getSchooledc($organ, $mysqlconnection);
        $ethy = $row['ethy'];
        $mhnesy = $row['mhnesy'];
        $hmeresy = $row['hmeresy'];
        $gamos = $row['gamos'];
        $paidia = $row['paidia'];
        $dhmos_anhk = $row['dhmos_anhk'];
        $dhmos_ent = $row['dhmos_ent'];
        $dhmos_syn = $row['dhmos_syn'];
        $aitisi = $row['aitisi'];
        $eidikh = $row['eidikh'];
        $apospash = $row['apospash'];
        $didakt = $row['didakt'];
        $metapt = $row['metapt'];
        $didask = $row['didask'];
        $paidag = $row['paidag'];
        $eth = $row['ethea'];
        $mhnes = $row['mhnesea'];
        $hmeres = $row['hmeresea'];
        $ygeia = $row['ygeia'];
        $ygeia_g = $row['ygeia_g'];
        $ygeia_a = $row['ygeia_a'];
        $eksw = $row['eksw'];
        $comments = $row['comments'];
        $ypdil = $row['ypdil'];
        $org_eid = $row['org_eid'];
        $allo = $row['allo'];
        
        // if choices are made, prepare...
        if (strlen($row['choices']) > 0) {
            $sch_arr = unserialize($row['choices']);
            for ($i = 0; $i < $av_choices; $i++) {
                ${'s'.$i} = strlen($sch_arr[$i]) > 0 ? getSchooledc($sch_arr[$i],$mysqlconnection) : null;
            }
        }

        $submitted = $row['submitted'];
        
        $klados = $row['klados'];
        $emp_id = $row['emp_id'];
        if ($av_athmia)
        {
            if (strpos($klados,"ΠΕ6") !== false)
                $dim = 1;
            else
                $dim = 2;
        }
        else
            $dim = 0;
        echo "<center>";   
        echo "<h2>Αίτηση εκπαιδευτικού</h2>";
        echo "<table id=\"mytbl\" class=\"table table-striped table-hover table-sm\" border=\"1\">\n";     
        // echo "<thead><th colspan=7>Αίτηση εκπαιδευτικού</th></thead>";
        echo "<tr><td colspan=2>Ονοματεπώνυμο Εκπ/κού:</td><td colspan=5>".$name." ".$surname."</td></tr>";
        echo "<tr><td colspan=2>Πατρώνυμο: </td><td colspan=5>".$patrwnymo."</td></tr>";
        echo "<tr><td colspan=2>Κλάδος: </td><td colspan=5>".$klados."</td></tr>";
        if ($av_type == 3){
            echo "<tr><td colspan=2>A.Δ.Α: </td><td colspan=5>".$row['ada']."</td></tr>";    
        } else {
        echo "<tr><td colspan=2>A.M.: </td><td colspan=5>".$am."</td></tr>";
        }
        echo "<form id='src' name='src' action='admin.php' method='POST'>\n";
        // organ & synolikh yphresia can be changed
        if (!$av_canalter)
            echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>".$organ."</td></tr>";
        else {
            $schools = getSchools('organ',$dim,0,$mysqlconnection,$organ, TRUE);
            echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>".$schools."</td></tr>";
        }
        if ($av_type == 2) {
          echo "<tr><td colspan=2>Μόρια βελτίωσης: </td><td colspan=2>".$row['moria']."</td></tr>";
          echo "<tr><td colspan=2>Δήμος εντοπιότητας: </td><td colspan=2>".$row['entopiothta']."</td></tr>";
          echo "<tr><td colspan=2>Δήμος συνυπηρέτησης: </td><td colspan=2>".$row['synyphrethsh']."</td></tr>";
        }
        // if apospaseis, show related data
        if ($av_type == 1)
        {
            if (!$av_canalter) {
              echo "<tr><td colspan=2>Συνολική υπηρεσία: </td><td colspan=5>$ethy Έτη, $mhnesy Μήνες, $hmeresy Ημέρες</td></tr>";
            } else {
              echo "<tr><td colspan=2>Συνολική υπηρεσία: </td><td colspan=5><input size=2 name='ethy' value=$ethy> Έτη,<input size=2 name='mhnesy' value=$mhnesy> Μήνες,<input size=2 name='hmeresy' value=$hmeresy> Ημέρες</td></tr>";
            }
            if ($av_canalter)
                echo "<tr><td colspan=2></td><td colspan=4><input type='submit' class='btn btn-info' onclick='return myaction()' value='Αποθήκευση'></td></tr>";
            // end of changeable elements
            // print moria analysis
            echo "<tr><td colspan=2>Ανάλυση μορίων: </td><td colspan=5>";
            $moria = compute_moria($emp_id, $mysqlconnection);
            foreach ($moria as $key => $value) {
                echo moria_key2per($key).": $value<br>";
            }
            echo "</td></tr>";
            if ($org_eid)
                echo "<tr><td colspan=7><input type='checkbox' name='org_eid' value='1' checked disabled>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
            else
                echo "<tr><td colspan=7><input type='checkbox' name='org_eid' value='1' disabled>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
            if ($aitisi)
                echo "<tr><td colspan=7><input type='checkbox' name='aitisi' value='1' disabled checked>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
            else
                echo "<tr><td colspan=7><input type='checkbox' name='aitisi' value='1' disabled>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το $av_etos</td></tr>";
            echo "<tr><td colspan=7><center>Οικογενειακή Κατάσταση</center></td></tr>";
            echo "<tr><td>Γάμος</td><td>";
            echo getGamos($gamos);
            echo "</td><td>Παιδιά</td><td>$paidia</td><td>Δήμος</td><td>$dhmos_anhk</td></tr>";
            echo "<tr><td colspan=7><center>Εντοπιότητα</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας Ηρακλείου που έχω εντοπιότητα</td><td colspan=5>";
            echo getDimos($dhmos_ent, $mysqlconnection);
            echo "</td></tr>";
            echo "<tr><td colspan=7><center>Συνυπηρέτηση</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας Ηρακλείου που έχω συνυπηρέτηση</td><td colspan=5>";
            echo getDimos($dhmos_syn, $mysqlconnection);
            echo "</td></tr>";
            if ($eidikh)
                echo "<tr><td colspan=2><center>Ειδική Κατηγορία</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' disabled checked>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
            else
                echo "<tr><td colspan=2><center>Ειδική Κατηγορία</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' disabled>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
            if ($apospash)
                echo "<tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><input type='checkbox' name='apospash' value='1' disabled checked>Απο τη Γενική στην Ειδική Αγωγή</td></tr>";
            else
                echo "<tr><td colspan=2><center>Επιθυμώ απόσπαση</center></td><td colspan=5><input type='checkbox' name='apospash' value='1' disabled>Απο τη Γενική στην Ειδική Αγωγή</td></tr>";
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
            echo "ε) Προϋπηρεσία στην Ειδ.Αγωγή: $eth Έτη, $mhnes Μήνες, $hmeres Ημέρες<br>";
            echo "στ) Άλλο προσόν (π.χ. Braille, νοηματική): $allo";
            echo "</td></tr></div>";

            echo "<tr><td colspan=7><center>Σοβαροί λόγοι υγείας</center></td></tr>";
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
            echo "<tr><td colspan=2>Σχόλια - Παρατηρήσεις</td><td colspan=5>$comments</td></tr>";    
            $blabla = "Δηλώνω υπεύθυνα ότι δεν έχω οριστεί στέλεχος εκπαίδευσης (λ.χ. προϊστάμενος/μένη ολιγοθέσιας σχολικής μονάδας, διευθυντής/ντρια σχολ. μονάδας)<br> και ότι δεν υπηρετώ σε θέση με θητεία που λήγει μετά τις 31-08-$av_etos.";
            echo "<tr><td colspan=7><input type='checkbox' name='ypdil' value='1' checked disabled>$blabla</td></tr>";
            echo "</table>";    
        }
        echo "<br>";
        echo "<table id=\"mytbl\" class=\"table table-striped table-bordered table-responsive\" border=\"2\">\n";
        echo "<tr><td colspan=4><center><strong>Προτιμήσεις</strong></center></td></tr>";
        
        for ($i = 0; $i < $av_choices; $i++) {
          if (strlen(${'s'.$i}) == 0) continue;
          echo "<tr><td>".($i+1)."η προτίμηση</td><td>".${'s'.$i}."</td></tr>\n";
        }
        
        if ($submitted)
            echo "<tr><td colspan=4><small>Υποβλήθηκε στις: ".  date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
        else
            echo "<tr><td colspan=4><small>Αποθηκεύθηκε στις: ".  date("d-m-Y, H:i:s", strtotime($row['updated']))."</small></td></tr>";
        echo "<tr><td colspan=4><center><input type='hidden' name='id' value=$id></td></tr>";
        echo "<tr><td colspan=4><center><input type='hidden' name='emp_id' value=$emp_id></td></tr>";
        // change employee elements
        if ($av_type == 1){
            echo "<tr><td colspan=4><center><a href='criteria.php?userid=$am'><button type='button' class='btn btn-success'>Επεξεργασία</button></a></center></td></tr>";
        }
        echo "</form>";
        echo "<tr><td colspan=4><center><form action='admin.php'><input type='submit' class='btn btn-danger' value='Eπιστροφή'></form></center></td></tr>";
        echo "</table>";   
        if ($submitted && $av_type == 1){
            echo "<form action='admin.php' method='post'>";
            echo "<br><table class='table table-striped table-sm' border=2><th colspan=2>Έλεγχος αίτησης</th>";
            echo "<tr><td>Ειδική κατηγορία:</td><td>";
            $chkd = $row['eid_kat'] ? 'checked' : '';
            echo "<input type='checkbox' name='eid_kat' value='1' $chkd>";
            echo "</td></tr>";
            echo "<tr><td>Έλεγχθηκε:</td><td>";
            echo $row['checked'] ? 
                "<input type='checkbox' name='checked' value='1' checked> <small>(Στις ".date("d-m-Y, H:i:s", strtotime($row['check_date'])).")</small>" :
                "<input type='checkbox' name='checked' value='1'>";
            echo "</td></tr>";
            echo "<td>Σχόλια ελέγχου:</td><td><textarea rows=4 cols=60 name='check_comments' >".$row['check_comments']."</textarea></td></tr>";
            show_uploaded_files($am);
            echo "<tr><td colspan=2><input type='submit' class='btn btn-success' value='Αποθήκευση' onclick='return myaction()'/></td></tr>";
            echo "<input type='hidden' name='id' value=$id>";
            echo "</form>";
        } else if ($av_type == 1){
            show_uploaded_files($am, true);
        }
    }
    ///////////////////
    // export to excel
    ///////////////////
    elseif ($_GET['action']=="export" || $_GET['action']=="export_eid")
    {
        $apospaseis = $av_type == 1 ? 1 : 0;
        $i=0;
        $data = array();
        if ($apospaseis){
            $andClause = $_GET['action']=="export_eid" ? ' AND (a.apospash = 1 or a.org_eid = 1)' : '';
            $query = "SELECT e.id,e.am,e.name,e.surname,e.patrwnymo,s.name as sch_name,e.org,e.klados,a.choices,a.dhmos_ent,a.dhmos_syn,a.apospash,a.checked,a.check_comments,a.org_eid,a.eid_kat
            FROM $av_ait a 
            JOIN $av_emp e ON a.emp_id=e.id 
            JOIN $av_sch s ON s.kwdikos = e.org 
            WHERE submitted=1 $andClause";
        } elseif ($av_type == 3) {
            $query = "SELECT e.id,LPAD(e.afm,9,0),e.name,e.surname,e.patrwnymo,e.klados,a.choices
            FROM $av_ait a 
            JOIN $av_emp e ON a.emp_id=e.id 
            WHERE submitted=1";
        }
        else {
            $query = "SELECT e.id,e.am,e.name,e.surname,e.patrwnymo,s.name as sch_name,e.moria,e.entopiothta,e.synyphrethsh,e.org,e.klados,a.choices
            FROM $av_ait a 
            JOIN $av_emp e ON a.emp_id=e.id 
            JOIN $av_sch s ON s.kwdikos = e.org 
            WHERE submitted=1";
        }
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num == 0) {
            echo "Δεν υπάρχουν δεδομένα για εξαγωγή!<br><br>";
            echo "<form action='admin.php'><input type='submit' value='Επιστροφή'></form>";
            die();
        }
        // fetch data
        while ($row0 = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            // skip first column (id)
            $tmpdata = array_slice($row0,1);
            // fetch choices as array
            $choices = unserialize($row0['choices']);

            // get rid of unnecessary (for export) data
            unset($tmpdata['choices']);
            unset($tmpdata['dhmos_ent']);
            unset($tmpdata['dhmos_syn']);
            unset($tmpdata['apospash']);
            unset($tmpdata['checked']);
            unset($tmpdata['check_comments']);
            unset($tmpdata['org_eid']);
            unset($tmpdata['eid_kat']);
            unset($tmpdata['moria']);
            unset($tmpdata['entopiothta']);
            unset($tmpdata['synyphrethsh']);
            
            // Mark diathesi pyspe/pysde or veltiwsh
            if ($av_type == 1 || $av_type == 2){
                $tmpdata['mdv'] = $row0['org'] == 2222222 ?
                    'Δ' : 'Β';
            }
            // merge arrays
            if ($av_dntes) {
		        $tmp = '';
                $cnt = 1;
                foreach($choices as $ch){
                    if (!$ch) continue;
                    $tmp .= $cnt.". ".getSchooledc($ch, $mysqlconnection)."<br>\n";
                    $cnt++;
                }
                array_push($tmpdata, $tmp);
                $data = $tmpdata;
            } else {
                // if geniki to eidiki OR eidiki to eidiki, skip
                if ($_GET['action']=="export" && ($row0['apospash'] || $row0['org_eid'])){
                    continue;
                }
                // if schools is enabled, show school names
                if ($_GET['schools'] == 1){
                    $tmpArr = Array();
                    foreach($choices as $ch){
                        if (!$ch) {
                            $tmpArr[] = '';
                            continue;
                        }
                        $tmpArr[] = getSchooledc($ch, $mysqlconnection);
                    }
                    $choices = $tmpArr;
                }
                // if veltiwseis
                if ($av_type == 2) {
                  $moria_array = Array(
                    'moria'=>$row0['moria'],
                    'entopiothta'=>$row0['entopiothta'],
                    'synyphrethsh'=>$row0['synyphrethsh'],
                  );
                  $tmpdata = array_merge($tmpdata, $moria_array);
                }
                
                // add choices
                $tmpdata = array_merge($tmpdata, $choices);
                
                // add specific columns
                if ($apospaseis){
                    // compute moria
                    $moria = compute_moria($row0['id'],$mysqlconnection,$row0['eid_kat']);
                    // get entopiothta, synhphrethsh
                    $dim_ent = getDimos($row0['dhmos_ent'], $mysqlconnection);
                    $dim_syn = getDimos($row0['dhmos_syn'], $mysqlconnection);
                    // construct moria array for export
                    $moria_array = Array(
                        'synolo'=>$moria['synolo'],
                        'entopiothta'=>$moria['entopiothta'],
                        'dim_entop'=>$dim_ent,
                        'synyphrethsh'=>$moria['synyphrethsh'],
                        'dim_synyp'=>$dim_syn
                    );
                    $tmpdata = array_merge($tmpdata, $moria_array);
                    $tmpdata[] = $row0['checked'] ? 'Ναι' : 'Όχι';
                    $tmpdata[] = $row0['check_comments'];
                }
                $data[] = $tmpdata;
            }
        }
        
        $columns = array();
                        
        if ($av_type == 3) {
            array_push($columns,'afm','name','surname','patrwnymo','klados');
        } else {
            array_push($columns,'am','name','surname','patrwnymo','org','org_code','klados','mdv');
        }
        
        if ($av_type == 2) {
          array_push($columns,'moria','entopiothta','synyphrethsh');
        }

        if ($av_dntes) {
            $columns[] = "choices";
        }
        else {
            for ($j=0; $j<$av_choices; $j++) {
                $columns[] = "p".($j+1);
            }
        }
        if ($apospaseis){
            $columns = array_merge($columns,Array('synolo','entopiothta','dimos_entopiothtas','synyphrethsh','dimos_synyphrethshs','checked','check_comments'));
        }
        
        ob_start();
        echo "<table id=\"mytbl\" border=\"1\">\n";
        echo "<thead>";
        echo "<tr>";
        foreach ($columns as $col)
            echo "<th>$col</th>";
        echo "</tr><thead><tbody>";
        while ($i < $num)
        {
            echo "<tr>";
            foreach ($data[$i] as $res) {
                echo "<td>$res</td>";
            }
                
            echo "</tr>";
            $i++;
        }

        echo "</tbody></table>";
        echo "<br>";
        $page = ob_get_contents(); 
        ob_end_flush();
        
        echo "<form action='2excel.php' method='post'>";
        $page = str_replace("'", "", $page);
        echo "<input type='hidden' name = 'data' value='$page'>";
        // link to show/hide school names
        $expLink = $_GET['action']=="export" ? 'export' : 'export_eid';
        $getSchoolLink = $_GET['schools'] == 1 ? 
            "<a href='admin.php?action=$expLink'>Απόκρυψη σχολείων</a>" : 
            "<a href='admin.php?action=$expLink&schools=1'>Εμφάνιση σχολείων";
        echo $getSchoolLink."<br><br>";
        echo "<BUTTON TYPE='submit' class='btn btn-success'>Εξαγωγή στο excel</BUTTON><br><br>";
        echo "</form>";
        echo "<form action='admin.php'><input type='submit' class='btn btn-info' value='Επιστροφή'></form>";
        //ob_end_clean();
    }
    /////////////////
    // end of export
    /////////////////
    // view employees that haven't submitted or done nothing.
    elseif ($_GET['action'] == 'nothing' || $_GET['action'] == 'saved')
    {
            if ($_GET['action'] == 'nothing')
                    $nothing = 1;

            $i = 0;
            if ($nothing)
            {
                $query = "SELECT *,a.id as ait_id FROM $av_emp e LEFT JOIN $av_ait a ON e.id = a.emp_id WHERE a.id IS NULL";
                echo "<h3>Εκπ/κοί που δεν έχουν κάνει καμία αποθήκευση ή υποβολή</h3>";
            }
            else
            {
                $query = "SELECT *,a.id as ait_id from $av_ait a JOIN $av_emp e ON a.emp_id=e.id WHERE submitted IS NULL OR submitted=0";
                echo "<h3>Εκπ/κοί που έχουν αποθηκεύσει αλλά δεν έχουν υποβάλει αίτηση</h3>";
            }
            $result = mysqli_query($mysqlconnection, $query);
            $num = mysqli_num_rows($result);
            if ($num > 0){
                echo "<table id=\"mytbl\" class=\"table table-striped table-bordered table-responsive\" border=\"2\">\n";
                echo "<thead>";
                echo "<th>Επώνυμο</th>\n";
                echo "<th>Όνομα</th>\n";
                echo "<th>Ειδικότητα</th>\n";
                echo $av_type == 3 ? "<th>A.Φ.M.</th>\n" : "<th>A.M.</th>\n";
                echo "<th>Οργανική θέση</th>\n";
                echo "</thead>";
                echo "<tbody>";
                while ($i < $num){
                    $row = mysqli_fetch_assoc($result);
                    $surname = $row['surname'];
                    $name = $row['name'];
                    $klados = $row['klados'];
                    $am = $av_type == 3 ? filterAFM($row['afm']) : $row['am'];
                    echo $nothing ? "<tr><td>$surname</td>" : "<tr><td><a href='admin.php?id=".$row['ait_id']."&action=view'>$surname</a></td>";
                    echo "<td>$name</td><td>$klados</td><td>$am</td>";
                    echo "<td>". getSchoolfromcode($row['org'], $mysqlconnection) . "</td>";
                    echo "</tr>";

                    $i++;
                }
                echo "</tbody>";
                echo "</table>";
            }
            echo "Σύνολο: $num";
            echo "<form action='admin.php'><input type='submit' class='btn btn-info' value='Επιστροφή'></form>";	
    }
    /////////////////////////////////////
    // applications ONLY in eidiki agogi
    /////////////////////////////////////
    elseif ($_GET['action'] == 'eidiki')
    {
        $i = 0;
        $query = "SELECT *,a.id as ait_id from $av_ait a JOIN $av_emp e ON a.emp_id=e.id WHERE a.apospash = 1 or a.org_eid = 1";
        echo "<h3>Εκπ/κοί που έχουν αιτηθεί απόσπαση α) από Γενική σε Ειδική Αγωγή, β) από Ειδική σε Ειδική αγωγή</h3>";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num > 0){
            echo "<table id=\"mytbl\" class=\"table table-striped table-bordered table-responsive\" border=\"2\">\n";
            echo "<thead>";
            echo "<th>Α/Α</th>\n";
            echo "<th>Επώνυμο</th>\n";
            echo "<th>Όνομα</th>\n";
            echo "<th>Ειδικότητα</th>\n";
            echo "<th>A.M.</th>\n";
            echo "<th>Κατηγορία</th></thead>";
            while ($i < $num){
                $row = mysqli_fetch_assoc($result);
                $id = $row['ait_id'];
                $surname = $row['surname'];
                $name = $row['name'];
                $klados = $row['klados'];
                $am = $row['am'];
                echo "<tr><td>$id</td>";
                echo "<td><a href='admin.php?id=".$row['ait_id']."&action=view'>$surname</a></td>";
                echo "<td>$name</td><td>$klados</td><td>$am</td>";
                echo $row['apospash'] ? '<td>Γενική σε Ειδική</td>' : '<td>Ειδική σε Ειδική</td>';
                echo "</tr>";
                $i++;
            }
            echo "</table>";
            echo "Σύνολο: $num";
            echo "<br><a href='admin.php?action=export_eid' class='btn btn-success'>Εξαγωγή</a><br><br>";
        } else {
            echo "<h3>Δεν υπάρχουν αιτήσεις...</h3>";
        }
        echo "<form action='admin.php'><input type='submit' class='btn btn-info' value='Επιστροφή'></form>";	
    }
    // of nothing or not submitted
    ///////////////////
    // Main admin view
    ///////////////////
    else
    {
      session_start();
      echo "<center><h1>$av_title ($av_foreas)</h1>";
      echo "<h2>Διαχείριση ";
      if ($av_type == 1){ 
        echo "<i>(λειτουργία αποσπάσεων)</i>";
      } elseif ($av_type == 2) {
        echo "<i>(λειτουργία βελτιώσεων)</i>";
      } elseif ($av_type == 3) {
        echo "<i>(λειτουργία τοποθετήσεων αναπληρωτών)</i>";
      }
      echo "</h2></center>";
      echo "<center>";
      if (file_exists('init.php')){
          echo "<p><b>ΠΡΟΣΟΧΗ</b>: Παρακαλώ διαγράψτε το αρχείο <b>init.php</b> για λόγους ασφαλείας!</p>";
      }

      ?>
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
  <!-- <a class="navbar-brand" href="#">Navbar</a> -->
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#">Αρχική<span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Εισαγωγή
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="import.php">Εισαγωγή δεδομένων</a>
          <?php if ($av_type == 3): ?>
          <a class="dropdown-item" href="import_kena.php">Εισαγωγή κενών</a>
          <?php endif; ?>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Εξαγωγή
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
        <?php if ($av_type == 1): ?>
          <a class="dropdown-item" href="admin.php?action=eidiki">Αιτήσεις στην Ειδική αγωγή (από τη Γενική αγωγή ή από την Ειδική αγωγή)</a>
        <?php
          endif;
          if ($av_type == 1):
        ?>
          <a class="dropdown-item" href="admin.php?action=export">Από τη Γενική στη Γενική (για πρόγραμμα PPYSDE)</a>
        </div>
        <?php
          elseif ($av_type == 2 || $av_type == 3): ?>
          <a class="dropdown-item" href="admin.php?action=export">Αιτήσεων</a>
          <?php endif; ?>
      </li>
      <?php if ($av_type == 3): ?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Τοποθέτηση
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="topothetish_anapl.php">Τοποθέτηση αναπληρωτών</a>
          <a class="dropdown-item" href="topothetish_neod.php">Τοποθέτηση νεοδιόριστων</a>
        </div>
      </li> 
      <?php endif; ?>
      <li class="nav-item">
        <a class="nav-link" href="params.php">Παράμετροι</a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0" action='login.php'>
      <input type='hidden' name = 'logout' value=1>
      <!-- <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search"> -->
      <button class="btn btn-danger my-2 my-sm-0" type="submit">Έξοδος</button>
    </form>
  </div>
</nav>
      <script language="javascript" type="text/javascript">
        function onCheck() {
            var checkBox = document.getElementById("submitted_box");
            jQuery.ajax({
                url: 'sessionhelper.php',
                type: 'POST',
                data: { value: checkBox.checked ? 1 : 0 },
                dataType : 'json',
            });
            location.reload();
        }
      </script>

      <?php      
      $i=$hasFilter=0;
      $aa = 1;
      $where = '';
      if (isset ($_GET['filter'])){
          $hasFilter = 1;
          $where = " WHERE klados = '" . $_GET['filter'] . "'";
      }
      $query = "SELECT *,a.id as aitisi_id from $av_ait a JOIN $av_emp e ON a.emp_id=e.id".$where;

      $result = mysqli_query($mysqlconnection, $query);
      if ($result)
        $num = mysqli_num_rows($result);
      else $num = 0;
      if ($num == 0){
        echo "<h3>Δεν έχουν υποβληθεί αιτήσεις...</h3>";
        } else {
            echo "<h3>Λίστα αιτήσεων</h3>";
            
            // submitted only checkbox
            echo "<input id='submitted_box' type='checkbox' ";
            echo $_SESSION['only_submitted'] == 1  ? 'checked' : '';
            echo " name='only_submitted' onclick='onCheck()'>Εμφάνιση μόνο όσων έχουν υποβληθεί</td>";

            // check if filter
            if ($hasFilter){
                echo "<p>Ενεργό φίλτρο: ".$_GET['filter'];
                echo "&nbsp;&nbsp;<input type=\"button\" class='btn btn-info' onclick=\"location.href='admin.php';\" value=\"Επαναφορά\" /> ";
                echo "<br><br>";
            }
            echo "<div class='table-responsive'>";
            echo "<table id=\"mytbl\" class=\"table table-striped table-hover table-sm\" border=\"2\">\n";
            echo "<thead class='thead-light'>";
            echo "<tr><th>Α/Α</th>\n";
            echo "<th>Επώνυμο</th>\n";
            echo "<th>Όνομα</th>\n";
            echo "<th>Ειδικότητα</th>\n";
            echo $av_type == 3 ? "<th>A.Φ.M.</th>\n<th>Α.Δ.Α.</th>\n" : "<th>A.M.</th>\n";
            echo "<th>Υποβλήθηκε</th>\n";
            echo "<th>Ημ/νία - Ώρα</th>\n";
            if ($av_type == 1){
              echo "<th>Έλεγχος</th>\n";
              echo "<th>Κατηγορία</th>\n";
            }
            echo "</tr>\n</thead><tbody>\n";
            $sub_total = $blanks = 0;

            while ($i < $num){
                $row = mysqli_fetch_assoc($result);
                // if user has selected only submitted apps, skip non-submitted
                if ($_SESSION['only_submitted'] && !$row['submitted']){
                    $i++;
                    continue;
                }
                $id = $row['aitisi_id'];
                $surname = $row['surname'];
                $name = $row['name'];
                $klados = $row['klados'];
                $submitted = $row['submitted'];
                $am = $av_type == 3 ? filterAFM($row['afm']) : $row['am'];
                $ada = $av_type == 3 ? '<td>'.$row['ada'].'</td>' : '';
                $choices = $row['choices'];
                if ($submitted==0)
                {
                    $sub = "Όχι";
                    $my_date = date("d-m-Y, H:i:s", strtotime($row['updated']));
                }
                else
                {
                    $sub = "Ναι";
                    $my_date = date("d-m-Y, H:i:s", strtotime($row['submit_date']));
                    $sub_total++;
                }

                echo "<tr><td>$aa&nbsp;";
                echo "<span title='Προβολή'><a href='admin.php?id=$id&action=view'><img style='border: 0pt none' src='images/view.png'/></a></span>";
                if ($av_type == 1){
                    echo "&nbsp;<span title='Επεξεργασία'><a href='criteria.php?userid=$am'><img style='border: 0pt none' src='images/edit.png'/></a></span>";
                }
                echo "</td>";
                echo "<td><span title='Προβολή'><a href='admin.php?id=$id&action=view'>$surname</a></span></td><td>$name</td>";
                echo "<td><a href='admin.php?filter=$klados'>$klados</a></td><td>$am</td>$ada<td>$sub";
                if ($submitted && $av_canundo)
                {
                    echo "&nbsp;<span title=\"Αναίρεση Υποβολής\"><a href=\"admin.php?id=$id&action=undo\"><img style=\"border: 0pt none;\" src=\"images/undo.png\" onclick='return myaction_yp()'/></a></span>";
                    if (!has_choices($choices))
                    {
                    echo "&nbsp;(KENH)";
                    $blanks++;
                    }
                }
                echo "</td><td>$my_date</td>";
                if ($av_type == 1){
                  echo "<td>";
                  echo $row['checked'] ? 
                      '<span title="'.$row['check_comments'].'">Ναι</span>' : 
                      'Οχι';
                  echo "</td>";
                  // if geniki 2 eidiki, mark
                  $categ = 'Γεν.σε.Γεν.';
                  if ($row['apospash']){
                      $categ = 'Γεν.σε.Ειδ.';
                  } elseif ($row['org_eid']) {
                      $categ = 'Ειδ.σε.Ειδ.';
                  }
                  if ($row['eid_kat']){
                      $categ .=  '&nbsp;<i>(Ειδ.Κατηγορία)</i>';
                  }
                  echo "<td>$categ</td>";
                }
                echo "</tr>";

                $i++;
                $aa++;
            }
            echo "</tbody></table></div>";
            $query = "select count(*) as plithos from $av_emp";
            $result = mysqli_query($mysqlconnection, $query);
            $row = mysqli_fetch_assoc($result);
            // -1 because of admin account
            $total_ypal = $row['plithos'] - 1;
            $saved = $num - $sub_total;
            $nothing = $total_ypal - $sub_total - $saved;
            //echo "Έχουν υποβληθεί <strong>$sub_total</strong> από $total_ypal αιτήσεις.<br>";
            echo "Έχουν υποβληθεί <strong>$sub_total</strong> αιτήσεις.<br>";
            if ($blanks)
                echo "<strong>$blanks</strong> κενές (αρνητικές) αιτήσεις.<br>";
            echo "<br><a href='admin.php?action=nothing'>Εκπ/κοί που δεν έχουν κάνει καμία αποθήκευση ή υποβολή ($nothing)</a>";
            echo "<br><a href='admin.php?action=saved'>Εκπ/κοί που έχουν αποθηκεύσει αλλά δεν έχουν υποβάλει αίτηση ($saved)</a>";
        }       
        echo "</center>";
    }   

     mysqli_close($mysqlconnection);   
  } // of if($loggedin)
    
?>
</center>
</div>
<?php
    require_once('footer.html');
?>
  </body>
</html>
