<?php
header('Content-type: text/html; charset=utf-8'); 
require_once "../config.php";
require_once 'functions.php';
session_start();

if($_SESSION['loggedin'] == false || !is_admin()) {   
    header("Location: login.php");
    exit;
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_set_charset($mysqlconnection,"utf8");

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['insert'])) {
        $name = mysqli_real_escape_string($mysqlconnection, $_POST['name']);
        $surname = mysqli_real_escape_string($mysqlconnection, $_POST['surname']);
        $patrwnymo = mysqli_real_escape_string($mysqlconnection, $_POST['patrwnymo']);
        $klados = mysqli_real_escape_string($mysqlconnection, $_POST['klados']);
        $am = (int)$_POST['am'];
        $afm = (int)$_POST['afm'];
        $org = (int)$_POST['org'];
        $eth = (int)$_POST['eth'];
        $mhnes = (int)$_POST['mhnes'];
        $hmeres = (int)$_POST['hmeres'];
        $moria = isset($_POST['moria']) ? (float)$_POST['moria'] : null;
        $entopiothta = mysqli_real_escape_string($mysqlconnection, $_POST['entopiothta']);
        $synyphrethsh = mysqli_real_escape_string($mysqlconnection, $_POST['synyphrethsh']);
        
        $query = "INSERT INTO $av_emp (name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres, moria, entopiothta, synyphrethsh)
                  VALUES ('$name', '$surname', '$patrwnymo', '$klados', $am, $afm, $org, $eth, $mhnes, $hmeres, " . ($moria !== null ? $moria : 'NULL') . ", '$entopiothta', '$synyphrethsh')";
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Ο υπάλληλος προστέθηκε με επιτυχία.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $name = mysqli_real_escape_string($mysqlconnection, $_POST['name']);
        $surname = mysqli_real_escape_string($mysqlconnection, $_POST['surname']);
        $patrwnymo = mysqli_real_escape_string($mysqlconnection, $_POST['patrwnymo']);
        $klados = mysqli_real_escape_string($mysqlconnection, $_POST['klados']);
        $am = (int)$_POST['am'];
        $afm = (int)$_POST['afm'];
        $org = (int)$_POST['org'];
        $eth = (int)$_POST['eth'];
        $mhnes = (int)$_POST['mhnes'];
        $hmeres = (int)$_POST['hmeres'];
        $moria = isset($_POST['moria']) ? (float)$_POST['moria'] : null;
        $entopiothta = mysqli_real_escape_string($mysqlconnection, $_POST['entopiothta']);
        $synyphrethsh = mysqli_real_escape_string($mysqlconnection, $_POST['synyphrethsh']);

        $query = "UPDATE $av_emp SET name='$name', surname='$surname', patrwnymo='$patrwnymo', klados='$klados',
                  am=$am, afm=$afm, org=$org, eth=$eth, mhnes=$mhnes, hmeres=$hmeres, moria=" . ($moria !== null ? $moria : 'NULL') . ", entopiothta='$entopiothta', synyphrethsh='$synyphrethsh' WHERE id=$id";
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Τα στοιχεία του υπαλλήλου ενημερώθηκαν.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    } elseif (isset($_POST['import_csv'])) {
        if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
            $num = 0;
            $headers = 1;
            $errors = [];
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data = array_map('mb_helper', $data);
                if ($headers) { $headers = 0; continue; }
                
                // Minimum required fields: 7 common + at least 2 more based on av_type
                $min_fields = ($av_type == 2) ? 9 : 10;
                if (count($data) < $min_fields) continue;

                $name = mysqli_real_escape_string($mysqlconnection, $data[0]);
                $surname = mysqli_real_escape_string($mysqlconnection, $data[1]);
                $patrwnymo = mysqli_real_escape_string($mysqlconnection, $data[2]);
                $klados = mysqli_real_escape_string($mysqlconnection, $data[3]);
                $am = (int)$data[4];
                $afm = (int)$data[5];
                $org = (int)$data[6];
                
                // Parse fields based on av_type
                $eth = 0;
                $mhnes = 0;
                $hmeres = 0;
                $moria = null;
                $entopiothta = '';
                $synyphrethsh = '';
                
                if ($av_type == 1) {
                    // av_type 1: eth, mhnes, hmeres, entopiothta, synyphrethsh
                    $eth = (int)$data[7];
                    $mhnes = (int)$data[8];
                    $hmeres = (int)$data[9];
                    $entopiothta = isset($data[10]) ? mysqli_real_escape_string($mysqlconnection, $data[10]) : '';
                    $synyphrethsh = isset($data[11]) ? mysqli_real_escape_string($mysqlconnection, $data[11]) : '';
                } elseif ($av_type == 2) {
                    // av_type 2: moria, entopiothta, synyphrethsh
                    $moria = (float)$data[7];
                    $entopiothta = isset($data[8]) ? mysqli_real_escape_string($mysqlconnection, $data[8]) : '';
                    $synyphrethsh = isset($data[9]) ? mysqli_real_escape_string($mysqlconnection, $data[9]) : '';
                }

                $import = "INSERT INTO $av_emp (name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres, moria, entopiothta, synyphrethsh)
                           VALUES ('$name', '$surname', '$patrwnymo', '$klados', $am, $afm, $org, $eth, $mhnes, $hmeres, " . ($moria !== null ? $moria : 'NULL') . ", '$entopiothta', '$synyphrethsh')";
                if (mysqli_query($mysqlconnection, $import)) {
                    $num++;
                } else {
                    $errors[] = "Σφάλμα στην AM $am: " . mysqli_error($mysqlconnection);
                }
            }
            fclose($handle);
            $message = "<div class='alert alert-success'>Η εισαγωγή ολοκληρώθηκε. Εισήχθησαν $num εγγραφές.</div>";
            if (!empty($errors)) {
                $message .= "<div class='alert alert-warning'>Παρουσιάστηκαν σφάλματα σε " . count($errors) . " εγγραφές.</div>";
            }
        }
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM $av_emp WHERE id=$id";
    if (mysqli_query($mysqlconnection, $query)) {
        $message = "<div class='alert alert-success'>Ο υπάλληλος διαγράφηκε.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
    }
    $action = 'list';
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('head.php'); ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <title>Διαχείριση Εκπαιδευτικών</title>
</head>
<body>
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="admin.php">Διαχείριση</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="admin.php">Επιστροφή</a></li>
                <li class="nav-item active"><a class="nav-link" href="manage_employees.php">Εκπαιδευτικοί</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_schools.php">Σχολεία</a></li>
            </ul>
            <span class="navbar-text">Χρήστης: <?php echo $_SESSION['user']; ?></span>
        </div>
    </nav>

    <div class="mt-3">
        <?php echo $message; ?>

        <?php if ($action == 'list'): ?>
            <div class="d-flex justify-content-between mb-3">
                <h2>Λίστα Εκπαιδευτικών</h2>
                <div>
                    <a href="manage_employees.php?action=add" class="btn btn-primary">Προσθήκη Εκπαιδευτικού</a>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importModal">Εισαγωγή από CSV</button>
                </div>
            </div>

            <table id="empTable" class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Επώνυμο</th>
                        <th>Όνομα</th>
                        <th>Κλάδος</th>
                        <th>AM</th>
                        <th>ΑΦΜ</th>
                        <th>Οργανική</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM $av_emp ORDER BY id DESC";
                    $result = mysqli_query($mysqlconnection, $query);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['surname']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['klados']; ?></td>
                        <td><?php echo $row['am']; ?></td>
                        <td><?php echo $row['afm']; ?></td>
                        <td><?php echo getSchooledc($row['org'], $mysqlconnection); ?></td>
                        <td>
                            <a href="manage_employees.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Επεξεργασία</a>
                            <a href="manage_employees.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Είστε σίγουροι;')">Διαγραφή</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Import Modal -->
            <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form action="manage_employees.php" method="post" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Εισαγωγή από CSV</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p>Επιλέξτε αρχείο CSV (διαχωριστικό ερωτηματικό ';').<br>
                                Σειρά πεδίων: Όνομα;Επώνυμο;Πατρώνυμο;Κλάδος;ΑΜ;ΑΦΜ;Οργανική;<br><?php echo ($av_type == 1) ? 'Έτη;Μήνες;Ημέρες;Εντοπιότητα;Συνυπηρέτηση' : 'Μόρια;Εντοπιότητα;Συνυπηρέτηση'; ?></p>
                                <input type="file" name="csv_file" required class="form-control-file">
                                <input type="hidden" name="import_csv" value="1">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Ακύρωση</button>
                                <button type="submit" class="btn btn-success">Εισαγωγή (Append)</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    $('#empTable').DataTable({
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Greek.json"
                        }
                    });
                });
            </script>

        <?php elseif ($action == 'add' || $action == 'edit'):
            $row = ['name'=>'','surname'=>'','patrwnymo'=>'','klados'=>'','am'=>'','afm'=>'','org'=>'','eth'=>0,'mhnes'=>0,'hmeres'=>0,'moria'=>null,'entopiothta'=>'','synyphrethsh'=>''];
            if ($action == 'edit' && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $result = mysqli_query($mysqlconnection, "SELECT * FROM $av_emp WHERE id=$id");
                $row = mysqli_fetch_assoc($result);
            }
        ?>
            <h2><?php echo $action == 'add' ? 'Προσθήκη' : 'Επεξεργασία'; ?> Εκπαιδευτικού</h2>
            <form action="manage_employees.php" method="post" class="mt-4">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="update" value="1">
                <?php else: ?>
                    <input type="hidden" name="insert" value="1">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Όνομα</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Επώνυμο</label>
                        <input type="text" name="surname" class="form-control" value="<?php echo $row['surname']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Πατρώνυμο</label>
                        <input type="text" name="patrwnymo" class="form-control" value="<?php echo $row['patrwnymo']; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Κλάδος</label>
                        <input type="text" name="klados" class="form-control" value="<?php echo $row['klados']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>ΑΜ</label>
                        <input type="number" name="am" class="form-control" value="<?php echo $row['am']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>ΑΦΜ</label>
                        <input type="number" name="afm" class="form-control" value="<?php echo $row['afm']; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Οργανική (Κωδικός)</label>
                        <input type="number" name="org" class="form-control" value="<?php echo $row['org']; ?>" required>
                    </div>
                </div>

                <?php if ($av_type == 1): ?>
                <h5>Προϋπηρεσία</h5>
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Έτη</label>
                        <input type="number" name="eth" class="form-control" value="<?php echo $row['eth']; ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Μήνες</label>
                        <input type="number" name="mhnes" class="form-control" value="<?php echo $row['mhnes']; ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Ημέρες</label>
                        <input type="number" name="hmeres" class="form-control" value="<?php echo $row['hmeres']; ?>">
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($av_type == 2): ?>
                <h5>Μόρια</h5>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Μόρια</label>
                        <input type="number" step="0.01" name="moria" class="form-control" value="<?php echo $row['moria']; ?>">
                    </div>
                </div>
                <?php endif; ?>

                <h5>Στοιχεία</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Εντοπιότητα</label>
                        <textarea name="entopiothta" class="form-control" rows="3"><?php echo $row['entopiothta']; ?></textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Συνυπηρέτηση</label>
                        <textarea name="synyphrethsh" class="form-control" rows="3"><?php echo $row['synyphrethsh']; ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Αποθήκευση</button>
                <a href="manage_employees.php" class="btn btn-secondary">Ακύρωση</a>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
