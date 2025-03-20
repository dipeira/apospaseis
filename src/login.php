<?php

if (isset($_SESSION))
	session_destroy();

include_once '../config.php';
include_once 'functions.php';
//session_start();

include_once ("class.login.php");   
$log = new logmein();
$log->dbconnect();

// Handle CAS authentication if enabled and requested
if ($av_use_cas && isset($_REQUEST['cas'])) {
    require_once '../vendor/autoload.php';
    
    // Initialize phpCAS
    phpCAS::client(CAS_VERSION_3_0, $av_cas_host, $av_cas_port, '', $av_cas_local_server);
    
    // Set CA certificate if provided
    if (!empty($av_cas_server_ca_cert_path)) {
        phpCAS::setCasServerCACert($av_cas_server_ca_cert_path);
    } else {
        phpCAS::setNoCasServerValidation();
    }
    
    // Force CAS authentication
    phpCAS::forceAuthentication();
    
    // Get employee number from CAS
    $employeeNumber = phpCAS::getAttribute('employeeNumber');
    
    if ($employeeNumber) {
        // Connect to database
        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
        mysqli_set_charset($mysqlconnection, "utf8");
        
        // Check if employee number exists in database
        $query = "SELECT * FROM $av_emp WHERE afm = ?";
        $stmt = mysqli_prepare($mysqlconnection, $query);
        mysqli_stmt_bind_param($stmt, "s", $employeeNumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Valid user - start session
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = $row['am'];
            $_SESSION['timeout'] = time() + (60 * 60);
            
            // Redirect to appropriate page
            $page = ($av_type == 1) ? 'criteria.php' : 'choices.php';
            header("Location: $page");
            exit();
        }
        mysqli_close($mysqlconnection);
    }
    
    // If we get here, authentication failed
    phpCAS::logout();
    die("Η είσοδος απέτυχε. Παρακαλώ επικοινωνήστε με το διαχειριστή.");
}

// Handle logout
if ($_REQUEST['logout'] == 1) {
    $_SESSION['loggedin'] = false;
    if ($av_use_cas) {
        require_once '../vendor/autoload.php';
        phpCAS::client(CAS_VERSION_3_0, $av_cas_host, $av_cas_port, '', $av_cas_local_server);
        phpCAS::logout();
    }
    header("Location: login.php");
    exit();
}

// Handle regular login
if ($_REQUEST['action'] == "login") {
    if ($log->login("logon", $_REQUEST['username'], $_REQUEST['password'], $_REQUEST[$av_extra_name]) == true) {
        session_start();
        $_SESSION['timeout'] = time() + (60 * 60);
        
        // if system inactive, allow only administrator
        if (!$av_is_active && !is_authorized()) {
            echo "<h3>H είσοδος απέτυχε διότι το σύστημα δεν είναι ενεργό...</h3>";
            echo "<FORM><INPUT Type='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'></FORM>";
            die();
        }

        if ($av_use_cas && !is_authorized()) {
            echo "<h3>H είσοδος απέτυχε διότι ο χρήστης δεν είναι διαχειριστής...</h3>";
            echo "<FORM><INPUT Type='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'></FORM>";
            die();
        }
        
        $page = is_authorized() ? 'admin.php' : (($av_type == 1) ? 'criteria.php' : 'choices.php');
        header("Location: $page");
        exit();
    } else {
        $login_error = true;
    }
}
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title><?php echo $av_title." ".$av_foreas; ?></title></head>
    <?php include_once('head.php'); ?>
<body>
<!-- <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed&subset=greek,latin' rel='stylesheet' type='text/css'>
<LINK href="style.css" rel="stylesheet" type="text/css"> -->
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

        <?php if (!$av_is_active): ?>
            <br><h3>Το σύστημα δεν είναι ενεργό αυτή τη στιγμή.</h3><br><br>
        <?php endif; ?>

        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger">
                <h3>H είσοδος απέτυχε...</h3>
                <?php 
                $extra_col = $av_extra ? " - ".$av_extra_label : '';
                echo $av_type == 3 ? 
                    "<p>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό Επωνύμου - Α.Φ.Μ.$extra_col</p>" :
                    "<p>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό Α.Μ. - Α.Φ.Μ.$extra_col</p>";
                ?>
            </div>
        <?php endif; ?>

        <?php if ($av_display_login): ?>
            <div class="row">
                <?php if ($av_use_cas): ?>
                    <div class="col-md-6">
                        <div class="text-center mt-4">
                            <a href="login.php?cas=1" class="btn btn-primary btn-lg">
                                Είσοδος μέσω Κεντρικής Υπηρεσίας Ταυτοποίησης (CAS) του ΠΣΔ
                            </a>
                            <p class="mt-2 text-muted">
                                Παρακαλώ χρησιμοποιήστε το παραπάνω κουμπί για είσοδο στο σύστημα με τα προσωπικά στοιχεία σας στο ΠΣΔ.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <strong>Είσοδος ΜΟΝΟ για διαχειριστές</strong>
                            </div>
                            <div class="card-body">
                                <?php
                                $log->loginform("login", "id", "", 'Όνομα χρήστη', 'Κωδικός');
                                ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-6">
                        <?php
                        $username_text = $av_type == '3' ? 'Επώνυμο εκπ/κού' : 'Αριθμός Μητρώου Εκπ/κού';
                        $log->loginform("login", "id", "", $username_text);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <br><br>
        <small><?= $av_custom ?></small>
        <br><br>
        <small>
            Για τη σωστή λειτουργία της εφαρμογής προτείνεται η χρήση<br>
            ενός σύγχρονου προγράμματος περιήγησης (browser),<br>
            π.χ. Mozilla Firefox, Google Chrome ή Internet Explorer (έκδοση 7 ή νεότερη).
        </small>
    </div>
<?php
    require_once('footer.html');
?>
</body>
</html>