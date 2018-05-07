<?php

  header('Content-type: text/html; charset=utf-8'); 
  require_once "config.php";
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
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title><?php echo $av_title." ($av_foreas) - Διαχείριση"; ?></title>
	
	<script type="text/javascript" src="js/jquery.js"></script>
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
    </script>
    
    <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed:400,400italic&subset=greek,latin' rel='stylesheet' type='text/css'>

<?php
  if ($loggedin)
  {
    // check if admin 
    if ($_SESSION['user']!=$av_admin)
        die("Authentication error");

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    mysqli_set_charset($mysqlconnection,"utf8");
  
    // if POST change apo_employee organ & synolikh yphresia
    if (isset($_POST['porgan']))
    {
        $qry = "SELECT kwdikos FROM $av_sch WHERE name='".$_POST['porgan']."'";
        $res = mysqli_query($mysqlconnection, $qry);
        $row = mysqli_fetch_assoc($res);
        $organ = $row['kwdikos'];
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
        $query = "SELECT *,e.mhnes as mhnesy,e.hmeres as hmeresy from $av_ait a JOIN $av_emp e ON a.emp_id=e.id WHERE a.id=$id";
        $result = mysqli_query($mysqlconnection, $query);
        $row = mysqli_fetch_assoc($result);
        
        $name = $row['name'];
        $surname = $row['surname'];
        $patrwnymo = $row['patrwnymo'];
        $klados = $row['klados'];
        $am = $row['am'];
        $organ = $row['org'];
        $organ = getSchooledc($organ, $mysqlconnection);
        $ethy = $row['eth'];
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
        $eth = $row['eth'];
        $mhnes = $row['mhnes'];
        $hmeres = $row['hmeres'];
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
        echo "<table id=\"mytbl\" class=\"imagetable\" border=\"2\">\n";
        echo "<thead><th colspan=7>Προτιμήσεις εκπαιδευτικού</th></thead>";
        echo "<tr><td colspan=2>Ονοματεπώνυμο Εκπ/κού:</td><td colspan=5>".$name." ".$surname."</td></tr>";
        echo "<tr><td colspan=2>Πατρώνυμο: </td><td colspan=5>".$patrwnymo."</td></tr>";
        echo "<tr><td colspan=2>Κλάδος: </td><td colspan=5>".$klados."</td></tr>";
        echo "<tr><td colspan=2>A.M.: </td><td colspan=5>".$am."</td></tr>";
        echo "<form id='src' name='src' action='admin.php' method='POST'>\n";
        // organ & synolikh yphresia can be changed
        if ($submitted || !$av_canalter)
            echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>".$organ."</td></tr>";
        else {
            $schools = getSchools('organ',$dim,0,$mysqlconnection,$organ);
            echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>".$schools."</td></tr>";
        }
        // if apospaseis, show related data
        if ($av_type == 1)
        {
            if ($submitted || !$av_canalter) {
              echo "<tr><td colspan=2>Συνολική υπηρεσία: </td><td colspan=5>$ethy Έτη, $mhnesy Μήνες, $hmeresy Ημέρες</td></tr>";
            } else {
              echo "<tr><td colspan=2>Συνολική υπηρεσία: </td><td colspan=5><input size=2 name='ethy' value=$ethy> Έτη,<input size=2 name='mhnesy' value=$mhnesy> Μήνες,<input size=2 name='hmeresy' value=$hmeresy> Ημέρες</td></tr>";
            }
            // end of changeable elements
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
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας Ηρακλείου που έχω εντοπιότητα</td><td colspan=5>";
            getDimos($dhmos_ent, $mysqlconnection);
            echo "</td></tr>";
            echo "<tr height=20></tr><tr><td colspan=7><center>Συνυπηρέτηση</center></td></tr>";
            echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας Ηρακλείου που έχω συνυπηρέτηση</td><td colspan=5>";
            getDimos($dhmos_syn, $mysqlconnection);
            echo "</td></tr>";
            if ($eidikh)
                echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' disabled checked>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
            else
                echo "<tr height=20></tr><tr><td colspan=2><center>Ειδική Κατηγορία</center></td><td colspan=5><input type='checkbox' name='eidikh' value='1' disabled>Επιθυμώ να υπαχθώ σε ειδική κατηγορία αποσπάσεων</td></tr>";
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
            echo "ε) Προϋπηρεσία στην Ειδ.Αγωγή: $eth Έτη, $mhnes Μήνες, $hmeres Ημέρες<br>";
            echo "στ) Άλλο προσόν (π.χ. Braille, νοηματική): $allo";
            echo "</td></tr></div>";

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
            $blabla = "Δηλώνω υπεύθυνα ότι δεν έχω οριστεί στέλεχος εκπαίδευσης (λ.χ. προϊστάμενος/μένη ολιγοθέσιας σχολικής μονάδας, διευθυντής/ντρια σχολ. μονάδας)<br> και ότι δεν υπηρετώ σε θέση με θητεία που λήγει μετά τις 31-08-$av_etos.";
            echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='ypdil' value='1' checked disabled>$blabla</td></tr>";
            echo "</table>";    
        }
        echo "<br>";
        echo "<table id=\"mytbl\" class=\"imagetable\" border=\"2\">\n";
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
        if (!$submitted && $av_canalter)
            echo "<tr><td colspan=4><center><input type='submit' onclick='return myaction()' value='Αποθήκευση'></form></center></td></tr>";
        echo "<tr><td colspan=4><center><form action='admin.php'><input type='submit' value='Έπιστροφή'></form></center></td></tr>";   
    }
    ///////////////////
    // export to excel
    ///////////////////
    elseif ($_GET['action']=="export")
    {
        $apospaseis = $av_type == 1 ? 1 : 0;
        $i=0;
        $data = array();
        if ($apospaseis)
            $query = "SELECT a.*,e.am,e.name,e.surname,e.patrwnymo,s.name,e.eth,e.mhnes,e.hmeres,e.klados
            FROM $av_ait a 
            JOIN $av_emp e ON a.emp_id=e.id 
            JOIN $av_sch s ON s.kwdikos = e.org 
            WHERE submitted=1";
        else
            $query = "SELECT e.am,e.name,e.surname,e.patrwnymo,s.name,e.eth,e.mhnes,e.hmeres,e.klados,a.choices
            FROM $av_ait a 
            JOIN $av_emp e ON a.emp_id=e.id 
            JOIN $av_sch s ON s.kwdikos = e.org 
            WHERE submitted=1";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num == 0) {
            echo "Δεν υπάρχουν δεδομένα για εξαγωγή!<br><br>";
            echo "<form action='admin.php'><input type='submit' value='Επιστροφή'></form>";
            die();
        }
        // fetch data
        while ($row0 = mysqli_fetch_array($result,MYSQLI_NUM)) {
            $tmpdata = $row0;
            // fetch choices as array
            $choices = $apospaseis ? unserialize($row0[28]) : unserialize($row0[9]);
            // remove choices column
            if ($apospaseis)
                unset($tmpdata[28]);
            else
                unset($tmpdata[9]);
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
                $data[] = $tmpdata;
            } else {
                $data[] = array_merge($tmpdata, $choices);
            }
        }
        
        $columns = array();
        if ($apospaseis)
        {
            $qry1 = "SHOW COLUMNS FROM $av_ait";
            $res1 = mysqli_query($mysqlconnection, $qry1);
        
            while ($row = mysqli_fetch_array($res1,MYSQLI_NUM))
                $columns[] = $row[0];
            // remove 'choices' from columns
            unset($columns[28]);
        }
                        
        array_push($columns,'am','name','surname','patrwnymo','school','ethy','mhnesy','hmeresy','klados');
        if ($av_dntes) {
            $columns[] = "choices";
        }
        else {
            for ($j=0; $j<$av_choices; $j++) {
                $columns[] = "p".($j+1);
            }
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
	
        echo "<BUTTON TYPE='submit'>Εξαγωγή στο excel</BUTTON>";
        echo "</form>";
        echo "<form action='admin.php'><input type='submit' value='Επιστροφή'></form>";
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
                    $query = "SELECT * FROM $av_emp e LEFT JOIN $av_ait a ON e.id = a.emp_id WHERE a.id IS NULL";
                    echo "<h3>Εκπ/κοί που δεν έχουν κάνει καμία αποθήκευση ή υποβολή</h3>";
            }
            else
            {
                    $query = "SELECT * from $av_ait a JOIN $av_emp e ON a.emp_id=e.id WHERE submitted = 0";
                    echo "<h3>Εκπ/κοί που έχουν αποθηκεύσει αλλά δεν έχουν υποβάλει αίτηση</h3>";
            }
            $result = mysqli_query($mysqlconnection, $query);
            $num = mysqli_num_rows($result);
            echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead>";
            echo "<tr><th>Α/Α</th>\n";
            echo "<th>Επώνυμο</th>\n";
            echo "<th>Όνομα</th>\n";
            echo "<th>Ειδικότητα</th>\n";
            echo "<th>A.M.</th>\n";
            while ($i < $num){
                $row = mysqli_fetch_assoc($result);
                $id = $row['id'];
                $surname = $row['surname'];
                $name = $row['name'];
                $klados = $row['klados'];
                $am = $row['am'];
                echo "<tr><td>$id</td><td>$surname</td><td>$name</td><td>$klados</td><td>$am</td></tr>";

                $i++;
            }
            echo "</table>";
            echo "Σύνολο: $num";
            echo "<form action='admin.php'><input type='submit' value='Επιστροφή'></form>";	
    }
    // of nothing or not submitted
    ///////////////////
    // Main admin view
    ///////////////////
    else
    {
      echo "<center><h2>$av_title ($av_foreas) <br> Διαχείριση</h2></center>";
      echo "<center>";
      if (file_exists('init.php')){
          echo "<p><b>ΠΡΟΣΟΧΗ</b>: Παρακαλώ διαγράψτε το αρχείο <b>init.php</b> για λόγους ασφαλείας!</p>";
      }
      echo "<h3>Λίστα αιτήσεων</h3>";

      $i=0;
      $aa = 1;
      $query = "SELECT *,a.id as aitisi_id from $av_ait a JOIN $av_emp e ON a.emp_id=e.id";

      $result = mysqli_query($mysqlconnection, $query);
      if ($result)
        $num = mysqli_num_rows($result);
      else $num = 0;

      echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
      echo "<thead>";
      echo "<tr><th>Α/Α</th>\n";
      echo "<th>Επώνυμο</th>\n";
      echo "<th>Όνομα</th>\n";
      echo "<th>Ειδικότητα</th>\n";
      echo "<th>A.M.</th>\n";
      echo "<th>Υποβλήθηκε</th>\n";
      echo "<th>Ημ/νία - Ώρα</th>\n";
        echo "</tr>\n</thead>\n";
      $sub_total = $blanks = 0;

      while ($i < $num){
        $row = mysqli_fetch_assoc($result);
        $id = $row['aitisi_id'];
        $surname = $row['surname'];
        $name = $row['name'];
        $klados = $row['klados'];
        $submitted = $row['submitted'];
        $am = $row['am'];
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

        echo "<tr><td>$aa</td><td><a href=\"admin.php?id=$id&action=view\">$surname</a></td><td>$name</td><td>$klados</td><td>$am</td><td>$sub";
        if ($submitted && $av_canundo)
        {
            echo "&nbsp;<span title=\"Αναίρεση Υποβολής\"><a href=\"admin.php?id=$id&action=undo\"><img style=\"border: 0pt none;\" src=\"images/undo.png\" onclick='return myaction_yp()'/></a></span>";
            if (!has_choices($choices))
            {
              echo "&nbsp;(KENH)";
              $blanks++;
            }
        }
        echo "</td><td>$my_date</td></tr>";

        $i++;
        $aa++;
      }
      echo "</table>";
			
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
			//echo "<br><a href='admin.php?action=nothing'>Εκπ/κοί που δεν έχουν κάνει καμία αποθήκευση ή υποβολή ($nothing)</a>";
			echo "<br><a href='admin.php?action=saved'>Εκπ/κοί που έχουν αποθηκεύσει αλλά δεν έχουν υποβάλει αίτηση ($saved)</a>";
            echo "<br><br><a href='admin.php?action=export'>Εξαγωγή</a><br>";
            echo "<br><a href=\"import.php\">Εισαγωγή Δεδομένων</a><br>";
            echo "<br><a href=\"params.php\">Μεταβολή παραμέτρων</a><br>";
            echo "<br><form action='login.php'><input type='hidden' name = 'logout' value=1><input type='submit' value='Έξοδος'></form>";
            echo "</center>";
    }   

     mysqli_close($mysqlconnection);   
  } // of if($loggedin)
    
?>
</center>
</div>
  </body>
</html>
