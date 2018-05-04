<?php
	header('Content-type: text/html; charset=utf-8'); 
        require_once "config.php";
        require_once "functions.php";
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Εκτύπωση αίτησης</title>
    <LINK href="style.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        @media print {
        .noprint {display:none;}
        }
        @media screen {
        }
    </style>
  </head>
  <body> 
<?php
    echo "<div class=\"print\">";
    echo "<center><h3>$av_title ($av_foreas)</h3></center>";
    $serial = $_POST['sch_arr'];
    $ser_cred = $_POST['cred_arr'];
    $scharr = unserialize(stripslashes($serial));
    
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password,$db_name);
    mysqli_set_charset($mysqlconnection,"utf8");
    //user 
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
    $ethy = $row['eth'];
    $mhnesy = $row['mhnes'];
    $hmeresy = $row['hmeres'];
    
    // aithsh
    $query = "SELECT * from $av_ait WHERE emp_id=$id";
    $result = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($result);
    
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
   
    
    echo "<center>";
    echo "<table id=\"mytbl\" class=\"\" border=\"2\">\n";
    echo "<thead><th colspan=7>Φόρμα υποβολής στοιχείων</th></thead>";
    echo "<tr><td colspan=2>Ονοματεπώνυμο Εκπ/κού:</td><td colspan=5>".$name." ".$surname."</td></tr>";
    echo "<tr><td colspan=2>Πατρώνυμο: </td><td colspan=5>".$patrwnymo."</td></tr>";
    echo "<tr><td colspan=2>Κλάδος: </td><td colspan=5>".$klados."</td></tr>";
    echo "<tr><td colspan=2>A.M.: </td><td colspan=5>".$am."</td></tr>";
    echo "<tr><td colspan=2>Οργανική θέση: </td><td colspan=5>$organ</td></tr>";    
    if ($av_type == 1)
    {
		echo "<tr><td colspan=2>Συνολική υπηρεσία: </td><td colspan=5>$ethy Έτη, $mhnesy Μήνες, $hmeresy Ημέρες</td></tr>";
        if ($org_eid)
            echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1' checked disabled>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
        else
            echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='org_eid' value='1' disabled>Έχω οργανική στην ειδική αγωγή (σε Ειδικό σχολείο ή τμήμα ένταξης)</td></tr>";
        if ($aitisi)
            echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1' disabled checked>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το 2013</td></tr>";
        else
            echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='aitisi' value='1' disabled>Υπέβαλα αίτηση βελτίωσης θέσης / οριστικής τοποθέτησης το 2013</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><center>Οικογενειακή Κατάσταση</center></td></tr>";
        echo "<tr><td>Γάμος</td><td>";
        echo getGamos($gamos);
        echo "</td><td>Παιδιά</td><td>$paidia</td><td>Δήμος</td><td>$dhmos_anhk</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><center>Εντοπιότητα</center></td></tr>";
        echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας Ηρακλείου που έχω εντοπιότητα</td><td colspan=5>";
        getDimos($dhmos_ent,$mysqlconnection);
        echo "</td></tr>";
        echo "<tr height=20></tr><tr><td colspan=7><center>Συνυπηρέτηση</center></td></tr>";
        echo "<tr><td colspan=2>Δήμος της Περιφερειακής Ενότητας Ηρακλείου που έχω συνυπηρέτηση</td><td colspan=5>";
        getDimos($dhmos_syn,$mysqlconnection);
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
        $blabla = "Δηλώνω υπεύθυνα ότι δεν έχω οριστεί στέλεχος εκπαίδευσης (λ.χ. προϊστάμενος/μένη ολιγοθέσιας σχολικής μονάδας, διευθυντής/ντρια σχολ. μονάδας)<br> και ότι δεν υπηρετώ σε θέση με θητεία που λήγει μετά τις 31-08-2013.";
        echo "<tr height=20></tr><tr><td colspan=7><input type='checkbox' name='ypdil' value='1' checked disabled>$blabla</td></tr>";

        echo "<input type='hidden' name = 'id' value='$id'>";
    }
    
  echo "<tr><td colspan=7><center><strong>Προτιμήσεις</strong></center></td></tr>";
  $i=1;
  $sum=0;
  foreach ($scharr as $arr)
  {
      if ($arr)
        echo "<tr><td>".$i."η προτίμηση</td><td colspan=6>$arr</td></tr>\n";
        //echo $i." epilogh: ".$arr."<br>";
      $i++;
	  //$sum+=$arr;
//	  $sum.=$arr;
  }
  //if (!$sum)
  //    echo "<tr><td colspan=2><center>ΑΡΝΗΤΙΚΗ ΔΗΛΩΣΗ</center></td></tr>\n";
  echo "<tr><td colspan=7><small>Υποβλήθηκε στις: ".  date("d-m-Y, H:i:s", strtotime($row['submit_date']))."</small></td></tr>";
  echo "<tr style='height:30px'><td colspan=7>&nbsp;</td></tr>";
  echo "<tr><td colspan=4></td><td align='center'>Ο/Η εκπαιδευτικός</td></tr>";
  echo "<tr style='height:60px'><td colspan=7>&nbsp;</td></tr>";
  echo "<tr><td colspan=4></td><td align='center'>$name $surname</td></tr>";
  echo "</table>";
  echo "</center>";
  echo "</div>";
  
  echo "<div class=\"noprint\">";
  echo "<center>";
  echo "<br>";
  
  echo "<table><center>";
  echo "<tr><td><input type='button' value='Εκτύπωση' onclick='javascript:window.print()' /></td></tr>";
  echo "<tr><td><form action='index2.php'><input type='submit' value='Επιστροφή'></form></center></td></tr></table>";
  echo "</div>";


echo "</body>";
echo "</html>";

?>