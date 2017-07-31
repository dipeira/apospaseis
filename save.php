<?php   
	header('Content-type: text/html; charset=utf-8'); 
        session_start();
?>
<html>
  <?php require_once('head.php'); ?>
  <body> 
<?php
  $error_found = 0;
  require_once "config.php";
  require_once 'functions.php';
      
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_set_charset($mysqlconnection,"utf8");


  // checks for blanks
  function array_check($arr)
  {
      global $av_choices;
      for ($i = $av_choices; $i>=1; $i--)
      {
          if ((preg_match('/^\s+$/', $arr[$i-1])) == 1)
              $err[] = $i-1;
          if (strlen($arr[$i])>0 && (strlen($arr[$i-1])==0))
              $err[] = $i-1;
      }
      if ($err)
        return array_reverse($err);
  }
  // check for doubles
  function array_dbl($arr)
  {
      if(count(array_unique($arr))<count($arr)){
          $diff = count($arr)-count(array_unique($arr))+1;
          $blanks = count(array_keys($arr,''));
          if ($diff != $blanks)
              return 1;
          else 
              return 0;
      }
      else
          return 0;
  }
  
  // check if called from part 1 or 2 of the form and act accordingly
  //if page 1
  if (isset($_POST['part']))
  {
      $gamos = ($_POST['gamos']=='') ? 0 : $_POST['gamos'];
      $paidia = ($_POST['paidia']=='') ? 0 : $_POST['paidia'];
      $aitisi = (!isset($_POST['aitisi'])) ? 0 : $_POST['aitisi'];
      $dhmos_anhk = $_POST['dhmos_anhk'];
      $dhmos_ent = $_POST['dhmos_ent'];
      $dhmos_syn = $_POST['dhmos_syn'];
      $eidikh = (!isset($_POST['eidikh'])) ? 0 : $_POST['eidikh'];
      $apospash = (!isset($_POST['apospash'])) ? 0 : $_POST['apospash'];
      $didakt = (!isset($_POST['didakt'])) ? 0 : $_POST['didakt'];
      $metapt = (!isset($_POST['metapt'])) ? 0 : $_POST['metapt'];
      $didask = (!isset($_POST['didask'])) ? 0 : $_POST['didask'];
      $paidag = (!isset($_POST['paidag'])) ? 0 : $_POST['paidag'];
      $eth = ($_POST['eth']=='') ? 0 : $_POST['eth'];
      $mhnes = ($_POST['mhnes']=='') ? 0 : $_POST['mhnes'];
      $hmeres = ($_POST['hmeres']=='') ? 0 : $_POST['hmeres'];
      $ygeia = $_POST['ygeia'];
      $ygeia_g = $_POST['ygeia_g'];
      $ygeia_a = $_POST['ygeia_a'];
      $eksw = (!isset($_POST['eksw'])) ? 0 : $_POST['eksw'];
      $comments = (strlen($_POST['comments'])==0) ? '' : $_POST['comments'];  
      $comments = str_replace('"', "", $comments);
      $comments = str_replace("'", "", $comments);
      $org_eid = (!isset($_POST['org_eid'])) ? 0 : $_POST['org_eid'];
      $allo = $_POST['allo'];
        // Oxi synhphrethsh gia osoys den einai eggamoi
		if ($gamos<>1)
          $dhmos_syn = 0;
		  
	  if (!$_POST['ypdil'])
      {
        echo "<h2>Λάθος!</h2>";
        echo "Για να συνεχίσετε, πρέπει <strong>υποχρεωτικά</strong> να διαβάσετε και να τσεκάρετε την υπεύθυνη δήλωση στο τέλος της προηγούμενης σελίδας.<br><br>";
        echo "<form action='index.php'><input type='submit' value='Επιστροφή'></form>";
        exit;
      }
      
        $emp_id = $_POST['id'];
        
        $query = "SELECT * from $av_ait WHERE emp_id = $emp_id";
        $result = mysqli_query($mysqlconnection, $query);
        if (mysqli_num_rows($result) > 0)
        {
            $query = "UPDATE $av_ait SET gamos=$gamos,paidia=$paidia,aitisi=$aitisi,dhmos_anhk='$dhmos_anhk',dhmos_ent=$dhmos_ent,dhmos_syn=$dhmos_syn,eidikh=$eidikh,
                apospash=$apospash,didakt=$didakt,metapt=$metapt,didask=$didask,eth=$eth,mhnes=$mhnes,hmeres=$hmeres,ygeia=$ygeia,ygeia_g=$ygeia_g,ygeia_a=$ygeia_a,eksw=$eksw,
                comments='$comments',org_eid=$org_eid,allo='$allo',ypdil=1,paidag=$paidag WHERE emp_id='$emp_id'";
            mysqli_query($mysqlconnection, $query);
        }
        else
        {
                $qry0 = "INSERT INTO $av_ait (emp_id,gamos,paidia,aitisi,dhmos_anhk,dhmos_ent,dhmos_syn,eidikh,apospash,didakt,metapt,didask,eth,mhnes,hmeres,ygeia,ygeia_g,ygeia_a,eksw,comments,org_eid,allo,ypdil,paidag) ";
                $qry1 = "values ($emp_id,$gamos,$paidia,$aitisi,'$dhmos_anhk',$dhmos_ent,$dhmos_syn,$eidikh,$apospash,$didakt,$metapt,$didask,$eth,$mhnes,$hmeres,$ygeia,$ygeia_g,$ygeia_a,$eksw,'$comments',$org_eid,'$allo',1,$paidag)";
                $query = $qry0.$qry1;
        }
            mysqli_query($mysqlconnection, $query);
                
        // if save button was pressed return, else proceed to 2nd page
        if (isset($_POST['save']))
           echo "  <meta http-equiv=\"refresh\" content=\"0; URL=index.php\">";
        else
           echo "  <meta http-equiv=\"refresh\" content=\"0; URL=index2.php\">";
  }
  else
  // if page 2
  {
    $sch_arr = array();
    for ($i = 1; $i < $av_choices+1; $i++) {
        array_push($sch_arr, $_POST['p'.$i]);
    }

    $error = array_check($sch_arr);
    $dbl = array_dbl($sch_arr);
    $ret="";
    // check if all schools blank
    $sum = 0;
    foreach ($sch_arr as $el)
        $sum += strlen ($el);
    // only for apospaseis (επιτρέπει αρνητική δήλωση (χωρίς καμία επιλογή) μόνο στις βελτιώσεις, στις αποσπάσεις βγάζει μήνυμα λάθους)
	if (!$sum && $av_type == 1){
        echo "Πρέπει να εισάγετε τουλάχιστον μία επιλογή";
        $error_found = 1;
    }
    // if blanks
    if (!empty($error)){
        $error_found = 1;
        echo "<br>ΛΑΘΟΣ στην(-ις) προτίμηση(-εις): ";
        foreach ($error as $ar_er)
            $ret .= ($ar_er+1).", ";
        $ret = rtrim($ret,", ");
        echo "<strong>$ret</strong>";
    }
    // if doubles
    if ($dbl)
    {
        $error_found = 1;
        echo "<br>ΛΑΘΟΣ: H αίτηση περιέχει διπλές προτιμήσεις:";
        $arr2 = array_diff_key($sch_arr, array_unique($sch_arr));
        //print_r($arr2);
        foreach ($arr2 as $ar1)
            if (!empty($ar1))
                echo "<br><strong>".getSchooledc($ar1,$mysqlconnection);
        //echo "</body></html>";
        //exit;
    }

    if ($error_found && $_POST['submit'])
    {
        echo "<br><br><strong>Βρέθηκαν λάθη και η υποβολή δεν μπορεί να πραγματοποιηθεί.</strong>";
        echo "<form action='index2.php'><input type='submit' value='Επιστροφή'></form>";
        exit;
    }
    if ($error_found)
    {
        echo "<br><br>Παρακαλώ διορθώστε.";
        echo "<form action='index2.php'><input type='submit' value='Επιστροφή'></form>";
    }

    for ($i = 1; $i < 21; $i++) {
      ${'p'.$i} = $_POST['p'.$i] ? getSchoolcode($_POST['p'.$i], $mysqlconnection) : 0;
    }

    $emp_id = $_POST['id'];

    if ($_POST['submit'])
        $submit=1;

    $query = "SELECT * from $av_ait WHERE emp_id = $emp_id";
    $result = mysqli_query($mysqlconnection, $query);
    $num_rows = $result ? mysqli_num_rows($result) : 0;
    $exists = $num_rows > 0 ? 1 : 0;
    // serialize user choices
    $ser_p = serialize($sch_arr);

    if ($exists)
    {
        if ($submit){
            $phpdate = date('Y-m-d H:i:s'); 
            $mysqldate = date( 'Y-m-d H:i:s', $phpdate );
            $query = "UPDATE $av_ait SET submitted=1,submit_date=NOW(),choices='$ser_p' WHERE emp_id='$emp_id'";
        }
        else {
            $query = "UPDATE $av_ait SET choices='$ser_p' WHERE emp_id='$emp_id'";
        }
        mysqli_query($mysqlconnection, $query);
    }
    else
    {
        if ($submit)
        {
            $query = "INSERT INTO $av_ait (emp_id,choices,submit_date,submitted) values ($emp_id,'$ser_p',".date("Y-m-d H:i:s").",1)";
        }
        else
        {
            $query = "INSERT INTO $av_ait (emp_id,choices) values ($emp_id,'$ser_p')";
        }
        mysqli_query($mysqlconnection, $query);
    }
    mysqli_close($mysqlconnection);

    if (!$error_found)
        echo "  <meta http-equiv=\"refresh\" content=\"0; URL=index2.php\">";
    else
        exit;

    if (isset($_POST['save']))
        echo "  <meta http-equiv=\"refresh\" content=\"0; URL=index2.php\">";
    else
        echo "  <meta http-equiv=\"refresh\" content=\"0; URL=index.php\">";
}
echo "</body>";
echo "</html>";

?>
