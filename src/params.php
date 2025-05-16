<?php
  session_start();
  require_once "../config.php";
  require_once 'functions.php';
  require_once 'head.php';
  
  // check if logged in
  include_once("class.login.php");
  $log = new logmein();
  if($_SESSION['loggedin'] == false)
    header("Location: login.php");
  // check if admin (only admin can change params)
  if (!is_admin()){
    echo "<h3>ΣΦΑΛΜΑ: Η πρόσβαση επιτρέπεται μόνο στο διαχειριστή...</h3>";
    echo "<a class='btn btn-info' href='admin.php'>Επιστροφή</a>";
    die();
  }

?>
<style>
  .param-input {
    width: 100%
  }
</style>
<script>
  $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
<html>
  <?php require_once('head.php'); ?>
  <body>
  <div class="container">
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $pcount = 0;
      foreach ($_POST as $key => $value) {
        if ($value == 'on')
          $value = 1;
        //echo $key . ' / ' . $value . "<br>";
        global $av_params;
        $query = "UPDATE $av_params SET pvalue='$value' where pkey='$key'";
        $updresult = mysqli_query($conn, $query);
        $pcount += mysqli_affected_rows($conn);
      }
      echo $pcount == 1 ? "<div class='alert alert-success' role='alert'>Μεταβλήθηκε $pcount παράμετρος...</div>" :
      "<div class='alert alert-success' role='alert'>Μεταβλήθηκαν $pcount παράμετροι...</div>";
    }
    $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    mysqli_set_charset($conn,"utf8");
    $query = "SELECT * from $av_params";
    $result = mysqli_query($conn, $query);
    global $av_type;
    ?>
    <h2>Παράμετροι εφαρμογής</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    <table class='table table-striped table-hover table-sm' border='1'>
      <thead>
        <th>Περιγραφή</th>
        <th>Τιμή</th>
      </thead>
      <tbody>
      <?php
        while ($row = mysqli_fetch_array($result)) {
          echo "<tr><td>".$row['pdescr']."</td>";
          if ($row['pcheck'] == 1){
            echo "<td>";
            echo "<input type='hidden' value='0' name='".$row['pkey']."'>";
            echo "<input type='checkbox' ";
            echo (int)$row['pvalue'] == 1  ? 'checked' : '';
            echo " name='".$row['pkey']."'/></td>";
          } else if ($row['pkey'] == 'av_type') {
            echo '<td><select name="av_type" id="av_type">';
            if ($av_type == '1') { 
              echo '<option value="1" selected="selected">Αποσπάσεις</option><option value="2">Βελτιώσεις</option><option value="3">Τοποθετήσεις αναπληρωτών / νεοδιόριστων</option>';
            } else if ($av_type == '2') {
              echo '<option value="1">Αποσπάσεις</option><option value="2" selected="selected">Βελτιώσεις</option><option value="3">Τοποθετήσεις αναπληρωτών / νεοδιόριστων</option>';
            } else if ($av_type == '3') {
              echo '<option value="1">Αποσπάσεις</option><option value="2">Βελτιώσεις</option><option value="3" selected="selected">Τοποθετήσεις αναπληρωτών / νεοδιόριστων</option>';
            }
            echo '</select></td>';
          } else
            echo "<td><textarea class='param-input' rows='2' cols='40' name='".$row['pkey']."'>".$row['pvalue']."</textarea></td>";
          echo "</tr>";
        }
      ?>
      </tbody>
    </table>
    <a href="#" id="srv-info" data-toggle="tooltip" data-placement="right" data-html="true" title="
      Πίνακες σε χρήση:
      Υπάλληλοι: <b><?= $av_emp ?></b><br>
      Αιτήσεις: <b><?= $av_ait ?></b><br>
      Σχολεία: <b><?= $av_sch ?></b><br>
      Παράμετροι: <b><?= $av_params ?></b>">
      Βάση δεδομένων σε χρήση: <b><?= $db_name ?></b>&nbsp;<i class="fas fa-info-circle"></i>
    </a>
    <br>
    <input type="submit" name="submit" class='btn btn-success' value="Υποβολή">
    </form>
    <input type="button" class='btn btn-info' onclick="location.href='admin.php';" value="Επιστροφή" /> 
    <br><br>
    </div>
  </body>
</html>