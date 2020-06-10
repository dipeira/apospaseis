<?php
  header('Content-type: text/html; charset=utf-8'); 
  session_start();
  require_once "../config.php";
  //require_once 'functions.php';
  $_SESSION['auth'] = NULL;
?>
<html>
  <?php require_once('head.php'); ?>
  <body>
<?php
  if (!isset($_POST['pass']) && !isset($_SESSION['auth']))
  {
    echo "<h2> Αρχικοποίηση βάσης δεδομένων εφαρμογής</h2>";
    echo "<h3>Δημιουργία βάσης δεδομένων ή επαναφορά στην αρχική κατάσταση (κενή βάση)</h3>";
    echo "ΣΗΜ.: To script αυτό δημιουργεί τη βάση με όνομα <strong>$db_name</strong> και πίνακες <strong>$av_emp, $av_ait, $av_sch, $av_dimos</strong>.<br><br>";
    echo "ΠΡΟΣΟΧΗ: Πριν προχωρήσετε, πρέπει να ρυθμίσετε τις παραμέτρους στο αρχείο <strong><i>config.php</i></strong><br><br>";
    echo "<strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη...</strong><br><br>";
    echo "<form action='init.php' method='POST'>";
    echo "Δώστε κωδικό ασφαλείας για αρχικοποίηση <small>(βλ. config.php)</small>:&nbsp;&nbsp;&nbsp;<input type='text' id='pass' name='pass'><br><input type='submit' value='Αρχικοποίηση'></form>";
    exit;
  }
if (($_POST['pass'] == $av_init_pass) && !isset($_SESSION['auth'])) {
    $_SESSION['auth'] = 1;
}
elseif (!isset ($_SESSION['auth'])) {
  die ('Λάθος κωδικός');
}

if ($_SESSION['auth'])
{
    echo "<h3>Αρχικοποίηση βάσης δεδομένων</h3>";

    // create database
    # MySQL with PDO_MYSQL  
    $db = new PDO("mysql:host=$db_host", $db_user, $db_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
    
    $tags = array (
      'apo_employee', 'apo_dimos',
      'apo_aitisi','apo_school','apo_params',
      '%aposp%'
    );
    $elem = array ( 
      $av_emp, $av_dimos, 
      $av_ait, $av_sch, $av_params, 
      $db_name
    );
    $query = str_replace($tags, $elem, file_get_contents('aposp.sql'));

    try {
      $db->exec($query);
    }
    catch (PDOException $e){
      echo "H αρχικοποίηση της Βάσης Δεδομένων απέτυχε...";
      echo $e->getMessage();
      die();
    }
    $db = NULL;
    echo "<h2>Η αρχικοποίηση της βάσης δεδομένων ήταν επιτυχής!</h2>";
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password,$db_name);
    mysqli_set_charset($mysqlconnection,"utf8");    

    // insert admin user
    $query = "INSERT INTO $av_emp(name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres, lastlogin) VALUES ('admin', '', '', '', '$av_admin', '$av_admin_pass', '0', '0', '0', '0', CURRENT_TIMESTAMP)";
    $result = mysqli_query($mysqlconnection, $query);
    if ($result) {
      echo "<h3>Έγινε επιτυχής εισαγωγή διαχειριστή στη βάση δεδομένων!</h3>";
    }

    echo "<h3>Επόμενο βήμα: <a href='import.php'>Εισαγωγή δεδομένων</a></h2>";

    echo "<h3>Για λόγους ασφαλείας, παρακαλώ διαγράψτε το αρχείο init.php αφου τελειώσετε...</h3><br>";
    echo "<a href='login.php'>Επιστροφή</a>";
    exit;
}	
	
?>

</body>
</html>
	