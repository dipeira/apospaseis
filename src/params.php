<?php
session_start();
require_once "../config.php";
require_once 'functions.php';

// check if logged in
include_once("class.login.php");
$log = new logmein();
if ($_SESSION['loggedin'] == false)
  header("Location: login.php");
// check if admin (only admin can change params)
if (!is_admin()) {
  echo "<h3>ΣΦΑΛΜΑ: Η πρόσβαση επιτρέπεται μόνο στο διαχειριστή...</h3>";
  echo "<a class='btn btn-info' href='admin.php'>Επιστροφή</a>";
  die();
}

$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_set_charset($conn, "utf8");

$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pcount = 0;
  foreach ($_POST as $key => $value) {
    if ($value == 'on')
      $value = 1;
    global $av_params;
    $query = "UPDATE $av_params SET pvalue='$value' where pkey='$key'";
    $updresult = mysqli_query($conn, $query);
    $pcount += mysqli_affected_rows($conn);
  }
  $msg = $pcount == 1 ? "<div class='alert alert-success mt-3 shadow-sm' role='alert'>Μεταβλήθηκε $pcount παράμετρος.</div>" :
    "<div class='alert alert-success mt-3 shadow-sm' role='alert'>Μεταβλήθηκαν $pcount παράμετροι.</div>";
}

$query = "SELECT * from $av_params";
$result = mysqli_query($conn, $query);
global $av_type;
?>
<!DOCTYPE html>
<html>
<?php require_once('head.php'); ?>
<style>
  body {
    background-color: #f8f9fa;
  }

  .card-header {
    background-color: #007bff;
    color: white;
  }
</style>
<script>
  $(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>

<body>
  <div class="container mt-4 mb-5">
    <?= $msg ?>
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Παράμετροι εφαρμογής</h4>
      </div>
      <div class="card-body p-0">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-0">
          <div class="table-responsive">
            <table class='table table-striped table-hover mb-0'>
              <thead class="thead-light">
                <tr>
                  <th class="w-50 pl-4">Περιγραφή</th>
                  <th class="w-50 pr-4">Τιμή</th>
                </tr>
              </thead>
              <tbody>
                <?php
                while ($row = mysqli_fetch_array($result)) {
                  echo "<tr><td class='align-middle pl-4'>" . $row['pdescr'] . "</td>";
                  if ($row['pcheck'] == 1) {
                    echo "<td class='align-middle pr-4'>";
                    echo "<input type='hidden' value='0' name='" . $row['pkey'] . "'>";
                    echo "<div class='custom-control custom-switch'>";
                    echo "<input type='checkbox' class='custom-control-input' id='" . $row['pkey'] . "' name='" . $row['pkey'] . "' ";
                    echo (int) $row['pvalue'] == 1 ? 'checked' : '';
                    echo ">";
                    echo "<label class='custom-control-label' for='" . $row['pkey'] . "'></label>";
                    echo "</div></td>";
                  } else if ($row['pkey'] == 'av_type') {
                    echo '<td class="pr-4"><select class="form-control" name="av_type" id="av_type">';
                    if ($av_type == '1') {
                      echo '<option value="1" selected="selected">Αποσπάσεις</option><option value="2">Βελτιώσεις</option><option value="3">Τοποθετήσεις αναπληρωτών / νεοδιόριστων</option>';
                    } else if ($av_type == '2') {
                      echo '<option value="1">Αποσπάσεις</option><option value="2" selected="selected">Βελτιώσεις</option><option value="3">Τοποθετήσεις αναπληρωτών / νεοδιόριστων</option>';
                    } else if ($av_type == '3') {
                      echo '<option value="1">Αποσπάσεις</option><option value="2">Βελτιώσεις</option><option value="3" selected="selected">Τοποθετήσεις αναπληρωτών / νεοδιόριστων</option>';
                    }
                    echo '</select></td>';
                  } else if ($row['pkey'] == 'av_active_from') {
                    echo "<td class='pr-4'><input type='date' class='form-control' name='" . $row['pkey'] . "' value='" . $row['pvalue'] . "'></td>";
                  } else if ($row['pkey'] == 'av_active_to') {
                    echo "<td class='pr-4'><input type='datetime-local' class='form-control' name='" . $row['pkey'] . "' value='" . $row['pvalue'] . "'></td>";
                  } else {
                    echo "<td class='pr-4'><textarea class='form-control' rows='2' name='" . $row['pkey'] . "'>" . $row['pvalue'] . "</textarea></td>";
                  }
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div>
          <button type="submit" name="submit" class='btn btn-success'><i class="fas fa-save mr-1"></i> Υποβολή</button>
          <a href='admin.php' class='btn btn-secondary ml-2'><i class="fas fa-undo mr-1"></i> Επιστροφή</a>
        </div>
        <?php $counts = count_rows($conn); ?>
        <a href="#" id="srv-info" class="text-info" data-toggle="tooltip" data-placement="left" data-html="true" title="
            Πίνακες σε χρήση:<br>
            Υπάλληλοι: <b><?= $av_emp ?></b> (<?= $counts['emp'] ?>)<br>
            Αιτήσεις: <b><?= $av_ait ?></b><br>
            Σχολεία: <b><?= $av_sch ?></b> (<?= $counts['sch'] ?>)<br>
            Παράμετροι: <b><?= $av_params ?></b>">
          <i class="fas fa-database mr-1"></i> <b><?= $db_name ?></b>
        </a>
      </div>
      </form>
    </div>
  </div>
</body>

</html>