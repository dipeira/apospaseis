<?php
if (isset($_SESSION))
	session_destroy();

include_once '../config.php';
include_once 'functions.php';
//session_start();
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title><?php echo $av_title." ".$av_foreas; ?></title></head>
    <?php include_once('head.php'); ?>
<body>
<!-- <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed&subset=greek,latin' rel='stylesheet' type='text/css'>
<LINK href="style.css" rel="stylesheet" type="text/css"> -->
<?php
include_once ("class.login.php");   
    $log = new logmein();     //Instentiate the class
    $log->dbconnect();        //Connect to the database
   // $log->logout();
    
if ($_REQUEST['logout']==1)
{
    $_SESSION['loggedin'] = false;
    $page = 'login.php';
	echo '<script type="text/javascript">';
	echo 'window.location.href="'.$page.'";';
	echo '</script>';
}

if (!isset($_REQUEST['action']))
{
    ?>
    <div class="container">
        <div class="jumbotron">
            <h1 class="display-4"><?= $av_title; ?></h1>
            <p class="lead"><?= $av_dnsh; ?></p>
            <hr class="my-4">
            <p><?= $av_foreas; ?></p>
        </div>
<div class="row">
    <div class="col-md-12">
<?="<h4>Διάστημα υποβολής αιτήσεων: από $av_active_from έως $av_active_to και ώρα $av_active_to_time.</h4>";?>
</div>
</div>
    

<?php
    if (!$av_is_active)
        echo "<br><h3>Το σύστημα δεν είναι ενεργό αυτή τη στιγμή.</h3><br><br>";
    if ($av_display_login){
        $username_text = $av_type == '3' ? 'Επώνυμο εκπ/κού' : 'Αριθμός Μητρώου Εκπ/κού';
        $log->loginform("login", "id", "", $username_text);
    }
    
	echo "<br><br><small>$av_custom</small><br><br>";
		
    echo "<small>Για τη σωστή λειτουργία της εφαρμογής προτείνεται η χρήση<br>
        ενός σύγχρονου προγράμματος περιήγησης (browser),<br>π.χ. Mozilla Firefox, Google Chrome ή Internet Explorer (έκδοση 7 ή νεότερη).</small>";
	echo "<br><br>";
	
}
//if (!$_SESSION['timeout'])
//    $_SESSION['timeout'] = time() + (30 * 60);
//$timeout = time() > $_SESSION['timeout'];
//if ($timeout){
//    echo "Η συνεδρία σας έχει λήξει.<br>Παρακαλώ κάντε ξανά είσοδο στο σύστημα.";
//    echo "<form action='login.php'><input type='submit' value='Είσοδος'></form>";
//    exit;
//}
//if($_REQUEST['action'] == "login" && !$timeout){
if($_REQUEST['action'] == "login"){
    if($log->login("logon", $_REQUEST['username'], $_REQUEST['password'], $_REQUEST[$av_extra_name]) == true)
    {
        session_start();
        $_SESSION['timeout'] = time() + (60 * 60);
		// if system inactive, allow only administrator
		if (!$av_is_active && !is_authorized())
		{
			echo "<h3>H είσοδος απέτυχε διότι το σύστημα δεν είναι ενεργό...</h3>";
			echo "<FORM><INPUT Type='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'></FORM>";
			die();
		}
        if (is_authorized()) {
            $page = 'admin.php';
        } else {
            $page = ($av_type == 1) ? 'criteria.php' : 'choices.php';    
        }
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$page.'";';
        echo '</script>';
    }
else {
    echo "<div class='container'>";
    echo "<h3>H είσοδος απέτυχε...</h3>";
    $extra_col = $av_extra ? " - ".$av_extra_label : '';
    echo $av_type == 3 ? "<br><p>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό Επωνύμου - Α.Φ.Μ.$extra_col</p>" :
        "<br><p>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό Α.Μ. - Α.Φ.Μ.$extra_col</p>";
    echo "<FORM><INPUT Type='button' class='btn btn-info' VALUE='Επιστροφή' onClick='history.go(-1);return true;'></FORM>";
    echo "</div>";
}
}
?>
</div>
<?php
    require_once('footer.html');
?>
</body>
</html>