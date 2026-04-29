<?php
session_start();
include_once '../config.php';
include_once 'functions.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['suffix']) && !empty($_POST['suffix'])) {
        $suffix = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['suffix']);
        $new_emp = "apo_employee_" . $suffix;
        $new_ait = "apo_aitisi_" . $suffix;
        $new_sch = "apo_school_" . $suffix;

        // Create new tables based on original structure
        $queries = [
            "CREATE TABLE IF NOT EXISTS `$new_emp` LIKE `apo_employee`",
            "CREATE TABLE IF NOT EXISTS `$new_ait` LIKE `apo_aitisi`",
            "CREATE TABLE IF NOT EXISTS `$new_sch` LIKE `apo_school`",
            "INSERT INTO `$new_sch` SELECT * FROM `apo_school`"
        ];

        foreach ($queries as $q) {
            mysqli_query($conn, $q);
        }

        // Update apo_params
        setParam('av_emp', "'$new_emp'", $conn);
        setParam('av_ait', "'$new_ait'", $conn);
        setParam('av_sch', "'$new_sch'", $conn);

        $message = "Οι νέοι πίνακες δημιουργήθηκαν και ορίστηκαν επιτυχώς.";
        
        // Reload params
        $av_emp = $new_emp;
        $av_ait = $new_ait;
        $av_sch = $new_sch;

    } elseif (isset($_POST['av_emp']) && isset($_POST['av_ait']) && isset($_POST['av_sch'])) {
        $new_emp = mysqli_real_escape_string($conn, $_POST['av_emp']);
        $new_ait = mysqli_real_escape_string($conn, $_POST['av_ait']);
        $new_sch = mysqli_real_escape_string($conn, $_POST['av_sch']);

        setParam('av_emp', "'$new_emp'", $conn);
        setParam('av_ait', "'$new_ait'", $conn);
        setParam('av_sch', "'$new_sch'", $conn);

        $message = "Οι επιλεγμένοι πίνακες ορίστηκαν επιτυχώς.";
        
        // Reload params
        $av_emp = $new_emp;
        $av_ait = $new_ait;
        $av_sch = $new_sch;
    }
}

function get_tables_exact_or_suffixed($base, $conn) {
    $tables = [];
    $res = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($res)) {
        $t = $row[0];
        if ($t === $base || strpos($t, $base . '_') === 0) {
            $tables[] = $t;
        }
    }
    return $tables;
}

$emp_tables = get_tables_exact_or_suffixed('apo_employee', $conn);
$ait_tables = get_tables_exact_or_suffixed('apo_aitisi', $conn);
$sch_tables = get_tables_exact_or_suffixed('apo_school', $conn);

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ρύθμιση Έτους</title>
</head>
<?php include_once('head.php'); ?>
<body>
<div class="container">
    <div class="jumbotron">
        <h1 class="display-4">Ρύθμιση Έτους (Πίνακες)</h1>
        <p class="lead">Επιλέξτε τους ενεργούς πίνακες ή δημιουργήστε νέους για το τρέχον έτος.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Δημιουργία νέου έτους</div>
        <div class="card-body">
            <form method="post" action="year_setup.php">
                <div class="form-group">
                    <label>Εισάγετε κατάληξη (π.χ. 2024)</label>
                    <input type="text" name="suffix" class="form-control" placeholder="Κατάληξη πινάκων...">
                    <small class="form-text text-muted">Θα δημιουργηθούν οι πίνακες: apo_employee_XXXX, apo_aitisi_XXXX, apo_school_XXXX</small>
                </div>
                <button type="submit" class="btn btn-success">Δημιουργία & Ορισμός</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Επιλογή υφιστάμενων πινάκων</div>
        <div class="card-body">
            <form method="post" action="year_setup.php">
                <div class="form-group">
                    <label>Πίνακας Υπαλλήλων ($av_emp)</label>
                    <select name="av_emp" class="form-control">
                        <?php foreach ($emp_tables as $t): ?>
                            <option value="<?= $t ?>" <?= $t == $av_emp ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Πίνακας Αιτήσεων ($av_ait)</label>
                    <select name="av_ait" class="form-control">
                        <?php foreach ($ait_tables as $t): ?>
                            <option value="<?= $t ?>" <?= $t == $av_ait ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Πίνακας Σχολείων ($av_sch)</label>
                    <select name="av_sch" class="form-control">
                        <?php foreach ($sch_tables as $t): ?>
                            <option value="<?= $t ?>" <?= $t == $av_sch ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Αποθήκευση Επιλογών</button>
            </form>
        </div>
    </div>
    
    <a href="admin.php" class="btn btn-secondary">Επιστροφή στη Διαχείριση</a>
</div>
<?php require_once('footer.html'); ?>
</body>
</html>
