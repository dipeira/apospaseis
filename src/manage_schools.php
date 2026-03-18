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
        $kwdikos = mysqli_real_escape_string($mysqlconnection, $_POST['kwdikos']);
        $dim = (int)$_POST['dim'];
        $omada = (int)$_POST['omada'];
        $inactive = isset($_POST['inactive']) ? 1 : 0;
        
        $query = "INSERT INTO $av_sch (name, kwdikos, dim, omada, inactive) 
                  VALUES ('$name', '$kwdikos', $dim, $omada, $inactive)";
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Το σχολείο προστέθηκε με επιτυχία.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $name = mysqli_real_escape_string($mysqlconnection, $_POST['name']);
        $kwdikos = mysqli_real_escape_string($mysqlconnection, $_POST['kwdikos']);
        $dim = (int)$_POST['dim'];
        $omada = (int)$_POST['omada'];
        $inactive = isset($_POST['inactive']) ? 1 : 0;

        $query = "UPDATE $av_sch SET name='$name', kwdikos='$kwdikos', dim=$dim, omada=$omada, inactive=$inactive WHERE id=$id";
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Τα στοιχεία του σχολείου ενημερώθηκαν.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM $av_sch WHERE id=$id";
    if (mysqli_query($mysqlconnection, $query)) {
        $message = "<div class='alert alert-success'>Το σχολείο διαγράφηκε.</div>";
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
    <title>Διαχείριση Σχολείων</title>
</head>
<body>
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="admin.php">Διαχείριση</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="admin.php">Αρχική</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_employees.php">Εκπαιδευτικοί</a></li>
                <li class="nav-item active"><a class="nav-link" href="manage_schools.php">Σχολεία</a></li>
            </ul>
            <span class="navbar-text">Χρήστης: <?php echo $_SESSION['user']; ?></span>
        </div>
    </nav>

    <div class="mt-3">
        <?php echo $message; ?>

        <?php if ($action == 'list'): ?>
            <div class="d-flex justify-content-between mb-3">
                <h2>Λίστα Σχολείων</h2>
                <a href="manage_schools.php?action=add" class="btn btn-primary">Προσθήκη Σχολείου</a>
            </div>

            <table id="schTable" class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Όνομα</th>
                        <th>Κωδικός (ΥΠΑΙΘ)</th>
                        <th>Τύπος</th>
                        <th>Ομάδα</th>
                        <th>Ενεργό</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM $av_sch ORDER BY name";
                    $result = mysqli_query($mysqlconnection, $query);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['kwdikos']; ?></td>
                        <td><?php echo $row['dim'] == 2 ? 'Δημοτικό' : ($row['dim'] == 1 ? 'Νηπιαγωγείο' : 'Άλλο'); ?></td>
                        <td><?php echo $row['omada']; ?></td>
                        <td><?php echo $row['inactive'] ? '<span class="text-danger">Όχι</span>' : '<span class="text-success">Ναι</span>'; ?></td>
                        <td>
                            <a href="manage_schools.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Επεξεργασία</a>
                            <a href="manage_schools.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Είστε σίγουροι;')">Διαγραφή</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <script>
                $(document).ready(function() {
                    $('#schTable').DataTable({
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Greek.json"
                        }
                    });
                });
            </script>

        <?php elseif ($action == 'add' || $action == 'edit'): 
            $row = ['name'=>'','kwdikos'=>'','dim'=>2,'omada'=>0,'inactive'=>0];
            if ($action == 'edit' && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $result = mysqli_query($mysqlconnection, "SELECT * FROM $av_sch WHERE id=$id");
                $row = mysqli_fetch_assoc($result);
            }
        ?>
            <h2><?php echo $action == 'add' ? 'Προσθήκη' : 'Επεξεργασία'; ?> Σχολείου</h2>
            <form action="manage_schools.php" method="post" class="mt-4">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="update" value="1">
                <?php else: ?>
                    <input type="hidden" name="insert" value="1">
                <?php endif; ?>

                <div class="form-group mb-3 col-md-6">
                    <label>Όνομα Σχολείου</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                </div>

                <div class="form-group mb-3 col-md-4">
                    <label>Κωδικός ΥΠΑΙΘ (7ψήφιος)</label>
                    <input type="text" name="kwdikos" class="form-control" value="<?php echo $row['kwdikos']; ?>" required>
                </div>

                <div class="form-group mb-3 col-md-4">
                    <label>Τύπος Σχολείου</label>
                    <select name="dim" class="form-control">
                        <option value="2" <?php if($row['dim']==2) echo 'selected'; ?>>Δημοτικό</option>
                        <option value="1" <?php if($row['dim']==1) echo 'selected'; ?>>Νηπιαγωγείο</option>
                        <option value="0" <?php if($row['dim']==0) echo 'selected'; ?>>Άλλο</option>
                    </select>
                </div>

                <div class="form-group mb-3 col-md-2">
                    <label>Ομάδα</label>
                    <input type="number" name="omada" class="form-control" value="<?php echo $row['omada']; ?>">
                </div>

                <div class="form-group mb-3 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="inactive" id="inactive" <?php if($row['inactive']) echo 'checked'; ?>>
                        <label class="form-check-label" for="inactive">
                            Ανενεργό (να μην εμφανίζεται στις επιλογές)
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Αποθήκευση</button>
                <a href="manage_schools.php" class="btn btn-secondary">Ακύρωση</a>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
