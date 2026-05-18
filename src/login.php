<?php
if (isset($_SESSION))
    session_destroy();

include_once '../config.php';
include_once 'functions.php';
//session_start();
?>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $av_title . " " . $av_foreas; ?></title>
</head>
<?php include_once('head.php'); ?>

<body>
    <!-- <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed&subset=greek,latin' rel='stylesheet' type='text/css'>
<LINK href="style.css" rel="stylesheet" type="text/css"> -->
    <?php
    include_once("class.login.php");
    $log = new logmein(); //Instentiate the class
    $log->dbconnect(); //Connect to the database
// $log->logout();
    
    $time_rem_str = "";
    $time_rem_str = "";
    $active_from = strtotime($av_active_from);
    $active_until = strtotime($av_active_to);

    if ($active_until && $av_is_active == '1') {
        $time_remaining = $active_until - time();
        if ($time_remaining > 0) {
            $days_rem = floor($time_remaining / 86400);
            $hours_rem = floor(($time_remaining % 86400) / 3600);
            $minutes_rem = floor(($time_remaining % 3600) / 60);
            $time_rem_str = "<br><small><i>(Υπολείπονται: $days_rem ημέρες, $hours_rem ώρες, $minutes_rem λεπτά)</i></small><br>";
        } else if (isset($av_auto_disable) && $av_auto_disable == '1') {
            $av_is_active = '0';
            $query = "UPDATE $av_params SET pvalue='0' WHERE pkey='av_is_active'";
            mysqli_query($conn, $query);
        }
    }

    // Check if system is globally active within dates
    $system_active = ($av_is_active == '1');
    if ($av_auto_disable == 1) {
        if ($active_from && time() < $active_from)
            $system_active = false;
        if ($active_until && time() > $active_until)
            $system_active = false;
    }


    if ($_REQUEST['logout'] == 1) {
        $_SESSION['loggedin'] = false;
        $page = 'login.php';
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $page . '";';
        echo '</script>';
    }

    if (!isset($_REQUEST['action'])) {
        ?>
        <div class="container container-wide mt-5">
            <div class="jumbotron shadow-sm">
                <h1 class="display-4"><?= $av_title; ?></h1>
                <p class="lead"><?= $av_dnsh; ?></p>
                <hr class="my-4">
                <p><?= $av_foreas; ?></p>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    function get_greek_day($timestamp)
                    {
                        $days = [
                            'Sunday' => 'Κυριακή',
                            'Monday' => 'Δευτέρα',
                            'Tuesday' => 'Τρίτη',
                            'Wednesday' => 'Τετάρτη',
                            'Thursday' => 'Πέμπτη',
                            'Friday' => 'Παρασκευή',
                            'Saturday' => 'Σάββατο'
                        ];
                        return $days[date('l', $timestamp)];
                    }
                    $disp_from = $active_from ? get_greek_day($active_from) . ' ' . date("d/m/Y", $active_from) : $av_active_from;
                    $disp_to = $active_until ? get_greek_day($active_until) . ' ' . date("d/m/Y", $active_until) : $av_active_to;
                    $disp_to_time = date("H:i", $active_until);
                    echo "<div class='alert alert-info text-center shadow-sm'><strong>Διάστημα υποβολής αιτήσεων:</strong> από $disp_from έως $disp_to και ώρα $disp_to_time.$time_rem_str</div>";
                    ?>
                </div>
            </div>


            <?php
            if (!$system_active)
                echo "<br><div class='alert alert-danger text-center shadow-sm'>Το σύστημα δεν είναι ενεργό αυτή τη στιγμή.</div><br>";
            if ($av_display_login) {
                echo "<div class='row justify-content-center mt-2'><div class='col-md-8 col-lg-6'>";
                echo "<div class='card card-custom shadow-sm mb-4 border-0'><div class='card-body p-4'>";
                echo "<h4 class='text-center mb-4 text-primary'>Είσοδος στο Σύστημα</h4>";
                $username_text = $av_type == '3' ? 'Επώνυμο εκπ/κού' : 'Αριθμός Μητρώου Εκπ/κού';
                $log->loginform("login", "id", "", $username_text);
                echo "</div></div></div></div>";
            }

            echo "<div class='text-center mt-3'><small class='text-muted'>$av_custom</small><br>";

            echo "<small class='text-muted'>Για τη σωστή λειτουργία της εφαρμογής προτείνεται η χρήση<br>
        ενός σύγχρονου προγράμματος περιήγησης (browser),<br>π.χ. Mozilla Firefox, Google Chrome ή Microsoft Edge.</small></div>";
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
    if ($_REQUEST['action'] == "login") {
        if ($log->login("logon", $_REQUEST['username'], $_REQUEST['password'], $_REQUEST[$av_extra_name]) == true) {
            session_start();
            $_SESSION['timeout'] = time() + (60 * 60);
            // if system inactive, allow only administrator
            if (!$system_active && !is_authorized()) {
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
            echo 'window.location.href="' . $page . '";';
            echo '</script>';
        } else {
            echo "<div class='container'>";
            echo "<h3>H είσοδος απέτυχε...</h3>";
            $extra_col = $av_extra ? " - " . $av_extra_label : '';
            echo $av_type == 3 ? "<br><p>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό Επωνύμου - Α.Φ.Μ.$extra_col</p>" :
                "<br><p>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό Α.Μ. - Α.Φ.Μ.$extra_col</p>";
            echo "<FORM><INPUT Type='button' class='btn btn-info' VALUE='Επιστροφή' onClick='history.go(-1);return true;'></FORM>";
            echo "</div>";
        }
    }
    ?>
        <div class="text-right mt-4"><a href="admin_login.php" class="text-muted"><small>Είσοδος Διαχειριστή</small></a>
        </div>
    </div>
    <?php
    require_once('footer.html');
    ?>
</body>

</html>