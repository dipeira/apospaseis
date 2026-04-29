<?php
if (isset($_SESSION)) session_destroy();
include_once '../config.php';
include_once 'functions.php';

$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['timeout'] = time() + (60 * 60);
            
            header("Location: admin.php");
            exit;
        }
    }
    $error = true;
}
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Είσοδος Διαχειριστή</title>
</head>
<?php include_once('head.php'); ?>
<body>
<div class="container">
    <div class="jumbotron">
        <h1 class="display-4">Είσοδος Διαχειριστή</h1>
        <p class="lead">Σύστημα διαχείρισης αποσπάσεων - βελτιώσεων</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">Λάθος όνομα χρήστη ή κωδικός πρόσβασης.</div>
    <?php endif; ?>

    <form method="post" action="admin_login.php">
        <div class="form-group">
            <label for="username">Όνομα χρήστη</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Κωδικός πρόσβασης</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <input name="action" id="action" value="login" type="hidden">
        <button type="submit" class="btn btn-primary">Είσοδος</button>
    </form>
</div>
<?php require_once('footer.html'); ?>
</body>
</html>
