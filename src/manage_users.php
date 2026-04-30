<?php
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
require_once 'functions.php';
session_start();

if ($_SESSION['loggedin'] == false || !is_admin()) {
    header("Location: login.php");
    exit;
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_set_charset($mysqlconnection, "utf8");

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['insert'])) {
        $username = mysqli_real_escape_string($mysqlconnection, $_POST['username']);
        $role = mysqli_real_escape_string($mysqlconnection, $_POST['role']);
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Ο χρήστης προστέθηκε με επιτυχία.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    } elseif (isset($_POST['update'])) {
        $id = (int) $_POST['id'];
        $username = mysqli_real_escape_string($mysqlconnection, $_POST['username']);
        $role = mysqli_real_escape_string($mysqlconnection, $_POST['role']);
        $password = $_POST['password'];
        
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET username='$username', password='$hashed_password', role='$role' WHERE id=$id";
        } else {
            $query = "UPDATE users SET username='$username', role='$role' WHERE id=$id";
        }
        
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Τα στοιχεία του χρήστη ενημερώθηκαν.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    // Prevent deleting the currently logged in user
    $query = "SELECT username FROM users WHERE id=$id";
    $res = mysqli_query($mysqlconnection, $query);
    $row = mysqli_fetch_assoc($res);
    if ($row && $row['username'] === $_SESSION['user']) {
        $message = "<div class='alert alert-danger'>Δεν μπορείτε να διαγράψετε τον εαυτό σας.</div>";
    } else {
        $query = "DELETE FROM users WHERE id=$id";
        if (mysqli_query($mysqlconnection, $query)) {
            $message = "<div class='alert alert-success'>Ο χρήστης διαγράφηκε.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Σφάλμα: " . mysqli_error($mysqlconnection) . "</div>";
        }
    }
    $action = 'list';
}

?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once('head.php'); ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <script type="text/javascript" language="javascript"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript"
        src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <title>Διαχείριση Χρηστών</title>
</head>

<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="admin.php">Διαχείριση</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="admin.php">Επιστροφή</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_employees.php">Εκπαιδευτικοί</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_schools.php">Σχολεία</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_dimos.php">Δήμοι</a></li>
                    <li class="nav-item active"><a class="nav-link" href="manage_users.php">Χρήστες</a></li>
                </ul>
                <span class="navbar-text">Χρήστης: <?php echo $_SESSION['user']; ?></span>
            </div>
        </nav>

        <div class="mt-3">
            <?php echo $message; ?>

            <?php if ($action == 'list'): ?>
                <div class="d-flex justify-content-between mb-3">
                    <h2>Λίστα Χρηστών</h2>
                    <a href="manage_users.php?action=add" class="btn btn-primary">Προσθήκη Χρήστη</a>
                </div>

                <table id="usersTable" class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Όνομα Χρήστη</th>
                            <th>Ρόλος</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users ORDER BY username";
                        $result = mysqli_query($mysqlconnection, $query);
                        while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['role'] == 'admin' ? 'Διαχειριστής' : 'Χρήστης (User)'; ?></td>
                                <td>
                                    <a href="manage_users.php?action=edit&id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-info">Επεξεργασία</a>
                                    <a href="manage_users.php?action=delete&id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-danger" onclick="return confirm('Είστε σίγουροι;')">Διαγραφή</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <script>
                    $(document).ready(function () {
                        $('#usersTable').DataTable({
                            "language": {
                                "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Greek.json"
                            }
                        });
                    });
                </script>

            <?php elseif ($action == 'add' || $action == 'edit'):
                $row = ['username' => '', 'role' => 'user'];
                if ($action == 'edit' && isset($_GET['id'])) {
                    $id = (int) $_GET['id'];
                    $result = mysqli_query($mysqlconnection, "SELECT * FROM users WHERE id=$id");
                    $row = mysqli_fetch_assoc($result);
                }
                ?>
                <h2><?php echo $action == 'add' ? 'Προσθήκη' : 'Επεξεργασία'; ?> Χρήστη</h2>
                <form action="manage_users.php" method="post" class="mt-4">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="update" value="1">
                    <?php else: ?>
                        <input type="hidden" name="insert" value="1">
                    <?php endif; ?>

                    <div class="form-group mb-3 col-md-6">
                        <label>Όνομα Χρήστη (Username)</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $row['username']; ?>" required>
                    </div>

                    <div class="form-group mb-3 col-md-6">
                        <label>Κωδικός Πρόσβασης (Password)</label>
                        <input type="password" name="password" class="form-control" <?php echo $action == 'add' ? 'required' : ''; ?>>
                        <?php if ($action == 'edit'): ?>
                            <small class="form-text text-muted">Αφήστε κενό αν δεν θέλετε να αλλάξετε τον κωδικό.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3 col-md-4">
                        <label>Ρόλος</label>
                        <select name="role" class="form-control">
                            <option value="user" <?php echo $row['role'] == 'user' ? 'selected' : ''; ?>>Χρήστης (User)</option>
                            <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Διαχειριστής (Admin)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Αποθήκευση</button>
                    <a href="manage_users.php" class="btn btn-secondary">Ακύρωση</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
